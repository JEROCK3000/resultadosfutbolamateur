<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

/**
 * AuthController.php — Login / Logout del sistema
 */
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /** GET /login */
    public function login(): void
    {
        // Si ya está logueado, redirigir al dashboard
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        ob_start();
        require BASE_PATH . '/app/Views/auth/login.php';
        $content = ob_get_clean();

        // Login usa layout mínimo (sin sidebar)
        require BASE_PATH . '/app/Views/layouts/auth.php';
    }

    /** POST /login/authenticate */
    public function authenticate(): void
    {
        $this->requireMethod('POST');

        $email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $this->setFlash('danger', 'Ingresa tu correo y contraseña.');
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            writeLog('WARNING', "Intento de login fallido para: {$email}");
            $this->setFlash('danger', 'Correo o contraseña incorrectos.');
            $this->redirect('/login');
            return;
        }

        // Iniciar sesión
        session_regenerate_id(true);
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['user_name']    = $user['name'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_role']    = $user['role'];
        $_SESSION['user_league']  = $user['league_id'];

        $this->userModel->updateLastLogin((int) $user['id']);
        AuditModel::log((int) $user['id'], 'login', "Inicio de sesión desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''));
        writeLog('INFO', "Login exitoso: {$email} — Rol: {$user['role']}");

        $this->setFlash('success', "¡Bienvenido, {$user['name']}!");
        $this->redirect('/');
    }

    /** GET /logout */
    public function logout(): void
    {
        $userId   = (int) ($_SESSION['user_id'] ?? 0);
        $userName = $_SESSION['user_name'] ?? 'desconocido';

        AuditModel::log($userId, 'logout', "Cierre de sesión: {$userName}");
        writeLog('INFO', "Logout: {$userName}");

        session_destroy();
        $this->redirect('/login');
    }
}
