<?php /** app/Views/players/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Jugadores</span>
</nav>

<div class="card">
  <div class="card-header">
    <h1 class="card-title">👤 Gestión de Jugadores</h1>
    <p style="font-size:.85rem; color:var(--color-text-muted); margin-top:4px">
      Selecciona un campeonato y equipo para ver o gestionar su roster.
    </p>
  </div>

  <?php if (empty($leagues)): ?>
    <div class="empty-state"><div class="empty-icon">🏆</div><p>No hay campeonatos registrados.</p></div>
  <?php else: ?>
    <?php foreach ($leagues as $league):
      require_once BASE_PATH . '/app/Models/TeamModel.php';
      $tm    = new TeamModel();
      $teams = $tm->getByLeague((int)$league['id']);
      if (empty($teams)) continue;
    ?>
    <div style="margin-bottom:28px">
      <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary); padding:8px 0; border-bottom:2px solid var(--color-primary); margin-bottom:12px">
        🏆 <?= e($league['name']) ?>
      </h2>
      <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px">
        <?php foreach ($teams as $t): ?>
        <div class="card" style="margin:0; padding:14px; display:flex; align-items:center; gap:12px">
          <?php if (!empty($t['logo'])): ?>
            <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($t['logo']) ?>" width="36" height="36" style="border-radius:50%; object-fit:contain; flex-shrink:0">
          <?php else: ?>
            <span style="font-size:28px; flex-shrink:0">🛡️</span>
          <?php endif; ?>
          <div style="flex:1; min-width:0">
            <strong style="display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis"><?= e($t['name']) ?></strong>
            <a href="<?= url("jugadores/equipo/{$t['id']}/liga/{$league['id']}") ?>"
               style="font-size:.8rem; color:var(--color-primary)">Ver roster →</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
