<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class MatchEventModel extends Model
{
    protected string $table = 'match_events';

    public function getByMatch(int $matchId): array
    {
        $stmt = $this->db->prepare("
            SELECT me.*, t.name AS team_name,
                   p.name AS player_name_db
            FROM match_events me
            LEFT JOIN teams   t ON me.team_id   = t.id
            LEFT JOIN players p ON me.player_id = p.id
            WHERE me.match_id = :match_id
            ORDER BY me.minute ASC
        ");
        $stmt->execute([':match_id' => $matchId]);
        return $stmt->fetchAll();
    }

    public function countByType(int $matchId, string $type): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM match_events
            WHERE match_id = :match_id AND event_type = :type
        ");
        $stmt->execute([':match_id' => $matchId, ':type' => $type]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO match_events (match_id, team_id, player_id, player_name, event_type, minute, created_at)
            VALUES (:match_id, :team_id, :player_id, :player_name, :event_type, :minute, NOW())
        ");
        return $stmt->execute([
            ':match_id'    => $data['match_id'],
            ':team_id'     => $data['team_id'],
            ':player_id'   => $data['player_id'] ?? null,
            ':player_name' => $data['player_name'] ?? null,
            ':event_type'  => $data['event_type'],
            ':minute'      => $data['minute'] ?? null,
        ]);
    }
}
