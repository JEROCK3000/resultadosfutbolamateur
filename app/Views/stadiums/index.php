<?php
/**
 * app/Views/stadiums/index.php — Listado de Estadios
 */
?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a>
  <span class="breadcrumb-sep">›</span>
  <span>Estadios</span>
</nav>

<div class="card">
  <div class="card-header">
    <h1 class="card-title">🏟️ Estadios</h1>
    <a href="<?= url('estadios/crear') ?>" class="btn btn-primary">
      + Nuevo Estadio
    </a>
  </div>

  <?php if (empty($stadiums)): ?>
    <div class="empty-state">
      <div class="empty-icon">🏟️</div>
      <p>No hay estadios registrados aún</p>
      <a href="<?= url('estadios/crear') ?>" class="btn btn-primary">
        + Agregar primer estadio
      </a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Ciudad</th>
            <th>País</th>
            <th>Capacidad</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stadiums as $i => $s): ?>
          <tr>
            <td class="text-muted"><?= $i + 1 ?></td>
            <td><strong><?= e($s['name']) ?></strong></td>
            <td><?= e($s['city']) ?></td>
            <td><?= e($s['country']) ?></td>
            <td>
              <?= $s['capacity']
                ? number_format((int)$s['capacity'], 0, ',', '.')
                : '<span class="text-muted">—</span>' ?>
            </td>
            <td>
              <div class="actions">
                <a href="<?= url("estadios/editar/{$s['id']}") ?>"
                   class="btn btn-warning btn-sm"
                   title="Editar">✏️</a>
                <form action="<?= url("estadios/eliminar/{$s['id']}") ?>"
                      method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar el estadio «<?= e($s['name']) ?>»? Esta acción no se puede deshacer."
                          title="Eliminar">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-muted mt-2" style="font-size:.8rem">
      Total: <strong><?= count($stadiums) ?></strong> estadio<?= count($stadiums) !== 1 ? 's' : '' ?>
    </p>
  <?php endif; ?>
</div>
