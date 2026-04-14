<?php /** app/Views/draws/show.php */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('sorteos') ?>">Sorteos</a><span class="breadcrumb-sep">›</span>
  <span><?= e($league['name']) ?></span>
</nav>
<div class="card">
  <div class="card-header" style="justify-content:space-between">
    <div>
        <h1 class="card-title">🎲 Sorteo de Campeonato</h1>
        <span class="badge badge-info" style="margin-top:5px;display:inline-block"><?= e($league['name']) ?></span>
    </div>
    <button id="btn-draw" class="btn btn-primary pb-blue" style="font-size:1.1rem; padding:10px 20px" <?= empty($teams) ? 'disabled' : '' ?>>
      <span class="icon" style="margin-right:8px">🔀</span> ¡Realizar Sorteo!
    </button>
  </div>
  
  <?php if (empty($teams)): ?>
    <div class="empty-state">
      <div class="empty-icon">👕</div>
      <p>No hay equipos registrados en este campeonato</p>
    </div>
  <?php else: ?>
    <p class="text-muted" style="margin-bottom:15px">Presiona el botón superior para realizar un sorteo aleatorio entre los <strong><?= count($teams) ?> equipos</strong> del campeonato. Ideal para confirmar el ordenamiento estocástico de las inscripciones.</p>
    
    <div id="draw-container" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px; position:relative; overflow:hidden">
      <?php foreach ($teams as $idx => $t): ?>
        <div class="draw-item" data-id="<?= $t['id'] ?>" style="padding:15px; border:2px solid var(--color-border); border-radius:10px; background:var(--color-surface); text-align:center; transition:all 0.4s ease; transform:scale(1)">
          <div class="draw-number" style="width:30px; height:30px; line-height:30px; background:rgba(30,64,175,0.1); color:var(--color-primary); border-radius:50%; margin:0 auto 10px auto; font-weight:800; font-size:.9rem; opacity:0">?</div>
          <strong style="display:block; font-size:1rem"><?= e($t['name']) ?></strong>
          <?php if($t['short_name']): ?>
              <span style="font-size:0.75rem; color:var(--color-text-muted)"><?= e($t['short_name']) ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-draw');
    if(!btn) return;
    
    btn.addEventListener('click', () => {
        const container = document.getElementById('draw-container');
        const items = Array.from(container.querySelectorAll('.draw-item'));
        
        if (items.length === 0) return;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="icon">🔄</span> Sorteando...';
        
        // Efecto de desorden visual (ruleta)
        let shuffles = 0;
        const maxShuffles = 15;
        const shuffleInterval = setInterval(() => {
            // Mover pseudo-aleatorio en el UI cambiando orden flex
            items.forEach(el => {
                el.style.order = Math.floor(Math.random() * items.length);
                el.style.borderColor = `hsl(${Math.random() * 360}, 70%, 60%)`;
                el.style.transform = 'scale(0.95)';
            });
            shuffles++;
            
            if (shuffles >= maxShuffles) {
                clearInterval(shuffleInterval);
                finishDraw(container, items, btn);
            }
        }, 120);
    });
    
    function finishDraw(container, items, btn) {
        // Orden final estricto matemáticamente aleatorio (Fisher-Yates)
        for (let i = items.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [items[i], items[j]] = [items[j], items[i]];
        }
        
        // Aplicar orden
        items.forEach((el, index) => {
            el.style.order = index;
            // Estilo final de revelación rápida secuencial
            setTimeout(() => {
                el.style.transition = 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                el.style.transform = 'scale(1)';
                el.style.borderColor = 'var(--color-success)';
                const nBadge = el.querySelector('.draw-number');
                nBadge.innerText = (index + 1);
                nBadge.style.opacity = 1;
                nBadge.style.background = 'var(--color-success)';
                nBadge.style.color = '#fff';
            }, index * 100);
        });
        
        setTimeout(() => {
            btn.innerHTML = '<span class="icon">✅</span> Sorteo Finalizado';
            btn.classList.add('btn-success');
            setTimeout(() => {
              btn.disabled = false;
              btn.innerHTML = '<span class="icon">🔄</span> Sortear Nuevamente';
              btn.classList.remove('btn-success');
            }, 2000);
        }, items.length * 100 + 300);
    }
});
</script>
