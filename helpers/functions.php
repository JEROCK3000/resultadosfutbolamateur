<?php
declare(strict_types=1);

/**
 * helpers/functions.php — Funciones globales del sistema
 */

/**
 * Registra un evento en el log diario del sistema.
 *
 * @param string $level    Nivel: INFO | WARNING | ERROR | DEBUG | CRITICAL
 * @param string $message  Descripción del evento
 * @param array  $context  Datos adicionales (opcional)
 */
function writeLog(string $level, string $message, array $context = []): void
{
    $dir = BASE_PATH . '/storage/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file  = $dir . '/' . date('Y-m-d') . '.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message;
    if (!empty($context)) {
        $entry .= ' | Contexto: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    @file_put_contents($file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Sanitiza una cadena para mostrar en HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna y limpia el mensaje flash de sesión.
 */
function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Genera la URL completa a partir de una ruta relativa.
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Genera la URL de un asset (CSS, JS, imagen).
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Carga las variables del archivo .env al $_ENV.
 */
function loadEnv(string $filePath): void
{
    if (!file_exists($filePath)) return;

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}
