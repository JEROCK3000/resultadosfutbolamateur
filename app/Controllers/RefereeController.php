<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/RefereeModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

class RefereeController extends Controller
{
    private RefereeModel $model;

    public function __construct()
    {
        $this->requireAuth();
        // Los registradores también pueden ver/usar árbitros, pero la gestión pesada
        // podría ser de admin. Dejémoslo abierto a todos por simplicidad,
        // o proteger create/update a requireRole('admin') si se desea.
        $this->model = new RefereeModel();
    }

    public function index(): void
    {
        $referees = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/referees/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Árbitros — Gestión',
            'content'   => $content,
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        ob_start();
        require BASE_PATH . '/app/Views/referees/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Nuevo Árbitro',
            'content'   => $content,
        ]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');

        $data = [
            'name'    => filter_input(INPUT_POST, 'name'),
            'license' => filter_input(INPUT_POST, 'license'),
            'phone'   => filter_input(INPUT_POST, 'phone'),
            'status'  => filter_input(INPUT_POST, 'status') ?? 'active',
        ];

        if (empty(trim((string)$data['name']))) {
            $this->setFlash('danger', 'El nombre es obligatorio.');
            $this->redirect('/arbitros/crear');
            return;
        }

        if ($this->model->create($data)) {
            $id = $this->model->getLastInsertId();
            AuditModel::log('referee_created', "Árbitro creado exitosamente: ID {$id} - {$data['name']}");
            $this->setFlash('success', 'Árbitro registrado exitosamente.');
            $this->redirect('/arbitros');
        } else {
            $this->setFlash('danger', 'Error al registrar.');
            $this->redirect('/arbitros/crear');
        }
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $id = (int) $id;
        $referee = $this->model->getById($id);

        if (!$referee) {
            $this->setFlash('danger', 'Árbitro no encontrado.');
            $this->redirect('/arbitros');
            return;
        }

        ob_start();
        require BASE_PATH . '/app/Views/referees/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', [
            'pageTitle' => 'Editar Árbitro',
            'content'   => $content,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $id = (int) $id;

        $data = [
            'name'    => filter_input(INPUT_POST, 'name'),
            'license' => filter_input(INPUT_POST, 'license'),
            'phone'   => filter_input(INPUT_POST, 'phone'),
            'status'  => filter_input(INPUT_POST, 'status') ?? 'active',
        ];

        if (empty(trim((string)$data['name']))) {
            $this->setFlash('danger', 'El nombre es obligatorio.');
            $this->redirect("/arbitros/editar/{$id}");
            return;
        }

        if ($this->model->update($id, $data)) {
            AuditModel::log('referee_updated', "Árbitro editado: ID {$id} - {$data['name']}");
            $this->setFlash('success', 'Árbitro actualizado exitosamente.');
            $this->redirect('/arbitros');
        } else {
            $this->setFlash('danger', 'Error al actualizar el árbitro.');
            $this->redirect("/arbitros/editar/{$id}");
        }
    }

    public function destroy(string $id): void
    {
        $this->requireRole('admin');
        $this->requireMethod('POST');
        $id = (int) $id;

        $r = $this->model->getById($id);
        if (!$r) {
            $this->setFlash('danger', 'Árbitro no encontrado.');
            $this->redirect('/arbitros');
            return;
        }

        $this->model->delete($id);
        AuditModel::log('referee_deleted', "Árbitro eliminado: ID {$id} - {$r['name']}");
        $this->setFlash('success', "Árbitro {$r['name']} eliminado.");
        $this->redirect('/arbitros');
    }
}
