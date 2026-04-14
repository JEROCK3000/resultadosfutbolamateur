<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/StadiumModel.php';
require_once BASE_PATH . '/app/Models/AuditModel.php';

/**
 * StadiumController.php — Controlador de Estadios (CRUD completo)
 */
class StadiumController extends Controller
{
    private StadiumModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new StadiumModel();
    }

    /** GET /estadios — Listado */
    public function index(): void
    {
        $stadiums = $this->model->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/stadiums/index.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Estadios', 'content' => $content]);
    }

    /** GET /estadios/crear — Formulario de creación */
    public function create(): void
    {
        ob_start();
        require BASE_PATH . '/app/Views/stadiums/create.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Nuevo Estadio', 'content' => $content]);
    }

    /** POST /estadios/guardar — Procesar creación */
    public function store(): void
    {
        $this->requireMethod('POST');

        $name     = trim(filter_input(INPUT_POST, 'name',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $city     = trim(filter_input(INPUT_POST, 'city',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $country  = trim(filter_input(INPUT_POST, 'country',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: null;

        if (empty($name) || empty($city) || empty($country)) {
            $this->setFlash('danger', 'Nombre, ciudad y país son obligatorios.');
            $this->redirect('/estadios/crear');
            return;
        }

        if ($this->model->existsByName($name)) {
            $this->setFlash('warning', "Ya existe un estadio con el nombre \"{$name}\".");
            $this->redirect('/estadios/crear');
            return;
        }

        $created = $this->model->create(compact('name', 'city', 'country', 'capacity'));

        if ($created) {
            $stadiumId = $this->model->getLastInsertId();
            AuditModel::log($this->currentUser()['id'] ?? 0, 'CREATE', "Estadio creado: {$name}", 'Stadium', $stadiumId);
            writeLog('INFO', "Estadio creado: {$name}");
            $this->setFlash('success', "Estadio «{$name}» creado exitosamente.");
        } else {
            writeLog('ERROR', "Error al crear estadio: {$name}");
            $this->setFlash('danger', 'Ocurrió un error al guardar el estadio.');
        }

        $this->redirect('/estadios');
    }

    /** GET /estadios/editar/{id} — Formulario de edición */
    public function edit(string $id): void
    {
        $stadium = $this->model->getById((int) $id);
        if (!$stadium) {
            $this->setFlash('danger', 'Estadio no encontrado.');
            $this->redirect('/estadios');
            return;
        }

        ob_start();
        require BASE_PATH . '/app/Views/stadiums/edit.php';
        $content = ob_get_clean();
        $this->view('layouts/app', ['pageTitle' => 'Editar Estadio', 'content' => $content]);
    }

    /** POST /estadios/actualizar/{id} — Procesar edición */
    public function update(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;

        $stadium = $this->model->getById($id);
        if (!$stadium) {
            $this->setFlash('danger', 'Estadio no encontrado.');
            $this->redirect('/estadios');
            return;
        }

        $name     = trim(filter_input(INPUT_POST, 'name',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $city     = trim(filter_input(INPUT_POST, 'city',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $country  = trim(filter_input(INPUT_POST, 'country',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: null;

        if (empty($name) || empty($city) || empty($country)) {
            $this->setFlash('danger', 'Nombre, ciudad y país son obligatorios.');
            $this->redirect("/estadios/editar/{$id}");
            return;
        }

        if ($this->model->existsByName($name, $id)) {
            $this->setFlash('warning', "Ya existe otro estadio con el nombre \"{$name}\".");
            $this->redirect("/estadios/editar/{$id}");
            return;
        }

        $updated = $this->model->update($id, compact('name', 'city', 'country', 'capacity'));

        if ($updated) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'UPDATE', "Estadio actualizado: {$name}", 'Stadium', $id);
            writeLog('INFO', "Estadio actualizado: ID {$id} - {$name}");
            $this->setFlash('success', "Estadio «{$name}» actualizado exitosamente.");
        } else {
            $this->setFlash('danger', 'Error al actualizar el estadio.');
        }

        $this->redirect('/estadios');
    }

    /** POST /estadios/eliminar/{id} — Eliminar */
    public function destroy(string $id): void
    {
        $this->requireMethod('POST');
        $id = (int) $id;

        $stadium = $this->model->getById($id);
        if (!$stadium) {
            $this->setFlash('danger', 'Estadio no encontrado.');
            $this->redirect('/estadios');
            return;
        }

        $deleted = $this->model->delete($id);

        if ($deleted) {
            AuditModel::log($this->currentUser()['id'] ?? 0, 'DELETE', "Estadio eliminado: {$stadium['name']}", 'Stadium', $id);
            writeLog('INFO', "Estadio eliminado: ID {$id} - {$stadium['name']}");
            $this->setFlash('success', "Estadio «{$stadium['name']}» eliminado correctamente.");
        } else {
            $this->setFlash('danger', 'No se pudo eliminar el estadio. Puede tener encuentros asociados.');
        }

        $this->redirect('/estadios');
    }
}
