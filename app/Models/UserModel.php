<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';

    public function getAll(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $stmt = $this->db->query("
            SELECT u.*, l.name AS league_name, t.name AS team_name
            FROM users u
            LEFT JOIN leagues l ON u.league_id = l.id
            LEFT JOIN teams   t ON u.team_id   = t.id
            ORDER BY {$orderBy} {$direction}
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, l.name AS league_name, t.name AS team_name, t.league_id AS team_league_id
            FROM users u
            LEFT JOIN leagues l ON u.league_id = l.id
            LEFT JOIN teams   t ON u.team_id   = t.id
            WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, t.league_id AS team_league_id
            FROM users u
            LEFT JOIN teams t ON u.team_id = t.id
            WHERE u.email = :email AND u.status = 'active' LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash, role, league_id, team_id, status, created_at, updated_at)
            VALUES (:name, :email, :password_hash, :role, :league_id, :team_id, :status, NOW(), NOW())
        ");
        return $stmt->execute([
            ':name'          => $data['name'],
            ':email'         => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role'          => $data['role'],
            ':league_id'     => $data['league_id'] ?: null,
            ':team_id'       => $data['team_id']   ?: null,
            ':status'        => $data['status'] ?? 'active',
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $fields = "name=:name, email=:email, role=:role, league_id=:league_id, team_id=:team_id, status=:status, updated_at=NOW()";
        $params = [
            ':id'        => $id,
            ':name'      => $data['name'],
            ':email'     => $data['email'],
            ':role'      => $data['role'],
            ':league_id' => $data['league_id'] ?: null,
            ':team_id'   => $data['team_id']   ?: null,
            ':status'    => $data['status'] ?? 'active',
        ];
        if (!empty($data['password'])) {
            $fields .= ", password_hash=:password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $stmt = $this->db->prepare("UPDATE users SET {$fields} WHERE id=:id");
        return $stmt->execute($params);
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login=NOW() WHERE id=:id");
        $stmt->execute([':id' => $id]);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email=:email AND id!=:id");
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
