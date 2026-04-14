<?php /** app/Views/matches/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('encuentros') ?>">Encuentros</a><span class="breadcrumb-sep">›</span>
  <span>Editar Encuentro</span>
</nav>
<div class="card" style="max-width:700px">
  <div class="card-header">
    <h1 class="card-title">✏️ Editar Encuentro</h1>
    <span class="badge badge-muted">ID: <?= (int)$match['id'] ?></span>
  </div>
  <form action="<?= url("encuentros/actualizar/{$match['id']}") ?>" method="POST" novalidate>

    <div class="form-group">
      <label class="form-label" for="league_id">Campeonato <span style="color:var(--color-danger)">*</span></label>
      <select id="league_id" name="league_id" class="form-control" required>
        <option value="">— Seleccione un campeonato —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>" <?= ($match['league_id']==$l['id']?'selected':'') ?>>
            <?= e($l['name']) ?> (<?= e($l['season']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="home_team_id">Equipo Local <span style="color:var(--color-danger)">*</span></label>
        <select id="home_team_id" name="home_team_id" class="form-control" required>
          <?php foreach ($teams as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ($match['home_team_id']==$t['id']?'selected':'') ?>>
              <?= e($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="away_team_id">Equipo Visitante <span style="color:var(--color-danger)">*</span></label>
        <select id="away_team_id" name="away_team_id" class="form-control" required>
          <?php foreach ($teams as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ($match['away_team_id']==$t['id']?'selected':'') ?>>
              <?= e($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="stadium_id">Estadio <span style="color:var(--color-danger)">*</span></label>
      <select id="stadium_id" name="stadium_id" class="form-control" required>
        <?php foreach ($stadiums as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= ($match['stadium_id']==$s['id']?'selected':'') ?>>
            <?= e($s['name']) ?> — <?= e($s['city']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="referee_id">Árbitro Central <span class="text-muted" style="font-weight:normal; font-size:0.85em">(Opcional)</span></label>
      <select id="referee_id" name="referee_id" class="form-control">
        <option value="">— Sin Asignar —</option>
        <?php foreach ($referees as $r): ?>
          <option value="<?= (int)$r['id'] ?>" <?= (($match['referee_id']??'')==$r['id']?'selected':'') ?>>
            <?= e($r['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="match_date">Fecha <span style="color:var(--color-danger)">*</span></label>
        <input type="date" id="match_date" name="match_date" class="form-control" required
               value="<?= e($match['match_date']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="match_time">Hora <span style="color:var(--color-danger)">*</span></label>
        <input type="time" id="match_time" name="match_time" class="form-control" required
               value="<?= e(substr($match['match_time'], 0, 5)) ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="status">Estado</label>
      <select id="status" name="status" class="form-control">
        <?php foreach (['scheduled'=>'Programado','live'=>'En vivo','finished'=>'Finalizado','postponed'=>'Postergado'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($match['status']===$v?'selected':'') ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Encuentro</button>
      <a href="<?= url('encuentros') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
