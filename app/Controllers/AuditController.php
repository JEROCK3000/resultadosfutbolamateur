<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';
require_once BASE_PATH . '/app/Models/UserModel.php';

/**
 * AuditController.php — Módulo de Auditoría (solo admin)
 */
class AuditController extends Controller
{
    private AuditModel $model;
    private UserModel  $userModel;

    public function __construct()
    {
        $this->model     = new AuditModel();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $this->requireRole('admin');

        $filters = [
            'user_id'   => filter_input(INPUT_GET, 'user_id',   FILTER_VALIDATE_INT) ?: '',
            'action'    => trim($_GET['action']    ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to']   ?? ''),
        ];

        $logs         = $this->model->getFiltered($filters);
        $usersWithLog = $this->model->getUsersWithLogs();

        $actions = ['login','logout','create','update','delete'];

        ob_start();
        require BASE_PATH . '/app/Views/audit/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Auditoría', 'content' => $content]);
    }
}
