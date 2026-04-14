<?php /** app/Views/referees/index.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <span>Árbitros</span>
</nav>

<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <h1 class="card-title" style="margin:0">🟨 Árbitros</h1>
    <div style="display:flex;gap:8px">
      <a href="<?= BASE_URL ?>/exportar/arbitros/pdf" target="_blank" class="btn btn-sm" style="background:#1e40af;color:#fff;border:none">📄 PDF</a>
      <a href="<?= BASE_URL ?>/exportar/arbitros/excel" class="btn btn-sm" style="background:#047857;color:#fff;border:none">📊 Excel</a>
      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('arbitros/crear') ?>" class="btn btn-primary btn-sm">+ Nuevo Árbitro</a>
      <?php endif; ?>
    </div>
  </div>
  
  <?php if (empty($referees)): ?>
    <div class="empty-state">
      <div class="empty-icon">🟨</div>
      <p>No hay árbitros registrados aún</p>
      <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= url('arbitros/crear') ?>" class="btn btn-primary">+ Registrar Primer Árbitro</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Licencia / Carnet</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <?php if ($_SESSION['user_role'] === 'admin'): ?><th>Acciones</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($referees as $r): ?>
          <tr>
            <td><strong><?= e($r['name']) ?></strong></td>
            <td style="font-family: monospace; color:var(--color-primary)"><?= e($r['license'] ?? 'N/A') ?></td>
            <td><?= e($r['phone'] ?? '—') ?></td>
            <td>
              <span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                <?= $r['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <td>
              <div class="actions">
                <a href="<?= url("arbitros/editar/{$r['id']}") ?>" class="btn btn-warning btn-sm" title="Editar">✏️</a>
                <form action="<?= url("arbitros/eliminar/{$r['id']}") ?>" method="POST" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="¿Eliminar a este árbitro de forma permanente?">🗑️</button>
                </form>
              </div>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
