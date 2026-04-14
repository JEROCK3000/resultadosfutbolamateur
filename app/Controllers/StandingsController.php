<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Models/MatchResultModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';

/**
 * StandingsController.php — Tabla de Posiciones
 */
class StandingsController extends Controller
{
    private LeagueModel      $leagueModel;
    private MatchModel       $matchModel;
    private MatchResultModel $resultModel;

    public function __construct()
    {
        $this->leagueModel = new LeagueModel();
        $this->matchModel  = new MatchModel();
        $this->resultModel = new MatchResultModel();
    }

    /** GET /posiciones — Seleccionar liga */
    public function index(): void
    {
        $this->requireAuth();
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/standings/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Tabla de Posiciones', 'content' => $content]);
    }

    /** GET /posiciones/{league_id} — Tabla de un campeonato */
    public function show(string $leagueId): void
    {
        $this->requireAuth();
        $league = $this->leagueModel->getById((int) $leagueId);
        if (!$league) {
            $this->setFlash('danger', 'Campeonato no encontrado.');
            $this->redirect('/posiciones');
            return;
        }

        $standings = $this->calculateStandings((int) $leagueId);
        $matches   = $this->matchModel->getByLeague((int) $leagueId);

        ob_start();
        require BASE_PATH . '/app/Views/standings/show.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Posiciones — ' . $league['name'],
            'content'   => $content,
        ]);
    }

    /**
     * Calcula la tabla de posiciones de un campeonato.
     * PJ | PG | PE | PP | GF | GC | DG | PTS
     */
    /** Alias público para uso desde otros controladores (ej: TournamentController) */
    public function getStandingsPublic(int $leagueId): array
    {
        return $this->calculateStandings($leagueId);
    }

    public function calculateStandings(int $leagueId): array
    {
        $matches = $this->matchModel->getByLeague($leagueId);

        $table = [];

        foreach ($matches as $match) {
            if ($match['status'] !== 'finished') continue;
            if (!isset($match['home_goals']) || !isset($match['away_goals'])) continue;

            $homeId = $match['home_team_id'];
            $awayId = $match['away_team_id'];
            $hg     = (int) $match['home_goals'];
            $ag     = (int) $match['away_goals'];
            $hyc    = (int) ($match['home_yellow_cards'] ?? 0);
            $ayc    = (int) ($match['away_yellow_cards'] ?? 0);
            $hrc    = (int) ($match['home_red_cards'] ?? 0);
            $arc    = (int) ($match['away_red_cards'] ?? 0);

            // Inicializar equipos si no existen
            if (!isset($table[$homeId])) {
                $table[$homeId] = $this->emptyRow($match['home_team'], $homeId, $match['home_logo']);
            }
            if (!isset($table[$awayId])) {
                $table[$awayId] = $this->emptyRow($match['away_team'], $awayId, $match['away_logo']);
            }

            // Goles y Tarjetas
            $table[$homeId]['GF'] += $hg;
            $table[$homeId]['GC'] += $ag;
            $table[$homeId]['TA'] += $hyc;
            $table[$homeId]['TR'] += $hrc;

            $table[$awayId]['GF'] += $ag;
            $table[$awayId]['GC'] += $hg;
            $table[$awayId]['TA'] += $ayc;
            $table[$awayId]['TR'] += $arc;

            // Partidos jugados
            $table[$homeId]['PJ']++;
            $table[$awayId]['PJ']++;

            // Puntos
            if ($hg > $ag) {
                $table[$homeId]['PG']++; $table[$homeId]['PTS'] += 3;
                $table[$awayId]['PP']++;
            } elseif ($hg < $ag) {
                $table[$awayId]['PG']++; $table[$awayId]['PTS'] += 3;
                $table[$homeId]['PP']++;
            } else {
                $table[$homeId]['PE']++; $table[$homeId]['PTS']++;
                $table[$awayId]['PE']++; $table[$awayId]['PTS']++;
            }
        }

        // Calcular diferencia de goles
        foreach ($table as &$row) {
            $row['DG'] = $row['GF'] - $row['GC'];
        }
        unset($row);

        // Ordenar: PTS → DG → GF → nombre
        usort($table, function ($a, $b) {
            if ($b['PTS'] !== $a['PTS']) return $b['PTS'] <=> $a['PTS'];
            if ($b['DG']  !== $a['DG'])  return $b['DG']  <=> $a['DG'];
            if ($b['GF']  !== $a['GF'])  return $b['GF']  <=> $a['GF'];
            return strcmp($a['name'], $b['name']);
        });

        return array_values($table);
    }

    private function emptyRow(string $name, int $id, ?string $logo = null): array
    {
        return [
            'id'=>$id, 'name'=>$name, 'logo'=>$logo, 'PJ'=>0, 'PG'=>0, 'PE'=>0, 'PP'=>0, 
            'GF'=>0, 'GC'=>0, 'DG'=>0, 'PTS'=>0, 'TA'=>0, 'TR'=>0
        ];
    }
}
