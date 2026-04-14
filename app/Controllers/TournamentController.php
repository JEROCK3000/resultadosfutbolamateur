<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/TournamentModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Controllers/StandingsController.php';

/**
 * TournamentController.php — Fases Finales
 * Dos modos: Estándar (1°vs2°) y Cruzado (1°vsÚltimo, 2°vsPenúltimo...)
 */
class TournamentController extends Controller
{
    private TournamentModel  $model;
    private LeagueModel      $leagueModel;
    private StandingsController $standingsCtrl;

    public function __construct()
    {
        $this->requireAuth();
        $this->model         = new TournamentModel();
        $this->leagueModel   = new LeagueModel();
        $this->standingsCtrl = new StandingsController();
    }

    /** GET /torneos — Listado de torneos */
    public function index(): void
    {
        $tournaments = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/tournaments/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Fases Finales', 'content' => $content]);
    }

    /** GET /torneos/crear/{league_id} — Formulario de creación */
    public function create(string $leagueId): void
    {
        $league    = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger', 'Campeonato no encontrado.'); $this->redirect('/torneos'); return; }
        $standings = $this->standingsCtrl->getStandingsPublic((int) $leagueId);

        ob_start();
        require BASE_PATH . '/app/Views/tournaments/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nueva Fase Final', 'content' => $content]);
    }

    /** POST /torneos/generar — Generar llaves */
    public function generate(): void
    {
        $this->requireMethod('POST');
        $league_id = filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $name      = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $type      = in_array($_POST['type'] ?? '', ['knockout','seeded','random']) ? $_POST['type'] : 'knockout';
        $teams_in  = filter_input(INPUT_POST, 'teams_in', FILTER_VALIDATE_INT) ?: 8;

        if (!$league_id || empty($name)) {
            $this->setFlash('danger', 'Campeonato y nombre son obligatorios.');
            $this->redirect("/torneos/crear/{$league_id}");
            return;
        }

        $standings = $this->standingsCtrl->getStandingsPublic($league_id);
        $available = count($standings);

        if ($available < 2) {
            $this->setFlash('warning', 'Se necesitan al menos 2 equipos con partidos jugados para generar una fase final.');
            $this->redirect("/torneos/crear/{$league_id}");
            return;
        }

        // Limitar equipos a los disponibles
        $teams_in = min($teams_in, $available);
        // Asegurar potencia de 2: 2,4,8,16,32
        $validSizes = [2, 4, 8, 16, 32];
        foreach (array_reverse($validSizes) as $size) {
            if ($teams_in >= $size) { $teams_in = $size; break; }
        }

        // Crear torneo
        $tournamentId = $this->model->create([
            'league_id' => $league_id,
            'name'      => $name,
            'type'      => $type,
        ]);

        // Seleccionar equipos
        $selectedTeams = array_slice($standings, 0, $teams_in);

        // Configurar cruces según tipo
        if ($type === 'seeded') {
            // Cruzado: 1° vs último, 2° vs penúltimo...
            $pairs = [];
            $n = count($selectedTeams);
            for ($i = 0; $i < intdiv($n, 2); $i++) {
                $pairs[] = [$selectedTeams[$i], $selectedTeams[$n - 1 - $i], $i+1, $n-$i];
            }
        } elseif ($type === 'random') {
            // Sorteo Aleatorio: shuffle del array
            shuffle($selectedTeams);
            $pairs = [];
            for ($i = 0; $i < count($selectedTeams); $i += 2) {
                if (isset($selectedTeams[$i + 1])) {
                    $pairs[] = [$selectedTeams[$i], $selectedTeams[$i+1], null, null];
                }
            }
        } else {
            // Estándar: 1°vs2°, 3°vs4°...
            $pairs = [];
            for ($i = 0; $i < count($selectedTeams); $i += 2) {
                if (isset($selectedTeams[$i + 1])) {
                    $pairs[] = [$selectedTeams[$i], $selectedTeams[$i+1], $i+1, $i+2];
                }
            }
        }

        // Determinar nombre de la ronda inicial
        $roundName = match($teams_in) {
            16 => 'Octavos de Final',
            8  => 'Cuartos de Final',
            4  => 'Semifinal',
            2  => 'Final',
            default => 'Ronda Inicial',
        };

        $roundId = $this->model->createRound($tournamentId, $roundName, 1);
        foreach ($pairs as [$home, $away, $posH, $posA]) {
            $this->model->createTournamentMatch($roundId, $home['id'], $away['id'], $posH, $posA);
        }

        // Crear estructura de rondas siguientes vacías
        $nextRounds = match($teams_in) {
            16 => [['Cuartos de Final',2],['Semifinal',3],['Final',4],['Tercer y Cuarto Lugar',5]],
            8  => [['Semifinal',2],['Final',3],['Tercer y Cuarto Lugar',4]],
            4  => [['Final',2],['Tercer y Cuarto Lugar',3]],
            default => [],
        };
        foreach ($nextRounds as [$rName, $rOrder]) {
            $nRid = $this->model->createRound($tournamentId, $rName, $rOrder);
            
            // Cantidad de encuentros a pre-crear en la ronda
            $matchCount = match($rName) {
                'Tercer y Cuarto Lugar', 'Final' => 1,
                'Semifinal'        => 2,
                'Cuartos de Final' => 4,
                default            => 0,
            };
            
            for ($i = 0; $i < $matchCount; $i++) {
                $this->model->createTournamentMatch($nRid, null, null, null, null);
            }
        }

        writeLog('INFO', "Fase final generada: {$name} — Torneo ID {$tournamentId} — Tipo: {$type}");
        $this->setFlash('success', "Fase final «{$name}» generada exitosamente con {$teams_in} equipos.");
        $this->redirect("/torneos/{$tournamentId}/llave");
    }

    /** GET /torneos/{id}/llave — Bracket visual */
    public function bracket(string $id): void
    {
        $tournament = $this->model->getById((int) $id);
        if (!$tournament) { $this->setFlash('danger', 'Torneo no encontrado.'); $this->redirect('/torneos'); return; }

        $rounds = $this->model->getBracket((int) $id);

        ob_start();
        require BASE_PATH . '/app/Views/tournaments/bracket.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Llave — ' . $tournament['name'],
            'content'   => $content,
        ]);
    }

    /** POST /torneos/marcador/{id} — Guarda el marcador y avanza equipos */
    public function saveScore(string $matchId): void
    {
        $this->requireAuth();
        $this->requireMethod('POST');
        
        $matchId = (int) $matchId;
        $homeGoals = filter_input(INPUT_POST, 'home_goals', FILTER_VALIDATE_INT);
        $awayGoals = filter_input(INPUT_POST, 'away_goals', FILTER_VALIDATE_INT);
        
        if ($homeGoals === false || $awayGoals === false || $homeGoals < 0 || $awayGoals < 0) {
            $this->setFlash('danger', 'Marcador inválido.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/torneos');
            return;
        }

        $matchInfo = $this->model->getTournamentMatchInfo($matchId);
        if (!$matchInfo) {
            $this->setFlash('danger', 'Cruce no encontrado.');
            $this->redirect('/torneos');
            return;
        }
        $tournamentId = $matchInfo['tournament_id'];
        
        $tournament = $this->model->getById($tournamentId);
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_league'] != $tournament['league_id']) {
            $this->setFlash('danger', 'No autorizado.');
            $this->redirect("/torneos/{$tournamentId}/llave");
            return;
        }

        if ($homeGoals === $awayGoals) {
            $this->setFlash('warning', 'En fases eliminatorias directas no hay empates (usar global o penales).');
            $this->redirect("/torneos/{$tournamentId}/llave");
            return;
        }

        $this->model->saveMatchScore($matchId, $homeGoals, $awayGoals);

        $winnerId = $homeGoals > $awayGoals ? $matchInfo['home_team_id'] : $matchInfo['away_team_id'];
        $loserId  = $homeGoals > $awayGoals ? $matchInfo['away_team_id'] : $matchInfo['home_team_id'];

        $rounds = $this->model->getBracket($tournamentId);
        
        $currentRoundIndex = -1;
        $matchIndexInRound = -1;
        foreach ($rounds as $rIdx => $rRound) {
            if ($rRound['id'] == $matchInfo['round_id']) {
                $currentRoundIndex = $rIdx;
                foreach ($rRound['matches'] as $mIdx => $mObj) {
                    if ($mObj['id'] == $matchId) {
                        $matchIndexInRound = $mIdx;
                        break;
                    }
                }
                break;
            }
        }
        
        if ($currentRoundIndex !== -1 && isset($rounds[$currentRoundIndex + 1])) {
            $nextRound = $rounds[$currentRoundIndex + 1];
            $targetIndex = (int) floor($matchIndexInRound / 2);
            $targetSide  = ($matchIndexInRound % 2 === 0) ? 'home' : 'away';

            // Semifinal -> Perdedor va a 3er Lugar (que es N+2)
            if ($matchInfo['round_name'] === 'Semifinal' && isset($rounds[$currentRoundIndex + 2])) {
                $thirdPlaceRound = $rounds[$currentRoundIndex + 2];
                if (isset($thirdPlaceRound['matches'][0])) {
                    $this->model->updateMatchTeam((int)$thirdPlaceRound['matches'][0]['id'], $targetSide, (int)$loserId);
                }
            }
            
            if (isset($nextRound['matches'][$targetIndex])) {
                $this->model->updateMatchTeam((int)$nextRound['matches'][$targetIndex]['id'], $targetSide, (int)$winnerId);
            }
        }

        writeLog('INFO', "Marcador guardado en llave: Torneo {$tournamentId}, Cruce {$matchId} ({$homeGoals}-{$awayGoals})");
        $this->setFlash('success', 'Marcador guardado y equipo promovido automáticamente.');
        $this->redirect("/torneos/{$tournamentId}/llave");
    }

    /** POST /torneos/eliminar/{id} — Eliminar torneo */
    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;
        $t  = $this->model->getById($id);
        if (!$t) { $this->setFlash('danger', 'Torneo no encontrado.'); $this->redirect('/torneos'); return; }
        $this->model->delete($id);
        writeLog('INFO', "Torneo eliminado: ID {$id} - {$t['name']}");
        $this->setFlash('success', "Torneo «{$t['name']}» eliminado.");
        $this->redirect('/torneos');
    }
}
