<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Model.php';

/**
 * AuditModel.php — Modelo de Auditoría
 */
class AuditModel extends Model
{
    protected string $table = 'audit_logs';

    /**
     * Registra una acción en el log de auditoría.
     * Se llama desde cualquier controlador tras operaciones relevantes.
     */
    public static function log(
        int    $userId,
        string $action,
        string $description,
        string $entityType = '',
        ?int   $entityId   = null
    ): void {
        try {
            $db   = \Database::getInstance();
            $ip   = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at)
                VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip, NOW())
            ");
            $stmt->execute([
                ':user_id'     => $userId,
                ':action'      => $action,
                ':entity_type' => $entityType,
                ':entity_id'   => $entityId,
                ':description' => $description,
                ':ip'          => $ip,
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el logging
            writeLog('WARNING', 'AuditModel::log falló: ' . $e->getMessage());
        }
    }

    /** Obtiene todos los logs con datos del usuario */
    public function getAll(string $orderBy = 'a.created_at', string $direction = 'DESC'): array
    {
        $stmt = $this->db->query("
            SELECT a.*, u.name AS user_name, u.email AS user_email, u.role AS user_role
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY {$orderBy} {$direction}
            LIMIT 500
        ");
        return $stmt->fetchAll();
    }

    /** Filtra logs por usuario, acción y rango de fechas */
    public function getFiltered(array $filters): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[]  = 'a.user_id = :user_id';
            $params[':user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $where[]  = 'a.action = :action';
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(a.created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(a.created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $sql  = "SELECT a.*, u.name AS user_name, u.role AS user_role
                 FROM audit_logs a
                 LEFT JOIN users u ON a.user_id = u.id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY a.created_at DESC LIMIT 500";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lista de usuarios que tienen logs (para filtro) */
    public function getUsersWithLogs(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id, u.name, u.role
            FROM audit_logs a
            INNER JOIN users u ON a.user_id = u.id
            ORDER BY u.name
        ");
        return $stmt->fetchAll();
    }
}
