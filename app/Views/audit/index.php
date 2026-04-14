<?php /** app/Views/audit/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Auditoría</span>
</nav>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-header"><h1 class="card-title">🔍 Filtrar Registros</h1></div>
  <form method="GET" action="<?= url('auditoria') ?>">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">
      <div class="form-group">
        <label class="form-label">Usuario</label>
        <select name="user_id" class="form-control">
          <option value="">— Todos —</option>
          <?php foreach ($usersWithLog as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= (($_GET['user_id']??'')==$u['id']?'selected':'') ?>>
              <?= e($u['name']) ?> (<?= e($u['role']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Acción</label>
        <select name="action" class="form-control">
          <option value="">— Todas —</option>
          <?php foreach ($actions as $a): ?>
            <option value="<?= $a ?>" <?= (($_GET['action']??'')===$a?'selected':'') ?>><?= ucfirst($a) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Desde</label>
        <input type="date" name="date_from" class="form-control" value="<?= e($_GET['date_from'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Hasta</label>
        <input type="date" name="date_to" class="form-control" value="<?= e($_GET['date_to'] ?? '') ?>">
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end;gap:8px">
        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
        <a href="<?= url('auditoria') ?>" class="btn btn-secondary">✕</a>
      </div>
    </div>
  </form>
</div>

<!-- Tabla de logs -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">📋 Registro de Actividad</h2>
    <span class="badge badge-muted"><?= count($logs) ?> registros</span>
  </div>
  <?php if (empty($logs)): ?>
    <div class="empty-state"><div class="empty-icon">📋</div><p>No hay registros con los filtros seleccionados</p></div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table" style="font-size:.83rem">
        <thead><tr><th>Fecha/Hora</th><th>Usuario</th><th>Rol</th><th>Acción</th><th>Entidad</th><th>Descripción</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
          <?php
            $actionBadge = match($log['action']) {
              'login'  => 'badge-success', 'logout' => 'badge-muted',
              'create' => 'badge-info',    'update' => 'badge-warning',
              'delete' => 'badge-danger',  default  => 'badge-muted'
            };
          ?>
          <tr>
            <td style="white-space:nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
            <td><?= e($log['user_name'] ?? '—') ?></td>
            <td><?= e($log['user_role'] ?? '—') ?></td>
            <td><span class="badge <?= $actionBadge ?>"><?= ucfirst(e($log['action'])) ?></span></td>
            <td><?= e($log['entity_type'] ?? '—') ?><?= $log['entity_id'] ? " #".(int)$log['entity_id'] : '' ?></td>
            <td><?= e($log['description']) ?></td>
            <td style="font-family:monospace;font-size:.75rem"><?= e($log['ip_address'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
