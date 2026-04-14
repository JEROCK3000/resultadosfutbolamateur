<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * StadiumModel.php — Modelo de Estadios
 */
class StadiumModel extends Model
{
    protected string $table = 'stadiums';

    /** Obtiene todos los estadios ordenados por nombre */
    public function getAll(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        return parent::getAll($orderBy, $direction);
    }

    /** Crea un nuevo estadio */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO stadiums (name, city, country, capacity, created_at, updated_at)
            VALUES (:name, :city, :country, :capacity, NOW(), NOW())
        ");
        return $stmt->execute([
            ':name'     => $data['name'],
            ':city'     => $data['city'],
            ':country'  => $data['country'],
            ':capacity' => $data['capacity'] ?? null,
        ]);
    }

    /** Actualiza un estadio existente */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE stadiums
            SET name = :name, city = :city, country = :country, capacity = :capacity, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name'     => $data['name'],
            ':city'     => $data['city'],
            ':country'  => $data['country'],
            ':capacity' => $data['capacity'] ?? null,
            ':id'       => $id,
        ]);
    }

    /** Verifica si el nombre ya existe (para evitar duplicados) */
    public function existsByName(string $name, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM stadiums WHERE name = :name AND id != :id
        ");
        $stmt->execute([':name' => $name, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
