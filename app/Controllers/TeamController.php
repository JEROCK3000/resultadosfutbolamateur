<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

class TeamController extends Controller
{
    private TeamModel $model;
    private LeagueModel $leagueModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model       = new TeamModel();
        $this->leagueModel = new LeagueModel();
    }

    public function index(): void
    {
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/teams/leagues.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Equipos', 'content' => $content]);
    }

    public function show(string $leagueId): void
    {
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) {
            $this->redirect('/equipos');
            return;
        }

        $teams = $this->model->getByLeague($id);
        ob_start();
        require BASE_PATH . '/app/Views/teams/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => "Equipos — {$league['name']}", 'content' => $content]);
    }

    public function create(): void
    {
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/teams/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Equipo', 'content' => $content]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');
        $league_id    = filter_input(INPUT_POST, 'league_id',    FILTER_VALIDATE_INT);
        $name         = trim(filter_input(INPUT_POST, 'name',         FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $short_name   = trim(filter_input(INPUT_POST, 'short_name',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $founded_year = filter_input(INPUT_POST, 'founded_year', FILTER_VALIDATE_INT) ?: null;

        if (!$league_id || empty($name)) {
            $this->setFlash('danger', 'El campeonato y el nombre del equipo son obligatorios.');
            $this->redirect('/equipos/crear');
            return;
        }

        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logoName = uniqid('logo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], BASE_PATH . '/public/assets/img/teams/' . $logoName)) {
                $logo = $logoName;
            }
        }

        $created = $this->model->create(compact('league_id', 'name', 'short_name', 'logo', 'founded_year'));
        if ($created) {
            $teamId = $this->model->getLastInsertId();
            AuditModel::log($this->currentUser()['id'] ?? 0, 'CREATE', "Equipo creado: {$name}", 'Team', $teamId);
            writeLog('INFO', "Equipo creado: {$name}");
            $this->setFlash('success', "Equipo «{$name}» creado exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al crear el equipo.');
        }
        $this->redirect("/equipos/liga/{$league_id}");
    }

    public function edit(string $id): void
    {
        $team = $this->model->getById((int) $id);
        if (!$team) { $this->setFlash('danger', 'Equipo no encontrado.'); $this->redirect('/equipos'); return; }
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/teams/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Equipo', 'content' => $content]);
    }

    public function update(string $id): void
    {
        $this->requireMethod('POST');
        $id   = (int) $id;
        $team = $this->model->getById($id);
        if (!$team) { $this->setFlash('danger', 'Equipo no encontrado.'); $this->redirect('/equipos'); return; }

        $league_id    = filter_input(INPUT_POST, 'league_id',    FILTER_VALIDATE_INT);
        $name         = trim(filter_input(INPUT_POST, 'name',         FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $short_name   = trim(filter_input(INPUT_POST, 'short_name',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $founded_year = filter_input(INPUT_POST, 'founded_year', FILTER_VALIDATE_INT) ?: null;

        if (!$league_id || empty($name)) {
            $this->setFlash('danger', 'El campeonato y el nombre son obligatorios.');
            $this->redirect("/equipos/editar/{$id}");
            return;
        }

        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logoName = uniqid('logo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], BASE_PATH . '/public/assets/img/teams/' . $logoName)) {
                $logo = $logoName;
            }
        }

        $updated = $this->model->update($id, compact('league_id', 'name', 'short_name', 'logo', 'founded_year'));
        if ($updated) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'UPDATE', "Equipo actualizado: {$name}", 'Team', $id);
            writeLog('INFO', "Equipo actualizado: ID {$id} - {$name}");
            $this->setFlash('success', "Equipo «{$name}» actualizado exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al actualizar el equipo.');
        }
        $this->redirect("/equipos/liga/{$league_id}");
    }

    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $id   = (int) $id;
        $team = $this->model->getById($id);
        if (!$team) { $this->setFlash('danger', 'Equipo no encontrado.'); $this->redirect('/equipos'); return; }

        $deleted = $this->model->delete($id);
        if ($deleted) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'DELETE', "Equipo eliminado: {$team['name']}", 'Team', $id);
            writeLog('INFO', "Equipo eliminado: ID {$id} - {$team['name']}");
            $this->setFlash('success', "Equipo «{$team['name']}» eliminado correctamente.");
        } else {
            $this->setFlash('danger', 'No se pudo eliminar el equipo.');
        }
        $this->redirect("/equipos/liga/{$team['league_id']}");
    }

    /** GET /equipos/por-liga/{id} — Retorna JSON para select encadenado */
    public function porLiga(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $teams = $this->model->getByLeague((int) $id);
        echo json_encode($teams, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function importShow(string $leagueId): void
    {
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) { $this->redirect('/equipos'); return; }

        $allLeagues = $this->leagueModel->getAll();
        $otherLeagues = array_filter($allLeagues, fn($l) => $l['id'] !== $id);

        ob_start();
        require BASE_PATH . '/app/Views/teams/import.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Clonar Equipos', 'content' => $content]);
    }

    public function importStore(string $leagueId): void
    {
        $this->requireMethod('POST');
        $id = (int)$leagueId;
        $teamIds = $_POST['teams'] ?? [];

        if (empty($teamIds)) {
            $this->setFlash('warning', 'No seleccionaste ningún equipo para clonar.');
            $this->redirect("/equipos/liga/{$id}");
            return;
        }

        $imported = 0;
        foreach ($teamIds as $tid) {
            $team = $this->model->getById((int)$tid);
            if ($team) {
                // Clona reusando los campos
                $this->model->create([
                    'league_id' => $id,
                    'name' => $team['name'],
                    'short_name' => $team['short_name'],
                    'logo' => $team['logo'],
                    'founded_year' => $team['founded_year']
                ]);
                $imported++;
            }
        }

        $this->setFlash('success', "Se clonaron {$imported} equipos correctamente al campeonato.");
        $this->redirect("/equipos/liga/{$id}");
    }
}
