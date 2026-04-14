<?php /** app/Views/leagues/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a>
  <span class="breadcrumb-sep">›</span>
  <span>Campeonatos</span>
</nav>
<div class="card">
  <div class="card-header">
    <h1 class="card-title">🏆 Campeonatos</h1>
    <a href="<?= url('ligas/crear') ?>" class="btn btn-primary">+ Nuevo Campeonato</a>
  </div>
  <?php if (empty($leagues)): ?>
    <div class="empty-state">
      <div class="empty-icon">🏆</div>
      <p>No hay campeonatos registradas aún</p>
      <a href="<?= url('ligas/crear') ?>" class="btn btn-primary">+ Agregar primera liga</a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>#</th><th>Nombre</th><th>Temporada</th><th>País</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($leagues as $i => $l): ?>
          <tr>
            <td class="text-muted"><?= $i+1 ?></td>
            <td><strong><?= e($l['name']) ?></strong></td>
            <td><?= e($l['season']) ?></td>
            <td><?= e($l['country']) ?></td>
            <td>
              <?php $badge = match($l['status']) {
                'active'   => 'badge-success',
                'finished' => 'badge-muted',
                default    => 'badge-warning'
              }; ?>
              <span class="badge <?= $badge ?>">
                <?= match($l['status']) { 'active' => 'Activa', 'finished' => 'Finalizada', default => 'Inactiva' } ?>
              </span>
            </td>
            <td>
              <div class="actions">
                <a href="<?= url("calendario/generar/{$l['id']}") ?>" class="btn btn-primary btn-sm" title="Sorteo Automático">🎲</a>
                <a href="<?= url("posiciones/{$l['id']}") ?>" class="btn btn-info btn-sm" title="Ver posiciones">📊</a>
                <a href="<?= url("torneos/crear/{$l['id']}") ?>" class="btn btn-secondary btn-sm" title="Fases Finales">🏆</a>
                <a href="<?= url("ligas/editar/{$l['id']}") ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>
                <form action="<?= url("ligas/eliminar/{$l['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar el campeonato «<?= e($l['name']) ?>»?">🗑️</button>
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
