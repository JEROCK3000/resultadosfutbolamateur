<?php /** app/Views/registrations/index.php — Admin */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Inscripciones</span>
</nav>
<div class="card">
  <div class="card-header">
    <h1 class="card-title">📋 Solicitudes de Inscripción</h1>
  </div>
  <?php if (empty($registrations)): ?>
    <div class="empty-state"><div class="empty-icon">📋</div><p>No hay solicitudes de inscripción</p></div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr><th>Equipo</th><th>Campeonato</th><th>Solicitado por</th><th>Fecha</th><th>Estado</th><th>Notas</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($registrations as $r): ?>
          <tr>
            <td><strong><?= e($r['team_name']) ?></strong></td>
            <td><?= e($r['league_name']) ?></td>
            <td style="font-size:.82rem"><?= e($r['submitted_name']) ?></td>
            <td style="font-size:.8rem;color:var(--color-text-muted)"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
            <td>
              <?php
                $badgeClass = match($r['status']) { 'approved'=>'badge-success','rejected'=>'badge-danger', default=>'badge-warning' };
                $label      = match($r['status']) { 'approved'=>'Aprobada','rejected'=>'Rechazada', default=>'Pendiente' };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
            </td>
            <td style="font-size:.82rem;max-width:200px"><?= e($r['notes'] ?? '—') ?></td>
            <td>
              <?php if ($r['status'] === 'pending'): ?>
              <div class="actions">
                <form action="<?= url("inscripciones/aprobar/{$r['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="notes" value="Aprobado por administrador">
                  <button class="btn btn-success btn-sm" data-confirm="¿Aprobar inscripción de «<?= e($r['team_name']) ?>»?">✅ Aprobar</button>
                </form>
                <form action="<?= url("inscripciones/rechazar/{$r['id']}") ?>" method="POST" style="display:inline">
                  <input type="hidden" name="notes" value="">
                  <button class="btn btn-danger btn-sm" data-confirm="¿Rechazar inscripción de «<?= e($r['team_name']) ?>»?">❌ Rechazar</button>
                </form>
              </div>
              <?php else: ?>
                <span style="font-size:.8rem;color:var(--color-text-muted)"><?= e($r['reviewed_name'] ?? '—') ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
