<?php /** app/Views/teams/import.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url("equipos/liga/{$league['id']}") ?>"><?= e($league['name']) ?></a><span class="breadcrumb-sep">›</span>
  <span>Clonar Equipos</span>
</nav>

<div class="card" style="max-width: 700px; margin: 0 auto;">
  <div class="card-header pb-blue" style="color:#fff; border-radius:8px 8px 0 0; margin:-20px -20px 20px -20px; padding:15px 20px">
    <h2 class="card-title" style="margin:0; color:#fff">🔄 Clonar Equipos desde otro Campeonato</h2>
  </div>

  <div class="card-body">
    <p style="color:var(--color-text-muted); margin-bottom:20px">
      Reutiliza los equipos (incluyendo sus escudos y abreviaturas) que ya participaron en campeonatos anteriores. Se copiarán inmediatamente a <strong><?= e($league['name']) ?></strong>.
    </p>

    <div class="form-group mb-4">
      <label class="form-label">Campeonato de Origen</label>
      <select id="source_league" class="form-control" style="font-weight:bold; background:var(--color-surface)">
        <option value="">— Seleccione campeonato —</option>
        <?php foreach ($otherLeagues as $l): ?>
          <option value="<?= $l['id'] ?>"><?= e($l['name']) ?> (<?= e($l['season']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <form action="<?= url("equipos/importar/{$league['id']}") ?>" method="POST" id="import_form" style="display:none">
      <div class="form-group">
        <label class="form-label">Selecciona los equipos a clonar</label>
        <div id="teams_list" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; background:var(--color-bg); padding:15px; border-radius:8px; border:1px solid var(--color-border); max-height:300px; overflow-y:auto">
           <!-- cargado via json -->
        </div>
      </div>
      
      <div style="margin-top:20px; display:flex; gap:10px">
        <a href="<?= url("equipos/liga/{$league['id']}") ?>" class="btn btn-secondary" style="padding:10px 20px">Cancelar</a>
        <button type="submit" class="btn btn-primary pb-blue" id="btn_submit" style="padding:10px 20px; font-weight:bold">⬇️ Clonar Seleccionados</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sl = document.getElementById('source_league');
    const form = document.getElementById('import_form');
    const container = document.getElementById('teams_list');
    const btnSubmit = document.getElementById('btn_submit');

    sl.addEventListener('change', async (e) => {
        const lid = e.target.value;
        if(!lid){
            form.style.display = 'none';
            return;
        }

        container.innerHTML = '<span style="color:var(--color-text-muted)">Cargando equipos...</span>';
        form.style.display = 'block';
        btnSubmit.disabled = true;

        try {
            const res = await fetch(window.BASE_URL + '/equipos/por-liga/' + lid);
            const teams = await res.json();
            
            if(teams.length === 0){
                container.innerHTML = '<span style="color:var(--color-warning)">No hay equipos inscritos en ese campeonato.</span>';
                return;
            }

            container.innerHTML = '';
            teams.forEach(t => {
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
                label.style.background = 'var(--color-surface)';
                label.style.padding = '8px 10px';
                label.style.borderRadius = '6px';
                label.style.cursor = 'pointer';
                label.style.border = '1px solid var(--color-border)';

                const imgHtml = t.logo 
                    ? `<img src="${window.BASE_URL}/assets/img/teams/${t.logo}" width="20" height="20" style="object-fit:contain; border-radius:50%">` 
                    : `<span style="font-size:16px">🛡️</span>`;

                label.innerHTML = `
                    <input type="checkbox" name="teams[]" value="${t.id}" checked>
                    ${imgHtml}
                    <strong>${t.name}</strong>
                `;
                container.appendChild(label);
            });
            btnSubmit.disabled = false;

        } catch (err){
            container.innerHTML = '<span style="color:var(--color-danger)">Error de conexión cargando equipos.</span>';
        }
    });
});
</script>
