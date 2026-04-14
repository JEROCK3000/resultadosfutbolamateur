<?php /** app/Views/standings/index.php — Selector de liga */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Tabla de Posiciones</span>
</nav>
<div class="card" style="max-width:560px">
  <div class="card-header"><h1 class="card-title">📊 Tabla de Posiciones</h1></div>
  <?php if (empty($leagues)): ?>
    <div class="empty-state">
      <div class="empty-icon">🏆</div>
      <p>No hay campeonatos registradas</p>
      <a href="<?= url('ligas/crear') ?>" class="btn btn-primary">+ Crear primera liga</a>
    </div>
  <?php else: ?>
    <p style="margin-bottom:16px;color:var(--color-text-muted)">Selecciona un campeonato para ver su tabla de posiciones:</p>
    <div style="display:grid;gap:10px">
      <?php foreach ($leagues as $l): ?>
        <a href="<?= url("posiciones/{$l['id']}") ?>"
           style="display:flex;align-items:center;justify-content:space-between;
                  padding:14px 16px;background:var(--color-bg);border:1px solid var(--color-border);
                  border-radius:var(--radius-md);transition:var(--transition);text-decoration:none">
          <div>
            <strong style="color:var(--color-text)"><?= e($l['name']) ?></strong>
            <div style="font-size:.8rem;color:var(--color-text-muted)"><?= e($l['country']) ?> — <?= e($l['season']) ?></div>
          </div>
          <span style="color:var(--color-primary-light)">→</span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
