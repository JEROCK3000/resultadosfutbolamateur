<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';

/**
 * ScheduleController.php — Generador Automático (Fixture)
 */
class ScheduleController extends Controller
{
    private LeagueModel  $leagueModel;
    private TeamModel    $teamModel;
    private StadiumModel $stadiumModel;
    private MatchModel   $matchModel;

    public function __construct()
    {
        $this->requireAuth();
        // Solo admins y registradores (será validado por liga)
        $this->leagueModel  = new LeagueModel();
        $this->teamModel    = new TeamModel();
        $this->stadiumModel = new StadiumModel();
        $this->matchModel   = new MatchModel();
    }

    /** GET /calendario/generar/{league_id} */
    public function create(string $leagueId): void
    {
        $id = (int) $leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) {
            $this->setFlash('danger', 'Campeonato no encontrado.');
            $this->redirect('/ligas');
            return;
        }

        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_league'] != $league['id']) {
            $this->setFlash('danger', 'No tienes permisos para este campeonato.');
            $this->redirect('/ligas');
            return;
        }

        $teams = $this->teamModel->getByLeague($id);
        $stadiums = $this->stadiumModel->getAll();

        if (count($teams) < 2) {
            $this->setFlash('warning', 'Se necesitan al menos 2 equipos para generar un calendario.');
            $this->redirect("/equipos");
            return;
        }

        ob_start();
        require BASE_PATH . '/app/Views/schedule/generate.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Sorteo Automático — ' . $league['name'],
            'content'   => $content,
        ]);
    }

    /** POST /calendario/generar/{league_id} */
    public function store(string $leagueId): void
    {
        $this->requireMethod('POST');
        $id = (int) $leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_league'] != $league['id'])) {
            $this->setFlash('danger', 'No permitido.');
            $this->redirect('/ligas');
            return;
        }

        $teams = $this->teamModel->getByLeague($id);
        if (count($teams) < 2) {
            $this->setFlash('danger', 'Equipos insuficientes.');
            $this->redirect("/calendario/generar/{$id}");
            return;
        }

        $mode = filter_input(INPUT_POST, 'mode') ?: '1_vuelta';

        // Algoritmo de Berger (Round-Robin)
        // Para que sea "Sorteo", desordenamos estocásticamente a los equipos antes de introducirlos al formato de Berger
        shuffle($teams);
        
        $schedule = $this->generateBergerBracket($teams);
        
        if ($mode === '2_vueltas') {
            $secondHalf = [];
            foreach ($schedule as $round) {
                $revRound = [];
                foreach ($round as $match) {
                    // Invertir localía
                    $revRound[] = [$match[1], $match[0]];
                }
                $secondHalf[] = $revRound;
            }
            $schedule = array_merge($schedule, $secondHalf);
        }

        $matchesToInsert = [];
        foreach ($schedule as $roundIdx => $roundMatches) {
            foreach ($roundMatches as $match) {
                // $match = [TeamHome, TeamAway]
                if ($match[0] === null || $match[1] === null) continue; // Descansa (Bye)
                
                $matchesToInsert[] = [
                    'league_id'    => $id,
                    'home_team_id' => $match[0]['id'],
                    'away_team_id' => $match[1]['id'],
                    'stadium_id'   => null,
                    'referee_id'   => null,
                    'match_date'   => null,
                    'match_time'   => null,
                    'status'       => 'unscheduled',
                    'round_number' => $roundIdx + 1
                ];
            }
        }

        // Insertar en BD
        $created = 0;
        foreach ($matchesToInsert as $m) {
            if ($this->matchModel->create($m)) {
                $created++;
            }
        }

        writeLog('INFO', "Sorteo Fixture Base generado: {$league['name']}, Modo: {$mode}, {$created} partidos base.");
        $this->setFlash('success', "¡Sorteo Base Exitoso! Se enfrentaron las llaves y se han estructurado {$created} encuentros listos para programación logística.");
        $this->redirect("/encuentros");
    }

    private function generateBergerBracket(array $teams): array
    {
        $n = count($teams);
        if ($n % 2 !== 0) {
            $teams[] = null; // Dummy team 'Bye'
            $n++;
        }

        $rounds = [];
        $half = $n / 2;
        $teamIndexes = array_keys($teams);
        // Quitar el primer elemento (el pivot)
        $pivot = array_shift($teamIndexes);

        for ($r = 0; $r < $n - 1; $r++) {
            $roundMatchups = [];
            // Primer partido con el pivot
            $t1 = $teams[$pivot];
            $t2 = $teams[$teamIndexes[0]];
            
            // Alternar localía del pivot
            if ($r % 2 === 0) {
                $roundMatchups[] = [$t1, $t2];
            } else {
                $roundMatchups[] = [$t2, $t1];
            }

            // Resto de los partidos
            for ($i = 1; $i < $half; $i++) {
                $idx1 = $i;
                $idx2 = $n - 1 - $i;
                
                $ta = $teams[$teamIndexes[$idx1]];
                $tb = $teams[$teamIndexes[$idx2]];
                
                // Balancear las otras localías
                if ($i % 2 === 1) {
                    $roundMatchups[] = [$ta, $tb];
                } else {
                    $roundMatchups[] = [$tb, $ta];
                }
            }
            $rounds[] = $roundMatchups;

            // Rotar (Robin) - el último elemento va al principio del array móvil
            $last = array_pop($teamIndexes);
            array_unshift($teamIndexes, $last);
        }

        return $rounds;
    }
}
