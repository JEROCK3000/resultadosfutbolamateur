<?php /** app/Views/transfers/index.php — Admin */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Pases y Transferencias</span>
</nav>

<!-- Crear ventana de pase -->
<div class="card mb-3">
  <div class="card-header"><h2 class="card-title">🪟 Ventanas de Pase</h2></div>
  <form action="<?= url('pases/ventana/guardar') ?>" method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding:0 0 4px">
    <div class="form-group" style="flex:1;min-width:180px;margin:0">
      <label class="form-label">Campeonato</label>
      <select name="league_id" class="form-control" required>
        <option value="">— Selecciona —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="flex:2;min-width:160px;margin:0">
      <label class="form-label">Nombre de la ventana</label>
      <input type="text" name="name" class="form-control" placeholder="ej. Ventana Enero 2026" required>
    </div>
    <div class="form-group" style="flex:1;min-width:130px;margin:0">
      <label class="form-label">Apertura</label>
      <input type="date" name="opens_at" class="form-control" required>
    </div>
    <div class="form-group" style="flex:1;min-width:130px;margin:0">
      <label class="form-label">Cierre</label>
      <input type="date" name="closes_at" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary" style="white-space:nowrap">+ Crear Ventana</button>
  </form>

  <?php if ($windows): ?>
  <div class="table-wrapper" style="margin-top:16px">
    <table class="table">
      <thead><tr><th>Campeonato</th><th>Nombre</th><th>Apertura</th><th>Cierre</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($windows as $w): ?>
        <tr>
          <td><?= e($w['league_name']) ?></td>
          <td><?= e($w['name']) ?></td>
          <td><?= date('d/m/Y', strtotime($w['opens_at'])) ?></td>
          <td><?= date('d/m/Y', strtotime($w['closes_at'])) ?></td>
          <td><span class="badge <?= $w['status']==='active' ? 'badge-success' : 'badge-muted' ?>"><?= $w['status']==='active' ? 'Activa' : 'Cerrada' ?></span></td>
          <td>
            <?php if ($w['status']==='active'): ?>
            <form action="<?= url("pases/ventana/cerrar/{$w['id']}") ?>" method="POST">
              <button class="btn btn-warning btn-sm" data-confirm="¿Cerrar esta ventana de pase?">🔒 Cerrar</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Solicitudes de pase -->
<div class="card">
  <div class="card-header"><h2 class="card-title">🔄 Solicitudes de Pase</h2></div>
  <?php if (empty($transfers)): ?>
    <div class="empty-state"><p>No hay solicitudes de pase</p></div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Jugador</th><th>De</th><th>A</th><th>Liga</th><th>Ventana</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($transfers as $t): ?>
          <tr>
            <td><strong><?= e($t['player_name']) ?></strong><br><small style="color:var(--color-text-muted)"><?= e($t['cedula']) ?></small></td>
            <td><?= e($t['from_team_name']) ?></td>
            <td><?= e($t['to_team_name']) ?></td>
            <td style="font-size:.82rem"><?= e($t['league_name']) ?></td>
            <td style="font-size:.8rem"><?= $t['window_name'] ? e($t['window_name']) : '—' ?></td>
            <td style="font-size:.8rem;color:var(--color-text-muted)"><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
            <td>
              <?php
                $bc = match($t['status']) { 'approved'=>'badge-success','rejected'=>'badge-danger', default=>'badge-warning' };
                $lb = match($t['status']) { 'approved'=>'Aprobado','rejected'=>'Rechazado', default=>'Pendiente' };
              ?>
              <span class="badge <?= $bc ?>"><?= $lb ?></span>
            </td>
            <td>
              <?php if ($t['status']==='pending'): ?>
              <div class="actions">
                <form action="<?= url("pases/aprobar/{$t['id']}") ?>" method="POST" style="display:inline">
                  <button class="btn btn-success btn-sm" data-confirm="¿Aprobar y ejecutar este pase?">✅</button>
                </form>
                <form action="<?= url("pases/rechazar/{$t['id']}") ?>" method="POST" style="display:inline">
                  <button class="btn btn-danger btn-sm" data-confirm="¿Rechazar esta solicitud?">❌</button>
                </form>
              </div>
              <?php else: ?>
                <span style="font-size:.8rem;color:var(--color-text-muted)"><?= e($t['reviewed_name'] ?? '—') ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
