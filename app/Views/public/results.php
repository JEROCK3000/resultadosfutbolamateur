<?php /** app/Views/public/results.php — Resultados filtrables por fecha */ ?>
<?php $pageTitle = 'Resultados — ' . $league['name']; ?>

<div style="margin-bottom:24px">
  <h1 style="font-family:var(--font-head);font-size:clamp(1.3rem,4vw,1.8rem);font-weight:800">
    ⚽ Resultados de la Jornada
  </h1>
  <p style="color:var(--pub-muted);font-size:.85rem"><?= e($league['name']) ?> · Temporada <?= e($league['season']) ?></p>
</div>

<!-- Filtro por fecha -->
<div class="pub-card" style="margin-bottom:20px; padding:15px">
  <form method="GET" action="<?= BASE_URL ?>/principal/liga/<?= (int)$league['id'] ?>/resultados" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap">
    <label for="fecha" style="font-weight:600; font-size:.9rem">Filtrar por Fecha:</label>
    <select name="fecha" id="fecha" class="pub-input" style="max-width:250px" onchange="this.form.submit()">
      <?php if (empty($availableDates)): ?>
        <option value="">No hay fechas disponibles</option>
      <?php else: ?>
        <?php foreach ($availableDates as $date): ?>
          <option value="<?= $date ?>" <?= $dateFilter === $date ? 'selected' : '' ?>>
            <?= date('d/m/Y', strtotime($date)) ?>
          </option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
    <noscript><button type="submit" class="btn-export pb-blue">Ver</button></noscript>
  </form>
</div>

<!-- Botones exportación -->
<div class="export-bar" style="margin-bottom:20px">
  <a href="<?= BASE_URL ?>/exportar/resultados/pdf/<?= (int)$league['id'] ?>" class="btn-export btn-pdf" target="_blank">📄 PDF</a>
  <a href="<?= BASE_URL ?>/exportar/resultados/excel/<?= (int)$league['id'] ?>" class="btn-export btn-excel">📊 Excel</a>
</div>

<!-- Listado de Resultados -->
<div class="results-container" style="display:grid; gap:15px">
  <?php if (empty($results)): ?>
    <div class="pub-card" style="text-align:center; padding:40px">
      <p style="color:var(--pub-muted)">No hay resultados registrados para esta fecha.</p>
    </div>
  <?php else: ?>
    <?php foreach ($results as $r): ?>
      <div class="pub-card" style="padding:0; overflow:hidden">
        <div style="background:rgba(255,255,255,.02); padding:10px 20px; border-bottom:1px solid var(--pub-border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
          <span style="font-size:.75rem; color:var(--pub-muted)">
            🕒 <?= date('H:i', strtotime($r['match_time'])) ?> · 🏟️ <?= e($r['stadium']) ?>
          </span>
          <span style="font-size:.75rem; background:rgba(52,211,153,.1); color:#34d399; padding:2px 8px; border-radius:12px; font-weight:600">
            Finalizado
          </span>
        </div>
        
        <div style="padding:25px 20px; display:flex; align-items:center; justify-content:center; gap:30px">
          <!-- Local -->
          <div style="flex:1; text-align:right">
            <div style="display:flex; justify-content:flex-end; align-items:center;">
               <span style="font-size:1.1rem; font-weight:700; display:block"><?= e($r['home_team']) ?></span>
               <?php if (!empty($r['home_logo'])): ?>
                  <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($r['home_logo']) ?>" width="32" height="32" style="object-fit:contain;margin-left:12px;border-radius:50%">
               <?php else: ?>
                  <span style="font-size:24px;margin-left:12px">🛡️</span>
               <?php endif; ?>
            </div>
            <div style="margin-top:5px; display:flex; justify-content:flex-end; gap:5px">
               <?php for($i=0; $i<$r['home_yellow_cards']; $i++): ?><span title="Amarilla" style="width:8px; height:12px; background:#fbbf24; border-radius:1px; display:inline-block"></span><?php endfor; ?>
               <?php for($i=0; $i<$r['home_red_cards']; $i++): ?><span title="Roja" style="width:8px; height:12px; background:#ef4444; border-radius:1px; display:inline-block"></span><?php endfor; ?>
            </div>
          </div>

          <!-- Marcador -->
          <div style="background:var(--pub-bg); border:1px solid var(--pub-border); padding:10px 20px; border-radius:8px; font-size:1.8rem; font-weight:800; font-family:var(--font-head); display:flex; align-items:center; gap:15px; min-width:120px; justify-content:center">
            <span><?= (int)$r['home_goals'] ?></span>
            <span style="color:var(--pub-border); font-size:1rem">:</span>
            <span><?= (int)$r['away_goals'] ?></span>
          </div>

          <!-- Visitante -->
          <div style="flex:1; text-align:left">
            <div style="display:flex; justify-content:flex-start; align-items:center;">
               <?php if (!empty($r['away_logo'])): ?>
                  <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($r['away_logo']) ?>" width="32" height="32" style="object-fit:contain;margin-right:12px;border-radius:50%">
               <?php else: ?>
                  <span style="font-size:24px;margin-right:12px">🛡️</span>
               <?php endif; ?>
               <span style="font-size:1.1rem; font-weight:700; display:block"><?= e($r['away_team']) ?></span>
            </div>
            <div style="margin-top:5px; display:flex; justify-content:flex-start; gap:5px">
               <?php for($i=0; $i<$r['away_yellow_cards']; $i++): ?><span title="Amarilla" style="width:8px; height:12px; background:#fbbf24; border-radius:1px; display:inline-block"></span><?php endfor; ?>
               <?php for($i=0; $i<$r['away_red_cards']; $i++): ?><span title="Roja" style="width:8px; height:12px; background:#ef4444; border-radius:1px; display:inline-block"></span><?php endfor; ?>
            </div>
          </div>
        </div>

        <?php if ($r['referee_name']): ?>
          <div style="padding:8px 20px; font-size:.7rem; color:var(--pub-muted); border-top:1px dashed var(--pub-border); text-align:center">
            Árbitro: <strong><?= e($r['referee_name']) ?></strong>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div style="margin-top:30px; text-align:center">
  <a href="<?= BASE_URL ?>/principal/liga/<?= (int)$league['id'] ?>" class="btn-export pb-muted">← Volver a Clasificación</a>
</div>
