<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/RefereeModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

/**
 * MatchController.php — Controlador de Encuentros (CRUD + validaciones)
 */
class MatchController extends Controller
{
    private MatchModel   $model;
    private LeagueModel  $leagueModel;
    private TeamModel    $teamModel;
    private StadiumModel $stadiumModel;
    private RefereeModel $refereeModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model        = new MatchModel();
        $this->leagueModel  = new LeagueModel();
        $this->teamModel    = new TeamModel();
        $this->stadiumModel = new StadiumModel();
        $this->refereeModel = new RefereeModel();
    }

    /** GET /encuentros — Listado de Campeonatos */
    public function index(): void
    {
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/matches/leagues.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Encuentros', 'content' => $content]);
    }

    /** GET /encuentros/liga/{id} — Encuentros de una Campeonato específica */
    public function show(string $leagueId): void
    {
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) {
            $this->redirect('/encuentros');
            return;
        }

        $matches = $this->model->getByLeague($id);
        ob_start();
        require BASE_PATH . '/app/Views/matches/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => "Encuentros — {$league['name']}", 'content' => $content]);
    }

    public function showMatch(string $id): void
    {
        $match = $this->model->getById((int) $id);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros');
            return;
        }
        $this->redirect("/resultados/{$id}");
    }

    /** GET /encuentros/crear — Formulario de creación */
    public function create(): void
    {
        $leagues  = $this->leagueModel->getAll();
        $stadiums = $this->stadiumModel->getAll();
        $referees = $this->refereeModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/matches/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Encuentro', 'content' => $content]);
    }

    /** POST /encuentros/guardar — Procesar creación */
    public function store(): void
    {
        $this->requireMethod('POST');

        $league_id    = filter_input(INPUT_POST, 'league_id',    FILTER_VALIDATE_INT);
        $home_team_id = filter_input(INPUT_POST, 'home_team_id', FILTER_VALIDATE_INT);
        $away_team_id = filter_input(INPUT_POST, 'away_team_id', FILTER_VALIDATE_INT);
        $stadium_id   = filter_input(INPUT_POST, 'stadium_id',   FILTER_VALIDATE_INT);
        $referee_id   = filter_input(INPUT_POST, 'referee_id',   FILTER_VALIDATE_INT) ?: null;
        $match_date   = trim($_POST['match_date'] ?? '');
        $match_time   = trim($_POST['match_time'] ?? '');
        $status       = in_array($_POST['status'] ?? '', ['scheduled','live','finished','postponed'])
                        ? $_POST['status'] : 'scheduled';

        // ── Validaciones básicas ──────────────────────────────
        if (!$league_id || !$home_team_id || !$away_team_id || !$stadium_id || !$match_date || !$match_time) {
            $this->setFlash('danger', 'Todos los campos son obligatorios.');
            $this->redirect('/encuentros/crear');
            return;
        }

        if ($home_team_id === $away_team_id) {
            $this->setFlash('danger', 'El equipo local y visitante no pueden ser el mismo.');
            $this->redirect('/encuentros/crear');
            return;
        }

        // ── Validación: conflicto de estadio ──────────────────
        if ($this->model->hasStadiumConflict($stadium_id, $match_date, $match_time)) {
            $this->setFlash('warning', 'Ya existe un encuentro en ese estadio a la misma fecha y hora.');
            $this->redirect('/encuentros/crear');
            return;
        }

        // ── Validación: conflicto de equipo local ─────────────
        if ($this->model->hasTeamConflict($home_team_id, $match_date)) {
            $this->setFlash('warning', 'El equipo local ya tiene un encuentro programado para ese día.');
            $this->redirect('/encuentros/crear');
            return;
        }

        // ── Validación: conflicto de equipo visitante ─────────
        if ($this->model->hasTeamConflict($away_team_id, $match_date)) {
            $this->setFlash('warning', 'El equipo visitante ya tiene un encuentro programado para ese día.');
            $this->redirect('/encuentros/crear');
            return;
        }

        $created = $this->model->create(compact(
            'league_id', 'home_team_id', 'away_team_id', 'stadium_id', 'referee_id', 'match_date', 'match_time', 'status'
        ));

        if ($created) {
            $matchId = $this->model->getLastInsertId();
            AuditModel::log($this->currentUser()['id'] ?? 0, 'CREATE', "Encuentro programado: {$match_date} {$match_time}", 'Match', $matchId);
            writeLog('INFO', "Encuentro creado: {$match_date} {$match_time}",
                ['liga' => $league_id, 'local' => $home_team_id, 'visitante' => $away_team_id]);
            $this->setFlash('success', 'Encuentro programado exitosamente.');
        } else {
            $this->setFlash('danger', 'Error al guardar el encuentro.');
        }
        $this->redirect("/encuentros/liga/{$league_id}");
    }

    /** GET /encuentros/editar/{id} — Formulario de edición */
    public function edit(string $id): void
    {
        $match = $this->model->getById((int) $id);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros');
            return;
        }
        $leagues  = $this->leagueModel->getAll();
        $stadiums = $this->stadiumModel->getAll();
        $referees = $this->refereeModel->getAll();
        $teams    = $this->teamModel->getByLeague((int) $match['league_id']);

        ob_start();
        require BASE_PATH . '/app/Views/matches/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Encuentro', 'content' => $content]);
    }

    /** POST /encuentros/actualizar/{id} — Procesar edición */
    public function update(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;

        $match = $this->model->getById($id);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros');
            return;
        }

        $league_id    = filter_input(INPUT_POST, 'league_id',    FILTER_VALIDATE_INT);
        $home_team_id = filter_input(INPUT_POST, 'home_team_id', FILTER_VALIDATE_INT);
        $away_team_id = filter_input(INPUT_POST, 'away_team_id', FILTER_VALIDATE_INT);
        $stadium_id   = filter_input(INPUT_POST, 'stadium_id',   FILTER_VALIDATE_INT);
        $referee_id   = filter_input(INPUT_POST, 'referee_id',   FILTER_VALIDATE_INT) ?: null;
        $match_date   = trim($_POST['match_date'] ?? '');
        $match_time   = trim($_POST['match_time'] ?? '');
        $status       = in_array($_POST['status'] ?? '', ['scheduled','live','finished','postponed'])
                        ? $_POST['status'] : 'scheduled';

        if (!$league_id || !$home_team_id || !$away_team_id || !$stadium_id || !$match_date || !$match_time) {
            $this->setFlash('danger', 'Todos los campos son obligatorios.');
            $this->redirect("/encuentros/editar/{$id}");
            return;
        }

        if ($home_team_id === $away_team_id) {
            $this->setFlash('danger', 'El equipo local y visitante no pueden ser el mismo.');
            $this->redirect("/encuentros/editar/{$id}");
            return;
        }

        if ($this->model->hasStadiumConflict($stadium_id, $match_date, $match_time, $id)) {
            $this->setFlash('warning', 'Ya existe otro encuentro en ese estadio a la misma fecha y hora.');
            $this->redirect("/encuentros/editar/{$id}");
            return;
        }

        if ($this->model->hasTeamConflict($home_team_id, $match_date, $id)) {
            $this->setFlash('warning', 'El equipo local ya tiene otro encuentro ese día.');
            $this->redirect("/encuentros/editar/{$id}");
            return;
        }

        if ($this->model->hasTeamConflict($away_team_id, $match_date, $id)) {
            $this->setFlash('warning', 'El equipo visitante ya tiene otro encuentro ese día.');
            $this->redirect("/encuentros/editar/{$id}");
            return;
        }

        $updated = $this->model->update($id, compact(
            'league_id', 'home_team_id', 'away_team_id', 'stadium_id', 'referee_id', 'match_date', 'match_time', 'status'
        ));

        if ($updated) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'UPDATE', "Encuentro actualizado: {$match_date} {$match_time}", 'Match', $id);
            writeLog('INFO', "Encuentro actualizado: ID {$id}");
            $this->setFlash('success', 'Encuentro actualizado exitosamente.');
        } else {
            $this->setFlash('danger', 'Error al actualizar el encuentro.');
        }
        $this->redirect("/encuentros/liga/{$league_id}");
    }

    /** POST /encuentros/eliminar/{id} — Eliminar */
    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $id    = (int) $id;
        $match = $this->model->getById($id);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros');
            return;
        }

        $deleted = $this->model->delete($id);
        if ($deleted) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'DELETE', "Encuentro eliminado: {$match['match_date']} {$match['match_time']}", 'Match', $id);
            writeLog('INFO', "Encuentro eliminado: ID {$id}");
            $this->setFlash('success', 'Encuentro eliminado correctamente.');
        } else {
            $this->setFlash('danger', 'No se pudo eliminar el encuentro.');
        }
        $this->redirect("/encuentros/liga/{$match['league_id']}");
    }
}
