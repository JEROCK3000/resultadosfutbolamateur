<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * MatchModel.php — Modelo de Encuentros
 * Incluye validaciones de conflicto de estadio y equipo por día.
 */
class MatchModel extends Model
{
    protected string $table = 'matches';

    /** Obtiene todos los encuentros con joins a ligas, equipos y estadio */
    public function getAll(string $orderBy = 'm.match_date', string $direction = 'DESC'): array
    {
        $stmt = $this->db->query("
            SELECT m.*,
                   l.name  AS league,
                   ht.name AS home_team, ht.logo AS home_logo,
                   at.name AS away_team, at.logo AS away_logo,
                   s.name  AS stadium,
                   ref.name AS referee_name,
                   r.home_goals, r.away_goals,
                   r.home_yellow_cards, r.away_yellow_cards, 
                   r.home_red_cards, r.away_red_cards
            FROM matches m
            LEFT JOIN leagues l  ON m.league_id      = l.id
            LEFT JOIN teams   ht ON m.home_team_id   = ht.id
            LEFT JOIN teams   at ON m.away_team_id   = at.id
            LEFT JOIN stadiums s ON m.stadium_id     = s.id
            LEFT JOIN referees ref ON m.referee_id   = ref.id
            LEFT JOIN match_results r ON r.match_id  = m.id
            ORDER BY {$orderBy} {$direction}
        ");
        return $stmt->fetchAll();
    }

    /** Obtiene un encuentro por ID con todos los joins */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   l.name  AS league,
                   ht.name AS home_team, ht.logo AS home_logo,
                   at.name AS away_team, at.logo AS away_logo,
                   s.name  AS stadium,
                   s.city  AS stadium_city,
                   ref.name AS referee_name,
                   r.home_goals, r.away_goals, r.status AS result_status,
                   r.home_yellow_cards, r.away_yellow_cards, 
                   r.home_red_cards, r.away_red_cards
            FROM matches m
            LEFT JOIN leagues l  ON m.league_id      = l.id
            LEFT JOIN teams   ht ON m.home_team_id   = ht.id
            LEFT JOIN teams   at ON m.away_team_id   = at.id
            LEFT JOIN stadiums s ON m.stadium_id     = s.id
            LEFT JOIN referees ref ON m.referee_id   = ref.id
            LEFT JOIN match_results r ON r.match_id  = m.id
            WHERE m.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Próximos encuentros (status = scheduled) */
    public function getUpcoming(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   l.name  AS league,
                   ht.name AS home_team, ht.logo AS home_logo,
                   at.name AS away_team, at.logo AS away_logo,
                   s.name  AS stadium,
                   ref.name AS referee_name
            FROM matches m
            LEFT JOIN leagues l  ON m.league_id    = l.id
            LEFT JOIN teams   ht ON m.home_team_id = ht.id
            LEFT JOIN teams   at ON m.away_team_id = at.id
            LEFT JOIN stadiums s ON m.stadium_id   = s.id
            LEFT JOIN referees ref ON m.referee_id   = ref.id
            WHERE m.status = 'scheduled' AND m.match_date >= CURDATE()
            ORDER BY m.match_date ASC, m.match_time ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Últimos encuentros finalizados */
    public function getFinished(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   l.name  AS league,
                   ht.name AS home_team, ht.logo AS home_logo,
                   at.name AS away_team, at.logo AS away_logo,
                   s.name  AS stadium,
                   ref.name AS referee_name,
                   r.home_goals, r.away_goals
            FROM matches m
            LEFT JOIN leagues l  ON m.league_id    = l.id
            LEFT JOIN teams   ht ON m.home_team_id = ht.id
            LEFT JOIN teams   at ON m.away_team_id = at.id
            LEFT JOIN stadiums s ON m.stadium_id   = s.id
            LEFT JOIN referees ref ON m.referee_id   = ref.id
            LEFT JOIN match_results r ON r.match_id = m.id
            WHERE m.status = 'finished'
            ORDER BY m.match_date DESC, m.match_time DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Encuentros por liga */
    public function getByLeague(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   ht.name AS home_team, ht.logo AS home_logo,
                   at.name AS away_team, at.logo AS away_logo,
                   s.name  AS stadium,
                   ref.name AS referee_name,
                   r.home_goals, r.away_goals,
                   r.home_yellow_cards, r.away_yellow_cards, 
                   r.home_red_cards, r.away_red_cards
            FROM matches m
            LEFT JOIN teams   ht ON m.home_team_id = ht.id
            LEFT JOIN teams   at ON m.away_team_id = at.id
            LEFT JOIN stadiums s ON m.stadium_id   = s.id
            LEFT JOIN referees ref ON m.referee_id = ref.id
            LEFT JOIN match_results r ON r.match_id = m.id
            WHERE m.league_id = :league_id
            ORDER BY m.round_number ASC, m.match_date ASC, m.match_time ASC
        ");
        $stmt->execute([':league_id' => $leagueId]);
        return $stmt->fetchAll();
    }

    /** Resultados por liga con filtro opcional de fecha */
    public function getResultsByLeague(int $leagueId, ?string $date = null): array
    {
        $sql = "
            SELECT m.*,
                   ht.name  AS home_team, ht.logo AS home_logo,
                   at.name  AS away_team, at.logo AS away_logo,
                   s.name   AS stadium,
                   ref.name AS referee_name,
                   r.home_goals, r.away_goals,
                   r.home_yellow_cards, r.away_yellow_cards,
                   r.home_red_cards, r.away_red_cards
            FROM matches m
            INNER JOIN match_results r ON r.match_id = m.id
            LEFT JOIN teams   ht ON m.home_team_id = ht.id
            LEFT JOIN teams   at ON m.away_team_id = at.id
            LEFT JOIN stadiums s ON m.stadium_id   = s.id
            LEFT JOIN referees ref ON m.referee_id = ref.id
            WHERE m.league_id = :league_id AND m.status = 'finished'";
        
        $params = [':league_id' => $leagueId];
        
        if ($date) {
            $sql .= " AND m.match_date = :date";
            $params[':date'] = $date;
        }
        
        $sql .= " ORDER BY m.match_date DESC, m.match_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Obtiene fechas únicas que tienen resultados en una liga */
    public function getAvailableDates(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT match_date 
            FROM matches 
            WHERE league_id = :league_id AND status = 'finished'
            ORDER BY match_date DESC
        ");
        $stmt->execute([':league_id' => $leagueId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Valida conflicto de estadio: mismo estadio, misma fecha y misma hora.
     */
    public function hasStadiumConflict(int $stadiumId, string $date, string $time, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM matches
            WHERE stadium_id = :stadium_id
              AND match_date = :date
              AND match_time = :time
              AND id != :exclude_id
        ");
        $stmt->execute([
            ':stadium_id' => $stadiumId,
            ':date'       => $date,
            ':time'       => $time,
            ':exclude_id' => $excludeId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Valida conflicto de equipo: mismo equipo jugando el mismo día (como local o visitante).
     */
    public function hasTeamConflict(int $teamId, string $date, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM matches
            WHERE (home_team_id = :team_id OR away_team_id = :team_id2)
              AND match_date = :date
              AND id != :exclude_id
        ");
        $stmt->execute([
            ':team_id'    => $teamId,
            ':team_id2'   => $teamId,
            ':date'       => $date,
            ':exclude_id' => $excludeId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO matches
              (league_id, home_team_id, away_team_id, stadium_id, referee_id, match_date, match_time, status, round_number, vuelta, created_at, updated_at)
            VALUES
              (:league_id, :home_team_id, :away_team_id, :stadium_id, :referee_id, :match_date, :match_time, :status, :round_number, :vuelta, NOW(), NOW())
        ");
        return $stmt->execute([
            ':league_id'    => $data['league_id'],
            ':home_team_id' => $data['home_team_id'],
            ':away_team_id' => $data['away_team_id'],
            ':stadium_id'   => !empty($data['stadium_id']) ? $data['stadium_id'] : null,
            ':referee_id'   => !empty($data['referee_id']) ? $data['referee_id'] : null,
            ':match_date'   => !empty($data['match_date']) ? $data['match_date'] : null,
            ':match_time'   => !empty($data['match_time']) ? $data['match_time'] : null,
            ':status'       => !empty($data['status']) ? $data['status'] : 'unscheduled',
            ':round_number' => !empty($data['round_number']) ? $data['round_number'] : null,
            ':vuelta'       => !empty($data['vuelta']) ? (int)$data['vuelta'] : 1,
        ]);
    }

    /** Cuenta cuántos partidos tiene una liga (para saber si ya tiene fixture generado). */
    public function countByLeague(int $leagueId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM matches WHERE league_id = :lid");
        $stmt->execute([':lid' => $leagueId]);
        return (int)$stmt->fetchColumn();
    }

    /** Elimina todos los partidos de una liga. Retorna la cantidad eliminada. */
    public function deleteAllByLeague(int $leagueId): int
    {
        $stmt = $this->db->prepare("DELETE FROM matches WHERE league_id = :lid");
        $stmt->execute([':lid' => $leagueId]);
        return $stmt->rowCount();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE matches
            SET league_id    = :league_id,
                home_team_id = :home_team_id,
                away_team_id = :away_team_id,
                stadium_id   = :stadium_id,
                referee_id   = :referee_id,
                match_date   = :match_date,
                match_time   = :match_time,
                round_number = :round_number,
                status       = :status,
                updated_at   = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':league_id'    => $data['league_id'],
            ':home_team_id' => $data['home_team_id'],
            ':away_team_id' => $data['away_team_id'],
            ':stadium_id'   => !empty($data['stadium_id']) ? $data['stadium_id'] : null,
            ':referee_id'   => !empty($data['referee_id']) ? $data['referee_id'] : null,
            ':match_date'   => !empty($data['match_date']) ? $data['match_date'] : null,
            ':match_time'   => !empty($data['match_time']) ? $data['match_time'] : null,
            ':status'       => !empty($data['status']) ? $data['status'] : 'unscheduled',
            ':round_number' => !empty($data['round_number']) ? $data['round_number'] : null,
            ':id'           => $id,
        ]);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Historial de slots (día_semana + hora) por equipo en una liga.
     * Incluye partidos programados y finalizados para tener memoria completa.
     * Retorna: [team_id => ['6_10:00' => 2, '0_14:00' => 1, ...], ...]
     */
    public function getTeamSlotHistory(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT home_team_id AS team_id, match_date, match_time FROM matches
            WHERE league_id = :lid
              AND status IN ('scheduled', 'finished')
              AND match_date IS NOT NULL AND match_time IS NOT NULL
            UNION ALL
            SELECT away_team_id, match_date, match_time FROM matches
            WHERE league_id = :lid2
              AND status IN ('scheduled', 'finished')
              AND match_date IS NOT NULL AND match_time IS NOT NULL
        ");
        $stmt->execute([':lid' => $leagueId, ':lid2' => $leagueId]);

        $history = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $dow  = (int)(new \DateTime($row['match_date']))->format('w');
            $time = substr($row['match_time'], 0, 5);
            $key  = $dow . '_' . $time;
            $tid  = (int)$row['team_id'];
            $history[$tid][$key] = ($history[$tid][$key] ?? 0) + 1;
        }
        return $history;
    }

    /**
     * Fechas ya ocupadas por equipo (para evitar doble partido el mismo día).
     * Retorna: [team_id => ['2026-01-10' => true, ...], ...]
     */
    public function getTeamOccupiedDates(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT home_team_id AS team_id, match_date FROM matches
            WHERE league_id = :lid
              AND status IN ('scheduled', 'finished')
              AND match_date IS NOT NULL
            UNION ALL
            SELECT away_team_id, match_date FROM matches
            WHERE league_id = :lid2
              AND status IN ('scheduled', 'finished')
              AND match_date IS NOT NULL
        ");
        $stmt->execute([':lid' => $leagueId, ':lid2' => $leagueId]);

        $occupied = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $occupied[(int)$row['team_id']][$row['match_date']] = true;
        }
        return $occupied;
    }

    /**
     * Slots de estadio ya reservados globalmente — claves "{stadium_id}_{date}_{time}".
     * La clave uq_stadium_slot es global (no por liga), por lo que se deben excluir
     * todos los slots ocupados en cualquier liga.
     */
    public function getOccupiedStadiumSlots(int $leagueId): array
    {
        $stmt = $this->db->query("
            SELECT stadium_id, match_date, match_time FROM matches
            WHERE status IN ('scheduled', 'finished')
              AND stadium_id IS NOT NULL AND match_date IS NOT NULL
        ");

        $slots = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $time = substr($row['match_time'], 0, 5);
            $key  = $row['stadium_id'] . '_' . $row['match_date'] . '_' . $time;
            $slots[$key] = true;
        }
        return $slots;
    }

    /**
     * Historial de slots (día_semana + hora) por árbitro en una liga.
     * Retorna: [referee_id => ['6_10:00' => 3, '0_14:00' => 1, ...], ...]
     */
    public function getRefereeSlotHistory(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT referee_id, match_date, match_time FROM matches
            WHERE league_id = :lid
              AND status IN ('scheduled', 'finished')
              AND referee_id IS NOT NULL
              AND match_date IS NOT NULL AND match_time IS NOT NULL
        ");
        $stmt->execute([':lid' => $leagueId]);

        $history = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $dow  = (int)(new \DateTime($row['match_date']))->format('w');
            $time = substr($row['match_time'], 0, 5);
            $key  = $dow . '_' . $time;
            $rid  = (int)$row['referee_id'];
            $history[$rid][$key] = ($history[$rid][$key] ?? 0) + 1;
        }
        return $history;
    }
}
