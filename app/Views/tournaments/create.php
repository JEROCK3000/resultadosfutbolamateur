<?php /** app/Views/tournaments/create.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('posiciones') ?>">Posiciones</a><span class="breadcrumb-sep">›</span>
  <span>Nueva Fase Final</span>
</nav>
<div class="card" style="max-width:620px">
  <div class="card-header">
    <h1 class="card-title">🎯 Generar Fase Final</h1>
    <span class="badge badge-info"><?= e($league['name']) ?></span>
  </div>

  <?php if (empty($standings)): ?>
    <div class="empty-state">
      <div class="empty-icon">📊</div>
      <p>No hay equipos con partidos jugados en este campeonato aún</p>
    </div>
  <?php else: ?>
    <p style="margin-bottom:20px;color:var(--color-text-muted)">
      Equipos disponibles: <strong><?= count($standings) ?></strong>
    </p>
    <form action="<?= url('torneos/generar') ?>" method="POST">
      <input type="hidden" name="league_id" value="<?= (int)$league['id'] ?>">

      <div class="form-group">
        <label class="form-label" for="name">Nombre de la Fase Final <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required
               placeholder="ej. Playoffs 2025" value="Campeonato <?= e($league['name']) ?> — Fase Final">
      </div>

      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label" for="teams_in">Cantidad de Equipos</label>
          <select id="teams_in" name="teams_in" class="form-control">
            <?php foreach ([2,4,8,16] as $n): ?>
              <?php if ($n <= count($standings)): ?>
                <option value="<?= $n ?>" <?= ($n === min(8, count($standings)) ? 'selected' : '') ?>>
                  <?= $n ?> equipos
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="type">Tipo de Cruces</label>
          <select id="type" name="type" class="form-control">
            <option value="knockout">⚡ Estándar (1°vs2°, 3°vs4°…)</option>
            <option value="seeded">🔀 Cruzado (1°vsÚltimo, 2°vsPenúltimo…)</option>
            <option value="random">🎲 Sorteo Aleatorio</option>
          </select>
        </div>
      </div>

      <!-- Vista previa de posiciones -->
      <div style="margin-bottom:20px">
        <p style="font-size:.85rem;font-weight:600;color:var(--color-text-muted);margin-bottom:8px">
          Posiciones actuales del campeonato:
        </p>
        <div class="table-wrapper">
          <table class="table" style="font-size:.82rem">
            <thead><tr><th>#</th><th>Equipo</th><th>PJ</th><th>PTS</th><th>DG</th></tr></thead>
            <tbody>
              <?php foreach ($standings as $i => $row): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= e($row['name']) ?></td>
                <td><?= $row['PJ'] ?></td>
                <td><strong><?= $row['PTS'] ?></strong></td>
                <td><?= $row['DG'] >= 0 ? '+' : '' ?><?= $row['DG'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">🎯 Generar Llave</button>
        <a href="<?= url("posiciones/{$league['id']}") ?>" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  <?php endif; ?>
</div>
