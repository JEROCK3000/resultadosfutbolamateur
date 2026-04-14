<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

class RefereeModel extends Model
{
    protected string $table = 'referees';

    public function getAll(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        return $this->db->query("SELECT * FROM referees ORDER BY {$orderBy} {$direction}")->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM referees WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO referees (name, license, phone, status, created_at, updated_at)
            VALUES (:name, :license, :phone, :status, NOW(), NOW())
        ");
        return $stmt->execute([
            ':name'    => $data['name'],
            ':license' => $data['license'] ?: null,
            ':phone'   => $data['phone'] ?: null,
            ':status'  => $data['status'] ?? 'active'
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE referees 
            SET name=:name, license=:license, phone=:phone, status=:status, updated_at=NOW()
            WHERE id=:id
        ");
        return $stmt->execute([
            ':id'      => $id,
            ':name'    => $data['name'],
            ':license' => $data['license'] ?: null,
            ':phone'   => $data['phone'] ?: null,
            ':status'  => $data['status'] ?? 'active'
        ]);
    }
}
