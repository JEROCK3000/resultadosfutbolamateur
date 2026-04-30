<?php /** app/Views/users/create.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('usuarios') ?>">Usuarios</a><span class="breadcrumb-sep">›</span>
  <span>Nuevo Usuario</span>
</nav>
<div class="card" style="max-width:580px">
  <div class="card-header"><h1 class="card-title">👤 Nuevo Usuario</h1></div>
  <form action="<?= url('usuarios/guardar') ?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label">Nombre completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="name" class="form-control" required
               placeholder="ej. Juan Picuasi" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Correo electrónico <span style="color:var(--color-danger)">*</span></label>
        <input type="email" name="email" class="form-control" required
               placeholder="ej. juan@liganapo.ec" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label">Contraseña <span style="color:var(--color-danger)">*</span></label>
        <input type="password" name="password" class="form-control" required
               placeholder="Mínimo 8 caracteres" minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label">Rol</label>
        <select name="role" class="form-control" id="roleSelect" onchange="onRoleChange(this.value)">
          <option value="registrador">📝 Registrador</option>
          <option value="team_manager">👕 Manager de Equipo</option>
          <option value="admin">🔑 Administrador</option>
        </select>
      </div>
    </div>

    <div id="leagueGroup" class="form-group">
      <label class="form-label">Campeonato asignado</label>
      <select name="league_id" class="form-control">
        <option value="">— Sin campeonato (acceso global) —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?> — <?= e($l['season']) ?></option>
        <?php endforeach; ?>
      </select>
      <p style="font-size:.77rem;color:var(--color-text-muted);margin-top:4px">
        Los registradores solo gestionan datos de su liga asignada.
      </p>
    </div>

    <div id="teamGroup" class="form-group" style="display:none">
      <label class="form-label">Equipo asignado <span style="color:var(--color-danger)">*</span></label>
      <select name="team_id" class="form-control">
        <option value="">— Selecciona un equipo —</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?> · <?= e($t['league_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <p style="font-size:.77rem;color:var(--color-text-muted);margin-top:4px">
        El manager solo puede gestionar los jugadores de este equipo.
      </p>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar Usuario</button>
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
