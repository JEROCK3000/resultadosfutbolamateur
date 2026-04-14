<?php /** app/Views/schedule/generate.php — Formulario del Sorteo */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('ligas') ?>">Campeonatos</a><span class="breadcrumb-sep">›</span>
  <span>Sorteo Automático</span>
</nav>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <h2 class="card-title">🎲 Sorteo y Fixture Automático</h2>
    <p style="font-size: .85rem; color: var(--color-text-muted); margin-top:4px">
      Campeonato: <?= htmlspecialchars($league['name']) ?>
    </p>
  </div>
  
  <div class="card-body">
    <form action="<?= url("calendario/generar/{$league['id']}") ?>" method="POST">
      
      <p style="margin-bottom:15px; color:var(--color-text-muted)">
        Este proceso creará los encuentros del ciclo "Todos contra Todos" y los agrupará por <strong>Jornadas o Fechas</strong> (FECHA 1, FECHA 2, etc.). 
        <br>Los emparejamientos nacerán como <em>"Por programar"</em>. Las fechas, horas, estadios y árbitros se asignarán semanalmente en el Módulo de Sorteo Semanal.
      </p>

      <div class="form-group">
        <label class="form-label">Modalidad de Campeonato</label>
        <select name="mode" class="form-control">
          <option value="1_vuelta">1 Vuelta (Ida) — Ideal torneos cortos</option>
          <option value="2_vueltas">2 Vueltas (Ida y Vuelta) — Sistema europeo</option>
        </select>
      </div>

      <?php if (count($teams) < 2): ?>
        <div class="alert alert-warning">
          Se necesitan al menos 2 equipos inscritos en este campeonato. Tienes <?= count($teams) ?>.
        </div>
      <?php else: ?>
        <div class="alert alert-info">
          Se sortearán y cruzarán matemáticamente a los <strong><?= count($teams) ?> equipos participantes</strong>.
        </div>
      <?php endif; ?>

      <div class="form-actions mt-4">
        <a href="<?= url('ligas') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary" <?= count($teams) < 2 ? 'disabled' : '' ?> style="font-size:1.1rem; padding:10px 20px">
          ✨ Generar Fixture Base
        </button>
      </div>

    </form>
  </div>
</div>
