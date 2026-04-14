<?php /** app/Views/leagues/edit.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('ligas') ?>">Campeonatos</a><span class="breadcrumb-sep">›</span>
  <span>Editar Campeonato</span>
</nav>
<div class="card" style="max-width:640px">
  <div class="card-header">
    <h1 class="card-title">✏️ Editar Campeonato</h1>
    <span class="badge badge-muted">ID: <?= (int)$league['id'] ?></span>
  </div>
  <form action="<?= url("ligas/actualizar/{$league['id']}") ?>" method="POST" novalidate>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="name">Nombre <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="name" name="name" class="form-control" required value="<?= e($league['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="season">Temporada <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="season" name="season" class="form-control" required value="<?= e($league['season']) ?>">
      </div>
    </div>
    <div class="form-row form-row-2">
      <div class="form-group">
        <label class="form-label" for="country">País <span style="color:var(--color-danger)">*</span></label>
        <input type="text" id="country" name="country" class="form-control" required value="<?= e($league['country']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="status">Estado</label>
        <select id="status" name="status" class="form-control">
          <option value="active"   <?= $league['status']==='active'   ? 'selected' : '' ?>>Activa</option>
          <option value="inactive" <?= $league['status']==='inactive' ? 'selected' : '' ?>>Inactiva</option>
          <option value="finished" <?= $league['status']==='finished' ? 'selected' : '' ?>>Finalizada</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="description">Descripción</label>
      <textarea id="description" name="description" class="form-control" rows="3"><?= e($league['description'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Actualizar Liga</button>
      <a href="<?= url('ligas') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
