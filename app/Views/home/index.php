<?php
/**
 * app/Views/home/index.php — Vista del Dashboard
 */
?>
<!-- Breadcrumb -->
<nav class="breadcrumb" aria-label="Ruta de navegación">
  <span>🏠 Dashboard</span>
</nav>

<!-- Estadísticas -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-value"><?= $stats['ligas'] ?></div>
    <div class="stat-label">🏆 Campeonatos</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['estadios'] ?></div>
    <div class="stat-label">🏟️ Estadios</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['equipos'] ?></div>
    <div class="stat-label">👕 Equipos</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['encuentros'] ?></div>
    <div class="stat-label">📅 Encuentros</div>
  </div>
</div>

<!-- Grid: próximos + últimos resultados -->
<div style="display:grid;gap:20px;grid-template-columns:1fr">

  <!-- Próximos Encuentros -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">📅 Próximos Encuentros</h2>
      <a href="<?= url('encuentros') ?>" class="btn btn-outline btn-sm">Ver todos</a>
    </div>

    <?php if (empty($proximosEncuentros)): ?>
      <div class="empty-state">
        <div class="empty-icon">📅</div>
        <p>No hay encuentros programados</p>
        <a href="<?= url('encuentros/crear') ?>" class="btn btn-primary btn-sm">
          + Programar encuentro
        </a>
      </div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Local</th>
              <th>Visitante</th>
              <th>Estadio</th>
              <th>Campeonato</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($proximosEncuentros as $m): ?>
            <tr>
              <td>
                <?php if ($m['status'] === 'unscheduled' || empty($m['match_date'])): ?>
                   <span class="badge badge-warning">Por programar</span>
                <?php else: ?>
                   <?= date('d/m/Y', strtotime($m['match_date'])) ?>
                <?php endif; ?>
              </td>
              <td><?= empty($m['match_time']) ? '—' : e(substr($m['match_time'], 0, 5)) ?></td>
              <td style="text-align:right">
                 <strong><?= e($m['home_team']) ?></strong>
                 <?php if(!empty($m['home_logo'])): ?><img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="16" height="16" style="border-radius:50%;vertical-align:middle;margin-left:6px"><?php else: ?><span style="margin-left:6px;font-size:12px">🛡️</span><?php endif; ?>
              </td>
              <td style="text-align:left">
                 <?php if(!empty($m['away_logo'])): ?><img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="16" height="16" style="border-radius:50%;vertical-align:middle;margin-right:6px"><?php else: ?><span style="margin-right:6px;font-size:12px">🛡️</span><?php endif; ?>
                 <?= e($m['away_team']) ?>
              </td>
              <td><?= e($m['stadium'] ?? 'Por asignar') ?></td>
              <td><span class="badge badge-info"><?= e($m['league']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Últimos Resultados -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">🏁 Últimos Resultados</h2>
      <a href="<?= url('posiciones') ?>" class="btn btn-outline btn-sm">Ver posiciones</a>
    </div>

    <?php if (empty($ultimosResultados)): ?>
      <div class="empty-state">
        <div class="empty-icon">🏁</div>
        <p>No hay resultados registrados aún</p>
      </div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Local</th>
              <th>Resultado</th>
              <th>Visitante</th>
              <th>Campeonato</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ultimosResultados as $m): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($m['match_date'])) ?></td>
              <td style="text-align:right">
                 <?= e($m['home_team']) ?>
                 <?php if(!empty($m['home_logo'])): ?><img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="16" height="16" style="border-radius:50%;vertical-align:middle;margin-left:6px"><?php else: ?><span style="margin-left:6px;font-size:12px">🛡️</span><?php endif; ?>
              </td>
              <td style="text-align:center;font-weight:700;font-size:1.1rem">
                <?= $m['home_goals'] ?? '—' ?> : <?= $m['away_goals'] ?? '—' ?>
              </td>
              <td style="text-align:left">
                 <?php if(!empty($m['away_logo'])): ?><img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="16" height="16" style="border-radius:50%;vertical-align:middle;margin-right:6px"><?php else: ?><span style="margin-right:6px;font-size:12px">🛡️</span><?php endif; ?>
                 <?= e($m['away_team']) ?>
              </td>
              <td><span class="badge badge-info"><?= e($m['league']) ?></span></td>
              <td>
                <a href="<?= url('resultados/' . $m['id']) ?>" class="btn btn-outline btn-sm">
                  Ver detalle
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Accesos rápidos -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px">
  <a href="<?= url('ligas/crear') ?>" class="card" style="text-align:center;padding:24px;cursor:pointer;transition:var(--transition);text-decoration:none">
    <div style="font-size:2rem;margin-bottom:8px">🏆</div>
    <div style="font-weight:600;color:var(--color-text)">Nuevo Campeonato</div>
  </a>
  <a href="<?= url('equipos/crear') ?>" class="card" style="text-align:center;padding:24px;cursor:pointer;transition:var(--transition);text-decoration:none">
    <div style="font-size:2rem;margin-bottom:8px">👕</div>
    <div style="font-weight:600;color:var(--color-text)">Nuevo Equipo</div>
  </a>
  <a href="<?= url('encuentros/crear') ?>" class="card" style="text-align:center;padding:24px;cursor:pointer;transition:var(--transition);text-decoration:none">
    <div style="font-size:2rem;margin-bottom:8px">📅</div>
    <div style="font-weight:600;color:var(--color-text)">Nuevo Encuentro</div>
  </a>
  <a href="<?= url('estadios/crear') ?>" class="card" style="text-align:center;padding:24px;cursor:pointer;transition:var(--transition);text-decoration:none">
    <div style="font-size:2rem;margin-bottom:8px">🏟️</div>
    <div style="font-weight:600;color:var(--color-text)">Nuevo Estadio</div>
  </a>
</div>
