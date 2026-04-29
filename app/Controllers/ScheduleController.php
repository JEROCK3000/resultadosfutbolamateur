<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';

/**
 * ScheduleController.php — Generador de Fixture Base (Round-Robin Berger)
 */
class ScheduleController extends Controller
{
    private LeagueModel  $leagueModel;
    private TeamModel    $teamModel;
    private MatchModel   $matchModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->leagueModel = new LeagueModel();
        $this->teamModel   = new TeamModel();
        $this->matchModel  = new MatchModel();
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

        if (!$this->canManageLeague($id)) {
            $this->setFlash('danger', 'No tienes permisos para este campeonato.');
            $this->redirect('/ligas');
            return;
        }

        $teams         = $this->teamModel->getByLeague($id);
        $existingCount = $this->matchModel->countByLeague($id);

        if (count($teams) < 2) {
            $this->setFlash('warning', 'Se necesitan al menos 2 equipos para generar un calendario.');
            $this->redirect('/equipos');
            return;
        }

        ob_start();
        require BASE_PATH . '/app/Views/schedule/generate.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Fixture Base — ' . $league['name'],
            'content'   => $content,
        ]);
    }

    /** POST /calendario/generar/{league_id} */
    public function store(string $leagueId): void
    {
        $this->requireMethod('POST');
        $id     = (int) $leagueId;
        $league = $this->leagueModel->getById($id);

        if (!$league || !$this->canManageLeague($id)) {
            $this->setFlash('danger', 'No permitido.');
            $this->redirect('/ligas');
            return;
        }

        $teams = $this->teamModel->getByLeague($id);
        if (count($teams) < 2) {
            $this->setFlash('danger', 'Equipos insuficientes para generar el fixture.');
            $this->redirect("/calendario/generar/{$id}");
            return;
        }

        $existingCount = $this->matchModel->countByLeague($id);
        $doReset       = filter_input(INPUT_POST, 'reset_fixture') === '1';

        // ── Guardia: ya existe un fixture ───────────────────────────────────
        if ($existingCount > 0 && !$doReset) {
            $this->setFlash('danger', "Este campeonato ya tiene {$existingCount} partidos generados. Si deseas regenerar el fixture activa la opción «Resetear y regenerar».");
            $this->redirect("/calendario/generar/{$id}");
            return;
        }

        // ── Resetear si el admin lo confirmó ────────────────────────────────
        if ($existingCount > 0 && $doReset) {
            $deleted = $this->matchModel->deleteAllByLeague($id);
            writeLog('INFO', "Fixture reseteado: Liga {$id} ({$league['name']}), {$deleted} partidos eliminados por " . ($_SESSION['user_name'] ?? 'admin') . '.');
        }

        $mode = filter_input(INPUT_POST, 'mode') ?: '1_vuelta';

        // ── Algoritmo de Berger (Round-Robin) ───────────────────────────────
        shuffle($teams); // Aleatorizar posiciones antes del Berger
        $primeraVuelta       = $this->generateBergerBracket($teams);
        $primeraVueltaRounds = count($primeraVuelta); // cuántas rondas tiene la 1ª vuelta

        $schedule = $primeraVuelta;

        if ($mode === '2_vueltas') {
            $segundaVuelta = [];
            foreach ($primeraVuelta as $round) {
                $revRound = [];
                foreach ($round as $match) {
                    $revRound[] = [$match[1], $match[0]]; // Invertir localía
                }
                $segundaVuelta[] = $revRound;
            }
            $schedule = array_merge($primeraVuelta, $segundaVuelta);
        }

        // ── Insertar en BD ──────────────────────────────────────────────────
        $created = 0;
        foreach ($schedule as $roundIdx => $roundMatches) {
            $vuelta = ($roundIdx < $primeraVueltaRounds) ? 1 : 2;

            foreach ($roundMatches as $match) {
                if ($match[0] === null || $match[1] === null) continue; // Bye

                $data = [
                    'league_id'    => $id,
                    'home_team_id' => $match[0]['id'],
                    'away_team_id' => $match[1]['id'],
                    'stadium_id'   => null,
                    'referee_id'   => null,
                    'match_date'   => null,
                    'match_time'   => null,
                    'status'       => 'unscheduled',
                    'round_number' => $roundIdx + 1,
                    'vuelta'       => $vuelta,
                ];

                if ($this->matchModel->create($data)) $created++;
            }
        }

        $modeLabel = $mode === '2_vueltas' ? '2 Vueltas (Ida y Vuelta)' : '1 Vuelta (Ida)';
        writeLog('INFO', "Fixture generado: {$league['name']}, Modo: {$modeLabel}, {$created} partidos.");
        $this->setFlash('success', "¡Fixture generado exitosamente! {$created} partidos en modalidad {$modeLabel}. Ahora programa cada jornada en «Programación Semanal».");
        $this->redirect("/encuentros/liga/{$id}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Algoritmo de Berger (Round-Robin balanceado)
    // ─────────────────────────────────────────────────────────────────────────

    private function generateBergerBracket(array $teams): array
    {
        $n = count($teams);
        if ($n % 2 !== 0) {
            $teams[] = null; // Dummy "Bye" para número impar
            $n++;
        }

        $rounds      = [];
        $half        = $n / 2;
        $teamIndexes = array_keys($teams);
        $pivot       = array_shift($teamIndexes);

        for ($r = 0; $r < $n - 1; $r++) {
            $roundMatchups = [];

            $t1 = $teams[$pivot];
            $t2 = $teams[$teamIndexes[0]];
            $roundMatchups[] = ($r % 2 === 0) ? [$t1, $t2] : [$t2, $t1];

            for ($i = 1; $i < $half; $i++) {
                $ta = $teams[$teamIndexes[$i]];
                $tb = $teams[$teamIndexes[$n - 1 - $i]];
                $roundMatchups[] = ($i % 2 === 1) ? [$ta, $tb] : [$tb, $ta];
            }

            $rounds[] = $roundMatchups;

            $last = array_pop($teamIndexes);
            array_unshift($teamIndexes, $last);
        }

        return $rounds;
    }
}
