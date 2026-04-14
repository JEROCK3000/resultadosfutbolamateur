<?php /** app/Views/referees/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('arbitros') ?>">Árbitros</a><span class="breadcrumb-sep">›</span>
  <span>Editar</span>
</nav>

<div class="card" style="max-width: 500px">
  <div class="card-header"><h2 class="card-title">Editar Árbitro</h2></div>
  
  <div class="card-body">
    <form action="<?= url("arbitros/actualizar/{$referee['id']}") ?>" method="POST">
      <div class="form-group">
        <label class="form-label">Nombre Completo <span style="color:#e11d48">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($referee['name']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label">Número de Licencia o Carnet</label>
        <input type="text" name="license" class="form-control" value="<?= e($referee['license']) ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Número de Teléfono</label>
        <input type="tel" name="phone" class="form-control" value="<?= e($referee['phone']) ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="status" class="form-control">
          <option value="active" <?= $referee['status'] === 'active' ? 'selected' : '' ?>>Activo</option>
          <option value="inactive" <?= $referee['status'] === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= url('arbitros') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Actualizar Árbitro</button>
      </div>
    </form>
  </div>
</div>
