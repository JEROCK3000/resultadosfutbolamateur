<?php /** app/Views/teams/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('equipos') ?>">Equipos</a><span class="breadcrumb-sep">›</span>
  <span>Editar Equipo</span>
</nav>
<div class="card" style="max-width:600px">
  <div class="card-header">
    <h1 class="card-title">✏️ Editar Equipo</h1>
    <span class="badge badge-muted">ID: <?= (int)$team['id'] ?></span>
  </div>
  <form action="<?= url("equipos/actualizar/{$team['id']}") ?>" method="POST" enctype="multipart/form-data" novalidate>
    <div class="form-group">
      <label class="form-label" for="league_id">Campeonato <span style="color:var(--color-danger)">*</span></label>
      <select id="league_id" name="league_id" class="form-control" required>
        <option value="">— Seleccione un campeonato —</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= (int)$l['id'] ?>" <?= ($team['league_id'] == $l['id'] ? 'selected' : '') ?>>
            <?= e($l['name']) ?> (<?= e($l['season']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre del Equipo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required value="<?= e($team['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="short_name">Abreviatura</label>
        <input type="text" id="short_name" name="short_name" class="form-control"
               maxlength="5" value="<?= e($team['short_name'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="logo">Escudo / Logo (Reemplazar escudo anterior)</label>
      <?php if(!empty($team['logo'])): ?>
        <div style="margin-bottom:10px">
          <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($team['logo']) ?>" width="48" height="48" style="object-fit:contain;background:#fff;padding:4px;border-radius:50%;border:1px solid var(--color-border)">
        </div>
      <?php endif; ?>
      <input type="file" id="logo" name="logo" class="form-control" accept="image/png, image/jpeg, image/webp">
      <small style="color:var(--color-text-muted)">Dejar en blanco para conservar el escudo actual.</small>
    </div>
    <div class="form-group">
      <label class="form-label" for="founded_year">Año de Fundación</label>
      <input type="number" id="founded_year" name="founded_year" class="form-control"
             min="1800" max="<?= date('Y') ?>" value="<?= e($team['founded_year'] ?? '') ?>">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Equipo</button>
      <a href="<?= url('equipos') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
