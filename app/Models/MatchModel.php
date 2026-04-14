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
              (league_id, home_team_id, away_team_id, stadium_id, referee_id, match_date, match_time, status, round_number, created_at, updated_at)
            VALUES
              (:league_id, :home_team_id, :away_team_id, :stadium_id, :referee_id, :match_date, :match_time, :status, :round_number, NOW(), NOW())
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
        ]);
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
}
