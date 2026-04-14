<?php /** app/Views/public/league.php — Posiciones y equipos de un campeonato */ ?>
<?php $pageTitle = $league['name']; ?>
<div style="margin-bottom:24px">
  <h1 style="font-family:var(--font-head);font-size:clamp(1.3rem,4vw,1.8rem);font-weight:800">
    🏆 <?= e($league['name']) ?>
  </h1>
  <p style="color:var(--pub-muted);font-size:.85rem"><?= e($league['country']) ?> · Temporada <?= e($league['season']) ?></p>
  <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
    <a href="<?= BASE_URL ?>/principal/liga/<?= (int)$league['id'] ?>/encuentros" class="pub-badge pb-blue" style="text-decoration:none;padding:6px 14px">
      📅 Próximos Encuentros →
    </a>
    <a href="<?= BASE_URL ?>/principal/liga/<?= (int)$league['id'] ?>/resultados" class="pub-badge pb-blue" style="text-decoration:none;padding:6px 14px; background:rgba(52,211,153,.1); color:#34d399; border-color:rgba(52,211,153,.2)">
      ⚽ Resultados de la Jornada →
    </a>
  </div>
</div>

<!-- Botones de exportación -->
<div class="export-bar">
  <a href="<?= BASE_URL ?>/exportar/posiciones/pdf/<?= (int)$league['id'] ?>" class="btn-export btn-pdf" target="_blank">📄 Exportar PDF</a>
  <a href="<?= BASE_URL ?>/exportar/posiciones/excel/<?= (int)$league['id'] ?>" class="btn-export btn-excel">📊 Exportar Excel</a>
</div>

<!-- Tabla de posiciones -->
<div class="pub-card">
  <div class="pub-card-title">📊 Tabla de Posiciones</div>
  <?php if (empty($standings)): ?>
    <p style="color:var(--pub-muted)">Aún no hay resultados registrados en este campeonato.</p>
  <?php else: ?>
    <div class="pub-table-wrap">
      <table class="pub-table">
        <thead>
          <tr>
            <th>#</th><th>Equipo</th><th>PJ</th><th>PG</th><th>PE</th>
            <th>PP</th><th>GF</th><th>GC</th><th>DG</th><th>TA</th><th>TR</th><th>PTS</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($standings as $pos => $row): ?>
          <tr style="<?= $pos===0 ? 'background:rgba(96,165,250,.06)' : '' ?>">
            <td><strong style="color:<?= $pos<3 ? '#fbbf24' : 'inherit' ?>"><?= $pos+1 ?></strong></td>
            <td><strong><?= e($row['name']) ?></strong></td>
            <td><?= $row['PJ'] ?></td>
            <td style="color:#34d399"><?= $row['PG'] ?></td>
            <td style="color:#fbbf24"><?= $row['PE'] ?></td>
            <td style="color:#f87171"><?= $row['PP'] ?></td>
            <td><?= $row['GF'] ?></td>
            <td><?= $row['GC'] ?></td>
            <td><?= $row['DG'] >= 0 ? '+'.$row['DG'] : $row['DG'] ?></td>
            <td style="color:#d97706"><?= $row['TA'] ?></td>
            <td style="color:#ef4444"><?= $row['TR'] ?></td>
            <td><strong style="font-size:1rem"><?= $row['PTS'] ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Equipos participantes -->
<div class="pub-card">
  <div class="pub-card-title">👕 Equipos Participantes (<?= count($teams) ?>)</div>
  <?php if (empty($teams)): ?>
    <p style="color:var(--pub-muted)">No hay equipos inscritos en este campeonato.</p>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px">
      <?php foreach ($teams as $t): ?>
        <div style="padding:12px 14px;background:rgba(255,255,255,.03);border:1px solid var(--pub-border);border-radius:8px">
          <strong style="font-size:.9rem"><?= e($t['name']) ?></strong>
          <?php if ($t['short_name']): ?>
            <span class="pub-badge pb-muted" style="margin-left:4px"><?= e($t['short_name']) ?></span>
          <?php endif; ?>
          <?php if ($t['founded_year']): ?>
            <p style="font-size:.75rem;color:var(--pub-muted);margin-top:4px">Fundado: <?= e($t['founded_year']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
