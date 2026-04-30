<?php /** app/Views/registrations/create.php — Team Manager */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Solicitar Inscripción</span>
</nav>

<div style="display:grid;gap:20px;grid-template-columns:1fr 1fr;align-items:start">

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">📋 Nueva Solicitud</h2>
      <?php if ($team): ?>
        <p style="font-size:.85rem;color:var(--color-text-muted);margin-top:4px">Equipo: <strong><?= e($team['name']) ?></strong></p>
      <?php endif; ?>
    </div>
    <form action="<?= url('inscripciones/solicitar') ?>" method="POST">
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
        <label class="form-label">Notas adicionales</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Información relevante para el administrador..."></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">📤 Enviar Solicitud</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">📊 Mis Solicitudes</h2></div>
    <?php if (empty($mine)): ?>
      <div class="empty-state" style="padding:20px"><p>No tienes solicitudes aún</p></div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead><tr><th>Campeonato</th><th>Fecha</th><th>Estado</th></tr></thead>
          <tbody>
            <?php foreach ($mine as $m): ?>
            <tr>
              <td><?= e($m['league_name']) ?></td>
              <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
              <td>
                <?php
                  $bc = match($m['status']) { 'approved'=>'badge-success','rejected'=>'badge-danger', default=>'badge-warning' };
                  $lb = match($m['status']) { 'approved'=>'Aprobada','rejected'=>'Rechazada', default=>'Pendiente' };
                ?>
                <span class="badge <?= $bc ?>"><?= $lb ?></span>
                <?php if ($m['notes']): ?>
                  <p style="font-size:.75rem;color:var(--color-text-muted);margin-top:4px"><?= e($m['notes']) ?></p>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
