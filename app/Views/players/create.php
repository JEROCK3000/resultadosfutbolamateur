<?php /** app/Views/players/create.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('jugadores') ?>">Jugadores</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url("jugadores/equipo/{$team['id']}/{$league['id']}") ?>"><?= e($team['name']) ?></a><span class="breadcrumb-sep">›</span>
  <span>Nuevo Jugador</span>
</nav>

<div class="card" style="max-width:620px; margin:0 auto">
  <div class="card-header">
    <h2 class="card-title">👤 Nuevo Jugador</h2>
    <p style="font-size:.85rem; color:var(--color-text-muted); margin-top:4px">
      Equipo: <strong><?= e($team['name']) ?></strong> · Campeonato: <strong><?= e($league['name']) ?></strong>
    </p>
  </div>

  <form action="<?= url('jugadores/guardar') ?>" method="POST">
    <input type="hidden" name="team_id"   value="<?= $team['id'] ?>">
    <input type="hidden" name="league_id" value="<?= $league['id'] ?>">

    <div style="display:flex; gap:14px; flex-wrap:wrap">
      <div class="form-group" style="flex:1; min-width:180px">
        <label class="form-label">Cédula / DNI <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="cedula" class="form-control" required maxlength="20"
               placeholder="Ej: 1234567890">
        <small style="color:var(--color-text-muted); font-size:.77rem">Si la cédula ya existe, el jugador se añade al equipo automáticamente.</small>
      </div>
      <div class="form-group" style="flex:2; min-width:220px">
        <label class="form-label">Nombre Completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="name" class="form-control" required maxlength="150"
               placeholder="Ej: JUAN CARLOS PÉREZ">
      </div>
    </div>

    <div style="display:flex; gap:14px; flex-wrap:wrap">
      <div class="form-group" style="flex:1; min-width:160px">
        <label class="form-label">Fecha de Nacimiento</label>
        <input type="date" name="birth_date" class="form-control">
      </div>
      <div class="form-group" style="flex:1; min-width:160px">
        <label class="form-label">Posición</label>
        <select name="position" class="form-control">
          <option value="portero">Portero</option>
          <option value="defensa">Defensa</option>
          <option value="mediocampista">Mediocampista</option>
          <option value="delantero" selected>Delantero</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div class="form-group" style="flex:1; min-width:120px">
        <label class="form-label">N° Camiseta</label>
        <input type="number" name="number" class="form-control" min="1" max="99" placeholder="—">
      </div>
    </div>

    <div class="form-actions">
      <a href="<?= url("jugadores/equipo/{$team['id']}/liga/{$league['id']}") ?>" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-primary">Guardar Jugador</button>
    </div>
  </form>
</div>
