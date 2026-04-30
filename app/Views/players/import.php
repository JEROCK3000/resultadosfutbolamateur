<?php /** app/Views/players/import.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('jugadores') ?>">Jugadores</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url("jugadores/equipo/{$team['id']}/liga/{$league['id']}") ?>"><?= e($team['name']) ?></a><span class="breadcrumb-sep">›</span>
  <span>Importar CSV</span>
</nav>

<div class="card" style="max-width:640px; margin:0 auto">
  <div class="card-header">
    <h2 class="card-title">📥 Importar Jugadores desde CSV</h2>
    <p style="font-size:.85rem; color:var(--color-text-muted); margin-top:4px">
      Equipo: <strong><?= e($team['name']) ?></strong> · Campeonato: <strong><?= e($league['name']) ?></strong>
    </p>
  </div>

  <!-- Instrucciones -->
  <div style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:8px; padding:16px; margin-bottom:20px">
    <p style="font-weight:700; margin:0 0 10px; font-size:.9rem">📋 Instrucciones</p>
    <ol style="margin:0; padding-left:18px; font-size:.85rem; color:var(--color-text-muted); line-height:1.8">
      <li>Descarga el <a href="<?= url("jugadores/template/{$team['id']}/{$league['id']}") ?>" style="color:var(--color-primary); font-weight:700">template CSV</a> y ábrelo en Excel o Google Sheets.</li>
      <li>Completa los datos de cada jugador. La primera fila es el encabezado — no la modifiques.</li>
      <li>Columnas: <strong>Cedula</strong> | <strong>Nombre</strong> | <strong>Fecha_Nacimiento</strong> (DD/MM/YYYY) | <strong>Posicion</strong> | <strong>Numero_Camiseta</strong></li>
      <li>Posiciones válidas: <code>portero</code>, <code>defensa</code>, <code>mediocampista</code>, <code>delantero</code>, <code>otro</code></li>
      <li>Si una cédula ya existe, el jugador se añade al equipo sin duplicar el registro.</li>
      <li>Guarda el archivo como <strong>CSV UTF-8</strong> antes de subir.</li>
    </ol>
  </div>

  <form action="<?= url("jugadores/importar/{$team['id']}/{$league['id']}") ?>" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label class="form-label">Archivo CSV <span style="color:var(--color-danger)">*</span></label>
      <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
    </div>

    <div class="form-actions">
      <a href="<?= url("jugadores/equipo/{$team['id']}/liga/{$league['id']}") ?>" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-primary">📥 Importar</button>
    </div>
  </form>
</div>
