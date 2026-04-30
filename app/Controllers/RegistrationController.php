<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/RegistrationModel.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/TeamModel.php';

class RegistrationController extends Controller
{
    private RegistrationModel $model;
    private LeagueModel       $leagueModel;
    private TeamModel         $teamModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->model       = new RegistrationModel();
        $this->leagueModel = new LeagueModel();
        $this->teamModel   = new TeamModel();
    }

    /** GET /inscripciones — Admin: lista todas */
    public function index(): void
    {
        $this->requireRole('admin', 'registrador');
        $registrations = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/registrations/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Inscripciones', 'content' => $content]);
    }

    /** GET /inscripciones/solicitar — Team manager: formulario */
    public function create(): void
    {
        $this->requireRole('team_manager');
        $teamId  = (int)($_SESSION['user_team_id'] ?? 0);
        $team    = $this->teamModel->getById($teamId);
        $leagues = $this->leagueModel->getAll();
        $mine    = $this->model->getByTeam($teamId);
        ob_start();
        require BASE_PATH . '/app/Views/registrations/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Solicitar Inscripción', 'content' => $content]);
    }

    /** POST /inscripciones/solicitar */
    public function store(): void
    {
        $this->requireRole('team_manager');
        $this->requireMethod('POST');

        $teamId   = (int)($_SESSION['user_team_id'] ?? 0);
        $leagueId = (int)filter_input(INPUT_POST, 'league_id', FILTER_VALIDATE_INT);
        $notes    = trim($_POST['notes'] ?? '');
        $userId   = $this->currentUser()['id'];

        if (!$leagueId) {
            $this->setFlash('danger', 'Debes seleccionar un campeonato.');
            $this->redirect('/inscripciones/solicitar'); return;
        }
        if ($this->model->exists($teamId, $leagueId)) {
            $this->setFlash('warning', 'Ya existe una solicitud para ese campeonato.');
            $this->redirect('/inscripciones/solicitar'); return;
        }

        $this->model->create(['team_id'=>$teamId,'league_id'=>$leagueId,'notes'=>$notes,'submitted_by'=>$userId]);
        $this->setFlash('success', 'Solicitud enviada. El administrador la revisará pronto.');
        $this->redirect('/inscripciones/solicitar');
    }

    /** POST /inscripciones/aprobar/{id} */
    public function approve(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');
        $notes = trim($_POST['notes'] ?? '');
        $this->model->review((int)$id, 'approved', $this->currentUser()['id'], $notes ?: null);
        $this->setFlash('success', 'Inscripción aprobada.');
        $this->redirect('/inscripciones');
    }

    /** POST /inscripciones/rechazar/{id} */
    public function reject(string $id): void
    {
        $this->requireRole('admin', 'registrador');
        $this->requireMethod('POST');
        $notes = trim($_POST['notes'] ?? '');
        $this->model->review((int)$id, 'rejected', $this->currentUser()['id'], $notes ?: null);
        $this->setFlash('success', 'Inscripción rechazada.');
        $this->redirect('/inscripciones');
    }
}
