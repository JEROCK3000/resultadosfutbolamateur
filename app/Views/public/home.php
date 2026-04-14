<?php /** app/Views/public/home.php */ ?>
<?php $pageTitle = 'Inicio'; ?>
<!-- Hero -->
<div style="text-align:center;padding:48px 16px 32px;background:linear-gradient(135deg,rgba(96,165,250,.1),rgba(167,139,250,.08));border-radius:16px;margin-bottom:32px">
  <h1 style="font-family:var(--font-head);font-size:clamp(1.8rem,6vw,3rem);font-weight:900;margin-bottom:12px">
    ⚽ <span style="background:linear-gradient(135deg,#60a5fa,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Fútbol Amateur de Napo</span>
  </h1>
  <p style="color:var(--pub-muted);font-size:1rem;max-width:500px;margin:0 auto">
    Resultados, tabla de posiciones y próximos encuentros de los campeonatos cantonales de la Amazonía Ecuatoriana
  </p>
</div>

<!-- Campeonatos disponibles -->
<h2 style="font-family:var(--font-head);font-size:1.2rem;font-weight:700;margin-bottom:16px">🏆 Campeonatos Disponibles</h2>

<?php if (empty($leagues)): ?>
  <div style="text-align:center;padding:40px;color:var(--pub-muted)">
    <div style="font-size:3rem;margin-bottom:16px">⚽</div>
    <p>No hay campeonatos registradas aún. Próximamente...</p>
  </div>
<?php else: ?>
  <div class="league-grid">
    <?php foreach ($leagues as $l): ?>
    <a class="league-card" href="<?= BASE_URL ?>/principal/liga/<?= (int)$l['id'] ?>">
      <h3>🏆 <?= e($l['name']) ?></h3>
      <p><?= e($l['country']) ?> &middot; Temporada <?= e($l['season']) ?></p>
      <p style="margin-top:8px">
        <span class="pub-badge <?= $l['status']==='active' ? 'pb-green' : 'pb-muted' ?>">
          <?= $l['status']==='active' ? 'En curso' : ucfirst($l['status']) ?>
        </span>
      </p>
      <p style="margin-top:12px;font-size:.78rem;color:var(--pub-primary)">Ver posiciones y encuentros →</p>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
