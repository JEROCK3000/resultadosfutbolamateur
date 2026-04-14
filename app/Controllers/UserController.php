<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

/**
 * UserController.php — Gestión de usuarios (solo admin)
 */
class UserController extends Controller
{
    private UserModel  $model;
    private LeagueModel $leagueModel;

    public function __construct()
    {
        $this->model       = new UserModel();
        $this->leagueModel = new LeagueModel();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $users = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/users/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Usuarios', 'content' => $content]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/users/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Usuario', 'content' => $content]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');

        $name      = trim(filter_input(INPUT_POST, 'name',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email     = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '');
        $password  = trim($_POST['password'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin','registrador']) ? $_POST['role'] : 'registrador';
        $league_id = filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT) ?: null;
        $status    = 'active';

        if (empty($name) || empty($email) || empty($password)) {
            $this->setFlash('danger', 'Nombre, correo y contraseña son obligatorios.');
            $this->redirect('/usuarios/crear');
            return;
        }
        if (strlen($password) < 8) {
            $this->setFlash('danger', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('/usuarios/crear');
            return;
        }
        if ($this->model->emailExists($email)) {
            $this->setFlash('danger', "El correo {$email} ya está registrado.");
            $this->redirect('/usuarios/crear');
            return;
        }

        $created = $this->model->create(compact('name','email','password','role','league_id','status'));
        if ($created) {
            $user = $this->currentUser();
            AuditModel::log($user['id'], 'create', "Usuario creado: {$name} ({$email}) — Rol: {$role}", 'Usuario');
            $this->setFlash('success', "Usuario {$name} creado exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al crear el usuario.');
        }
        $this->redirect('/usuarios');
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $userEdit = $this->model->getById((int) $id);
        if (!$userEdit) { $this->setFlash('danger','Usuario no encontrado.'); $this->redirect('/usuarios'); return; }
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/users/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Usuario', 'content' => $content]);
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $id = (int) $id;

        $name      = trim(filter_input(INPUT_POST, 'name',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email     = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
        $password  = trim($_POST['password'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin','registrador']) ? $_POST['role'] : 'registrador';
        $league_id = filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT) ?: null;
        $status    = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

        if (empty($name) || empty($email)) {
            $this->setFlash('danger', 'Nombre y correo son obligatorios.');
            $this->redirect("/usuarios/editar/{$id}");
            return;
        }
        if (!empty($password) && strlen($password) < 8) {
            $this->setFlash('danger', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect("/usuarios/editar/{$id}");
            return;
        }
        if ($this->model->emailExists($email, $id)) {
            $this->setFlash('danger', "El correo {$email} ya está en uso por otro usuario.");
            $this->redirect("/usuarios/editar/{$id}");
            return;
        }

        $updated = $this->model->update($id, compact('name','email','password','role','league_id','status'));
        if ($updated) {
            $user = $this->currentUser();
            AuditModel::log($user['id'], 'update', "Usuario actualizado: {$name} ({$email})", 'Usuario', $id);
            $this->setFlash('success', 'Usuario actualizado correctamente.');
        } else {
            $this->setFlash('danger', 'Error al actualizar el usuario.');
        }
        $this->redirect('/usuarios');
    }

    public function destroy(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $id = (int) $id;

        // No puede eliminarse a sí mismo
        if ($id === $this->currentUser()['id']) {
            $this->setFlash('danger', 'No puedes eliminarte a ti mismo.');
            $this->redirect('/usuarios');
            return;
        }

        $u = $this->model->getById($id);
        if (!$u) { $this->setFlash('danger','Usuario no encontrado.'); $this->redirect('/usuarios'); return; }

        $this->model->delete($id);
        $user = $this->currentUser();
        AuditModel::log($user['id'], 'delete', "Usuario eliminado: {$u['name']} ({$u['email']})", 'Usuario', $id);
        $this->setFlash('success', "Usuario {$u['name']} eliminado.");
        $this->redirect('/usuarios');
    }
}
