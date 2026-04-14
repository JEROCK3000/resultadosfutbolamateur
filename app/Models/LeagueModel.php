<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * LeagueModel.php — Modelo de Ligas
 */
class LeagueModel extends Model
{
    protected string $table = 'leagues';

    public function getAll(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        return parent::getAll($orderBy, $direction);
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO leagues (name, season, country, description, status, created_at, updated_at)
            VALUES (:name, :season, :country, :description, :status, NOW(), NOW())
        ");
        return $stmt->execute([
            ':name'        => $data['name'],
            ':season'      => $data['season'],
            ':country'     => $data['country'],
            ':description' => $data['description'] ?? null,
            ':status'      => $data['status'] ?? 'active',
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE leagues
            SET name = :name, season = :season, country = :country,
                description = :description, status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name'        => $data['name'],
            ':season'      => $data['season'],
            ':country'     => $data['country'],
            ':description' => $data['description'] ?? null,
            ':status'      => $data['status'] ?? 'active',
            ':id'          => $id,
        ]);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
