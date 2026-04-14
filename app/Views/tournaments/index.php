<?php /** app/Views/tournaments/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Fases Finales</span>
</nav>
<div class="card">
  <div class="card-header">
    <h1 class="card-title">🎯 Fases Finales</h1>
    <a href="<?= url('posiciones') ?>" class="btn btn-outline btn-sm">← Ir a Posiciones</a>
  </div>
  <?php if (empty($tournaments)): ?>
    <div class="empty-state">
      <div class="empty-icon">🎯</div>
      <p>No hay fases finales generadas aún</p>
      <p style="font-size:.85rem">Ve a la tabla de posiciones de un campeonato y haz clic en <strong>"Generar Fase Final"</strong></p>
      <a href="<?= url('posiciones') ?>" class="btn btn-primary">📊 Ir a Posiciones</a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Nombre</th><th>Campeonato</th><th>Temporada</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($tournaments as $t): ?>
          <tr>
            <td><strong><?= e($t['name']) ?></strong></td>
            <td><?= e($t['league_name']) ?></td>
            <td><?= e($t['season']) ?></td>
            <td>
              <span class="badge <?= $t['type']==='seeded' ? 'badge-warning' : 'badge-info' ?>">
                <?= $t['type']==='seeded' ? '🔀 Cruzado' : '⚡ Estándar' ?>
              </span>
            </td>
            <td><span class="badge badge-success"><?= e(ucfirst($t['status'])) ?></span></td>
            <td>
              <div class="actions">
                <a href="<?= url("torneos/{$t['id']}/llave") ?>" class="btn btn-primary btn-sm">🏆 Ver Llave</a>
                <form action="<?= url("torneos/eliminar/{$t['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar este torneo?">🗑️</button>
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
