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
        <label class="form-label" for="name">Nombre completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required
               placeholder="ej. Juan Picuasi" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Correo electrónico <span style="color:var(--color-danger)">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required
               placeholder="ej. juan@liganapo.ec" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="password">Contraseña <span style="color:var(--color-danger)">*</span></label>
        <input type="password" id="password" name="password" class="form-control" required
               placeholder="Mínimo 8 caracteres" minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label" for="role">Rol</label>
        <select id="role" name="role" class="form-control" id="roleSelect" onchange="toggleLeague(this.value)">
          <option value="registrador">📝 Registrador</option>
          <option value="admin">🔑 Administrador</option>
        </select>
      </div>
    </div>
    <div class="form-group" id="leagueGroup">
      <label class="form-label" for="league_id">Campeonato asignado</label>
      <select id="league_id" name="league_id" class="form-control">
        <option value="">— Sin campeonato (acceso global) —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?> — <?= e($l['season']) ?></option>
        <?php endforeach; ?>
      </select>
      <p style="font-size:.77rem;color:var(--color-text-muted);margin-top:4px">
        Los registradores solo pueden gestionar datos de su liga asignada.
      </p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar Usuario</button>
      <a href="<?= url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
<script>
function toggleLeague(role) {
  document.getElementById('leagueGroup').style.opacity = role === 'admin' ? '.4' : '1';
}
</script>
