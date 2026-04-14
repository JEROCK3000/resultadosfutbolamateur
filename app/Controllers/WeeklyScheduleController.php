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

        // Obtener todos los partidos del campeonato para extraer las rondas
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

        $selectedRound = filter_input(INPUT_GET, 'fecha', FILTER_VALIDATE_INT) ?: (array_key_first($rounds) ?? 1);
        $matchesInRound = $rounds[$selectedRound] ?? [];
        
        $stadiums = $this->stadiumModel->getAll();
        $referees = $this->refereeModel->getAll();

        ob_start();
        require BASE_PATH . '/app/Views/weekly_schedule/show.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => "Programación Fecha {$selectedRound}", 'content' => $content]);
    }

    public function store(string $leagueId): void
    {
        $this->requireMethod('POST');
        $id = (int)$leagueId;
        $roundNum = filter_input(INPUT_POST, 'round_number', FILTER_VALIDATE_INT);
        $startDateStr = filter_input(INPUT_POST, 'start_date');
        $playDays = $_POST['play_days'] ?? [];
        $stadiumsIn = $_POST['stadiums'] ?? [];
        $refereesIn = $_POST['referees'] ?? [];
        $timesStr = filter_input(INPUT_POST, 'play_times') ?: '10:00, 12:00, 14:00, 16:00';

        if (!$roundNum || empty($startDateStr) || empty($playDays) || empty($stadiumsIn)) {
            $this->setFlash('danger', 'Debe seleccionar día de inicio, días de juego y al menos un estadio.');
            $this->redirect("/programacion/{$id}?fecha={$roundNum}");
            return;
        }

        $allMatches = $this->matchModel->getByLeague($id);
        $matchesToSchedule = [];
        foreach ($allMatches as $m) {
            if ($m['round_number'] == $roundNum && $m['status'] === 'unscheduled') {
                $matchesToSchedule[] = $m;
            }
        }

        if (empty($matchesToSchedule)) {
            $this->setFlash('warning', 'No hay partidos por programar en esta jornada.');
            $this->redirect("/programacion/{$id}?fecha={$roundNum}");
            return;
        }

        // Randomizar los encuentros para asignarlos al azar en la grilla logística
        shuffle($matchesToSchedule);

        $timesArray = array_map('trim', explode(',', $timesStr));
        $timesArray = array_filter($timesArray);
        if (empty($timesArray)) $timesArray = ['10:00'];

        $currentDate = new DateTime($startDateStr);
        $currentDate = $this->getNextValidDay($currentDate, $playDays);

        $stadiumIdx = 0;
        $timeIdx = 0;
        $refereeIdx = 0;
        $scheduledCount = 0;

        foreach ($matchesToSchedule as $m) {
            $success = false;
            $attempts = 0;
            
            while (!$success && $attempts < 50) {
                $stadiumId = (int) $stadiumsIn[$stadiumIdx];
                $timeSlot  = $timesArray[$timeIdx] . ':00';
                $refereeId = !empty($refereesIn) ? (int)$refereesIn[$refereeIdx] : null;

                $m['stadium_id'] = $stadiumId;
                $m['match_time'] = $timeSlot;
                $m['match_date'] = $currentDate->format('Y-m-d');
                $m['referee_id'] = $refereeId;
                $m['status']     = 'scheduled';

                try {
                    if ($this->matchModel->update((int)$m['id'], $m)) {
                        $scheduledCount++;
                        $success = true;
                    } else {
                        // En caso de fallar por otra razón y no lanzar Exception
                        break;
                    }
                } catch (\PDOException $e) {
                    // 23000 = Integrity constraint violation (Duplicate entry uq_stadium_slot)
                    if ($e->getCode() == 23000 || $e->getCode() == 1062) {
                        $success = false;
                    } else {
                        throw $e;
                    }
                }

                // Alternar equitativamente (avanza tanto por éxito como por colisión)
                $stadiumIdx = ($stadiumIdx + 1) % count($stadiumsIn);
                if (!empty($refereesIn)) {
                    $refereeIdx = ($refereeIdx + 1) % count($refereesIn);
                }
                
                if ($stadiumIdx === 0) {
                    // Si ya usamos todos los estadios en este horario, pasamos al siguiente horario
                    $timeIdx++;
                }

                if ($timeIdx >= count($timesArray)) {
                    // Si se acabaron los horarios, pasamos al siguiente día
                    $timeIdx = 0;
                    $currentDate->modify('+1 day');
                    $currentDate = $this->getNextValidDay($currentDate, $playDays);
                }
                
                $attempts++;
            }
        }

        $this->setFlash('success', "¡Sorteo logístico completado! Se programaron exitosamente {$scheduledCount} partidos para la Fecha {$roundNum}.");
        $this->redirect("/programacion/{$id}?fecha={$roundNum}");
    }

    private function getNextValidDay(DateTime $date, array $allowedDays): DateTime
    {
        while (!in_array($date->format('w'), $allowedDays)) {
            $date->modify('+1 day');
        }
        return clone $date;
    }
}
