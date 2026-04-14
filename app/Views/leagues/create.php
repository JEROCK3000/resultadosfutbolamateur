<?php /** app/Views/leagues/create.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('ligas') ?>">Campeonatos</a><span class="breadcrumb-sep">›</span>
  <span>Nuevo Campeonato</span>
</nav>
<div class="card" style="max-width:640px">
  <div class="card-header"><h1 class="card-title">🏆 Nuevo Campeonato</h1></div>
  <form action="<?= url('ligas/guardar') ?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required
               placeholder="ej. Campeonato Parroquial de Borja" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="season">Temporada <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="season" name="season" class="form-control" required
               placeholder="ej. 2025" value="<?= e($_POST['season'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="country">País <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="country" name="country" class="form-control" required
               placeholder="ej. Ecuador" value="<?= e($_POST['country'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="status">Estado</label>
        <select id="status" name="status" class="form-control">
          <option value="active"   <?= (($_POST['status']??'active')==='active'  ?'selected':'') ?>>Activa</option>
          <option value="inactive" <?= (($_POST['status']??'')==='inactive'?'selected':'') ?>>Inactiva</option>
          <option value="finished" <?= (($_POST['status']??'')==='finished'?'selected':'') ?>>Finalizada</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="description">Descripción</label>
      <textarea id="description" name="description" class="form-control" rows="3"
                placeholder="ej. Campeonato organizada por el GAD Borja, Cantón Quijos, Provincia de Napo"><?= e($_POST['description'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar Liga</button>
      <a href="<?= url('ligas') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
