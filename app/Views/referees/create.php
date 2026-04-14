<?php /** app/Views/referees/create.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('arbitros') ?>">Árbitros</a><span class="breadcrumb-sep">›</span>
  <span>Nuevo</span>
</nav>

<div class="card" style="max-width: 500px">
  <div class="card-header"><h2 class="card-title">Registrar Árbitro</h2></div>
  <div class="card-body">
    <form action="<?= url('arbitros/guardar') ?>" method="POST">
      <div class="form-group">
        <label class="form-label">Nombre Completo <span style="color:#e11d48">*</span></label>
        <input type="text" name="name" class="form-control" required placeholder="Ej. Javier Castrillón">
      </div>
      <div class="form-group">
        <label class="form-label">Número de Licencia o Carnet</label>
        <input type="text" name="license" class="form-control" placeholder="Opcional">
      </div>
      <div class="form-group">
        <label class="form-label">Número de Teléfono</label>
        <input type="tel" name="phone" class="form-control" placeholder="Ej. 0987654321">
      </div>
      <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="status" class="form-control">
          <option value="active" selected>Activo (Disponible para pitar)</option>
          <option value="inactive">Inactivo</option>
        </select>
      </div>
      <div class="form-actions mt-4">
        <a href="<?= url('arbitros') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar Árbitro</button>
      </div>
    </form>
  </div>
</div>
