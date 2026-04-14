<?php
/**
 * app/Views/stadiums/create.php — Formulario de creación de Estadio
 */
?>
<nav class="breadcrumb">
  <a href="<?= url()?>">Dashboard</a>
  <span class="breadcrumb-sep">›</span>
  <a href="<?= url('estadios')?>">Estadios</a>
  <span class="breadcrumb-sep">›</span>
  <span>Nuevo Estadio</span>
</nav>

<div class="card" style="max-width:620px">
  <div class="card-header">
    <h1 class="card-title">🏟️ Nuevo Estadio</h1>
  </div>

  <form action="<?= url('estadios/guardar')?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre del Estadio <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" placeholder="ej. Estadio Victor Montenegro Borja"
          required value="<?= e($_POST['name'] ?? '')?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="capacity">Capacidad</label>
        <input type="number" id="capacity" name="capacity" class="form-control" placeholder="ej. 3500" min="0"
          value="<?= e($_POST['capacity'] ?? '')?>">
      </div>
    </div>

    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="city">Ciudad <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="city" name="city" class="form-control" placeholder="ej. Borja" required
          value="<?= e($_POST['city'] ?? '')?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="country">País <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="country" name="country" class="form-control" placeholder="ej. Ecuador" required
          value="<?= e($_POST['country'] ?? '')?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar Estadio</button>
      <a href="<?= url('estadios')?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>