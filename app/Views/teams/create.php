<?php /** app/Views/teams/create.php */?>
<nav class="breadcrumb">
  <a href="<?= url()?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('equipos')?>">Equipos</a><span class="breadcrumb-sep">›</span>
  <span>Nuevo Equipo</span>
</nav>
<div class="card" style="max-width:600px">
  <div class="card-header">
    <h1 class="card-title">👕 Nuevo Equipo</h1>
  </div>
  <form action="<?= url('equipos/guardar')?>" method="POST" enctype="multipart/form-data" novalidate>
    <div class="form-group">
      <label class="form-label" for="league_id">Campeonato <span style="color:var(--color-danger)">*</span></label>
      <select id="league_id" name="league_id" class="form-control" required>
        <option value="">— Seleccione un campeonato —</option>
        <?php foreach ($leagues as $l): ?>
        <option value="<?=(int)$l['id']?>" <?=(($_POST['league_id'] ?? '') == $l['id'] ? 'selected' : '')?>>
          <?= e($l['name'])?> (
          <?= e($l['season'])?>)
        </option>
        <?php
endforeach; ?>
      </select>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre del Equipo <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required placeholder="ej. BORJA S.C"
          value="<?= e($_POST['name'] ?? '')?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="short_name">Abreviatura</label>
        <input type="text" id="short_name" name="short_name" class="form-control" placeholder="ej. BSC" maxlength="5"
          value="<?= e($_POST['short_name'] ?? '')?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="logo">Escudo / Logo (Opcional)</label>
      <input type="file" id="logo" name="logo" class="form-control" accept="image/png, image/jpeg, image/webp">
      <small style="color:var(--color-text-muted)">Recomendado: Fondo transparente. Tamaño máx: 2MB.</small>
    </div>
    <div class="form-group">
      <label class="form-label" for="founded_year">Año de Fundación</label>
      <input type="number" id="founded_year" name="founded_year" class="form-control" placeholder="ej. 1998" min="1800"
        max="<?= date('Y')?>" value="<?= e($_POST['founded_year'] ?? '')?>">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar Equipo</button>
      <a href="<?= url('equipos')?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>