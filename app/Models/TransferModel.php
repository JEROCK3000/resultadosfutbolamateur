<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class TransferModel extends Model
{
    protected string $table = 'player_transfers';

    /* ── Ventanas ─────────────────────────────────────────────── */

    public function getWindows(int $leagueId = 0): array
    {
        if ($leagueId) {
            $stmt = $this->db->prepare("
                SELECT tw.*, l.name AS league_name FROM transfer_windows tw
                JOIN leagues l ON tw.league_id = l.id
                WHERE tw.league_id = :lid ORDER BY tw.opens_at DESC
            ");
            $stmt->execute([':lid' => $leagueId]);
        } else {
            $stmt = $this->db->query("
                SELECT tw.*, l.name AS league_name FROM transfer_windows tw
                JOIN leagues l ON tw.league_id = l.id
                ORDER BY tw.opens_at DESC
            ");
        }
        return $stmt->fetchAll();
    }

    public function getWindowById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT tw.*, l.name AS league_name FROM transfer_windows tw
            JOIN leagues l ON tw.league_id = l.id WHERE tw.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function createWindow(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO transfer_windows (league_id, name, opens_at, closes_at, status)
            VALUES (:league_id, :name, :opens_at, :closes_at, 'active')
        ");
        return $stmt->execute([
            ':league_id' => $data['league_id'],
            ':name'      => $data['name'],
            ':opens_at'  => $data['opens_at'],
            ':closes_at' => $data['closes_at'],
        ]);
    }

    public function closeWindow(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE transfer_windows SET status='closed' WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }

    public function getActiveWindow(int $leagueId): array|false
    {
        $today = date('Y-m-d');
        $stmt  = $this->db->prepare("
            SELECT * FROM transfer_windows
            WHERE league_id=:lid AND status='active' AND opens_at<=:d AND closes_at>=:d
            LIMIT 1
        ");
        $stmt->execute([':lid' => $leagueId, ':d' => $today]);
        return $stmt->fetch();
    }

    /* ── Transferencias ───────────────────────────────────────── */

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT pt.*, p.name AS player_name, p.cedula,
                   ft.name AS from_team_name, tt.name AS to_team_name,
                   l.name AS league_name, tw.name AS window_name,
                   ur.name AS requested_name, urv.name AS reviewed_name
            FROM player_transfers pt
            JOIN players p   ON pt.player_id    = p.id
            JOIN teams   ft  ON pt.from_team_id = ft.id
            JOIN teams   tt  ON pt.to_team_id   = tt.id
            JOIN leagues l   ON pt.league_id    = l.id
            LEFT JOIN transfer_windows tw ON pt.window_id   = tw.id
            LEFT JOIN users ur            ON pt.requested_by = ur.id
            LEFT JOIN users urv           ON pt.reviewed_by  = urv.id
            ORDER BY pt.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getByTeam(int $teamId): array
    {
        $stmt = $this->db->prepare("
            SELECT pt.*, p.name AS player_name, p.cedula,
                   ft.name AS from_team_name, tt.name AS to_team_name,
                   l.name AS league_name, urv.name AS reviewed_name
            FROM player_transfers pt
            JOIN players p   ON pt.player_id    = p.id
            JOIN teams   ft  ON pt.from_team_id = ft.id
            JOIN teams   tt  ON pt.to_team_id   = tt.id
            JOIN leagues l   ON pt.league_id    = l.id
            LEFT JOIN users urv ON pt.reviewed_by = urv.id
            WHERE pt.from_team_id=:t OR pt.to_team_id=:t
            ORDER BY pt.created_at DESC
        ");
        $stmt->execute([':t' => $teamId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO player_transfers
              (player_id, from_team_id, to_team_id, league_id, window_id, notes, requested_by)
            VALUES
              (:player_id, :from_team_id, :to_team_id, :league_id, :window_id, :notes, :requested_by)
        ");
        return $stmt->execute([
            ':player_id'    => $data['player_id'],
            ':from_team_id' => $data['from_team_id'],
            ':to_team_id'   => $data['to_team_id'],
            ':league_id'    => $data['league_id'],
            ':window_id'    => $data['window_id'] ?? null,
            ':notes'        => $data['notes']     ?? null,
            ':requested_by' => $data['requested_by'],
        ]);
    }

    public function review(int $id, string $status, int $reviewedBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE player_transfers SET status=:s, reviewed_by=:r, updated_at=NOW() WHERE id=:id
        ");
        return $stmt->execute([':s' => $status, ':r' => $reviewedBy, ':id' => $id]);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT pt.*, p.name AS player_name, p.cedula,
                   ft.name AS from_team_name, tt.name AS to_team_name,
                   l.name AS league_name
            FROM player_transfers pt
            JOIN players p   ON pt.player_id    = p.id
            JOIN teams   ft  ON pt.from_team_id = ft.id
            JOIN teams   tt  ON pt.to_team_id   = tt.id
            JOIN leagues l   ON pt.league_id    = l.id
            WHERE pt.id=:id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
