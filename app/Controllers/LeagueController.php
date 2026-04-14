<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

class LeagueController extends Controller
{
    private LeagueModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new LeagueModel();
    }

    public function index(): void
    {
        $leagues = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/leagues/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Campeonatos', 'content' => $content]);
    }

    public function create(): void
    {
        ob_start();
        require BASE_PATH . '/app/Views/leagues/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Campeonato', 'content' => $content]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');
        $name        = trim(filter_input(INPUT_POST, 'name',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $season      = trim(filter_input(INPUT_POST, 'season',      FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $country     = trim(filter_input(INPUT_POST, 'country',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $status      = in_array($_POST['status'] ?? '', ['active','inactive','finished']) ? $_POST['status'] : 'active';

        if (empty($name) || empty($season) || empty($country)) {
            $this->setFlash('danger', 'Nombre, temporada y país son obligatorios.');
            $this->redirect('/ligas/crear');
            return;
        }

        $created = $this->model->create(compact('name', 'season', 'country', 'description', 'status'));
        if ($created) {
            $leagueId = $this->model->getLastInsertId();
            AuditModel::log($this->currentUser()['id'] ?? 0, 'CREATE', "Campeonato creado: {$name}", 'League', $leagueId);
            writeLog('INFO', "Campeonato creado: {$name} — {$season}");
            $this->setFlash('success', "Campeonato «{$name}» creada exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al crear el campeonato.');
        }
        $this->redirect('/ligas');
    }

    public function edit(string $id): void
    {
        $league = $this->model->getById((int) $id);
        if (!$league) { $this->setFlash('danger', 'Campeonato no encontrado.'); $this->redirect('/ligas'); return; }
        ob_start();
        require BASE_PATH . '/app/Views/leagues/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Campeonato', 'content' => $content]);
    }

    public function update(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;
        $league = $this->model->getById($id);
        if (!$league) { $this->setFlash('danger', 'Campeonato no encontrado.'); $this->redirect('/ligas'); return; }

        $name        = trim(filter_input(INPUT_POST, 'name',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $season      = trim(filter_input(INPUT_POST, 'season',      FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $country     = trim(filter_input(INPUT_POST, 'country',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $status      = in_array($_POST['status'] ?? '', ['active','inactive','finished']) ? $_POST['status'] : 'active';

        if (empty($name) || empty($season) || empty($country)) {
            $this->setFlash('danger', 'Nombre, temporada y país son obligatorios.');
            $this->redirect("/ligas/editar/{$id}");
            return;
        }

        $updated = $this->model->update($id, compact('name', 'season', 'country', 'description', 'status'));
        if ($updated) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'UPDATE', "Campeonato actualizado: {$name}", 'League', $id);
            writeLog('INFO', "Campeonato actualizado: ID {$id} - {$name}");
            $this->setFlash('success', "Campeonato «{$name}» actualizada exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al actualizar el campeonato.');
        }
        $this->redirect('/ligas');
    }

    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;
        $league = $this->model->getById($id);
        if (!$league) { $this->setFlash('danger', 'Campeonato no encontrado.'); $this->redirect('/ligas'); return; }

        $deleted = $this->model->delete($id);
        if ($deleted) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'DELETE', "Campeonato eliminado: {$league['name']}", 'League', $id);
            writeLog('INFO', "Campeonato eliminado: ID {$id} - {$league['name']}");
            $this->setFlash('success', "Campeonato «{$league['name']}» eliminada correctamente.");
        } else {
            $this->setFlash('danger', 'No se pudo eliminar el campeonato. Puede tener equipos o encuentros asociados.');
        }
        $this->redirect('/ligas');
    }
}
