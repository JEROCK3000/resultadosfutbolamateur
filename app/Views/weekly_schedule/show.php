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
         $scheduledMatches = array_filter($matchesInRound, fn($m) => $m['status'] !== 'unscheduled');
      ?>

      <!-- Si existen partidos por programar de esta fecha, mostramos el Panel de Sorteo Logístico -->
      <?php if(!empty($unscheduledMatches)): ?>
      <div class="card" style="margin-bottom:20px; border:2px solid var(--color-primary)">
        <div class="card-header pb-blue" style="color:#fff; margin:-20px -20px 20px -20px; padding:15px 20px; border-radius:10px 10px 0 0">
           <h2 class="card-title" style="margin:0; font-size:1.2rem; color:#fff">🎲 Sorteo Semanal de Logística - FECHA <?= $selectedRound ?></h2>
           <p style="margin:0; font-size:.85rem; opacity:.8">Repartición equitativa de turnos para los partidos huérfanos.</p>
        </div>

        <form action="<?= url("programacion/{$league['id']}") ?>" method="POST">
          <input type="hidden" name="round_number" value="<?= $selectedRound ?>">
          
          <div style="display:flex; gap:15px; flex-wrap:wrap">
            <div class="form-group" style="flex:1; min-width:200px">
              <label class="form-label">Día de Inicio</label>
              <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d', strtotime('next saturday')) ?>">
            </div>
            <div class="form-group" style="flex:1; min-width:200px">
              <label class="form-label">Bloques de Horario</label>
              <input type="text" name="play_times" class="form-control" value="10:00, 12:00, 14:00, 16:00" required>
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
              <label class="form-label">Árbitros Asignados (Opcional)</label>
              <div style="background:var(--color-bg); padding:10px; border-radius:8px; border:1px solid var(--color-border); max-height:150px; overflow-y:auto">
                <?php foreach($referees as $r): ?>
                  <label style="display:block; margin-bottom:5px">
                    <input type="checkbox" name="referees[]" value="<?= $r['id'] ?>" checked> <?= e($r['name']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary pb-blue" style="width:100%; margin-top:15px; padding:12px; font-size:1.1rem; font-weight:bold">
            ⚡ Ejecutar Sorteo Logístico (<?= count($unscheduledMatches) ?> partidos)
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Encuentros actuales de la Jornada -->
      <div class="card">
         <h2 style="font-size:1.2rem; margin-bottom:15px">⚽ Cruces Oficiales de la FECHA <?= $selectedRound ?></h2>
         <div class="table-wrapper">
           <table class="table">
             <thead>
               <tr><th>Local</th><th></th><th>Visitante</th><th>Día</th><th>Hora</th><th>Estadio</th><th>Árbitro</th><th>Estado</th></tr>
             </thead>
             <tbody>
               <?php foreach($matchesInRound as $m): ?>
                 <?php $isUnscheduled = ($m['status'] === 'unscheduled'); ?>
                 <tr style="<?= $isUnscheduled ? 'background:rgba(245, 158, 11, 0.05)' : '' ?>">
                    <td style="text-align:right">
                       <strong><?= e($m['home_team'] ?? 'Desconocido') ?></strong>
                       <?php if (!empty($m['home_logo'])): ?>
                          <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-left:6px;border-radius:50%">
                       <?php else: ?>
                          <span style="font-size:14px;vertical-align:middle;margin-left:6px">🛡️</span>
                       <?php endif; ?>
                    </td>
                    <td style="text-align:center; color:var(--color-text-muted)">vs</td>
                    <td style="text-align:left">
                       <?php if (!empty($m['away_logo'])): ?>
                          <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="20" height="20" style="object-fit:contain;vertical-align:middle;margin-right:6px;border-radius:50%">
                       <?php else: ?>
                          <span style="font-size:14px;vertical-align:middle;margin-right:6px">🛡️</span>
                       <?php endif; ?>
                       <strong><?= e($m['away_team'] ?? 'Desconocido') ?></strong>
                    </td>
                    
                    <?php if($isUnscheduled): ?>
                       <td colspan="5" style="text-align:center; color:#d97706; font-style:italic; font-size:.85rem">
                         Pendiente de Sorteo Logístico
                       </td>
                    <?php else: ?>
                       <td style="font-size:.85rem"><?= date('d/m/Y', strtotime($m['match_date'])) ?></td>
                       <td style="font-weight:bold"><?= substr($m['match_time'], 0, 5) ?></td>
                       <td style="font-size:.85rem"><?= e($m['stadium'] ?? '—') ?></td>
                       <td style="font-size:.85rem; color:var(--color-text-muted)">🟨 <?= e($m['referee_name'] ?? '—') ?></td>
                       <td><span class="badge badge-success pt-1 pb-1">Programado</span></td>
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
