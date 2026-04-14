<?php /** app/Views/public/matches.php — Próximos encuentros con filtro de estadio */ ?>
<?php $pageTitle = 'Encuentros — '.$league['name']; ?>
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <div>
    <h1 style="font-family:var(--font-head);font-size:clamp(1.2rem,4vw,1.6rem);font-weight:800">
      📅 Próximos Encuentros
    </h1>
    <p style="color:var(--pub-muted);font-size:.85rem">🏆 <?= e($league['name']) ?> · <?= e($league['season']) ?></p>
  </div>
  <a href="<?= BASE_URL ?>/principal/liga/<?= (int)$league['id'] ?>" class="pub-badge pb-blue" style="text-decoration:none;padding:7px 14px">
    ← Posiciones
  </a>
</div>

<!-- Filtro por estadio -->
<form method="GET" style="margin-bottom:20px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label style="display:block;font-size:.8rem;color:var(--pub-muted);margin-bottom:4px">🏟️ Filtrar por Estadio</label>
      <select name="estadio" onchange="this.form.submit()"
              style="background:var(--pub-surface);border:1px solid var(--pub-border);color:var(--pub-text);
                     padding:8px 12px;border-radius:8px;font-size:.87rem;cursor:pointer">
        <option value="">— Todos los estadios —</option>
        <?php foreach ($stadiums as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= ($stadiumFilter===$s['id']?'selected':'') ?>>
            <?= e($s['name']) ?> — <?= e($s['city']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($stadiumFilter): ?>
      <a href="?" style="padding:8px 14px;border-radius:8px;font-size:.82rem;color:var(--pub-muted);
                         border:1px solid var(--pub-border);text-decoration:none;display:inline-flex;align-items:center">
        ✕ Limpiar
      </a>
    <?php endif; ?>
  </div>
</form>

<!-- Botones exportación -->
<div class="export-bar">
  <a href="<?= BASE_URL ?>/exportar/encuentros/pdf/<?= (int)$league['id'] ?>?estadio=<?= $stadiumFilter ?>" class="btn-export btn-pdf" target="_blank">📄 PDF</a>
  <a href="<?= BASE_URL ?>/exportar/encuentros/excel/<?= (int)$league['id'] ?>?estadio=<?= $stadiumFilter ?>" class="btn-export btn-excel">📊 Excel</a>
</div>

<!-- Listado de encuentros -->
<?php if (empty($upcomingMatches)): ?>
<div class="pub-card" style="text-align:center;padding:40px">
  <div style="font-size:3rem;margin-bottom:12px">📅</div>
  <p style="color:var(--pub-muted)">
    No hay encuentros próximos<?= $stadiumFilter ? ' en el estadio seleccionado' : '' ?>.
  </p>
</div>
<?php else: ?>
<div class="pub-card" style="padding:0;overflow:hidden">
  <div class="pub-table-wrap">
    <table class="pub-table">
      <thead>
        <tr><th>Fecha</th><th>Hora</th><th>Local</th><th></th><th>Visitante</th><th>Estadio</th><th>Árbitro</th></tr>
      </thead>
      <tbody>
        <?php foreach ($upcomingMatches as $m): ?>
        <tr>
          <td style="white-space:nowrap">
            <?php if ($m['status'] === 'unscheduled' || empty($m['match_date'])): ?>
               <strong style="color:#d97706;font-size:.85rem">Por programar</strong>
            <?php else: ?>
               <strong><?= date('d/m/Y', strtotime($m['match_date'])) ?></strong>
               <span style="font-size:.72rem;color:var(--pub-muted);display:block">
                 <?= strftime('%A', strtotime($m['match_date'])) ?>
               </span>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap;font-weight:600"><?= empty($m['match_time']) ? '—' : e(substr($m['match_time'],0,5)).'h' ?></td>
          <td style="text-align:right">
             <strong><?= e($m['home_team']) ?></strong>
             <?php if (!empty($m['home_logo'])): ?>
                 <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="24" height="24" style="object-fit:contain;vertical-align:middle;margin-left:8px;border-radius:50%">
             <?php else: ?>
                 <span style="font-size:16px;vertical-align:middle;margin-left:8px">🛡️</span>
             <?php endif; ?>
          </td>
          <td style="text-align:center;color:var(--pub-muted);font-size:.8rem">vs</td>
          <td>
             <?php if (!empty($m['away_logo'])): ?>
                 <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="24" height="24" style="object-fit:contain;vertical-align:middle;margin-right:8px;border-radius:50%">
             <?php else: ?>
                 <span style="font-size:16px;vertical-align:middle;margin-right:8px">🛡️</span>
             <?php endif; ?>
             <strong><?= e($m['away_team']) ?></strong>
          </td>
          <td style="font-size:.8rem;color:var(--pub-muted)"><?= e($m['stadium'] ?? 'Por asignar') ?></td>
          <td style="font-size:.8rem;color:var(--pub-muted)"><small>🟨 <?= e($m['referee_name'] ?? '—') ?></small></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
