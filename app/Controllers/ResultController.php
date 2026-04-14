<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Models/MatchResultModel.php';
require_once BASE_PATH . '/app/Models/MatchEventModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';

/**
 * ResultController.php — Controlador de Resultados
 * Permite registrar goles y tarjetas de un encuentro y actualizar el marcador.
 */
class ResultController extends Controller
{
    private MatchModel       $matchModel;
    private MatchResultModel $resultModel;
    private MatchEventModel  $eventModel;
    private TeamModel        $teamModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->matchModel  = new MatchModel();
        $this->resultModel = new MatchResultModel();
        $this->eventModel  = new MatchEventModel();
        $this->teamModel   = new TeamModel();
    }

    /** GET /resultados/{match_id} — Panel de resultado del partido */
    public function index(string $matchId): void
    {
        $match = $this->matchModel->getById((int) $matchId);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros');
            return;
        }

        $result = $this->resultModel->getByMatch((int) $matchId);
        $events = $this->eventModel->getByMatch((int) $matchId);

        // Equipos disponibles para el formulario de eventos
        $homeTeam = ['id' => $match['home_team_id'], 'name' => $match['home_team']];
        $awayTeam = ['id' => $match['away_team_id'], 'name' => $match['away_team']];

        // Separar eventos por tipo
        $goals        = array_filter($events, fn($e) => $e['event_type'] === 'goal');
        $yellowCards  = array_filter($events, fn($e) => $e['event_type'] === 'yellow_card');
        $redCards     = array_filter($events, fn($e) => $e['event_type'] === 'red_card');

        ob_start();
        require BASE_PATH . '/app/Views/results/index.php';
        $content = ob_get_clean();

        $this->view('layouts/app', [
            'pageTitle' => 'Resultado — ' . $match['home_team'] . ' vs ' . $match['away_team'],
            'content'   => $content,
        ]);
    }

    /** POST /resultados/guardar — Guardar/actualizar marcador */
    public function store(): void
    {
        $this->requireMethod('POST');
        $match_id   = filter_input(INPUT_POST, 'match_id',   FILTER_VALIDATE_INT);
        $home_goals = filter_input(INPUT_POST, 'home_goals', FILTER_VALIDATE_INT);
        $away_goals = filter_input(INPUT_POST, 'away_goals', FILTER_VALIDATE_INT);
        $home_yc = filter_input(INPUT_POST, 'home_yellow_cards', FILTER_VALIDATE_INT) ?: 0;
        $away_yc = filter_input(INPUT_POST, 'away_yellow_cards', FILTER_VALIDATE_INT) ?: 0;
        $home_rc = filter_input(INPUT_POST, 'home_red_cards', FILTER_VALIDATE_INT) ?: 0;
        $away_rc = filter_input(INPUT_POST, 'away_red_cards', FILTER_VALIDATE_INT) ?: 0;

        if (!$match_id || $home_goals === false || $away_goals === false) {
            $this->setFlash('danger', 'Datos inválidos para guardar el resultado.');
            $this->redirect('/encuentros');
            return;
        }

        // Guardar resultado
        $this->resultModel->upsert($match_id, (int)$home_goals, (int)$away_goals, (int)$home_yc, (int)$away_yc, (int)$home_rc, (int)$away_rc);

        // Marcar partido como finalizado
        $match = $this->matchModel->getById($match_id);
        if ($match) {
            $this->matchModel->update($match_id, array_merge($match, ['status' => 'finished']));
        }

        writeLog('INFO', "Resultado registrado: partido ID {$match_id} — {$home_goals}:{$away_goals}");
        $this->setFlash('success', 'Resultado guardado exitosamente.');
        $this->redirect("/resultados/{$match_id}");
    }

    /** POST /resultados/evento/guardar — Agregar evento (gol, tarjeta) */
    public function storeEvent(): void
    {
        $this->requireMethod('POST');
        $match_id    = filter_input(INPUT_POST, 'match_id',    FILTER_VALIDATE_INT);
        $team_id     = filter_input(INPUT_POST, 'team_id',     FILTER_VALIDATE_INT);
        $player_name = trim(filter_input(INPUT_POST, 'player_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $event_type  = $_POST['event_type'] ?? '';
        $minute      = filter_input(INPUT_POST, 'minute', FILTER_VALIDATE_INT) ?: null;

        $validTypes = ['goal', 'yellow_card', 'red_card'];
        if (!$match_id || !$team_id || !in_array($event_type, $validTypes)) {
            $this->setFlash('danger', 'Datos inválidos para el evento.');
            $this->redirect("/resultados/{$match_id}");
            return;
        }

        $created = $this->eventModel->create(compact('match_id', 'team_id', 'player_name', 'event_type', 'minute'));

        if ($created) {
            $labels = ['goal' => 'Gol', 'yellow_card' => 'Tarjeta amarilla', 'red_card' => 'Tarjeta roja'];
            writeLog('INFO', "Evento registrado: {$labels[$event_type]} en partido ID {$match_id}");
            $this->setFlash('success', $labels[$event_type] . ' registrado correctamente.');
        } else {
            $this->setFlash('danger', 'Error al registrar el evento.');
        }

        $this->redirect("/resultados/{$match_id}");
    }

    /** POST /resultados/evento/eliminar/{id} — Eliminar evento */
    public function destroyEvent(string $id): void
    {
        $this->requireMethod('POST');
        $event = $this->eventModel->getById((int) $id);
        if (!$event) {
            $this->setFlash('danger', 'Evento no encontrado.');
            $this->redirect('/encuentros');
            return;
        }
        $matchId = $event['match_id'];
        $this->eventModel->delete((int) $id);
        writeLog('INFO', "Evento eliminado: ID {$id}");
        $this->setFlash('success', 'Evento eliminado.');
        $this->redirect("/resultados/{$matchId}");
    }
}
