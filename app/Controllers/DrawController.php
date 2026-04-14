<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';

/**
 * DrawController.php — Módulo de Sorteos generales de Campeonato
 */
class DrawController extends Controller
{
    private LeagueModel $leagueModel;
    private TeamModel   $teamModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->leagueModel = new LeagueModel();
        $this->teamModel   = new TeamModel();
    }

    /** GET /sorteos */
    public function index(): void
    {
        $leagues = $this->leagueModel->getAll();
        
        ob_start();
        require BASE_PATH . '/app/Views/draws/index.php';
        $content = ob_get_clean();
        
        $this->view('layouts/app', [
            'pageTitle' => 'Módulo de Sorteos',
            'content'   => $content
        ]);
    }

    /** GET /sorteos/{league_id} */
    public function show(string $leagueId): void
    {
        $leagueId = (int)$leagueId;
        $league = $this->leagueModel->getById($leagueId);
        if (!$league) {
            $this->setFlash('danger', 'Campeonato no encontrado.');
            $this->redirect('/sorteos');
            return;
        }

        $teams = $this->teamModel->getByLeague($leagueId);

        ob_start();
        require BASE_PATH . '/app/Views/draws/show.php';
        $content = ob_get_clean();

        $this->view('layouts/app', [
            'pageTitle' => 'Sorteo de Equipos — ' . $league['name'],
            'content'   => $content
        ]);
    }
}
