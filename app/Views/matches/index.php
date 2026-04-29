<?php /** app/Views/matches/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('encuentros') ?>">Encuentros</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>

<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
      <h1 class="card-title" style="margin:0">📅 Calendario — <?= e($league['name']) ?></h1>
      <?php if (!empty($grouped)): ?>
        <?php
          $totalMatches = array_sum(array_map(fn($rounds) => array_sum(array_map('count', $rounds)), $grouped));
          $scheduled    = 0;
          $finished     = 0;
          foreach ($matches as $m) {
              if ($m['status'] === 'scheduled') $scheduled++;
              if ($m['status'] === 'finished')  $finished++;
          }
        ?>
        <p style="font-size:.82rem; color:var(--color-text-muted); margin:4px 0 0">
          <?= $totalMatches ?> partidos totales —
          <?= $finished ?> finalizados —
          <?= $scheduled ?> programados —
          <?= $totalMatches - $finished - $scheduled ?> por programar
        </p>
      <?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="<?= url("programacion/{$league['id']}") ?>" class="btn btn-sm" style="background:var(--color-primary);color:#fff">🎲 Programar jornada</a>
      <a href="<?= BASE_URL ?>/exportar/encuentros-admin/pdf/<?= (int)$league['id'] ?>" target="_blank" class="btn btn-sm" style="background:#1e40af;color:#fff">📄 PDF</a>
      <a href="<?= BASE_URL ?>/exportar/encuentros-admin/excel/<?= (int)$league['id'] ?>" class="btn btn-sm" style="background:#047857;color:#fff">📊 Excel</a>
      <a href="<?= url("encuentros/crear?league_id={$league['id']}") ?>" class="btn btn-sm btn-secondary">+ Partido suelto</a>
    </div>
  </div>

  <?php if (empty($grouped)): ?>
    <div class="empty-state">
      <div class="empty-icon">📅</div>
      <p>No se ha generado el fixture para este campeonato aún.</p>
      <a href="<?= url("calendario/generar/{$league['id']}") ?>" class="btn btn-primary">✨ Generar Fixture Base</a>
    </div>

  <?php else: ?>

    <?php
      $vueltaLabels = [1 => 'Primera Vuelta', 2 => 'Segunda Vuelta'];
      $vueltaColors = [1 => 'var(--color-primary)', 2 => '#7c3aed'];
    ?>

    <?php foreach ($grouped as $vuelta => $rounds): ?>

      <!-- ── Header de Vuelta ─────────────────────────────── -->
      <div style="margin:24px 0 10px; padding:10px 16px; border-radius:8px; background:<?= $vueltaColors[$vuelta] ?? 'var(--color-primary)' ?>1a; border-left:4px solid <?= $vueltaColors[$vuelta] ?? 'var(--color-primary)' ?>">
        <h2 style="margin:0; font-size:1.1rem; color:<?= $vueltaColors[$vuelta] ?? 'var(--color-primary)' ?>">
          <?= $vueltaLabels[$vuelta] ?? "Vuelta {$vuelta}" ?>
          <span style="font-size:.8rem; font-weight:400; color:var(--color-text-muted); margin-left:10px">
            (<?= count($rounds) ?> fechas — <?= array_sum(array_map('count', $rounds)) ?> partidos)
          </span>
        </h2>
      </div>

      <?php
        // Número local de fecha dentro de esta vuelta
        $localFecha = 1;
      ?>
      <?php foreach ($rounds as $roundNumber => $roundMatches): ?>

        <!-- ── Header de Fecha ─────────────────────────────── -->
        <?php
          // Contar estados de esta fecha
          $fechaFinished   = count(array_filter($roundMatches, fn($m) => $m['status'] === 'finished'));
          $fechaScheduled  = count(array_filter($roundMatches, fn($m) => $m['status'] === 'scheduled'));
          $fechaUnscheduled= count(array_filter($roundMatches, fn($m) => $m['status'] === 'unscheduled'));
          $totalFecha      = count($roundMatches);
          // Fecha del primer partido programado de esta jornada
          $fechaDates = array_filter(array_column($roundMatches, 'match_date'));
          $fechaDate  = !empty($fechaDates) ? date('d/m/Y', strtotime(min($fechaDates))) : null;
        ?>
        <details open style="margin-bottom:8px; border:1px solid var(--color-border); border-radius:8px; overflow:hidden">
          <summary style="
            cursor:pointer; list-style:none; padding:10px 16px;
            background:var(--color-surface);
            display:flex; align-items:center; justify-content:space-between;
            font-weight:600; font-size:.95rem;
            user-select:none;
          ">
            <div style="display:flex; align-items:center; gap:10px">
              <span style="
                background:<?= $vueltaColors[$vuelta] ?? 'var(--color-primary)' ?>;
                color:#fff; border-radius:50%; width:28px; height:28px;
                display:inline-flex; align-items:center; justify-content:center;
                font-size:.8rem; font-weight:800; flex-shrink:0
              "><?= $localFecha ?></span>
              <span>Fecha <?= $localFecha ?></span>
              <?php if ($fechaDate): ?>
                <span style="font-size:.8rem; color:var(--color-text-muted); font-weight:400"><?= $fechaDate ?></span>
              <?php endif; ?>
            </div>
            <div style="display:flex; gap:6px; align-items:center">
              <?php if ($fechaFinished > 0): ?>
                <span class="badge badge-success"><?= $fechaFinished ?> finalizados</span>
              <?php endif; ?>
              <?php if ($fechaScheduled > 0): ?>
                <span class="badge badge-info"><?= $fechaScheduled ?> programados</span>
              <?php endif; ?>
              <?php if ($fechaUnscheduled > 0): ?>
                <span class="badge badge-warning"><?= $fechaUnscheduled ?> por programar</span>
              <?php endif; ?>
              <span style="font-size:.8rem; color:var(--color-text-muted)">▾</span>
            </div>
          </summary>

          <!-- Tabla de partidos de la fecha -->
          <div class="table-wrapper" style="margin:0">
            <table class="table" style="margin:0">
              <thead>
                <tr>
                  <th style="width:90px">Fecha</th>
                  <th style="width:60px">Hora</th>
                  <th style="text-align:right">Local</th>
                  <th style="text-align:center; width:70px">Resultado</th>
                  <th>Visitante</th>
                  <th>Estadio</th>
                  <th>Árbitro</th>
                  <th style="width:100px">Estado</th>
                  <th style="width:100px">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($roundMatches as $m): ?>
                <?php
                  $statusBadge = match($m['status']) {
                    'unscheduled' => 'badge-warning',
                    'scheduled'   => 'badge-info',
                    'live'        => 'badge-danger',
                    'finished'    => 'badge-success',
                    'postponed'   => 'badge-secondary',
                    default       => 'badge-muted'
                  };
                  $statusLabel = match($m['status']) {
                    'unscheduled' => 'Por programar',
                    'scheduled'   => 'Programado',
                    'live'        => '🔴 En vivo',
                    'finished'    => 'Finalizado',
                    'postponed'   => 'Postergado',
                    default       => $m['status']
                  };
                ?>
                <tr>
                  <td style="font-size:.82rem">
                    <?= $m['match_date'] ? date('d/m/Y', strtotime($m['match_date'])) : '<span class="text-muted">—</span>' ?>
                    <?php if ($m['match_date']): ?>
                      <br><span style="font-size:.72rem; color:var(--color-text-muted)">
                        <?= ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w', strtotime($m['match_date']))] ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="font-weight:600">
                    <?= $m['match_time'] ? e(substr($m['match_time'], 0, 5)) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td style="text-align:right">
                    <?php if (!empty($m['home_logo'])): ?>
                      <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-left:6px;border-radius:50%">
                    <?php else: ?>
                      <span style="font-size:14px;vertical-align:middle;margin-left:6px">🛡️</span>
                    <?php endif; ?>
                    <strong><?= e($m['home_team']) ?></strong>
                  </td>
                  <td style="text-align:center; font-weight:700">
                    <?php if ($m['status'] === 'finished' && isset($m['home_goals'])): ?>
                      <span style="font-size:1.05rem"><?= (int)$m['home_goals'] ?> : <?= (int)$m['away_goals'] ?></span>
                    <?php else: ?>
                      <span class="text-muted" style="font-size:.9rem">vs</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= e($m['away_team']) ?></strong>
                    <?php if (!empty($m['away_logo'])): ?>
                      <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-left:6px;border-radius:50%">
                    <?php else: ?>
                      <span style="font-size:14px;vertical-align:middle;margin-left:6px">🛡️</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.82rem; white-space:nowrap">
                    <?= $m['stadium'] ? e($m['stadium']) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td style="font-size:.8rem; color:var(--color-text-muted); white-space:nowrap">
                    <?= $m['referee_name'] ? e($m['referee_name']) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
                  <td>
                    <div class="actions" style="gap:4px">
                      <?php if ($m['status'] !== 'unscheduled'): ?>
                        <a href="<?= url("resultados/{$m['id']}") ?>" class="btn btn-outline btn-sm" title="Resultado">⚽</a>
                      <?php endif; ?>
                      <a href="<?= url("encuentros/editar/{$m['id']}") ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>
                      <form action="<?= url("encuentros/eliminar/{$m['id']}") ?>" method="POST" style="display:inline">
                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="¿Eliminar este encuentro?">🗑️</button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </details>

        <?php $localFecha++; ?>
      <?php endforeach; ?>

    <?php endforeach; ?>

  <?php endif; ?>
</div>
