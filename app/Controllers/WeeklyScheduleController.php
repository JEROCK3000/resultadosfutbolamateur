<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/RefereeModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';

class WeeklyScheduleController extends Controller
{
    private LeagueModel  $leagueModel;
    private StadiumModel $stadiumModel;
    private RefereeModel $refereeModel;
    private MatchModel   $matchModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->leagueModel  = new LeagueModel();
        $this->stadiumModel = new StadiumModel();
        $this->refereeModel = new RefereeModel();
        $this->matchModel   = new MatchModel();
    }

    public function index(): void
    {
        $leagues = $this->leagueModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/weekly_schedule/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Programación Semanal', 'content' => $content]);
    }

    public function show(string $leagueId): void
    {
        $id = (int) $leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) {
            $this->redirect('/programacion');
            return;
        }

        $allMatches = $this->matchModel->getByLeague($id);
        $rounds = [];
        foreach ($allMatches as $m) {
            $r = (int)($m['round_number'] ?? 0);
            if ($r > 0) {
                if (!isset($rounds[$r])) $rounds[$r] = [];
                $rounds[$r][] = $m;
            }
        }
        ksort($rounds);

        $selectedRound  = filter_input(INPUT_GET, 'fecha', FILTER_VALIDATE_INT) ?: (array_key_first($rounds) ?? 1);
        $matchesInRound = $rounds[$selectedRound] ?? [];
        $stadiums       = $this->stadiumModel->getAll();
        $referees       = $this->refereeModel->getAll();

        ob_start();
        require BASE_PATH . '/app/Views/weekly_schedule/show.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => "Programación Fecha {$selectedRound}", 'content' => $content]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Sorteo logístico automático
    // ─────────────────────────────────────────────────────────────────────────

    public function store(string $leagueId): void
    {
        $this->requireMethod('POST');
        $id     = (int)$leagueId;
        $league = $this->leagueModel->getById($id);

        if (!$league || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_league'] != $league['id'])) {
            $this->setFlash('danger', 'No permitido.');
            $this->redirect('/ligas');
            return;
        }

        $roundNum      = filter_input(INPUT_POST, 'round_number', FILTER_VALIDATE_INT);
        $startDateStr  = filter_input(INPUT_POST, 'start_date');
        $endDateStr    = filter_input(INPUT_POST, 'end_date') ?: $startDateStr;
        $playDays      = $_POST['play_days'] ?? [];
        $stadiumsIn    = array_map('intval', $_POST['stadiums'] ?? []);
        $refereesIn    = array_map('intval', $_POST['referees'] ?? []);
        $timesStr      = filter_input(INPUT_POST, 'play_times') ?: '10:00, 12:00, 14:00, 16:00';
        $refereeMode   = filter_input(INPUT_POST, 'referee_mode') ?: 'random'; // 'equitable' | 'random'

        // Garantizar que end_date no sea anterior a start_date
        if ($endDateStr < $startDateStr) $endDateStr = $startDateStr;

        if (!$roundNum || empty($startDateStr) || empty($playDays) || empty($stadiumsIn)) {
            $this->setFlash('danger', 'Debe seleccionar día de inicio, días de juego y al menos un estadio.');
            $this->redirect("/programacion/{$id}?fecha={$roundNum}");
            return;
        }

        $timesArray = array_values(array_filter(array_map('trim', explode(',', $timesStr))));
        if (empty($timesArray)) $timesArray = ['10:00'];

        $allMatches        = $this->matchModel->getByLeague($id);
        $matchesToSchedule = array_values(array_filter(
            $allMatches,
            fn($m) => $m['round_number'] == $roundNum && $m['status'] === 'unscheduled'
        ));

        if (empty($matchesToSchedule)) {
            $this->setFlash('warning', 'No hay partidos por programar en esta jornada.');
            $this->redirect("/programacion/{$id}?fecha={$roundNum}");
            return;
        }

        shuffle($matchesToSchedule);

        // Cargar estado acumulado desde la BD
        $teamHistory          = $this->matchModel->getTeamSlotHistory($id);
        $teamOccupied         = $this->matchModel->getTeamOccupiedDates($id);
        $occupiedStadiumSlots = $this->matchModel->getOccupiedStadiumSlots($id);

        // Historial de árbitros solo si se pidió rotación equitativa
        $refereeHistory = ($refereeMode === 'equitable' && !empty($refereesIn))
            ? $this->matchModel->getRefereeSlotHistory($id)
            : [];

        $slotPool = $this->buildSlotPool(
            $startDateStr, $endDateStr, $playDays, $timesArray, $stadiumsIn,
            $occupiedStadiumSlots
        );

        if (empty($slotPool)) {
            $this->setFlash('danger', 'No se encontraron slots disponibles. Verifica estadios y fechas seleccionadas.');
            $this->redirect("/programacion/{$id}?fecha={$roundNum}");
            return;
        }

        $scheduledCount = 0;

        foreach ($matchesToSchedule as $m) {
            $homeId = (int)$m['home_team_id'];
            $awayId = (int)$m['away_team_id'];

            $bestIdx = $this->pickBestSlot($slotPool, $homeId, $awayId, $teamHistory, $teamOccupied);

            if ($bestIdx === null) continue;

            $slot = $slotPool[$bestIdx];
            unset($slotPool[$bestIdx]);

            // Asignar árbitro según el modo elegido
            $refereeId = null;
            if (!empty($refereesIn)) {
                $refereeId = ($refereeMode === 'equitable')
                    ? $this->pickBestReferee($refereesIn, $slot, $refereeHistory)
                    : $refereesIn[array_rand($refereesIn)];
            }

            $m['stadium_id'] = $slot['stadium_id'];
            $m['match_date'] = $slot['date'];
            $m['match_time'] = $slot['time'] . ':00';
            $m['referee_id'] = $refereeId;
            $m['status']     = 'scheduled';

            try {
                $updated = $this->matchModel->update((int)$m['id'], $m);
            } catch (\PDOException $e) {
                writeLog('WARNING', "Slot duplicado al programar partido ID {$m['id']}: " . $e->getMessage());
                $updated = false;
            }

            if ($updated) {
                $scheduledCount++;

                // Actualizar historial en memoria para los siguientes partidos del loop
                $key = $slot['dow'] . '_' . $slot['time'];
                $teamHistory[$homeId][$key]     = ($teamHistory[$homeId][$key] ?? 0) + 1;
                $teamHistory[$awayId][$key]     = ($teamHistory[$awayId][$key] ?? 0) + 1;
                $teamOccupied[$homeId][$slot['date']] = true;
                $teamOccupied[$awayId][$slot['date']] = true;

                if ($refereeId && $refereeMode === 'equitable') {
                    $refereeHistory[$refereeId][$key] = ($refereeHistory[$refereeId][$key] ?? 0) + 1;
                }
            }
        }

        $total   = count($matchesToSchedule);
        $skipped = $total - $scheduledCount;
        $msg     = "¡Sorteo logístico completado! {$scheduledCount} de {$total} partidos programados en la Fecha {$roundNum}.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} no asignados por conflictos — revísalos manualmente)";
        }

        writeLog('INFO', "Sorteo logístico: Liga {$id}, Fecha {$roundNum}, {$scheduledCount}/{$total} programados.");
        $this->setFlash($skipped > 0 ? 'warning' : 'success', $msg);
        $this->redirect("/programacion/{$id}?fecha={$roundNum}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Edición manual de un partido ya programado
    // ─────────────────────────────────────────────────────────────────────────

    public function updateMatch(string $leagueId, string $matchId): void
    {
        $this->requireMethod('POST');
        $lid = (int)$leagueId;
        $mid = (int)$matchId;

        $match = $this->matchModel->getById($mid);
        if (!$match || (int)$match['league_id'] !== $lid) {
            $this->setFlash('danger', 'Partido no encontrado.');
            $this->redirect("/programacion/{$lid}");
            return;
        }

        if (!$this->canManageLeague($lid)) {
            $this->setFlash('danger', 'No tienes permisos para modificar este partido.');
            $this->redirect("/programacion/{$lid}");
            return;
        }

        $matchDate = filter_input(INPUT_POST, 'match_date') ?: null;
        $matchTime = trim(filter_input(INPUT_POST, 'match_time') ?? '');
        $stadiumId = filter_input(INPUT_POST, 'stadium_id', FILTER_VALIDATE_INT) ?: null;
        $refereeId = filter_input(INPUT_POST, 'referee_id', FILTER_VALIDATE_INT) ?: null;
        $roundNum  = (int)$match['round_number'];

        // HTML time input da "HH:MM" — BD necesita "HH:MM:SS"
        if ($matchTime && strlen($matchTime) === 5) {
            $matchTime .= ':00';
        }

        // Validar conflicto de equipo (ninguno de los dos puede tener otro partido ese día)
        if ($matchDate) {
            $homeConflict = $this->matchModel->hasTeamConflict((int)$match['home_team_id'], $matchDate, $mid);
            $awayConflict = $this->matchModel->hasTeamConflict((int)$match['away_team_id'], $matchDate, $mid);

            if ($homeConflict || $awayConflict) {
                $team = $homeConflict ? $match['home_team'] : $match['away_team'];
                $this->setFlash('danger', "Conflicto: «{$team}» ya tiene otro partido programado el " . date('d/m/Y', strtotime($matchDate)) . '.');
                $this->redirect("/programacion/{$lid}?fecha={$roundNum}");
                return;
            }
        }

        // Validar conflicto de estadio (mismo estadio+fecha+hora ya ocupados)
        if ($stadiumId && $matchDate && $matchTime) {
            if ($this->matchModel->hasStadiumConflict($stadiumId, $matchDate, $matchTime, $mid)) {
                $this->setFlash('danger', 'Ese estadio ya tiene un partido asignado en esa fecha y hora.');
                $this->redirect("/programacion/{$lid}?fecha={$roundNum}");
                return;
            }
        }

        // Actualizar manteniendo todos los campos del partido
        $updateData                = $match;
        $updateData['match_date']  = $matchDate;
        $updateData['match_time']  = $matchTime ?: null;
        $updateData['stadium_id']  = $stadiumId;
        $updateData['referee_id']  = $refereeId;
        $updateData['status']      = 'scheduled';

        if ($this->matchModel->update($mid, $updateData)) {
            $this->setFlash('success', 'Partido actualizado correctamente.');
        } else {
            $this->setFlash('danger', 'Error al guardar los cambios.');
        }

        $this->redirect("/programacion/{$lid}?fecha={$roundNum}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Algoritmo de equidad
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Elige el índice del slot más equitativo del pool para un partido dado.
     * Score = veces que cada equipo ha jugado en ese día+hora acumuladas en el campeonato.
     * Restricción dura: ningún equipo puede tener dos partidos el mismo día.
     */
    private function pickBestSlot(
        array $slotPool,
        int   $homeId,
        int   $awayId,
        array $teamHistory,
        array $teamOccupied
    ): ?int {
        $bestIdx   = null;
        $bestScore = PHP_INT_MAX;

        foreach ($slotPool as $idx => $slot) {
            if (
                isset($teamOccupied[$homeId][$slot['date']]) ||
                isset($teamOccupied[$awayId][$slot['date']])
            ) {
                continue;
            }

            $key   = $slot['dow'] . '_' . $slot['time'];
            $score = ($teamHistory[$homeId][$key] ?? 0) + ($teamHistory[$awayId][$key] ?? 0);
            $score += mt_rand(0, 99) / 1000.0; // tiebreaker suave

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestIdx   = $idx;
            }
        }

        return $bestIdx;
    }

    /**
     * Elige el árbitro con menor carga acumulada en el día+hora del slot dado.
     * Tiebreaker aleatorio suave para variedad.
     */
    private function pickBestReferee(array $refereesIn, array $slot, array $refereeHistory): int
    {
        $bestId    = null;
        $bestScore = PHP_INT_MAX;
        $key       = $slot['dow'] . '_' . $slot['time'];

        foreach ($refereesIn as $rid) {
            $score  = ($refereeHistory[$rid][$key] ?? 0);
            $score += mt_rand(0, 99) / 1000.0;

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestId    = $rid;
            }
        }

        return (int)$bestId;
    }

    /**
     * Genera el pool de slots (fecha × hora × estadio) disponibles entre
     * $startDateStr y $endDateStr (ambas inclusive), solo en los días habilitados.
     * Excluye combinaciones ya reservadas en la BD.
     */
    private function buildSlotPool(
        string $startDateStr,
        string $endDateStr,
        array  $playDays,
        array  $times,
        array  $stadiumIds,
        array  $occupiedStadiumSlots
    ): array {
        $slots   = [];
        $current = new \DateTime($startDateStr);
        $end     = new \DateTime($endDateStr);

        while ($current <= $end) {
            $dow = (int)$current->format('w');

            if (in_array((string)$dow, $playDays, false)) {
                $dateStr = $current->format('Y-m-d');

                foreach ($times as $time) {
                    foreach ($stadiumIds as $stadiumId) {
                        $slotKey = $stadiumId . '_' . $dateStr . '_' . $time;
                        if (!isset($occupiedStadiumSlots[$slotKey])) {
                            $slots[] = [
                                'date'       => $dateStr,
                                'time'       => $time,
                                'stadium_id' => $stadiumId,
                                'dow'        => $dow,
                            ];
                        }
                    }
                }
            }

            $current->modify('+1 day');
        }

        return $slots;
    }

}
