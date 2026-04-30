<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/SanctionModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/PlayerModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';

class SanctionController extends Controller
{
    private SanctionModel $model;
    private LeagueModel   $leagueModel;
    private PlayerModel   $playerModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model       = new SanctionModel();
        $this->leagueModel = new LeagueModel();
        $this->playerModel = new PlayerModel();
    }

    /** GET /sanciones */
    public function index(): void
    {
        $this->requireRole('admin', 'registrador');
        $leagueId  = (int)filter_input(INPUT_GET, 'league_id', FILTER_VALIDATE_INT);
        $sanctions = $leagueId ? $this->model->getByLeague($leagueId) : $this->model->getAll();
        $leagues   = $this->leagueModel->getAll();
        $topScorers = $leagueId ? $this->model->getTopScorers($leagueId) : [];
        ob_start();
        require BASE_PATH . '/app/Views/sanctions/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Sanciones y Estadísticas', 'content' => $content]);
    }

    /** POST /sanciones/crear — Sanción disciplinaria manual */
    public function store(): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $playerId   = (int)filter_input(INPUT_POST, 'player_id',   FILTER_VALIDATE_INT);
        $leagueId   = (int)filter_input(INPUT_POST, 'league_id',   FILTER_VALIDATE_INT);
        $reason     = trim($_POST['reason']      ?? '');
        $matchesQty = (int)filter_input(INPUT_POST, 'matches_qty', FILTER_VALIDATE_INT);
        $fineUsd    = (float)str_replace(',', '.', $_POST['fine_usd'] ?? '0');

        if (!$playerId || !$leagueId || !$reason) {
            $this->setFlash('danger', 'Jugador, liga y motivo son obligatorios.');
            $this->redirect('/sanciones'); return;
        }

        $this->model->create([
            'player_id'   => $playerId,
            'league_id'   => $leagueId,
            'type'        => 'disciplinary',
            'reason'      => $reason,
            'matches_qty' => max(0, $matchesQty),
            'fine_usd'    => max(0, $fineUsd),
        ]);
        $this->setFlash('success', 'Sanción disciplinaria registrada.');
        $this->redirect("/sanciones?league_id={$leagueId}");
    }

    /** POST /sanciones/pagar/{id} */
    public function payFine(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');
        $this->model->markFinePaid((int)$id);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $this->setFlash('success', 'Multa marcada como pagada.');
        $this->redirect("/sanciones?league_id={$leagueId}");
    }

    /** POST /sanciones/cumplir/{id} — Reducir partido cumplido */
    public function serve(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');
        $this->model->serveMatch((int)$id);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $this->setFlash('success', 'Partido de sanción registrado como cumplido.');
        $this->redirect("/sanciones?league_id={$leagueId}");
    }

    /** POST /sanciones/anular/{id} */
    public function deactivate(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $this->model->deactivate((int)$id);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $this->setFlash('success', 'Sanción anulada.');
        $this->redirect("/sanciones?league_id={$leagueId}");
    }
}
