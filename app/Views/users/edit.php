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
        <label class="form-label">Nombre completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?= e($userEdit['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Correo <span style="color:var(--color-danger)">*</span></label>
        <input type="email" name="email" class="form-control" required value="<?= e($userEdit['email']) ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label">Nueva Contraseña <small>(vacío = no cambiar)</small></label>
        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="status" class="form-control">
          <option value="active"   <?= $userEdit['status']==='active'   ? 'selected' : '' ?>>Activo</option>
          <option value="inactive" <?= $userEdit['status']==='inactive' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Rol</label>
      <select name="role" class="form-control" id="roleSelect"
              onchange="onRoleChange(this.value)"
              data-current="<?= e($userEdit['role']) ?>">
        <option value="registrador"   <?= $userEdit['role']==='registrador'   ? 'selected' : '' ?>>📝 Registrador</option>
        <option value="team_manager"  <?= $userEdit['role']==='team_manager'  ? 'selected' : '' ?>>👕 Manager de Equipo</option>
        <option value="admin"         <?= $userEdit['role']==='admin'         ? 'selected' : '' ?>>🔑 Administrador</option>
      </select>
    </div>

    <div id="leagueGroup" class="form-group"
         style="<?= $userEdit['role']==='team_manager' || $userEdit['role']==='admin' ? 'display:none' : '' ?>">
      <label class="form-label">Campeonato asignado</label>
      <select name="league_id" class="form-control">
        <option value="">— Sin campeonato —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>" <?= ($userEdit['league_id']==$l['id'] ? 'selected' : '') ?>>
            <?= e($l['name']) ?> — <?= e($l['season']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="teamGroup" class="form-group"
         style="<?= $userEdit['role']==='team_manager' ? '' : 'display:none' ?>">
      <label class="form-label">Equipo asignado <span style="color:var(--color-danger)">*</span></label>
      <select name="team_id" class="form-control">
        <option value="">— Selecciona un equipo —</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ($userEdit['team_id']==$t['id'] ? 'selected' : '') ?>>
            <?= e($t['name']) ?> · <?= e($t['league_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Usuario</button>
      <a href="<?= url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
<script>
function onRoleChange(role) {
  document.getElementById('leagueGroup').style.display = role === 'team_manager' || role === 'admin' ? 'none' : '';
  document.getElementById('teamGroup').style.display   = role === 'team_manager' ? '' : 'none';
}
</script>
