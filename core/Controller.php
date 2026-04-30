<?php
declare(strict_types=1);

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

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('warning', 'Debes iniciar sesión para acceder.');
            $this->redirect('/login');
        }
    }

    /** Requiere uno de los roles dados (admin siempre pasa). */
    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $userRole = $_SESSION['user_role'] ?? '';
        if ($userRole === 'admin') return;
        foreach ($roles as $role) {
            if ($userRole === $role) return;
        }
        $this->setFlash('danger', 'No tienes permiso para acceder a esta sección.');
        $this->redirect('/');
    }

    protected function currentUser(): array
    {
        return [
            'id'       => (int) ($_SESSION['user_id']      ?? 0),
            'name'     => $_SESSION['user_name']            ?? '',
            'email'    => $_SESSION['user_email']           ?? '',
            'role'     => $_SESSION['user_role']            ?? '',
            'league'   => $_SESSION['user_league']          ?? null,
            'team_id'  => (int) ($_SESSION['user_team_id'] ?? 0),
        ];
    }

    protected function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    protected function isTeamManager(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'team_manager';
    }

    protected function canManageLeague(int $leagueId): bool
    {
        if ($this->isAdmin()) return true;
        return (int) ($_SESSION['user_league'] ?? 0) === $leagueId;
    }

    /** Bloquea al team_manager si intenta acceder a un equipo que no es el suyo. */
    protected function guardTeamAccess(int $teamId): void
    {
        if ($this->isTeamManager() && (int)($_SESSION['user_team_id'] ?? 0) !== $teamId) {
            $this->setFlash('danger', 'Solo puedes gestionar tu propio equipo.');
            $this->redirect('/jugadores');
        }
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
