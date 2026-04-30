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
    <div style="font-size:clamp(2.5rem,8vw,4rem);font-weight:800;font-family:var(--font-heading);min-width:120px;text-align:center">
      <span style="background:linear-gradient(135deg,var(--color-primary-light),var(--color-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        <?= $result ? (int)$result['home_goals'].' : '.(int)$result['away_goals'] : '? : ?' ?>
      </span>
    </div>
    <div style="font-size:clamp(1.2rem,4vw,1.8rem);font-weight:700;max-width:200px"><?= e($match['away_team']) ?></div>
  </div>
  <?php
    $statusBadge = match($match['status']) { 'scheduled'=>'badge-info','live'=>'badge-danger','finished'=>'badge-success','postponed'=>'badge-warning', default=>'badge-muted' };
    $statusLabel = match($match['status']) { 'scheduled'=>'Programado','live'=>'🔴 En vivo','finished'=>'Finalizado','postponed'=>'Postergado', default=>$match['status'] };
  ?>
  <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
</div>

<div style="display:grid;gap:20px;grid-template-columns:1fr 1fr">

  <!-- Actualizar marcador -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">⚽ Actualizar Marcador</h2></div>
    <form action="<?= url('resultados/guardar') ?>" method="POST">
      <input type="hidden" name="match_id" value="<?= (int)$match['id'] ?>">
      <div style="display:flex;gap:16px;flex-wrap:wrap">
        <!-- Goles -->
        <div style="flex:1;min-width:160px">
          <p style="font-size:.85rem;font-weight:700;text-align:center;margin-bottom:8px">⚽ Goles</p>
          <div style="display:flex;gap:10px;align-items:center;justify-content:center">
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['home_team']) ?></label>
              <input type="number" name="home_goals" class="form-control" min="0" max="99" value="<?= $result ? (int)$result['home_goals'] : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700">
            </div>
            <span style="font-size:1.4rem;font-weight:700;color:var(--color-text-muted);padding-top:14px">:</span>
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['away_team']) ?></label>
              <input type="number" name="away_goals" class="form-control" min="0" max="99" value="<?= $result ? (int)$result['away_goals'] : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700">
            </div>
          </div>
        </div>
        <!-- Amarillas -->
        <div style="flex:1;min-width:160px">
          <p style="font-size:.85rem;font-weight:700;text-align:center;margin-bottom:8px">🟨 Amarillas</p>
          <div style="display:flex;gap:10px;align-items:center;justify-content:center">
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['home_team']) ?></label>
              <input type="number" name="home_yellow_cards" class="form-control" min="0" max="99" value="<?= $result ? (int)($result['home_yellow_cards']??0) : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700;color:#d97706">
            </div>
            <span style="font-size:1.4rem;font-weight:700;color:var(--color-text-muted);padding-top:14px">-</span>
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['away_team']) ?></label>
              <input type="number" name="away_yellow_cards" class="form-control" min="0" max="99" value="<?= $result ? (int)($result['away_yellow_cards']??0) : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700;color:#d97706">
            </div>
          </div>
        </div>
        <!-- Rojas -->
        <div style="flex:1;min-width:160px">
          <p style="font-size:.85rem;font-weight:700;text-align:center;margin-bottom:8px">🟥 Rojas</p>
          <div style="display:flex;gap:10px;align-items:center;justify-content:center">
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['home_team']) ?></label>
              <input type="number" name="home_red_cards" class="form-control" min="0" max="99" value="<?= $result ? (int)($result['home_red_cards']??0) : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700;color:#dc2626">
            </div>
            <span style="font-size:1.4rem;font-weight:700;color:var(--color-text-muted);padding-top:14px">-</span>
            <div class="form-group" style="flex:1;max-width:100px;margin:0">
              <label class="form-label" style="font-size:.75rem;text-align:center"><?= e($match['away_team']) ?></label>
              <input type="number" name="away_red_cards" class="form-control" min="0" max="99" value="<?= $result ? (int)($result['away_red_cards']??0) : 0 ?>" style="font-size:1.3rem;text-align:center;font-weight:700;color:#dc2626">
            </div>
          </div>
        </div>
      </div>
      <div style="text-align:center;margin-top:16px">
        <button type="submit" class="btn btn-success">💾 Guardar Resultado</button>
      </div>
    </form>
  </div>

  <!-- Registrar evento por jugador -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">📝 Registrar Evento por Jugador</h2></div>
    <form action="<?= url('resultados/evento/guardar') ?>" method="POST">
      <input type="hidden" name="match_id" value="<?= (int)$match['id'] ?>">
      <div class="form-group">
        <label class="form-label">Equipo</label>
        <select name="team_id" class="form-control" id="evtTeam" onchange="filterPlayers(this.value)" required>
          <option value="">— Selecciona equipo —</option>
          <option value="<?= (int)$match['home_team_id'] ?>"><?= e($match['home_team']) ?></option>
          <option value="<?= (int)$match['away_team_id'] ?>"><?= e($match['away_team']) ?></option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Jugador</label>
        <select name="player_id" class="form-control" id="evtPlayer">
          <option value="">— Selecciona equipo primero —</option>
          <?php foreach ($homePlayers as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-team="<?= (int)$match['home_team_id'] ?>" style="display:none">
              #<?= $p['number'] ?? '?' ?> <?= e($p['name']) ?>
            </option>
          <?php endforeach; ?>
          <?php foreach ($awayPlayers as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-team="<?= (int)$match['away_team_id'] ?>" style="display:none">
              #<?= $p['number'] ?? '?' ?> <?= e($p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="player_name" class="form-control" style="margin-top:6px"
               placeholder="O escribe nombre manualmente">
      </div>
      <div style="display:flex;gap:12px">
        <div class="form-group" style="flex:2">
          <label class="form-label">Tipo de evento</label>
          <select name="event_type" class="form-control" required>
            <option value="goal">⚽ Gol</option>
            <option value="yellow_card">🟨 Tarjeta Amarilla</option>
            <option value="red_card">🟥 Tarjeta Roja</option>
          </select>
        </div>
        <div class="form-group" style="flex:1">
          <label class="form-label">Minuto</label>
          <input type="number" name="minute" class="form-control" min="1" max="120" placeholder="'">
        </div>
      </div>
      <div style="text-align:right">
        <button type="submit" class="btn btn-primary">+ Registrar</button>
      </div>
    </form>
  </div>

</div>

<!-- Eventos registrados -->
<?php if (!empty($events)): ?>
<div class="card mt-3">
  <div class="card-header"><h2 class="card-title">📋 Eventos del Partido</h2></div>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Min.</th><th>Equipo</th><th>Jugador</th><th>Evento</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($events as $ev): ?>
        <tr>
          <td style="font-weight:700"><?= $ev['minute'] ? $ev['minute']."'" : '—' ?></td>
          <td style="font-size:.85rem"><?= e($ev['team_name']) ?></td>
          <td style="font-size:.85rem"><?= e($ev['player_name_db'] ?? $ev['player_name'] ?? '—') ?></td>
          <td>
            <?php
              $evIcon = match($ev['event_type']) { 'goal'=>'⚽ Gol', 'yellow_card'=>'🟨 Amarilla', 'red_card'=>'🟥 Roja', default=>$ev['event_type'] };
              $evBadge = match($ev['event_type']) { 'goal'=>'badge-success', 'yellow_card'=>'badge-warning', 'red_card'=>'badge-danger', default=>'badge-muted' };
            ?>
            <span class="badge <?= $evBadge ?>"><?= $evIcon ?></span>
          </td>
          <td>
            <form action="<?= url("resultados/evento/eliminar/{$ev['id']}") ?>" method="POST">
              <button class="btn btn-danger btn-sm" data-confirm="¿Eliminar este evento?">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="form-actions mt-3">
  <a href="<?= url('encuentros') ?>" class="btn btn-secondary">← Volver</a>
  <a href="<?= url("posiciones/{$match['league_id']}") ?>" class="btn btn-outline">📊 Tabla de Posiciones</a>
</div>

<script>
const homeId = <?= (int)$match['home_team_id'] ?>;
const awayId = <?= (int)$match['away_team_id'] ?>;

function filterPlayers(teamId) {
  const sel = document.getElementById('evtPlayer');
  sel.innerHTML = '<option value="">— Selecciona jugador (opcional) —</option>';
  document.querySelectorAll('#evtPlayer option[data-team]').forEach(() => {});
  // rebuild from original options
  const tid = parseInt(teamId);
  <?php
    $allEvtPlayers = array_merge(
      array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'number'=>$p['number'],'team'=>$match['home_team_id']], $homePlayers),
      array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'number'=>$p['number'],'team'=>$match['away_team_id']], $awayPlayers)
    );
  ?>
  const players = <?= json_encode($allEvtPlayers) ?>;
  players.filter(p => p.team == tid).forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.id;
    opt.text  = (p.number ? '#'+p.number+' ' : '') + p.name;
    sel.appendChild(opt);
  });
}
</script>
