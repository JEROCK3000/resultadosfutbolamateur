<?php /** app/Views/users/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('usuarios') ?>">Usuarios</a><span class="breadcrumb-sep">›</span>
  <span>Editar Usuario</span>
</nav>
<div class="card" style="max-width:580px">
  <div class="card-header">
    <h1 class="card-title">✏️ Editar Usuario</h1>
    <span class="badge badge-muted">ID: <?= (int)$userEdit['id'] ?></span>
  </div>
  <form action="<?= url("usuarios/actualizar/{$userEdit['id']}") ?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required value="<?= e($userEdit['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Correo <span style="color:var(--color-danger)">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required value="<?= e($userEdit['email']) ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="password">Nueva Contraseña <small>(dejar vacío para no cambiar)</small></label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Mínimo 8 caracteres" minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label" for="status">Estado</label>
        <select id="status" name="status" class="form-control">
          <option value="active"   <?= $userEdit['status']==='active'   ? 'selected' : '' ?>>Activo</option>
          <option value="inactive" <?= $userEdit['status']==='inactive' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="role">Rol</label>
        <select id="role" name="role" class="form-control" onchange="toggleLeague(this.value)">
          <option value="registrador" <?= $userEdit['role']==='registrador' ? 'selected' : '' ?>>📝 Registrador</option>
          <option value="admin"       <?= $userEdit['role']==='admin'       ? 'selected' : '' ?>>🔑 Administrador</option>
        </select>
      </div>
      <div class="form-group" id="leagueGroup" <?= $userEdit['role']==='admin' ? 'style="opacity:.4"' : '' ?>>
        <label class="form-label" for="league_id">Campeonato asignado</label>
        <select id="league_id" name="league_id" class="form-control">
          <option value="">— Sin campeonato (acceso global) —</option>
          <?php foreach ($leagues as $l): ?>
            <option value="<?= (int)$l['id'] ?>" <?= ($userEdit['league_id']==$l['id'] ? 'selected' : '') ?>>
              <?= e($l['name']) ?> — <?= e($l['season']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Usuario</button>
      <a href="<?= url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
<script>
function toggleLeague(role) {
  document.getElementById('leagueGroup').style.opacity = role === 'admin' ? '.4' : '1';
}
</script>
