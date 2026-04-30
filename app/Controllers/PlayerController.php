<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/PlayerModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';

class PlayerController extends Controller
{
    private PlayerModel $model;
    private TeamModel   $teamModel;
    private LeagueModel $leagueModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model       = new PlayerModel();
        $this->teamModel   = new TeamModel();
        $this->leagueModel = new LeagueModel();
    }

    public function index(): void
    {
        // team_manager va directo a su roster
        if ($this->isTeamManager()) {
            $teamId = (int)($_SESSION['user_team_id'] ?? 0);
            $team   = $teamId ? $this->teamModel->getById($teamId) : null;
            if ($team) {
                $this->redirect("/jugadores/equipo/{$teamId}/liga/{$team['league_id']}");
                return;
            }
        }
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/players/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Jugadores', 'content' => $content]);
    }

    public function roster(string $teamId, string $leagueId): void
    {
        $tid = (int)$teamId;
        $lid = (int)$leagueId;
        $this->guardTeamAccess($tid);
        $team   = $this->teamModel->getById($tid);
        $league = $this->leagueModel->getById($lid);
        if (!$team || !$league) { $this->redirect('/jugadores'); return; }

        $players = $this->model->getByTeam($tid, $lid);
        ob_start();
        require BASE_PATH . '/app/Views/players/roster.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => "Roster — {$team['name']}", 'content' => $content]);
    }

    public function create(string $teamId, string $leagueId): void
    {
        $tid = (int)$teamId;
        $lid = (int)$leagueId;
        $this->guardTeamAccess($tid);
        $team   = $this->teamModel->getById($tid);
        $league = $this->leagueModel->getById($lid);
        if (!$team || !$league) { $this->redirect('/jugadores'); return; }

        ob_start();
        require BASE_PATH . '/app/Views/players/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Jugador', 'content' => $content]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');
        $teamId   = (int)filter_input(INPUT_POST, 'team_id',   FILTER_VALIDATE_INT);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $this->guardTeamAccess($teamId);

        $cedula   = trim($_POST['cedula']     ?? '');
        $name     = trim($_POST['name']       ?? '');
        $birth    = trim($_POST['birth_date'] ?? '');
        $position = $_POST['position'] ?? 'otro';
        $number   = isset($_POST['number']) && $_POST['number'] !== '' ? (int)$_POST['number'] : null;

        if (!$cedula || !$name || !$teamId || !$leagueId) {
            $this->setFlash('danger', 'Cédula, nombre, equipo y campeonato son obligatorios.');
            $this->redirect("/jugadores/crear/{$teamId}/{$leagueId}"); return;
        }

        $existing = $this->model->getByCedula($cedula);
        if ($existing) {
            if ($this->model->isInTeam((int)$existing['id'], $teamId, $leagueId)) {
                $this->setFlash('warning', "El jugador con cédula {$cedula} ya está inscrito en este equipo.");
                $this->redirect("/jugadores/equipo/{$teamId}/liga/{$leagueId}"); return;
            }
            $this->model->addToTeam((int)$existing['id'], $teamId, $leagueId, $number);
            $this->setFlash('success', "Jugador «{$existing['name']}» (ya registrado) añadido al equipo.");
            $this->redirect("/jugadores/equipo/{$teamId}/liga/{$leagueId}"); return;
        }

        $this->model->create(['cedula'=>$cedula,'name'=>$name,'birth_date'=>$birth,'position'=>$position]);
        $playerId = $this->model->getLastInsertId();
        $this->model->addToTeam($playerId, $teamId, $leagueId, $number);
        $this->setFlash('success', "Jugador «{$name}» registrado e inscrito correctamente.");
        $this->redirect("/jugadores/equipo/{$teamId}/liga/{$leagueId}");
    }

    public function edit(string $id): void
    {
        $player   = $this->model->getById((int)$id);
        if (!$player) { $this->setFlash('danger','Jugador no encontrado.'); $this->redirect('/jugadores'); return; }
        $teamId   = (int)filter_input(INPUT_GET, 'team_id',   FILTER_VALIDATE_INT);
        $leagueId = (int)filter_input(INPUT_GET, 'league_id', FILTER_VALIDATE_INT);
        if ($teamId) $this->guardTeamAccess($teamId);
        $team   = $teamId   ? $this->teamModel->getById($teamId)     : null;
        $league = $leagueId ? $this->leagueModel->getById($leagueId) : null;

        ob_start();
        require BASE_PATH . '/app/Views/players/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Jugador', 'content' => $content]);
    }

    public function update(string $id): void
    {
        $this->requireMethod('POST');
        $pid      = (int)$id;
        $teamId   = (int)filter_input(INPUT_POST, 'team_id',       FILTER_VALIDATE_INT);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id',     FILTER_VALIDATE_INT);
        $memberId = (int)filter_input(INPUT_POST, 'membership_id', FILTER_VALIDATE_INT);
        $this->guardTeamAccess($teamId);

        $cedula   = trim($_POST['cedula']     ?? '');
        $name     = trim($_POST['name']       ?? '');
        $birth    = trim($_POST['birth_date'] ?? '');
        $position = $_POST['position']   ?? 'otro';
        $number   = isset($_POST['number']) && $_POST['number'] !== '' ? (int)$_POST['number'] : null;
        $status   = $_POST['member_status'] ?? 'active';

        if (!$cedula || !$name) {
            $this->setFlash('danger', 'Cédula y nombre son obligatorios.');
            $this->redirect("/jugadores/editar/{$pid}?team_id={$teamId}&league_id={$leagueId}"); return;
        }
        $byCedula = $this->model->getByCedula($cedula);
        if ($byCedula && (int)$byCedula['id'] !== $pid) {
            $this->setFlash('danger', "La cédula {$cedula} ya está registrada para otro jugador.");
            $this->redirect("/jugadores/editar/{$pid}?team_id={$teamId}&league_id={$leagueId}"); return;
        }

        $this->model->update($pid, ['cedula'=>$cedula,'name'=>$name,'birth_date'=>$birth,'position'=>$position]);
        if ($memberId) $this->model->updateMembership($memberId, $number, $status);
        $this->setFlash('success', 'Jugador actualizado correctamente.');
        $this->redirect("/jugadores/equipo/{$teamId}/liga/{$leagueId}");
    }

    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $pid      = (int)$id;
        $teamId   = (int)filter_input(INPUT_POST, 'team_id',   FILTER_VALIDATE_INT);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $this->guardTeamAccess($teamId);

        $player = $this->model->getById($pid);
        if (!$player) { $this->setFlash('danger','Jugador no encontrado.'); $this->redirect('/jugadores'); return; }

        if (!$this->model->delete($pid)) {
            $this->model->removeFromTeam($pid, $teamId, $leagueId);
            $this->setFlash('warning', 'El jugador tiene historial y no puede eliminarse — fue dado de baja del equipo.');
        } else {
            $this->setFlash('success', 'Jugador eliminado correctamente.');
        }
        $this->redirect("/jugadores/equipo/{$teamId}/liga/{$leagueId}");
    }

    public function importShow(string $teamId, string $leagueId): void
    {
        $tid = (int)$teamId;
        $lid = (int)$leagueId;
        $this->guardTeamAccess($tid);
        $team   = $this->teamModel->getById($tid);
        $league = $this->leagueModel->getById($lid);
        if (!$team || !$league) { $this->redirect('/jugadores'); return; }

        ob_start();
        require BASE_PATH . '/app/Views/players/import.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Importar Jugadores', 'content' => $content]);
    }

    public function importStore(string $teamId, string $leagueId): void
    {
        $this->requireMethod('POST');
        $tid = (int)$teamId;
        $lid = (int)$leagueId;
        $this->guardTeamAccess($tid);

        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->setFlash('danger', 'Debes seleccionar un archivo CSV.');
            $this->redirect("/jugadores/importar/{$tid}/{$lid}"); return;
        }

        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            $this->setFlash('danger', 'No se pudo leer el archivo.');
            $this->redirect("/jugadores/importar/{$tid}/{$lid}"); return;
        }
        fgetcsv($handle, 1000, ',');
        $rows = [];
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (array_filter($row)) $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            $this->setFlash('warning', 'El archivo no contiene datos.');
            $this->redirect("/jugadores/importar/{$tid}/{$lid}"); return;
        }

        $result = $this->model->bulkImport($rows, $tid, $lid);
        $msg = "Importación completada: {$result['created']} nuevos, {$result['updated']} ya existían.";
        if (!empty($result['errors'])) {
            $msg .= ' Errores: ' . implode(' | ', $result['errors']);
            $this->setFlash('warning', $msg);
        } else {
            $this->setFlash('success', $msg);
        }
        $this->redirect("/jugadores/equipo/{$tid}/liga/{$lid}");
    }

    public function template(string $teamId, string $leagueId): void
    {
        $team = $this->teamModel->getById((int)$teamId);
        $name = $team ? preg_replace('/\s+/', '_', strtolower($team['name'])) : 'equipo';
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"jugadores_{$name}.csv\"");
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Cedula','Nombre','Fecha_Nacimiento(DD/MM/YYYY)','Posicion','Numero_Camiseta']);
        fputcsv($out, ['1234567890','JUAN CARLOS PEREZ','15/03/1998','delantero','9']);
        fputcsv($out, ['0987654321','MARIA GARCIA','','portero','1']);
        fclose($out);
        exit;
    }
}
