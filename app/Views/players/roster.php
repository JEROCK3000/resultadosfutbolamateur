<?php /** app/Views/players/roster.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('jugadores') ?>">Jugadores</a><span class="breadcrumb-sep">›</span>
  <span><?= e($team['name']) ?></span>
</nav>

<?php
$posLabel = ['portero'=>'POR','defensa'=>'DEF','mediocampista'=>'MED','delantero'=>'DEL','otro'=>'—'];
$active   = array_filter($players, fn($p) => $p['member_status'] === 'active');
$inactive = array_filter($players, fn($p) => $p['member_status'] !== 'active');
?>

<div class="card">
  <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
    <div>
      <h1 class="card-title" style="margin:0">👕 <?= e($team['name']) ?> — <?= e($league['name']) ?></h1>
      <p style="font-size:.82rem; color:var(--color-text-muted); margin:4px 0 0">
        <?= count($active) ?> jugadores activos<?= count($inactive) > 0 ? ' · ' . count($inactive) . ' inactivos' : '' ?>
      </p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap">
      <a href="<?= url("jugadores/importar/{$team['id']}/{$league['id']}") ?>" class="btn btn-sm btn-secondary">📥 Importar CSV</a>
      <a href="<?= url("jugadores/template/{$team['id']}/{$league['id']}") ?>" class="btn btn-sm btn-secondary">📄 Template CSV</a>
      <a href="<?= url("jugadores/crear/{$team['id']}/{$league['id']}") ?>" class="btn btn-sm btn-primary">+ Agregar Jugador</a>
    </div>
  </div>

  <?php if (empty($players)): ?>
    <div class="empty-state">
      <div class="empty-icon">👤</div>
      <p>No hay jugadores inscritos en este equipo para este campeonato.</p>
      <a href="<?= url("jugadores/crear/{$team['id']}/{$league['id']}") ?>" class="btn btn-primary">+ Agregar Jugador</a>
    </div>
  <?php else: ?>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>Nombre</th>
            <th style="width:130px">Cédula</th>
            <th style="width:70px; text-align:center">Pos.</th>
            <th style="width:110px">Nacimiento</th>
            <th style="width:90px; text-align:center">Estado</th>
            <th style="width:110px">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($players as $p): ?>
          <tr style="<?= $p['member_status'] !== 'active' ? 'opacity:.55' : '' ?>">
            <td style="font-weight:700; text-align:center; color:var(--color-primary)"><?= $p['number'] ?? '—' ?></td>
            <td>
              <strong><?= e($p['name']) ?></strong>
            </td>
            <td style="font-size:.83rem; color:var(--color-text-muted)"><?= e($p['cedula']) ?></td>
            <td style="text-align:center">
              <span class="badge badge-info" style="font-size:.7rem"><?= $posLabel[$p['position']] ?? '—' ?></span>
            </td>
            <td style="font-size:.82rem">
              <?= $p['birth_date'] ? date('d/m/Y', strtotime($p['birth_date'])) : '<span class="text-muted">—</span>' ?>
            </td>
            <td style="text-align:center">
              <?php if ($p['member_status'] === 'active'): ?>
                <span class="badge badge-success">Activo</span>
              <?php elseif ($p['member_status'] === 'suspended'): ?>
                <span class="badge badge-danger">Suspendido</span>
              <?php else: ?>
                <span class="badge badge-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="actions" style="gap:4px">
                <a href="<?= url("jugadores/editar/{$p['id']}?team_id={$team['id']}&league_id={$league['id']}") ?>"
                   class="btn btn-warning btn-sm" title="Editar">✏️</a>
                <form action="<?= url("jugadores/eliminar/{$p['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="team_id"   value="<?= $team['id'] ?>">
                  <input type="hidden" name="league_id" value="<?= $league['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar a <?= e($p['name']) ?>?">🗑️</button>
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
