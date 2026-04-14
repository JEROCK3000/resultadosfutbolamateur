<?php /** app/Views/matches/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('encuentros') ?>">Encuentros</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
      <h1 class="card-title" style="margin:0">📅 Encuentros</h1>
      <span class="badge badge-info pb-blue" style="margin-top:5px;display:inline-block"><?= e($league['name']) ?></span>
    </div>
    <div style="display:flex;gap:8px">
      <a href="<?= BASE_URL ?>/exportar/encuentros-admin/pdf/<?= (int)$league['id'] ?>" target="_blank" class="btn btn-sm" style="background:#1e40af;color:#fff;border:none">📄 PDF</a>
      <a href="<?= BASE_URL ?>/exportar/encuentros-admin/excel/<?= (int)$league['id'] ?>" class="btn btn-sm" style="background:#047857;color:#fff;border:none">📊 Excel</a>
      <a href="<?= url("encuentros/crear?league_id={$league['id']}") ?>" class="btn btn-primary btn-sm">+ Nuevo Encuentro</a>
    </div>
  </div>
  <?php if (empty($matches)): ?>
    <div class="empty-state">
      <div class="empty-icon">📅</div>
      <p>No hay encuentros programados aún</p>
      <a href="<?= url('encuentros/crear') ?>" class="btn btn-primary">+ Programar primer encuentro</a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th><th>Hora</th><th>Local</th><th>Resultado</th>
            <th>Visitante</th><th>Estadio</th><th>Árbitro</th><th>Campeonato</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($matches as $m): ?>
          <?php
            $statusBadge = match($m['status']) {
              'unscheduled' => 'badge-secondary',
              'scheduled' => 'badge-info',
              'live'      => 'badge-danger',
              'finished'  => 'badge-success',
              'postponed' => 'badge-warning',
              default     => 'badge-muted'
            };
            $statusLabel = match($m['status']) {
              'unscheduled' => 'Por programar',
              'scheduled' => 'Programado',
              'live'      => '🔴 En vivo',
              'finished'  => 'Finalizado',
              'postponed' => 'Postergado',
              default     => $m['status']
            };
          ?>
          <tr style="<?= $m['status']==='unscheduled' ? 'background:rgba(0,0,0,0.02)' : '' ?>">
            <td><?= $m['match_date'] ? date('d/m/Y', strtotime($m['match_date'])) : '<span class="text-muted">—</span>' ?></td>
            <td><?= $m['match_time'] ? e(substr($m['match_time'], 0, 5)) : '<span class="text-muted">—</span>' ?></td>
            <td style="text-align:right">
               <strong><?= e($m['home_team']) ?></strong>
               <?php if (!empty($m['home_logo'])): ?>
                 <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="24" height="24" style="object-fit:contain;vertical-align:middle;margin-left:8px;border-radius:50%">
               <?php else: ?>
                 <span style="font-size:16px;vertical-align:middle;margin-left:8px">🛡️</span>
               <?php endif; ?>
            </td>
            <td style="text-align:center;font-weight:700">
              <?php if ($m['status'] === 'finished' && isset($m['home_goals'])): ?>
                <?= (int)$m['home_goals'] ?> : <?= (int)$m['away_goals'] ?>
              <?php else: ?>
                <span class="text-muted">vs</span>
              <?php endif; ?>
            </td>
            <td>
               <?php if (!empty($m['away_logo'])): ?>
                 <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="24" height="24" style="object-fit:contain;vertical-align:middle;margin-right:8px;border-radius:50%">
               <?php else: ?>
                 <span style="font-size:16px;vertical-align:middle;margin-right:8px">🛡️</span>
               <?php endif; ?>
               <?= e($m['away_team']) ?>
            </td>
            <td style="white-space:nowrap"><?= $m['stadium'] ? e($m['stadium']) : '<span class="text-muted">—</span>' ?></td>
            <td style="white-space:nowrap; color:var(--color-text-muted)"><small>🟨 <?= e($m['referee_name'] ?? '—') ?></small></td>
            <td><span class="badge badge-info" style="white-space:nowrap"><?= e($league['name']) ?></span></td>
            <td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
            <td>
              <div class="actions">
                <a href="<?= url("resultados/{$m['id']}") ?>" class="btn btn-outline btn-sm" title="Resultado">⚽</a>
                <a href="<?= url("encuentros/editar/{$m['id']}") ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>
                <form action="<?= url("encuentros/eliminar/{$m['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar este encuentro?">🗑️</button>
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
