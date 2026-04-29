<?php /** app/Views/weekly_schedule/show.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('programacion') ?>">Programación</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>

<div style="display:flex; gap:20px; flex-wrap:wrap">

  <!-- BARRA LATERAL: Pestañas de Fechas -->
  <div style="flex:1; min-width:250px; max-width:300px">
    <div class="card" style="padding:15px">
      <h3 style="margin-bottom:15px; font-size:1.1rem; padding-bottom:10px; border-bottom:1px solid var(--color-border)">📅 Jornadas Generadas</h3>
      <?php if(empty($rounds)): ?>
         <div class="alert alert-warning" style="font-size:.85rem">No se ha generado el Fixture Base aún.</div>
         <a href="<?= url("calendario/generar/{$league['id']}") ?>" class="btn btn-primary btn-sm" style="display:block;text-align:center">Ir a Sorteo de Fixture</a>
      <?php else: ?>
         <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:5px">
           <?php foreach($rounds as $r => $matchesList):
              $unscheduledCount = count(array_filter($matchesList, fn($m) => $m['status'] === 'unscheduled'));
           ?>
            <li>
               <a href="?fecha=<?= $r ?>" style="display:flex; justify-content:space-between; padding:10px; border-radius:8px; text-decoration:none; background:<?= $r == $selectedRound ? 'var(--color-primary)' : 'var(--color-surface)' ?>; color:<?= $r == $selectedRound ? '#fff' : 'inherit' ?>; border:1px solid <?= $r == $selectedRound ? 'transparent' : 'var(--color-border)' ?>">
                 <strong>FECHA <?= $r ?></strong>
                 <?php if($unscheduledCount > 0): ?>
                    <span class="badge badge-warning"><?= $unscheduledCount ?> faltan</span>
                 <?php else: ?>
                    <span class="badge badge-success">Completa</span>
                 <?php endif; ?>
               </a>
            </li>
           <?php endforeach; ?>
         </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- PANEL PRINCIPAL -->
  <div style="flex:3; min-width:300px">
    <?php if(!empty($matchesInRound)): ?>

      <?php
         $unscheduledMatches = array_filter($matchesInRound, fn($m) => $m['status'] === 'unscheduled');
         $scheduledMatches   = array_filter($matchesInRound, fn($m) => $m['status'] !== 'unscheduled');
      ?>

      <!-- Panel de sorteo automático (solo si hay partidos pendientes) -->
      <?php if(!empty($unscheduledMatches)): ?>
      <div class="card" style="margin-bottom:20px; border:2px solid var(--color-primary)">
        <div class="card-header pb-blue" style="color:#fff; margin:-20px -20px 20px -20px; padding:15px 20px; border-radius:10px 10px 0 0">
           <h2 class="card-title" style="margin:0; font-size:1.2rem; color:#fff">🎲 Sorteo Semanal — FECHA <?= $selectedRound ?></h2>
           <p style="margin:0; font-size:.85rem; opacity:.8">Asignación equitativa de día, hora y estadio para <?= count($unscheduledMatches) ?> partido(s) pendiente(s).</p>
        </div>

        <form action="<?= url("programacion/{$league['id']}") ?>" method="POST">
          <input type="hidden" name="round_number" value="<?= $selectedRound ?>">

          <div style="display:flex; gap:15px; flex-wrap:wrap">
            <div class="form-group" style="flex:1; min-width:160px">
              <label class="form-label">Día de Inicio</label>
              <input type="date" name="start_date" id="inp-start" class="form-control" required value="<?= date('Y-m-d', strtotime('next saturday')) ?>">
            </div>
            <div class="form-group" style="flex:1; min-width:160px">
              <label class="form-label">Día Final</label>
              <input type="date" name="end_date" id="inp-end" class="form-control" required value="<?= date('Y-m-d', strtotime('next sunday')) ?>">
              <small style="color:var(--color-text-muted); font-size:.77rem">Solo se asignarán slots hasta esta fecha (inclusive).</small>
            </div>
            <div class="form-group" style="flex:1; min-width:160px">
              <label class="form-label">Bloques de Horario</label>
              <input type="text" name="play_times" class="form-control" value="10:00, 12:00, 14:00, 16:00" placeholder="10:00, 12:00, 14:00, 16:00" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Días Habilitados</label>
            <div style="display:flex; gap:16px; margin-top:8px">
              <label><input type="checkbox" name="play_days[]" value="6" checked> Sábados</label>
              <label><input type="checkbox" name="play_days[]" value="0" checked> Domingos</label>
            </div>
          </div>

          <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:10px">
            <div class="form-group" style="flex:1">
              <label class="form-label">Estadios Disponibles</label>
              <div style="background:var(--color-bg); padding:10px; border-radius:8px; border:1px solid var(--color-border); max-height:150px; overflow-y:auto">
                <?php foreach($stadiums as $s): ?>
                  <label style="display:block; margin-bottom:5px">
                    <input type="checkbox" name="stadiums[]" value="<?= $s['id'] ?>" checked> <?= e($s['name']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="form-group" style="flex:1">
              <label class="form-label">Árbitros (Opcional)</label>
              <div style="background:var(--color-bg); padding:10px; border-radius:8px; border:1px solid var(--color-border); max-height:120px; overflow-y:auto">
                <?php foreach($referees as $r): ?>
                  <label style="display:block; margin-bottom:5px">
                    <input type="checkbox" name="referees[]" value="<?= $r['id'] ?>" checked> <?= e($r['name']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
              <!-- Modo de asignación de árbitros -->
              <div style="margin-top:10px; padding:10px; background:var(--color-bg); border-radius:8px; border:1px solid var(--color-border)">
                <p style="font-size:.8rem; font-weight:600; margin-bottom:6px; color:var(--color-text-muted)">Modo de asignación:</p>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:4px">
                  <input type="radio" name="referee_mode" value="equitable" checked>
                  <span style="font-size:.85rem"><strong>Rotación equitativa</strong> — distribuye la carga por día/hora</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer">
                  <input type="radio" name="referee_mode" value="random">
                  <span style="font-size:.85rem"><strong>Aleatorio puro</strong> — asignación completamente al azar</span>
                </label>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary pb-blue" style="width:100%; margin-top:15px; padding:12px; font-size:1.1rem; font-weight:bold">
            ⚡ Ejecutar Sorteo Logístico
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Tabla de cruces de la jornada -->
      <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; flex-wrap:wrap; gap:10px">
          <h2 style="font-size:1.2rem; margin:0">⚽ Cruces Oficiales — FECHA <?= $selectedRound ?></h2>
          <?php if(!empty($scheduledMatches)): ?>
            <span style="font-size:.8rem; color:var(--color-text-muted)">Haz clic en ✏️ para editar un partido</span>
          <?php endif; ?>
        </div>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Local</th><th></th><th>Visitante</th>
                <th>Día</th><th>Hora</th><th>Estadio</th><th>Árbitro</th>
                <th>Estado</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($matchesInRound as $m): ?>
                <?php $isUnscheduled = ($m['status'] === 'unscheduled'); ?>
                <tr style="<?= $isUnscheduled ? 'background:rgba(245,158,11,0.05)' : '' ?>">
                  <td style="text-align:right">
                    <strong><?= e($m['home_team'] ?? '—') ?></strong>
                    <?php if(!empty($m['home_logo'])): ?>
                      <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-left:6px;border-radius:50%">
                    <?php else: ?>
                      <span style="font-size:14px;vertical-align:middle;margin-left:6px">🛡️</span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:center; color:var(--color-text-muted); font-weight:600">vs</td>
                  <td style="text-align:left">
                    <?php if(!empty($m['away_logo'])): ?>
                      <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-right:6px;border-radius:50%">
                    <?php else: ?>
                      <span style="font-size:14px;vertical-align:middle;margin-right:6px">🛡️</span>
                    <?php endif; ?>
                    <strong><?= e($m['away_team'] ?? '—') ?></strong>
                  </td>

                  <?php if($isUnscheduled): ?>
                    <td colspan="5" style="text-align:center; color:#d97706; font-style:italic; font-size:.85rem">
                      Pendiente de sorteo logístico
                    </td>
                    <td></td>
                  <?php else: ?>
                    <td style="font-size:.85rem; white-space:nowrap">
                      <?= date('d/m/Y', strtotime($m['match_date'])) ?>
                      <span style="font-size:.75rem; color:var(--color-text-muted)">
                        (<?= ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w', strtotime($m['match_date']))] ?>)
                      </span>
                    </td>
                    <td style="font-weight:700; white-space:nowrap"><?= substr($m['match_time'], 0, 5) ?></td>
                    <td style="font-size:.85rem"><?= e($m['stadium'] ?? '—') ?></td>
                    <td style="font-size:.85rem; color:var(--color-text-muted)"><?= e($m['referee_name'] ?? '—') ?></td>
                    <td><span class="badge badge-success">Programado</span></td>
                    <td>
                      <button
                        class="btn btn-sm btn-edit-match"
                        title="Editar este partido"
                        style="padding:4px 10px; font-size:.8rem"
                        data-id="<?= $m['id'] ?>"
                        data-home="<?= e($m['home_team'] ?? '') ?>"
                        data-away="<?= e($m['away_team'] ?? '') ?>"
                        data-date="<?= $m['match_date'] ?>"
                        data-time="<?= substr($m['match_time'], 0, 5) ?>"
                        data-stadium="<?= $m['stadium_id'] ?? '' ?>"
                        data-referee="<?= $m['referee_id'] ?? '' ?>"
                        data-action="<?= url("programacion/{$league['id']}/partido/{$m['id']}/actualizar") ?>">
                        ✏️ Editar
                      </button>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  Modal de edición manual de partido                    -->
<!-- ═══════════════════════════════════════════════════════ -->
<div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:2000; align-items:center; justify-content:center; padding:16px">
  <div style="background:var(--color-surface); border-radius:14px; padding:24px; width:100%; max-width:500px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.3)">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px">
      <div>
        <h3 style="margin:0; font-size:1.15rem">✏️ Editar Partido</h3>
        <p id="edit-matchup" style="margin:4px 0 0; font-size:.85rem; color:var(--color-text-muted)"></p>
      </div>
      <button id="close-modal" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:var(--color-text-muted); line-height:1; padding:0 4px">×</button>
    </div>

    <form id="edit-form" method="POST">

      <div style="display:flex; gap:12px; flex-wrap:wrap">
        <div class="form-group" style="flex:1; min-width:160px">
          <label class="form-label">Fecha del partido</label>
          <input type="date" name="match_date" id="edit-date" class="form-control" required>
        </div>
        <div class="form-group" style="flex:1; min-width:120px">
          <label class="form-label">Hora</label>
          <input type="time" name="match_time" id="edit-time" class="form-control" required step="1800">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Estadio</label>
        <select name="stadium_id" id="edit-stadium" class="form-control">
          <option value="">— Sin asignar —</option>
          <?php foreach($stadiums as $s): ?>
            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Árbitro</label>
        <select name="referee_id" id="edit-referee" class="form-control">
          <option value="">— Sin asignar —</option>
          <?php foreach($referees as $r): ?>
            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.3); border-radius:8px; padding:10px; margin-top:4px; font-size:.8rem; color:#92400e">
        ⚠️ El sistema validará que ninguno de los dos equipos tenga otro partido en la misma fecha y que el estadio esté disponible en ese horario.
      </div>

      <div style="display:flex; gap:10px; margin-top:20px">
        <button type="button" id="close-modal-btn" class="btn btn-secondary" style="flex:1">Cancelar</button>
        <button type="submit" class="btn btn-primary pb-blue" style="flex:2; font-weight:600">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sincronizar Día Final para que nunca sea menor que Día de Inicio
    const inpStart = document.getElementById('inp-start');
    const inpEnd   = document.getElementById('inp-end');
    if (inpStart && inpEnd) {
        inpStart.addEventListener('change', () => {
            if (inpEnd.value < inpStart.value) inpEnd.value = inpStart.value;
            inpEnd.min = inpStart.value;
        });
        inpEnd.min = inpStart.value;
    }

    const modal         = document.getElementById('edit-modal');
    const form          = document.getElementById('edit-form');
    const matchupLabel  = document.getElementById('edit-matchup');

    // Abrir modal al hacer clic en cualquier botón editar
    document.querySelectorAll('.btn-edit-match').forEach(btn => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.action;
            matchupLabel.textContent = btn.dataset.home + ' vs ' + btn.dataset.away;

            document.getElementById('edit-date').value    = btn.dataset.date    || '';
            document.getElementById('edit-time').value    = btn.dataset.time    || '';
            document.getElementById('edit-stadium').value = btn.dataset.stadium || '';
            document.getElementById('edit-referee').value = btn.dataset.referee || '';

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    // Cerrar modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.getElementById('close-modal').addEventListener('click', closeModal);
    document.getElementById('close-modal-btn').addEventListener('click', closeModal);

    // Clic fuera del contenido cierra el modal
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Escape también cierra
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
});
</script>
