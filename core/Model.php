<?php
declare(strict_types=1);

/**
 * Model.php — Clase base de todos los modelos
 * Provee acceso a PDO y métodos CRUD genéricos.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los registros de la tabla.
     */
    public function getAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un registro por su ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Elimina un registro por su ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Retorna el total de registros en la tabla.
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }
    /**
     * Retorna el ID del último registro insertado.
     */
    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
