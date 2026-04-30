<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class RegistrationModel extends Model
{
    protected string $table = 'championship_registrations';

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT r.*, t.name AS team_name, l.name AS league_name,
                   us.name AS submitted_name, ur.name AS reviewed_name
            FROM championship_registrations r
            JOIN  teams   t  ON r.team_id      = t.id
            JOIN  leagues l  ON r.league_id    = l.id
            JOIN  users   us ON r.submitted_by = us.id
            LEFT JOIN users ur ON r.reviewed_by = ur.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getByTeam(int $teamId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, l.name AS league_name, ur.name AS reviewed_name
            FROM championship_registrations r
            JOIN  leagues l ON r.league_id  = l.id
            LEFT JOIN users ur ON r.reviewed_by = ur.id
            WHERE r.team_id = :team_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function exists(int $teamId, int $leagueId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM championship_registrations
            WHERE team_id=:t AND league_id=:l
        ");
        $stmt->execute([':t' => $teamId, ':l' => $leagueId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO championship_registrations (team_id, league_id, status, notes, submitted_by)
            VALUES (:team_id, :league_id, 'pending', :notes, :submitted_by)
        ");
        return $stmt->execute([
            ':team_id'      => $data['team_id'],
            ':league_id'    => $data['league_id'],
            ':notes'        => $data['notes'] ?? null,
            ':submitted_by' => $data['submitted_by'],
        ]);
    }

    public function review(int $id, string $status, int $reviewedBy, ?string $notes): bool
    {
        $stmt = $this->db->prepare("
            UPDATE championship_registrations
            SET status=:status, reviewed_by=:reviewed_by, notes=:notes, updated_at=NOW()
            WHERE id=:id
        ");
        return $stmt->execute([
            ':id'          => $id,
            ':status'      => $status,
            ':reviewed_by' => $reviewedBy,
            ':notes'       => $notes,
        ]);
    }
}
