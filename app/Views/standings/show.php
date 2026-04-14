<?php /** app/Views/standings/show.php — Tabla de posiciones de un campeonato */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('posiciones') ?>">Posiciones</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>

<div class="card-header" style="margin-bottom:20px">
  <div>
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.2rem,4vw,1.6rem);font-weight:700">
      📊 <?= e($league['name']) ?>
    </h1>
    <p style="color:var(--color-text-muted);font-size:.85rem">
      <?= e($league['country']) ?> &mdash; Temporada <?= e($league['season']) ?>
    </p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="<?= url("torneos/crear/{$league['id']}") ?>" class="btn btn-primary btn-sm">🎯 Generar Fase Final</a>
    <a href="<?= url('posiciones') ?>" class="btn btn-secondary btn-sm">← Volver</a>
  </div>
</div>

<!-- Tabla de posiciones -->
<div class="card">
  <div class="card-header"><h2 class="card-title">Clasificación</h2></div>
  <?php if (empty($standings)): ?>
    <div class="empty-state">
      <div class="empty-icon">📊</div>
      <p>No hay resultados registrados en este campeonato aún</p>
    </div>
  <?php else: ?>
    <div class="table-wrapper standings-table">
      <table class="table">
        <thead>
          <tr>
            <th style="width:42px">#</th>
            <th>Equipo</th>
            <th title="Partidos Jugados">PJ</th>
            <th title="Partidos Ganados">PG</th>
            <th title="Partidos Empatados">PE</th>
            <th title="Partidos Perdidos">PP</th>
            <th title="Goles a Favor">GF</th>
            <th title="Goles en Contra">GC</th>
            <th title="Diferencia de Goles">DG</th>
            <th title="Tarjetas Amarillas">TA</th>
            <th title="Tarjetas Rojas">TR</th>
            <th title="Puntos"><strong>PTS</strong></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($standings as $pos => $row): ?>
          <?php
            $posClass = match($pos + 1) { 1 => 'pos-1', 2 => 'pos-2', 3 => 'pos-3', default => '' };
          ?>
          <tr>
            <td>
              <div class="pos-num <?= $posClass ?>"><?= $pos + 1 ?></div>
            </td>
            <td>
              <div style="display:flex;align-items:center;">
                <?php if(!empty($row['logo'])): ?>
                  <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($row['logo']) ?>" width="20" height="20" style="object-fit:contain;margin-right:8px;border-radius:50%">
                <?php else: ?>
                  <span style="font-size:14px;margin-right:8px">🛡️</span>
                <?php endif; ?>
                <strong><?= e($row['name']) ?></strong>
              </div>
            </td>
            <td><?= $row['PJ'] ?></td>
            <td style="color:var(--color-success)"><?= $row['PG'] ?></td>
            <td style="color:var(--color-warning)"><?= $row['PE'] ?></td>
            <td style="color:var(--color-danger)"><?= $row['PP'] ?></td>
            <td><?= $row['GF'] ?></td>
            <td><?= $row['GC'] ?></td>
            <td style="<?= $row['DG'] > 0 ? 'color:var(--color-success)' : ($row['DG'] < 0 ? 'color:var(--color-danger)' : '') ?>">
              <?= $row['DG'] > 0 ? '+' : '' ?><?= $row['DG'] ?>
            </td>
            <td style="color:#d97706"><?= $row['TA'] ?></td>
            <td style="color:#dc2626"><?= $row['TR'] ?></td>
            <td><strong style="font-size:1.05rem"><?= $row['PTS'] ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:12px;font-size:.75rem;color:var(--color-text-muted);display:flex;gap:16px;flex-wrap:wrap">
      <span>PJ=Partidos Jugados</span><span>PG=Ganados</span><span>PE=Empatados</span>
      <span>PP=Perdidos</span><span>GF=Goles a Favor</span><span>GC=Goles en Contra</span>
      <span>DG=Diferencia</span><span>TA=T. Amarillas</span><span>TR=T. Rojas</span><span>PTS=Puntos</span>
    </div>
  <?php endif; ?>
</div>

<!-- Partidos del campeonato -->
<?php if (!empty($matches)): ?>
<div class="card mt-3">
  <div class="card-header"><h2 class="card-title">📅 Partidos del Campeonato</h2></div>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Fecha</th><th>Hora</th><th>Local</th><th>Resultado</th><th>Visitante</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($matches as $m): ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($m['match_date'])) ?></td>
          <td><?= e(substr($m['match_time'],0,5)) ?></td>
          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end">
              <strong><?= e($m['home_team']) ?></strong>
              <?php if(!empty($m['home_logo'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="20" height="20" style="object-fit:contain;margin-left:8px;border-radius:50%">
              <?php else: ?>
                <span style="font-size:14px;margin-left:8px">🛡️</span>
              <?php endif; ?>
            </div>
          </td>
          <td style="text-align:center;font-weight:700">
            <?= $m['status']==='finished' && isset($m['home_goals'])
              ? (int)$m['home_goals'].' : '.(int)$m['away_goals']
              : '<span class="text-muted">vs</span>' ?>
          </td>
          <td>
            <div style="display:flex;align-items:center;">
              <?php if(!empty($m['away_logo'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="20" height="20" style="object-fit:contain;margin-right:8px;border-radius:50%">
              <?php else: ?>
                <span style="font-size:14px;margin-right:8px">🛡️</span>
              <?php endif; ?>
              <?= e($m['away_team']) ?>
            </div>
          </td>
          <td><span class="badge <?= $m['status']==='finished' ? 'badge-success' : 'badge-info' ?>">
            <?= $m['status']==='finished' ? 'Finalizado' : 'Programado' ?>
          </span></td>
          <td><a href="<?= url("resultados/{$m['id']}") ?>" class="btn btn-outline btn-sm">⚽</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
