<?php
/** app/Views/layouts/auth.php — Layout mínimo para login (sin sidebar) */
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle).' | ' : '' ?>Resultados Fútbol</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh;
           background: radial-gradient(ellipse at top, #1e2a4a 0%, #0d1117 70%); }
    .auth-card { width:100%; max-width:420px; padding:40px 36px; animation: fadeInUp .5s ease; }
    .auth-logo  { text-align:center; margin-bottom:32px; }
    .auth-logo h1 { font-family:var(--font-heading); font-size:2rem; font-weight:800; color:var(--color-text); }
    .auth-logo h1 span { background:linear-gradient(135deg,#60a5fa,#a78bfa); -webkit-background-clip:text;
                         -webkit-text-fill-color:transparent; background-clip:text; }
    .auth-logo p  { color:var(--color-text-muted); font-size:.85rem; margin-top:4px; }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
  </style>
</head>
<body>
  <?php
    $flash = getFlash();
    if ($flash):
  ?>
  <div class="alert alert-<?= e($flash['type']) ?>" style="position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:9999;min-width:300px;text-align:center">
    <?= e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <div class="card auth-card">
    <?= $content ?>
  </div>
</body>
</html>
