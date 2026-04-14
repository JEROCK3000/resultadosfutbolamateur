<?php /** app/Views/teams/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('equipos') ?>">Equipos</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
       <h1 class="card-title" style="margin:0">👕 Equipos</h1>
       <span class="badge badge-info pb-blue" style="margin-top:5px;display:inline-block"><?= e($league['name']) ?></span>
    </div>
    <div style="display:flex;gap:8px">
      <a href="<?= BASE_URL ?>/exportar/equipos/pdf/<?= (int)$league['id'] ?>" target="_blank" class="btn btn-sm" style="background:#1e40af;color:#fff;border:none">📄 PDF</a>
      <a href="<?= BASE_URL ?>/exportar/equipos/excel/<?= (int)$league['id'] ?>" class="btn btn-sm" style="background:#047857;color:#fff;border:none">📊 Excel</a>
      <a href="<?= url("equipos/importar/{$league['id']}") ?>" class="btn btn-sm pb-muted" style="background:var(--color-surface);color:var(--color-text);border:1px solid var(--color-border)">🔄 Clonar</a>
      <a href="<?= url("equipos/crear?league_id={$league['id']}") ?>" class="btn btn-primary btn-sm">+ Nuevo Equipo</a>
    </div>
  </div>
  <?php if (empty($teams)): ?>
    <div class="empty-state">
      <div class="empty-icon">👕</div>
      <p>No hay equipos registrados aún</p>
      <a href="<?= url('equipos/crear') ?>" class="btn btn-primary">+ Agregar primer equipo</a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr><th>#</th><th>Nombre</th><th>Abrev.</th><th>Campeonato</th><th>Fundado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($teams as $i => $t): ?>
          <tr>
            <td class="text-muted"><?= $i+1 ?></td>
            <td style="display:flex;align-items:center;">
              <?php if(!empty($t['logo'])): ?>
                 <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($t['logo']) ?>" width="20" height="20" style="object-fit:contain;margin-right:8px;border-radius:50%">
              <?php else: ?>
                 <span style="font-size:14px;margin-right:8px">🛡️</span>
              <?php endif; ?>
              <strong><?= e($t['name']) ?></strong>
            </td>
            <td><?= $t['short_name'] ? '<span class="badge badge-muted">'.e($t['short_name']).'</span>' : '<span class="text-muted">—</span>' ?></td>
            <td><span class="badge badge-info"><?= e($t['league_name'] ?? 'Sin campeonato') ?></span></td>
            <td><?= e($t['founded_year'] ?? '—') ?></td>
            <td>
              <div class="actions">
                <a href="<?= url("equipos/editar/{$t['id']}") ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>
                <form action="<?= url("equipos/eliminar/{$t['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar el equipo «<?= e($t['name']) ?>»?">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-muted mt-2" style="font-size:.8rem">
      Total: <strong><?= count($teams) ?></strong> equipo<?= count($teams) !== 1 ? 's' : '' ?>
    </p>
  <?php endif; ?>
</div>
