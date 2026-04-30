<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/TransferModel.php';
require_once BASE_PATH . '/app/Models/PlayerModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';

class TransferController extends Controller
{
    private TransferModel $model;
    private PlayerModel   $playerModel;
    private TeamModel     $teamModel;
    private LeagueModel   $leagueModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model       = new TransferModel();
        $this->playerModel = new PlayerModel();
        $this->teamModel   = new TeamModel();
        $this->leagueModel = new LeagueModel();
    }

    /** GET /pases — Admin: ventanas + solicitudes */
    public function index(): void
    {
        $this->requireRole('admin', 'registrador');
        $windows   = $this->model->getWindows();
        $transfers = $this->model->getAll();
        $leagues   = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/transfers/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Pases y Transferencias', 'content' => $content]);
    }

    /** POST /pases/ventana/guardar — Admin: crear ventana */
    public function storeWindow(): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $name     = trim($_POST['name']      ?? '');
        $opensAt  = trim($_POST['opens_at']  ?? '');
        $closesAt = trim($_POST['closes_at'] ?? '');

        if (!$leagueId || !$name || !$opensAt || !$closesAt) {
            $this->setFlash('danger', 'Todos los campos son obligatorios.');
            $this->redirect('/pases'); return;
        }
        $this->model->createWindow(compact('league_id','name','opens_at','closes_at') + ['league_id'=>$leagueId]);
        $this->setFlash('success', 'Ventana de pase creada.');
        $this->redirect('/pases');
    }

    /** POST /pases/ventana/cerrar/{id} */
    public function closeWindow(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $this->model->closeWindow((int)$id);
        $this->setFlash('success', 'Ventana cerrada.');
        $this->redirect('/pases');
    }

    /** GET /pases/solicitar — Team manager */
    public function create(): void
    {
        $this->requireRole('team_manager');
        $teamId   = (int)($_SESSION['user_team_id'] ?? 0);
        $team     = $this->teamModel->getById($teamId);
        $myPlayers = $this->playerModel->getByTeam($teamId, (int)($team['league_id'] ?? 0));
        $allTeams  = $this->teamModel->getAll();
        $leagues   = $this->leagueModel->getAll();
        $myTransfers = $this->model->getByTeam($teamId);
        ob_start();
        require BASE_PATH . '/app/Views/transfers/request.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Solicitar Pase', 'content' => $content]);
    }

    /** POST /pases/solicitar */
    public function store(): void
    {
        $this->requireRole('team_manager');
        $this->requireMethod('POST');

        $myTeamId  = (int)($_SESSION['user_team_id'] ?? 0);
        $playerId  = (int)filter_input(INPUT_POST, 'player_id',  FILTER_VALIDATE_INT);
        $toTeamId  = (int)filter_input(INPUT_POST, 'to_team_id', FILTER_VALIDATE_INT);
        $leagueId  = (int)filter_input(INPUT_POST, 'league_id',  FILTER_VALIDATE_INT);
        $notes     = trim($_POST['notes'] ?? '');
        $userId    = $this->currentUser()['id'];

        if (!$playerId || !$toTeamId || !$leagueId || $toTeamId === $myTeamId) {
            $this->setFlash('danger', 'Datos inválidos para solicitar el pase.');
            $this->redirect('/pases/solicitar'); return;
        }

        $window   = $this->model->getActiveWindow($leagueId);
        $windowId = $window ? (int)$window['id'] : null;

        $this->model->create([
            'player_id'    => $playerId,
            'from_team_id' => $myTeamId,
            'to_team_id'   => $toTeamId,
            'league_id'    => $leagueId,
            'window_id'    => $windowId,
            'notes'        => $notes,
            'requested_by' => $userId,
        ]);
        $this->setFlash('success', 'Solicitud de pase enviada al administrador.');
        $this->redirect('/pases/solicitar');
    }

    /** POST /pases/aprobar/{id} — Admin: ejecuta el pase en team_players */
    public function approve(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');

        $transfer = $this->model->getById((int)$id);
        if (!$transfer || $transfer['status'] !== 'pending') {
            $this->setFlash('danger', 'Solicitud no encontrada o ya procesada.');
            $this->redirect('/pases'); return;
        }

        // Ejecutar pase: dar de baja del equipo origen e inscribir en destino
        $this->playerModel->removeFromTeam(
            (int)$transfer['player_id'],
            (int)$transfer['from_team_id'],
            (int)$transfer['league_id']
        );
        $this->playerModel->addToTeam(
            (int)$transfer['player_id'],
            (int)$transfer['to_team_id'],
            (int)$transfer['league_id']
        );

        $this->model->review((int)$id, 'approved', $this->currentUser()['id']);
        $this->setFlash('success', "Pase aprobado — jugador transferido a {$transfer['to_team_name']}.");
        $this->redirect('/pases');
    }

    /** POST /pases/rechazar/{id} */
    public function reject(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');
        $this->model->review((int)$id, 'rejected', $this->currentUser()['id']);
        $this->setFlash('success', 'Solicitud de pase rechazada.');
        $this->redirect('/pases');
    }
}
