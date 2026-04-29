<?php /** app/Views/schedule/generate.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('ligas') ?>">Campeonatos</a><span class="breadcrumb-sep">›</span>
  <span>Fixture Base</span>
</nav>

<div class="card" style="max-width:620px; margin:0 auto">
  <div class="card-header">
    <h2 class="card-title">🎲 Generar Fixture Base</h2>
    <p style="font-size:.85rem; color:var(--color-text-muted); margin-top:4px">
      Campeonato: <strong><?= e($league['name']) ?></strong> — <?= count($teams) ?> equipos registrados
    </p>
  </div>

  <?php if ($existingCount > 0): ?>
  <!-- ── Aviso: ya existe fixture ────────────────────────────────────── -->
  <div style="background:rgba(239,68,68,.08); border:2px solid rgba(239,68,68,.3); border-radius:10px; padding:16px; margin-bottom:20px">
    <p style="margin:0 0 8px; font-weight:700; color:#b91c1c; font-size:1rem">⚠️ Este campeonato ya tiene un fixture generado</p>
    <p style="margin:0 0 12px; font-size:.9rem; color:#7f1d1d">
      Existen <strong><?= number_format($existingCount) ?> partidos</strong> en el sistema para este campeonato.
      Generar de nuevo <strong>eliminará todos los partidos existentes</strong>, incluyendo los que ya tengan fecha, hora y árbitro asignados.
    </p>
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; background:rgba(239,68,68,.1); padding:10px 14px; border-radius:8px; border:1px solid rgba(239,68,68,.3)">
      <input type="checkbox" id="reset-toggle" style="width:18px; height:18px; accent-color:#dc2626">
      <span style="font-size:.9rem; font-weight:600; color:#b91c1c">Entiendo el riesgo — deseo resetear y regenerar el fixture completo</span>
    </label>
  </div>
  <?php endif; ?>

  <form action="<?= url("calendario/generar/{$league['id']}") ?>" method="POST" id="fixture-form">
    <?php if ($existingCount > 0): ?>
      <input type="hidden" name="reset_fixture" id="reset-value" value="0">
    <?php endif; ?>

    <div class="form-group">
      <label class="form-label">Modalidad del Campeonato</label>
      <select name="mode" class="form-control">
        <option value="1_vuelta">1 Vuelta — Todos contra todos (ida)</option>
        <option value="2_vueltas">2 Vueltas — Todos contra todos (ida y vuelta)</option>
      </select>
      <p style="font-size:.8rem; color:var(--color-text-muted); margin-top:6px">
        Con <?= count($teams) ?> equipos:
        <strong>1 vuelta = <?= count($teams) - 1 ?> fechas × <?= intdiv(count($teams), 2) ?> partidos</strong> —
        <strong>2 vueltas = <?= (count($teams) - 1) * 2 ?> fechas × <?= intdiv(count($teams), 2) ?> partidos</strong>
      </p>
    </div>

    <div style="margin-top:20px; padding:14px; background:var(--color-bg); border-radius:8px; border:1px solid var(--color-border); font-size:.85rem; color:var(--color-text-muted)">
      <strong>¿Cómo funciona?</strong> El sistema sortea aleatoriamente el orden de los <?= count($teams) ?> equipos y aplica el
      <em>algoritmo de Berger (Round-Robin)</em> para crear todos los emparejamientos de manera balanceada.
      Los partidos se crean como «Por programar» — sin fecha ni hora. La fecha y hora de cada jornada
      se asigna semana a semana desde <a href="<?= url("programacion/{$league['id']}") ?>" style="color:var(--color-primary)">Programación Semanal</a>.
    </div>

    <div class="form-actions" style="margin-top:20px">
      <a href="<?= url('ligas') ?>" class="btn btn-secondary">Cancelar</a>
      <button type="submit" id="submit-btn" class="btn btn-primary pb-blue"
              <?= ($existingCount > 0) ? 'disabled' : '' ?>
              style="font-size:1rem; padding:10px 24px; font-weight:600">
        <?= $existingCount > 0 ? '🔒 Resetear y Generar' : '✨ Generar Fixture' ?>
      </button>
    </div>
  </form>
</div>

<?php if ($existingCount > 0): ?>
<script>
document.getElementById('reset-toggle').addEventListener('change', function() {
    const btn = document.getElementById('submit-btn');
    const val = document.getElementById('reset-value');
    btn.disabled = !this.checked;
    val.value    = this.checked ? '1' : '0';
});
</script>
<?php endif; ?>
