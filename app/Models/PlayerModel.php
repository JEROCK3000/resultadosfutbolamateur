<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class PlayerModel extends Model
{
    protected string $table = 'players';

    /** Roster completo de un equipo en una liga */
    public function getByTeam(int $teamId, int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, tp.number, tp.status AS member_status, tp.joined_at, tp.left_at, tp.id AS membership_id
            FROM team_players tp
            JOIN players p ON tp.player_id = p.id
            WHERE tp.team_id = :team_id AND tp.league_id = :league_id
            ORDER BY tp.status ASC, tp.number ASC, p.name ASC
        ");
        $stmt->execute([':team_id' => $teamId, ':league_id' => $leagueId]);
        return $stmt->fetchAll();
    }

    /** Jugador por ID con sus membresías */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   tp.team_id, tp.league_id, tp.number, tp.status AS member_status,
                   tp.joined_at, tp.left_at, tp.id AS membership_id,
                   t.name AS team_name, l.name AS league_name
            FROM players p
            LEFT JOIN team_players tp ON tp.player_id = p.id
            LEFT JOIN teams t ON tp.team_id = t.id
            LEFT JOIN leagues l ON tp.league_id = l.id
            WHERE p.id = :id
            ORDER BY tp.joined_at DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Buscar por cédula (para detección de duplicados) */
    public function getByCedula(string $cedula): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE cedula = :cedula LIMIT 1");
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch();
    }

    /** Crear jugador en catálogo */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO players (cedula, name, birth_date, position, photo)
            VALUES (:cedula, :name, :birth_date, :position, :photo)
        ");
        return $stmt->execute([
            ':cedula'     => trim($data['cedula']),
            ':name'       => trim($data['name']),
            ':birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
            ':position'   => $data['position'] ?? 'otro',
            ':photo'      => $data['photo'] ?? null,
        ]);
    }

    /** Actualizar datos del jugador */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE players SET cedula=:cedula, name=:name, birth_date=:birth_date,
                               position=:position, updated_at=NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':cedula'     => trim($data['cedula']),
            ':name'       => trim($data['name']),
            ':birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
            ':position'   => $data['position'] ?? 'otro',
            ':id'         => $id,
        ]);
    }

    /** Inscribir jugador en un equipo/liga */
    public function addToTeam(int $playerId, int $teamId, int $leagueId, ?int $number, string $joinedAt = ''): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO team_players (player_id, team_id, league_id, number, joined_at)
            VALUES (:player_id, :team_id, :league_id, :number, :joined_at)
            ON DUPLICATE KEY UPDATE number=VALUES(number), left_at=NULL, status='active', joined_at=VALUES(joined_at)
        ");
        return $stmt->execute([
            ':player_id' => $playerId,
            ':team_id'   => $teamId,
            ':league_id' => $leagueId,
            ':number'    => $number,
            ':joined_at' => $joinedAt ?: date('Y-m-d'),
        ]);
    }

    /** Actualizar número y estado de membresía */
    public function updateMembership(int $membershipId, ?int $number, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE team_players SET number=:number, status=:status WHERE id=:id
        ");
        return $stmt->execute([':number' => $number, ':status' => $status, ':id' => $membershipId]);
    }

    /** Dar de baja de un equipo (registra left_at) */
    public function removeFromTeam(int $playerId, int $teamId, int $leagueId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE team_players SET left_at=CURDATE(), status='inactive'
            WHERE player_id=:pid AND team_id=:tid AND league_id=:lid
        ");
        return $stmt->execute([':pid' => $playerId, ':tid' => $teamId, ':lid' => $leagueId]);
    }

    /** Verificar si el jugador ya pertenece al equipo en esa liga */
    public function isInTeam(int $playerId, int $teamId, int $leagueId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM team_players
            WHERE player_id=:pid AND team_id=:tid AND league_id=:lid AND left_at IS NULL
        ");
        $stmt->execute([':pid' => $playerId, ':tid' => $teamId, ':lid' => $leagueId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Eliminar jugador del catálogo (solo si no tiene eventos) */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM match_events WHERE player_id=:id");
        $stmt->execute([':id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        $this->db->prepare("DELETE FROM players WHERE id=:id")->execute([':id' => $id]);
        return true;
    }

    public function getLastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    /**
     * Importación masiva desde array de filas validadas.
     * Retorna ['created'=>N, 'updated'=>N, 'errors'=>[...]]
     */
    public function bulkImport(array $rows, int $teamId, int $leagueId): array
    {
        $created = 0; $updated = 0; $errors = [];

        foreach ($rows as $i => $row) {
            $lineNum   = $i + 2; // fila 1 = encabezado
            $cedula    = trim($row[0] ?? '');
            $name      = trim($row[1] ?? '');
            $birthRaw  = trim($row[2] ?? '');
            $position  = strtolower(trim($row[3] ?? 'otro'));
            $number    = isset($row[4]) && $row[4] !== '' ? (int)$row[4] : null;

            if (!$cedula || !$name) {
                $errors[] = "Fila {$lineNum}: Cédula y Nombre son obligatorios.";
                continue;
            }

            $validPositions = ['portero','defensa','mediocampista','delantero','otro'];
            if (!in_array($position, $validPositions)) $position = 'otro';

            // Convertir fecha DD/MM/YYYY → YYYY-MM-DD
            $birthDate = null;
            if ($birthRaw) {
                $parts = explode('/', $birthRaw);
                if (count($parts) === 3) {
                    $birthDate = sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
                }
            }

            // Crear o reutilizar jugador por cédula
            $existing = $this->getByCedula($cedula);
            if ($existing) {
                $playerId = (int)$existing['id'];
                $updated++;
            } else {
                $this->create(['cedula'=>$cedula,'name'=>$name,'birth_date'=>$birthDate,'position'=>$position]);
                $playerId = $this->getLastInsertId();
                $created++;
            }

            // Inscribir en equipo/liga
            $this->addToTeam($playerId, $teamId, $leagueId, $number);
        }

        return compact('created', 'updated', 'errors');
    }
}
