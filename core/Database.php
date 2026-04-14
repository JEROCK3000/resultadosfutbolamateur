<?php
declare(strict_types=1);

/**
 * Database.php — Conexión PDO Singleton
 * Patrón Singleton para evitar múltiples conexiones a la BD.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Retorna la instancia única de PDO.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }
        return self::$instance;
    }

    /**
     * Crea y configura la conexión PDO.
     */
    private static function connect(): PDO
    {
        $host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
        $port     = $_ENV['DB_PORT']     ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? 'resultadosfutbol';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            writeLog('CRITICAL', 'Error de conexión a la base de datos', ['error' => $e->getMessage()]);
            http_response_code(500);
            die('Error interno del servidor. Revise los logs del sistema.');
        }
    }

    /** Previene instanciación directa */
    private function __construct() {}
    /** Previene clonación */
    private function __clone() {}
}
