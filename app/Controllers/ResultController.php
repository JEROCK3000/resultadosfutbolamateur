<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Models/MatchResultModel.php';
require_once BASE_PATH . '/app/Models/MatchEventModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';
require_once BASE_PATH . '/app/Models/PlayerModel.php';
require_once BASE_PATH . '/app/Models/SanctionModel.php';

class ResultController extends Controller
{
    private MatchModel       $matchModel;
    private MatchResultModel $resultModel;
    private MatchEventModel  $eventModel;
    private TeamModel        $teamModel;
    private PlayerModel      $playerModel;
    private SanctionModel    $sanctionModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->matchModel    = new MatchModel();
        $this->resultModel   = new MatchResultModel();
        $this->eventModel    = new MatchEventModel();
        $this->teamModel     = new TeamModel();
        $this->playerModel   = new PlayerModel();
        $this->sanctionModel = new SanctionModel();
    }

    public function index(string $matchId): void
    {
        $match = $this->matchModel->getById((int) $matchId);
        if (!$match) {
            $this->setFlash('danger', 'Encuentro no encontrado.');
            $this->redirect('/encuentros'); return;
        }

        $result      = $this->resultModel->getByMatch((int) $matchId);
        $events      = $this->eventModel->getByMatch((int) $matchId);
        $goals       = array_filter($events, fn($e) => $e['event_type'] === 'goal');
        $yellowCards = array_filter($events, fn($e) => $e['event_type'] === 'yellow_card');
        $redCards    = array_filter($events, fn($e) => $e['event_type'] === 'red_card');

        $homePlayers = $this->playerModel->getByTeam((int)$match['home_team_id'], (int)$match['league_id']);
        $awayPlayers = $this->playerModel->getByTeam((int)$match['away_team_id'], (int)$match['league_id']);

        ob_start();
        require BASE_PATH . '/app/Views/results/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Resultado — ' . $match['home_team'] . ' vs ' . $match['away_team'],
            'content'   => $content,
        ]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');
        $match_id   = filter_input(INPUT_POST, 'match_id',   FILTER_VALIDATE_INT);
        $home_goals = filter_input(INPUT_POST, 'home_goals', FILTER_VALIDATE_INT);
        $away_goals = filter_input(INPUT_POST, 'away_goals', FILTER_VALIDATE_INT);
        $home_yc = filter_input(INPUT_POST, 'home_yellow_cards', FILTER_VALIDATE_INT) ?: 0;
        $away_yc = filter_input(INPUT_POST, 'away_yellow_cards', FILTER_VALIDATE_INT) ?: 0;
        $home_rc = filter_input(INPUT_POST, 'home_red_cards',    FILTER_VALIDATE_INT) ?: 0;
        $away_rc = filter_input(INPUT_POST, 'away_red_cards',    FILTER_VALIDATE_INT) ?: 0;

        if (!$match_id || $home_goals === false || $away_goals === false) {
            $this->setFlash('danger', 'Datos inválidos para guardar el resultado.');
            $this->redirect('/encuentros'); return;
        }

        $this->resultModel->upsert($match_id, (int)$home_goals, (int)$away_goals, (int)$home_yc, (int)$away_yc, (int)$home_rc, (int)$away_rc);
        $match = $this->matchModel->getById($match_id);
        if ($match) {
            $this->matchModel->update($match_id, array_merge($match, ['status' => 'finished']));
        }
        $this->setFlash('success', 'Resultado guardado exitosamente.');
        $this->redirect("/resultados/{$match_id}");
    }

    public function storeEvent(): void
    {
        $this->requireMethod('POST');
        $match_id    = filter_input(INPUT_POST, 'match_id',    FILTER_VALIDATE_INT);
        $team_id     = filter_input(INPUT_POST, 'team_id',     FILTER_VALIDATE_INT);
        $player_id   = filter_input(INPUT_POST, 'player_id',   FILTER_VALIDATE_INT) ?: null;
        $player_name = trim(filter_input(INPUT_POST, 'player_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $event_type  = $_POST['event_type'] ?? '';
        $minute      = filter_input(INPUT_POST, 'minute', FILTER_VALIDATE_INT) ?: null;

        $validTypes = ['goal', 'yellow_card', 'red_card'];
        if (!$match_id || !$team_id || !in_array($event_type, $validTypes)) {
            $this->setFlash('danger', 'Datos inválidos para el evento.');
            $this->redirect("/resultados/{$match_id}"); return;
        }

        // Si hay player_id, obtener nombre del jugador
        if ($player_id) {
            $player = $this->playerModel->getById((int)$player_id);
            if ($player) $player_name = $player['name'];
        }

        $created = $this->eventModel->create(compact('match_id','team_id','player_id','player_name','event_type','minute'));

        if ($created && $player_id) {
            $match    = $this->matchModel->getById((int)$match_id);
            $leagueId = (int)($match['league_id'] ?? 0);
            $this->applyAutoSanctions((int)$player_id, $leagueId, (int)$match_id, $event_type);
        }

        $labels = ['goal' => 'Gol', 'yellow_card' => 'Tarjeta amarilla', 'red_card' => 'Tarjeta roja'];
        $this->setFlash('success', $labels[$event_type] . ' registrado correctamente.');
        $this->redirect("/resultados/{$match_id}");
    }

    private function applyAutoSanctions(int $playerId, int $leagueId, int $matchId, string $eventType): void
    {
        if ($eventType === 'yellow_card') {
            // Multa por amarilla
            $this->sanctionModel->create([
                'player_id'   => $playerId,
                'league_id'   => $leagueId,
                'match_id'    => $matchId,
                'type'        => 'auto',
                'reason'      => 'Tarjeta amarilla',
                'matches_qty' => 0,
                'fine_usd'    => 2.00,
            ]);
            // Cada 4 amarillas → 1 partido suspendido
            $yellowCount = $this->sanctionModel->countYellowCards($playerId, $leagueId);
            if ($yellowCount > 0 && $yellowCount % 4 === 0) {
                $this->sanctionModel->create([
                    'player_id'   => $playerId,
                    'league_id'   => $leagueId,
                    'match_id'    => $matchId,
                    'type'        => 'auto',
                    'reason'      => "Acumulación de {$yellowCount} tarjetas amarillas",
                    'matches_qty' => 1,
                    'fine_usd'    => 0.00,
                ]);
            }
        } elseif ($eventType === 'red_card') {
            $this->sanctionModel->create([
                'player_id'   => $playerId,
                'league_id'   => $leagueId,
                'match_id'    => $matchId,
                'type'        => 'auto',
                'reason'      => 'Tarjeta roja directa',
                'matches_qty' => 2,
                'fine_usd'    => 2.50,
            ]);
        }
    }

    public function destroyEvent(string $id): void
    {
        $this->requireMethod('POST');
        $event = $this->eventModel->getById((int) $id);
        if (!$event) {
            $this->setFlash('danger', 'Evento no encontrado.');
            $this->redirect('/encuentros'); return;
        }
        $matchId = $event['match_id'];
        $this->eventModel->delete((int) $id);
        $this->setFlash('success', 'Evento eliminado.');
        $this->redirect("/resultados/{$matchId}");
    }
}
