<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Controllers/StandingsController.php';

/**
 * PublicController.php — Sitio web público (sin login requerido)
 * Los visitantes pueden ver posiciones, equipos y próximos encuentros.
 */
class PublicController extends Controller
{
    private LeagueModel       $leagueModel;
    private TeamModel         $teamModel;
    private MatchModel        $matchModel;
    private StadiumModel      $stadiumModel;
    private StandingsController $standingsCtrl;

    public function __construct()
    {
        $this->leagueModel   = new LeagueModel();
        $this->teamModel     = new TeamModel();
        $this->matchModel    = new MatchModel();
        $this->stadiumModel  = new StadiumModel();
        $this->standingsCtrl = new StandingsController();
    }

    /** GET /principal — Página de inicio pública (selector de ligas) */
    public function home(): void
    {
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/public/home.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/layouts/public.php';
    }

    /** GET /principal/liga/{id} — Posiciones + equipos de un campeonato */
    public function league(string $id): void
    {
        $league = $this->leagueModel->getById((int) $id);
        if (!$league) {
            header('Location: ' . BASE_URL . '/principal');
            exit;
        }

        $standings = $this->standingsCtrl->getStandingsPublic((int) $id);
        $teams     = $this->teamModel->getByLeague((int) $id);
        $leagues   = $this->leagueModel->getAll(); // para el nav

        ob_start();
        require BASE_PATH . '/app/Views/public/league.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/layouts/public.php';
    }

    /** GET /principal/liga/{id}/encuentros — Próximos encuentros con filtro de estadio */
    public function matches(string $id): void
    {
        $league = $this->leagueModel->getById((int) $id);
        if (!$league) {
            header('Location: ' . BASE_URL . '/principal');
            exit;
        }

        $stadiums   = $this->stadiumModel->getAll();
        $stadiumFilter = filter_input(INPUT_GET, 'estadio', FILTER_VALIDATE_INT) ?: 0;

        // Próximos encuentros del campeonato
        $all = $this->matchModel->getByLeague((int) $id);
        $upcomingMatches = array_filter($all, function ($m) use ($stadiumFilter) {
            if ($m['status'] === 'finished') return false;
            if ($stadiumFilter && (int)$m['stadium_id'] !== $stadiumFilter) return false;
            return true;
        });

        $leagues = $this->leagueModel->getAll(); // para nav

        ob_start();
        require BASE_PATH . '/app/Views/public/matches.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/layouts/public.php';
    }

    /** GET /principal/liga/{id}/resultados — Resultados de la jornada con filtro de fecha */
    public function results(string $id): void
    {
        $league = $this->leagueModel->getById((int) $id);
        if (!$league) {
            header('Location: ' . BASE_URL . '/principal');
            exit;
        }

        $dateFilter = filter_input(INPUT_GET, 'fecha');
        $availableDates = $this->matchModel->getAvailableDates((int) $id);
        
        // Si no hay filtro pero hay fechas, tomar la más reciente
        if (!$dateFilter && !empty($availableDates)) {
            $dateFilter = $availableDates[0];
        }

        $results = $this->matchModel->getResultsByLeague((int) $id, $dateFilter);
        $leagues = $this->leagueModel->getAll();

        ob_start();
        require BASE_PATH . '/app/Views/public/results.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/layouts/public.php';
    }
}
