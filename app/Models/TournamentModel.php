<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * TournamentModel.php — Modelo de Fases Finales / Torneos
 */
class TournamentModel extends Model
{
    protected string $table = 'tournaments';

    public function getAll(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $stmt = $this->db->query("
            SELECT t.*, l.name AS league_name, l.season
            FROM tournaments t
            LEFT JOIN leagues l ON t.league_id = l.id
            ORDER BY {$orderBy} {$direction}
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT t.*, l.name AS league_name
            FROM tournaments t
            LEFT JOIN leagues l ON t.league_id = l.id
            WHERE t.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tournaments (league_id, name, type, status, created_at, updated_at)
            VALUES (:league_id, :name, :type, 'active', NOW(), NOW())
        ");
        $stmt->execute([
            ':league_id' => $data['league_id'],
            ':name'      => $data['name'],
            ':type'      => $data['type'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE tournaments SET name=:name, status=:status, updated_at=NOW() WHERE id=:id");
        return $stmt->execute([':name'=>$data['name'],':status'=>$data['status'],':id'=>$id]);
    }

    /** Crea una ronda del torneo */
    public function createRound(int $tournamentId, string $roundName, int $order): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tournament_rounds (tournament_id, round_name, round_order, created_at)
            VALUES (:tournament_id, :round_name, :round_order, NOW())
        ");
        $stmt->execute([
            ':tournament_id' => $tournamentId,
            ':round_name'    => $roundName,
            ':round_order'   => $order,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Crea un cruce en una ronda */
    public function createTournamentMatch(int $roundId, ?int $homeId, ?int $awayId, ?int $posHome = null, ?int $posAway = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tournament_matches
              (round_id, home_team_id, away_team_id, position_home, position_away, created_at)
            VALUES (:round_id, :home_team_id, :away_team_id, :pos_home, :pos_away, NOW())
        ");
        $stmt->execute([
            ':round_id'     => $roundId,
            ':home_team_id' => $homeId,
            ':away_team_id' => $awayId,
            ':pos_home'     => $posHome,
            ':pos_away'     => $posAway,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Obtiene todas las rondas con sus cruces para un torneo */
    public function getBracket(int $tournamentId): array
    {
        $stmt = $this->db->prepare("
            SELECT tr.*, tr.round_name, tr.round_order
            FROM tournament_rounds tr
            WHERE tr.tournament_id = :tid
            ORDER BY tr.round_order ASC
        ");
        $stmt->execute([':tid' => $tournamentId]);
        $rounds = $stmt->fetchAll();

        foreach ($rounds as &$round) {
            $stmt2 = $this->db->prepare("
                SELECT tm.*,
                       ht.name AS home_team, ht.logo AS home_logo,
                       at.name AS away_team, at.logo AS away_logo
                FROM tournament_matches tm
                LEFT JOIN teams ht ON tm.home_team_id = ht.id
                LEFT JOIN teams at ON tm.away_team_id = at.id
                WHERE tm.round_id = :rid
                ORDER BY tm.id ASC
            ");
            $stmt2->execute([':rid' => $round['id']]);
            $round['matches'] = $stmt2->fetchAll();
        }
        unset($round);

        return $rounds;
    }

    /** Guarda el marcador de un cruce directo */
    public function saveMatchScore(int $tournamentMatchId, int $homeGoals, int $awayGoals): bool
    {
        $stmt = $this->db->prepare("UPDATE tournament_matches SET home_goals=:hg, away_goals=:ag WHERE id=:id");
        return $stmt->execute([':hg' => $homeGoals, ':ag' => $awayGoals, ':id' => $tournamentMatchId]);
    }

    /** Promueve un equipo a un cruce futuro */
    public function updateMatchTeam(int $tournamentMatchId, string $side, int $teamId): bool
    {
        $column = $side === 'home' ? 'home_team_id' : 'away_team_id';
        $stmt = $this->db->prepare("UPDATE tournament_matches SET {$column}=:tid WHERE id=:id");
        return $stmt->execute([':tid' => $teamId, ':id' => $tournamentMatchId]);
    }

    public function getTournamentMatchInfo(int $tournamentMatchId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT tm.*, tr.tournament_id, tr.round_order, tr.round_name
            FROM tournament_matches tm
            INNER JOIN tournament_rounds tr ON tm.round_id = tr.id
            WHERE tm.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $tournamentMatchId]);
        return $stmt->fetch();
    }
}
