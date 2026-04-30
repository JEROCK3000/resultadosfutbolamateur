<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class SanctionModel extends Model
{
    protected string $table = 'player_sanctions';

    public function getByLeague(int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT ps.*, p.name AS player_name, p.cedula,
                   l.name AS league_name,
                   t.name AS team_name
            FROM player_sanctions ps
            JOIN players p ON ps.player_id = p.id
            JOIN leagues l ON ps.league_id = l.id
            LEFT JOIN team_players tp ON tp.player_id = ps.player_id AND tp.league_id = ps.league_id
            LEFT JOIN teams t ON tp.team_id = t.id
            WHERE ps.league_id = :league_id
            ORDER BY ps.created_at DESC
        ");
        $stmt->execute([':league_id' => $leagueId]);
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT ps.*, p.name AS player_name, p.cedula,
                   l.name AS league_name,
                   t.name AS team_name
            FROM player_sanctions ps
            JOIN players p ON ps.player_id = p.id
            JOIN leagues l ON ps.league_id = l.id
            LEFT JOIN team_players tp ON tp.player_id = ps.player_id AND tp.league_id = ps.league_id
            LEFT JOIN teams t ON tp.team_id = t.id
            ORDER BY ps.active DESC, ps.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /** Cuenta tarjetas amarillas del jugador en la liga (para acumulación). */
    public function countYellowCards(int $playerId, int $leagueId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM match_events me
            JOIN matches m ON me.match_id = m.id
            WHERE me.player_id = :pid
              AND m.league_id  = :lid
              AND me.event_type = 'yellow_card'
        ");
        $stmt->execute([':pid' => $playerId, ':lid' => $leagueId]);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO player_sanctions
              (player_id, league_id, match_id, type, reason, matches_qty, fine_usd, active)
            VALUES
              (:player_id, :league_id, :match_id, :type, :reason, :matches_qty, :fine_usd, 1)
        ");
        return $stmt->execute([
            ':player_id'   => $data['player_id'],
            ':league_id'   => $data['league_id'],
            ':match_id'    => $data['match_id']    ?? null,
            ':type'        => $data['type']         ?? 'auto',
            ':reason'      => $data['reason'],
            ':matches_qty' => $data['matches_qty']  ?? 0,
            ':fine_usd'    => $data['fine_usd']     ?? 0.00,
        ]);
    }

    public function markFinePaid(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE player_sanctions SET fine_paid=1, updated_at=NOW() WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }

    public function serveMatch(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE player_sanctions
            SET matches_served = matches_served + 1,
                active = IF(matches_served + 1 >= matches_qty, 0, 1),
                updated_at = NOW()
            WHERE id=:id AND active=1 AND matches_qty > 0
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function deactivate(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE player_sanctions SET active=0, updated_at=NOW() WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }

    /** Verifica si un jugador tiene suspensión activa en la liga. */
    public function isSuspended(int $playerId, int $leagueId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM player_sanctions
            WHERE player_id=:pid AND league_id=:lid AND active=1 AND matches_qty > matches_served
        ");
        $stmt->execute([':pid' => $playerId, ':lid' => $leagueId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Stats de un jugador en una liga. */
    public function getPlayerStats(int $playerId, int $leagueId): array
    {
        $stmt = $this->db->prepare("
            SELECT
              SUM(CASE WHEN me.event_type='goal'        THEN 1 ELSE 0 END) AS goals,
              SUM(CASE WHEN me.event_type='yellow_card' THEN 1 ELSE 0 END) AS yellow_cards,
              SUM(CASE WHEN me.event_type='red_card'    THEN 1 ELSE 0 END) AS red_cards
            FROM match_events me
            JOIN matches m ON me.match_id = m.id
            WHERE me.player_id = :pid AND m.league_id = :lid
        ");
        $stmt->execute([':pid' => $playerId, ':lid' => $leagueId]);
        return $stmt->fetch() ?: ['goals' => 0, 'yellow_cards' => 0, 'red_cards' => 0];
    }

    /** Top goleadores de una liga. */
    public function getTopScorers(int $leagueId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name AS player_name, p.cedula,
                   t.name AS team_name,
                   COUNT(me.id) AS goals
            FROM match_events me
            JOIN players p ON me.player_id = p.id
            JOIN matches m ON me.match_id  = m.id
            JOIN team_players tp ON tp.player_id = p.id AND tp.league_id = m.league_id
            JOIN teams t ON tp.team_id = t.id
            WHERE me.event_type='goal' AND m.league_id=:lid
            GROUP BY p.id, t.id
            ORDER BY goals DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lid', $leagueId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,    \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
