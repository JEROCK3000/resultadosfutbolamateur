<?php /** app/Views/results/index.php — Panel de resultado del partido */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('encuentros') ?>">Encuentros</a><span class="breadcrumb-sep">›</span>
  <span>Resultado</span>
</nav>

<!-- Marcador principal -->
<div class="card mb-3" style="text-align:center;padding:32px 20px">
  <div style="font-size:.8rem;color:var(--color-text-muted);margin-bottom:8px">
    🏆 <?= e($match['league']) ?> &nbsp;|&nbsp;
    📅 <?= date('d/m/Y', strtotime($match['match_date'])) ?> <?= e(substr($match['match_time'],0,5)) ?>h &nbsp;|&nbsp;
    🏟️ <?= e($match['stadium']) ?>
  </div>
  <div style="display:flex;align-items:center;justify-content:center;gap:24px;flex-wrap:wrap;margin:16px 0">
    <div style="font-size:clamp(1.2rem,4vw,1.8rem);font-weight:700;max-width:200px"><?= e($match['home_team']) ?></div>
    <div style="font-size:clamp(2.5rem,8vw,4rem);font-weight:800;font-family:var(--font-heading);
                min-width:120px;text-align:center">
      <span style="background:linear-gradient(135deg,var(--color-primary-light),var(--color-accent));
                   -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        <?= $result ? (int)$result['home_goals'].' : '.(int)$result['away_goals'] : '? : ?' ?>
      </span>
    </div>
    <div style="font-size:clamp(1.2rem,4vw,1.8rem);font-weight:700;max-width:200px"><?= e($match['away_team']) ?></div>
  </div>
  <?php
    $statusBadge = match($match['status']) {
      'scheduled' => 'badge-info', 'live' => 'badge-danger',
      'finished'  => 'badge-success', 'postponed' => 'badge-warning', default => 'badge-muted'
    };
    $statusLabel = match($match['status']) {
      'scheduled'=>'Programado','live'=>'🔴 En vivo','finished'=>'Finalizado','postponed'=>'Postergado', default=>$match['status']
    };
  ?>
  <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
</div>

<!-- Grid: Formulario marcador + Formulario evento -->
<div style="display:grid;gap:20px;grid-template-columns:1fr">

  <!-- Actualizar marcador -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">⚽ Actualizar Marcador</h2></div>
    <form action="<?= url('resultados/guardar') ?>" method="POST">
      <input type="hidden" name="match_id" value="<?= (int)$match['id'] ?>">
        <div style="display:flex;gap:30px;width:100%;margin-top:15px">
          <!-- Goles -->
          <div style="flex:1">
            <h3 style="font-size:1rem;margin-bottom:10px;text-align:center">⚽ Goles</h3>
            <div style="display:flex;gap:16px;align-items:center;justify-content:center">
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['home_team']) ?></label>
                <input type="number" name="home_goals" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)$result['home_goals'] : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700">
              </div>
              <div style="font-size:1.5rem;font-weight:700;color:var(--color-text-muted);padding-top:15px">:</div>
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['away_team']) ?></label>
                <input type="number" name="away_goals" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)$result['away_goals'] : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700">
              </div>
            </div>
          </div>
          
          <!-- Tarjetas Amarillas -->
          <div style="flex:1">
            <h3 style="font-size:1rem;margin-bottom:10px;text-align:center">🟨 T. Amarillas</h3>
            <div style="display:flex;gap:16px;align-items:center;justify-content:center">
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['home_team']) ?></label>
                <input type="number" name="home_yellow_cards" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)($result['home_yellow_cards']??0) : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700;color:#d97706">
              </div>
              <div style="font-size:1.5rem;font-weight:700;color:var(--color-text-muted);padding-top:15px">-</div>
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['away_team']) ?></label>
                <input type="number" name="away_yellow_cards" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)($result['away_yellow_cards']??0) : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700;color:#d97706">
              </div>
            </div>
          </div>

          <!-- Tarjetas Rojas -->
          <div style="flex:1">
            <h3 style="font-size:1rem;margin-bottom:10px;text-align:center">🟥 T. Rojas</h3>
            <div style="display:flex;gap:16px;align-items:center;justify-content:center">
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['home_team']) ?></label>
                <input type="number" name="home_red_cards" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)($result['home_red_cards']??0) : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700;color:#dc2626">
              </div>
              <div style="font-size:1.5rem;font-weight:700;color:var(--color-text-muted);padding-top:15px">-</div>
              <div class="form-group" style="flex:1;max-width:150px">
                <label class="form-label text-center" style="font-size:.85rem"><?= e($match['away_team']) ?></label>
                <input type="number" name="away_red_cards" class="form-control" min="0" max="99"
                       value="<?= $result ? (int)($result['away_red_cards']??0) : 0 ?>" style="font-size:1.4rem;text-align:center;font-weight:700;color:#dc2626">
              </div>
            </div>
          </div>
        </div>

        <div style="text-align:center;margin-top:20px;width:100%">
          <button type="submit" class="btn btn-success" style="padding:12px 40px;font-size:1.1rem">💾 Guardar Resultado y Tarjetas</button>
        </div>
      </div>
    </form>
  </div>
</div>



<div class="form-actions mt-3">
  <a href="<?= url('encuentros') ?>" class="btn btn-secondary">← Volver a Encuentros</a>
  <a href="<?= url("posiciones/{$match['league_id']}") ?>" class="btn btn-outline">📊 Ver Tabla de Posiciones</a>
</div>
