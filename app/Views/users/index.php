<?php /** app/Views/users/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Usuarios</span>
</nav>
<div class="card">
  <div class="card-header">
    <h1 class="card-title">👥 Gestión de Usuarios</h1>
    <a href="<?= url('usuarios/crear') ?>" class="btn btn-primary">+ Nuevo Usuario</a>
  </div>
  <?php if (empty($users)): ?>
    <div class="empty-state"><div class="empty-icon">👥</div><p>No hay usuarios registrados</p></div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Campeonato</th><th>Último acceso</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><strong><?= e($u['name']) ?></strong></td>
            <td style="font-size:.85rem"><?= e($u['email']) ?></td>
            <td>
              <span class="badge <?= $u['role']==='admin' ? 'badge-danger' : 'badge-info' ?>">
                <?= $u['role']==='admin' ? '🔑 Admin' : '📝 Registrador' ?>
              </span>
            </td>
            <td><?= $u['league_name'] ? '<span class="badge badge-muted">'.e($u['league_name']).'</span>' : '<span class="text-muted">Global</span>' ?></td>
            <td style="font-size:.8rem;color:var(--color-text-muted)">
              <?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Nunca' ?>
            </td>
            <td><span class="badge <?= $u['status']==='active' ? 'badge-success' : 'badge-muted' ?>">
              <?= $u['status']==='active' ? 'Activo' : 'Inactivo' ?>
            </span></td>
            <td>
              <div class="actions">
                <a href="<?= url("usuarios/editar/{$u['id']}") ?>" class="btn btn-warning btn-sm">✏️</a>
                <form action="<?= url("usuarios/eliminar/{$u['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar al usuario «<?= e($u['name']) ?>»?">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
