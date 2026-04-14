<?php
declare(strict_types=1);

/**
 * Controller.php — Clase base de todos los controladores
 * Provee métodos para cargar vistas, redirigir y controlar acceso.
 */
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            writeLog('ERROR', "Vista no encontrada: {$view}");
            http_response_code(404);
            die("Vista no encontrada: {$view}");
        }
        require_once $viewPath;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    protected function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->redirect('/');
        }
    }

    /** Requiere sesión activa. Redirige al login si no existe. */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('warning', 'Debes iniciar sesión para acceder.');
            $this->redirect('/login');
        }
    }

    /** Requiere rol específico. Admin siempre tiene acceso. */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('danger', 'No tienes permiso para acceder a esta sección.');
            $this->redirect('/');
        }
    }

    /** Devuelve datos del usuario actualmente en sesión. */
    protected function currentUser(): array
    {
        return [
            'id'     => (int) ($_SESSION['user_id']    ?? 0),
            'name'   => $_SESSION['user_name']          ?? '',
            'email'  => $_SESSION['user_email']         ?? '',
            'role'   => $_SESSION['user_role']          ?? '',
            'league' => $_SESSION['user_league']        ?? null,
        ];
    }

    /** Admin gestiona todas las ligas; registrador solo la suya. */
    protected function canManageLeague(int $leagueId): bool
    {
        if (($_SESSION['user_role'] ?? '') === 'admin') return true;
        return (int) ($_SESSION['user_league'] ?? 0) === $leagueId;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
