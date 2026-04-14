<?php /** app/Views/matches/leagues.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Encuentros (Campeonatos)</span>
</nav>
<div class="card">
  <div class="card-header">
    <h1 class="card-title">⚽ Encuentros Programados</h1>
  </div>
  <p class="text-muted" style="margin-bottom:20px">Selecciona un torneo o liga para ver y administrar sus encuentros, o para crear nuevos de forma manual.</p>
  
  <?php if (empty($leagues)): ?>
    <div class="empty-state">
      <div class="empty-icon">⚽</div>
      <p>No hay campeonatos registradas aún</p>
    </div>
  <?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:15px">
      <?php foreach ($leagues as $l): ?>
        <a href="<?= url("encuentros/liga/{$l['id']}") ?>" style="text-decoration:none; color:inherit">
          <div style="padding:20px; border:1px solid var(--color-border); border-radius:12px; background:var(--color-surface); transition:transform 0.2s, box-shadow 0.2s; cursor:pointer" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
            <div style="font-size:2rem; margin-bottom:10px">🏆</div>
            <strong style="display:block; font-size:1.1rem; color:var(--color-primary)"><?= e($l['name']) ?></strong>
            <span style="font-size:0.85rem; color:var(--color-text-muted)"><?= e($l['season']) ?></span>
            <div style="margin-top:15px; text-align:right">
              <span class="badge badge-success pb-blue">Ver Encuentros →</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
