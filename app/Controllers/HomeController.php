<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';

/**
 * HomeController.php — Dashboard principal del sistema
 */
class HomeController extends Controller
{
    /**
     * Muestra el panel principal con estadísticas globales.
     */
    public function index(): void
    {
        $this->requireAuth();

        $stadiumModel = new StadiumModel();
        $leagueModel  = new LeagueModel();
        $teamModel    = new TeamModel();
        $matchModel   = new MatchModel();

        $stats = [
            'ligas'      => $leagueModel->count(),
            'estadios'   => $stadiumModel->count(),
            'equipos'    => $teamModel->count(),
            'encuentros' => $matchModel->count(),
        ];

        $proximosEncuentros  = $matchModel->getUpcoming(5);
        $ultimosResultados   = $matchModel->getFinished(5);

        ob_start();
        require BASE_PATH . '/app/Views/home/index.php';
        $content = ob_get_clean();

        $this->view('layouts/app', [
            'pageTitle' => 'Dashboard',
            'content'   => $content,
        ]);
    }
}
