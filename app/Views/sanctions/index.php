<?php /** app/Views/sanctions/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Sanciones y Estadísticas</span>
</nav>

<!-- Filtro por liga -->
<div class="card mb-3">
  <form action="<?= url('sanciones') ?>" method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="flex:1;min-width:220px;margin:0">
      <label class="form-label">Filtrar por campeonato</label>
      <select name="league_id" class="form-control" onchange="this.form.submit()">
        <option value="">— Todos —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>" <?= ($leagueId ?? 0)==$l['id'] ? 'selected' : '' ?>>
            <?= e($l['name']) ?> — <?= e($l['season']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php if (!empty($topScorers)): ?>
<!-- Top Goleadores -->
<div class="card mb-3">
  <div class="card-header"><h2 class="card-title">⚽ Top Goleadores</h2></div>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>#</th><th>Jugador</th><th>Equipo</th><th>Goles</th></tr></thead>
      <tbody>
        <?php foreach ($topScorers as $i => $s): ?>
        <tr>
          <td><strong><?= $i+1 ?></strong></td>
          <td><?= e($s['player_name']) ?> <small style="color:var(--color-text-muted)"><?= e($s['cedula']) ?></small></td>
          <td><?= e($s['team_name']) ?></td>
          <td><span class="badge badge-success">⚽ <?= (int)$s['goals'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Sanción disciplinaria manual -->
<?php if (($_SESSION['user_role']??'') === 'admin'): ?>
<div class="card mb-3">
  <div class="card-header"><h2 class="card-title">➕ Nueva Sanción Disciplinaria</h2></div>
  <form action="<?= url('sanciones/crear') ?>" method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
    <div class="form-group" style="flex:1;min-width:180px;margin:0">
      <label class="form-label">Campeonato</label>
      <select name="league_id" class="form-control" required id="sanLeague" onchange="loadPlayers(this.value)">
        <option value="">— Selecciona —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>" <?= ($leagueId ?? 0)==$l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="flex:2;min-width:180px;margin:0">
      <label class="form-label">Jugador</label>
      <input type="text" name="player_id" class="form-control" placeholder="ID de jugador" required>
    </div>
    <div class="form-group" style="flex:3;min-width:200px;margin:0">
      <label class="form-label">Motivo</label>
      <input type="text" name="reason" class="form-control" required placeholder="Motivo de la sanción">
    </div>
    <div class="form-group" style="flex:1;min-width:100px;margin:0">
      <label class="form-label">Partidos</label>
      <input type="number" name="matches_qty" class="form-control" min="0" value="0">
    </div>
    <div class="form-group" style="flex:1;min-width:100px;margin:0">
      <label class="form-label">Multa USD</label>
      <input type="text" name="fine_usd" class="form-control" value="0.00">
    </div>
    <button type="submit" class="btn btn-danger">Sancionar</button>
  </form>
</div>
<?php endif; ?>

<!-- Listado de sanciones -->
<div class="card">
  <div class="card-header"><h2 class="card-title">📋 Registro de Sanciones</h2></div>
  <?php if (empty($sanctions)): ?>
    <div class="empty-state"><p>No hay sanciones registradas<?= $leagueId ? ' para este campeonato' : '' ?></p></div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Jugador</th><th>Equipo</th><th>Liga</th><th>Tipo</th><th>Motivo</th>
            <th>Partidos</th><th>Multa</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sanctions as $s): ?>
          <tr style="<?= !$s['active'] ? 'opacity:.55' : '' ?>">
            <td>
              <strong><?= e($s['player_name']) ?></strong><br>
              <small style="color:var(--color-text-muted)"><?= e($s['cedula']) ?></small>
            </td>
            <td style="font-size:.82rem"><?= e($s['team_name'] ?? '—') ?></td>
            <td style="font-size:.8rem"><?= e($s['league_name']) ?></td>
            <td>
              <span class="badge <?= $s['type']==='auto' ? 'badge-warning' : 'badge-danger' ?>">
                <?= $s['type']==='auto' ? '⚙️ Auto' : '👨‍⚖️ Discipl.' ?>
              </span>
            </td>
            <td style="font-size:.82rem;max-width:180px"><?= e($s['reason']) ?></td>
            <td style="text-align:center">
              <?php if ($s['matches_qty'] > 0): ?>
                <span class="badge <?= $s['active'] ? 'badge-danger' : 'badge-muted' ?>">
                  <?= (int)$s['matches_served'] ?>/<?= (int)$s['matches_qty'] ?>
                </span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:center">
              <?php if ($s['fine_usd'] > 0): ?>
                <span class="badge <?= $s['fine_paid'] ? 'badge-success' : 'badge-warning' ?>">
                  $<?= number_format((float)$s['fine_usd'], 2) ?>
                  <?= $s['fine_paid'] ? '✓' : '' ?>
                </span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $s['active'] ? 'badge-danger' : 'badge-muted' ?>">
                <?= $s['active'] ? 'Activa' : 'Cumplida/Anulada' ?>
              </span>
            </td>
            <td>
              <div class="actions">
                <?php if ($s['active'] && $s['matches_qty'] > $s['matches_served']): ?>
                <form action="<?= url("sanciones/cumplir/{$s['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="league_id" value="<?= (int)$s['league_id'] ?>">
                  <button class="btn btn-warning btn-sm" title="Registrar partido cumplido">🔢 Partido</button>
                </form>
                <?php endif; ?>
                <?php if ($s['fine_usd'] > 0 && !$s['fine_paid']): ?>
                <form action="<?= url("sanciones/pagar/{$s['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="league_id" value="<?= (int)$s['league_id'] ?>">
                  <button class="btn btn-success btn-sm" title="Marcar multa pagada">💵 Pagar</button>
                </form>
                <?php endif; ?>
                <?php if ($s['active'] && ($_SESSION['user_role']??'')==='admin'): ?>
                <form action="<?= url("sanciones/anular/{$s['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="league_id" value="<?= (int)$s['league_id'] ?>">
                  <button class="btn btn-danger btn-sm" data-confirm="¿Anular esta sanción?">🗑️</button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
