<?php
/**
 * app/Views/stadiums/edit.php — Formulario de edición de Estadio
 */
?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a>
  <span class="breadcrumb-sep">›</span>
  <a href="<?= url('estadios') ?>">Estadios</a>
  <span class="breadcrumb-sep">›</span>
  <span>Editar Estadio</span>
</nav>

<div class="card" style="max-width:620px">
  <div class="card-header">
    <h1 class="card-title">✏️ Editar Estadio</h1>
    <span class="badge badge-muted">ID: <?= (int)$stadium['id'] ?></span>
  </div>

  <form action="<?= url("estadios/actualizar/{$stadium['id']}") ?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre del Estadio <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control"
               required value="<?= e($stadium['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="capacity">Capacidad</label>
        <input type="number" id="capacity" name="capacity" class="form-control"
               min="0" value="<?= e($stadium['capacity'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="city">Ciudad <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="city" name="city" class="form-control"
               required value="<?= e($stadium['city']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="country">País <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="country" name="country" class="form-control"
               required value="<?= e($stadium['country']) ?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Estadio</button>
      <a href="<?= url('estadios') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
