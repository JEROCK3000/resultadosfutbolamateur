<?php /** app/Views/players/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('jugadores') ?>">Jugadores</a><span class="breadcrumb-sep">›</span>
  <?php if ($team): ?>
    <a href="<?= url("jugadores/equipo/{$team['id']}/liga/{$league['id']}") ?>"><?= e($team['name']) ?></a><span class="breadcrumb-sep">›</span>
  <?php endif; ?>
  <span>Editar Jugador</span>
</nav>

<div class="card" style="max-width:620px; margin:0 auto">
  <div class="card-header">
    <h2 class="card-title">✏️ Editar Jugador</h2>
  </div>

  <form action="<?= url("jugadores/actualizar/{$player['id']}") ?>" method="POST">
    <input type="hidden" name="team_id"       value="<?= $teamId ?>">
    <input type="hidden" name="league_id"     value="<?= $leagueId ?>">
    <input type="hidden" name="membership_id" value="<?= $player['membership_id'] ?? '' ?>">

    <div style="display:flex; gap:14px; flex-wrap:wrap">
      <div class="form-group" style="flex:1; min-width:180px">
        <label class="form-label">Cédula / DNI <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="cedula" class="form-control" required maxlength="20"
               value="<?= e($player['cedula']) ?>">
      </div>
      <div class="form-group" style="flex:2; min-width:220px">
        <label class="form-label">Nombre Completo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="name" class="form-control" required maxlength="150"
               value="<?= e($player['name']) ?>">
      </div>
    </div>

    <div style="display:flex; gap:14px; flex-wrap:wrap">
      <div class="form-group" style="flex:1; min-width:160px">
        <label class="form-label">Fecha de Nacimiento</label>
        <input type="date" name="birth_date" class="form-control"
               value="<?= e($player['birth_date'] ?? '') ?>">
      </div>
      <div class="form-group" style="flex:1; min-width:160px">
        <label class="form-label">Posición</label>
        <select name="position" class="form-control">
          <?php foreach(['portero','defensa','mediocampista','delantero','otro'] as $pos): ?>
            <option value="<?= $pos ?>" <?= ($player['position'] ?? '') === $pos ? 'selected' : '' ?>>
              <?= ucfirst($pos) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <?php if (!empty($player['membership_id'])): ?>
    <div style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:8px; padding:14px; margin-bottom:16px">
      <p style="font-size:.82rem; font-weight:700; color:var(--color-text-muted); margin:0 0 10px">Membresía en el equipo</p>
      <div style="display:flex; gap:14px; flex-wrap:wrap">
        <div class="form-group" style="flex:1; min-width:120px; margin-bottom:0">
          <label class="form-label">N° Camiseta</label>
          <input type="number" name="number" class="form-control" min="1" max="99"
                 value="<?= $player['number'] ?? '' ?>" placeholder="—">
        </div>
        <div class="form-group" style="flex:1; min-width:160px; margin-bottom:0">
          <label class="form-label">Estado</label>
          <select name="member_status" class="form-control">
            <option value="active"    <?= ($player['member_status'] ?? '') === 'active'    ? 'selected' : '' ?>>Activo</option>
            <option value="suspended" <?= ($player['member_status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspendido</option>
            <option value="inactive"  <?= ($player['member_status'] ?? '') === 'inactive'  ? 'selected' : '' ?>>Inactivo</option>
          </select>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-actions">
      <?php if ($team): ?>
        <a href="<?= url("jugadores/equipo/{$team['id']}/liga/{$league['id']}") ?>" class="btn btn-secondary">Cancelar</a>
      <?php else: ?>
        <a href="<?= url('jugadores') ?>" class="btn btn-secondary">Cancelar</a>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
  </form>
</div>
