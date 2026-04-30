<?php /** app/Views/transfers/request.php — Team Manager */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Solicitar Pase</span>
</nav>

<div style="display:grid;gap:20px;grid-template-columns:1fr 1fr;align-items:start">

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">🔄 Solicitar Pase de Jugador</h2>
      <?php if ($team): ?>
        <p style="font-size:.85rem;color:var(--color-text-muted);margin-top:4px">Equipo: <strong><?= e($team['name']) ?></strong></p>
      <?php endif; ?>
    </div>
    <form action="<?= url('pases/solicitar') ?>" method="POST">
      <div class="form-group">
        <label class="form-label">Jugador a ceder <span style="color:var(--color-danger)">*</span></label>
        <select name="player_id" class="form-control" required>
          <option value="">— Selecciona jugador —</option>
          <?php foreach ($myPlayers as $p): ?>
            <option value="<?= (int)$p['id'] ?>">
              <?= e($p['name']) ?> — Cédula: <?= e($p['cedula']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Campeonato <span style="color:var(--color-danger)">*</span></label>
        <select name="league_id" class="form-control" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($leagues as $l): ?>
            <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?> — <?= e($l['season']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Equipo destino <span style="color:var(--color-danger)">*</span></label>
        <select name="to_team_id" class="form-control" required>
          <option value="">— Selecciona equipo destino —</option>
          <?php foreach ($allTeams as $t): ?>
            <?php if ($t['id'] == ($team['id'] ?? 0)) continue; ?>
            <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?> · <?= e($t['league_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Notas</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Motivo o condiciones del pase..."></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">📤 Enviar Solicitud</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">📊 Mis Solicitudes de Pase</h2></div>
    <?php if (empty($myTransfers)): ?>
      <div class="empty-state" style="padding:20px"><p>No hay solicitudes aún</p></div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead><tr><th>Jugador</th><th>De → A</th><th>Estado</th></tr></thead>
          <tbody>
            <?php foreach ($myTransfers as $tr): ?>
            <tr>
              <td><?= e($tr['player_name']) ?></td>
              <td style="font-size:.8rem"><?= e($tr['from_team_name']) ?> → <?= e($tr['to_team_name']) ?></td>
              <td>
                <?php
                  $bc = match($tr['status']) { 'approved'=>'badge-success','rejected'=>'badge-danger', default=>'badge-warning' };
                  $lb = match($tr['status']) { 'approved'=>'Aprobado','rejected'=>'Rechazado', default=>'Pendiente' };
                ?>
                <span class="badge <?= $bc ?>"><?= $lb ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
