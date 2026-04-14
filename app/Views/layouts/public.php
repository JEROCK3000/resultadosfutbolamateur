<?php
/** app/Views/layouts/public.php — Layout del sitio web público */
$pageTitle = $pageTitle ?? 'Resultados Campeonato de Fútbol';
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Campeonato de Fútbol</title>
  <meta name="description" content="Resultados, tabla de posiciones y próximos encuentros del fútbol amateur de Borja, Quijos, Amazonía Ecuatoriana.">
  
  <!-- PWA Meta Tags -->
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="#0d1117">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Liga Borja">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --pub-bg:       #0d1117;
      --pub-surface:  #161b22;
      --pub-border:   #30363d;
      --pub-text:     #e6edf3;
      --pub-muted:    #8b949e;
      --pub-primary:  #60a5fa;
      --pub-accent:   #a78bfa;
      --pub-success:  #34d399;
      --pub-warning:  #fbbf24;
      --pub-danger:   #f87171;
      --radius:       12px;
      --font-body:    'Inter', sans-serif;
      --font-head:    'Outfit', sans-serif;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:var(--font-body); background:var(--pub-bg); color:var(--pub-text); min-height:100vh; }

    /* ── Header ── */
    .pub-header {
      background:linear-gradient(135deg, #111827 0%, #1e2a4a 100%);
      border-bottom:1px solid var(--pub-border);
      padding:0 20px;
    }
    .pub-header-inner {
      max-width:1100px; margin:0 auto;
      display:flex; align-items:center; justify-content:space-between;
      height:64px; gap:16px; flex-wrap:wrap;
    }
    .pub-logo { font-family:var(--font-head); font-size:1.4rem; font-weight:800;
                text-decoration:none; color:var(--pub-text); }
    .pub-logo span { background:linear-gradient(135deg,#60a5fa,#a78bfa);
                     -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    .pub-nav { display:flex; gap:8px; flex-wrap:wrap; }
    .pub-nav a { padding:6px 14px; border-radius:20px; font-size:.82rem; font-weight:500;
                 text-decoration:none; color:var(--pub-muted); transition:.2s; border:1px solid transparent; }
    .pub-nav a:hover, .pub-nav a.active { color:var(--pub-primary); border-color:var(--pub-primary); background:rgba(96,165,250,.08); }
    .pub-login-btn { font-size:.8rem; padding:6px 14px; border-radius:20px;
                     background:var(--pub-primary); color:#fff; text-decoration:none; font-weight:600;
                     border:none; transition:.2s; }
    .pub-login-btn:hover { background:#3b82f6; }

    /* ── Content ── */
    .pub-main { max-width:1100px; margin:0 auto; padding:24px 16px 60px; }

    /* ── Cards ── */
    .pub-card { background:var(--pub-surface); border:1px solid var(--pub-border); border-radius:var(--radius); padding:24px; margin-bottom:20px; }
    .pub-card-title { font-family:var(--font-head); font-size:1.2rem; font-weight:700; margin-bottom:16px; }

    /* ── League Cards ── */
    .league-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
    .league-card { background:linear-gradient(135deg,#161b22,#1e2a4a); border:1px solid var(--pub-border);
                   border-radius:var(--radius); padding:20px; text-decoration:none; transition:.2s;
                   display:block; }
    .league-card:hover { border-color:var(--pub-primary); transform:translateY(-2px); box-shadow:0 8px 24px rgba(96,165,250,.15); }
    .league-card h3 { color:var(--pub-text); font-size:1rem; font-weight:700; margin-bottom:6px; }
    .league-card p  { color:var(--pub-muted); font-size:.82rem; }

    /* ── Tables ── */
    .pub-table-wrap { overflow-x:auto; }
    .pub-table { width:100%; border-collapse:collapse; font-size:.87rem; }
    .pub-table th { background:rgba(96,165,250,.1); padding:10px 12px; text-align:left; font-weight:600;
                    font-size:.78rem; letter-spacing:.04em; text-transform:uppercase; color:var(--pub-muted); border-bottom:1px solid var(--pub-border); }
    .pub-table td { padding:10px 12px; border-bottom:1px solid rgba(48,54,61,.6); }
    .pub-table tr:last-child td { border-bottom:none; }
    .pub-table tr:hover td { background:rgba(255,255,255,.02); }

    /* ── Badge ── */
    .pub-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.74rem; font-weight:600; }
    .pb-green  { background:rgba(52,211,153,.15); color:var(--pub-success); }
    .pb-blue   { background:rgba(96,165,250,.15); color:var(--pub-primary); }
    .pb-muted  { background:rgba(139,148,158,.12); color:var(--pub-muted); }

    /* ── Export buttons ── */
    .export-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .btn-export { padding:6px 14px; border-radius:8px; font-size:.8rem; font-weight:600; text-decoration:none;
                  border:1px solid; cursor:pointer; transition:.2s; display:inline-flex; align-items:center; gap:6px; }
    .btn-pdf   { color:#f87171; border-color:#f87171; background:rgba(248,113,113,.08); }
    .btn-excel { color:var(--pub-success); border-color:var(--pub-success); background:rgba(52,211,153,.08); }
    .btn-pdf:hover   { background:rgba(248,113,113,.18); }
    .btn-excel:hover { background:rgba(52,211,153,.18); }

    /* ── Responsive ── */
    @media (max-width:600px) {
      .pub-header-inner { height:auto; padding:12px 0; }
      .pub-logo { font-size:1.1rem; }
    }
  </style>
</head>
<body>
  <header class="pub-header">
    <div class="pub-header-inner">
      <a class="pub-logo" href="<?= BASE_URL ?>/principal">⚽ <span>Campeonato de Fútbol</span></a>
      <nav class="pub-nav">
        <?php if (!empty($leagues ?? [])): foreach ($leagues as $l): ?>
          <a href="<?= BASE_URL ?>/principal/liga/<?= (int)$l['id'] ?>"
             <?= (isset($league) && $league['id']==$l['id'] ? 'class="active"' : '') ?>>
            <?= e($l['name']) ?>
          </a>
        <?php endforeach; endif; ?>
        <a href="<?= BASE_URL ?>/playoffs_custom.php" 
           style="background:rgba(251,191,36,0.1); color:#fbbf24; border:1px solid rgba(251,191,36,0.2); font-weight:700;">
           🏆 Llaves finales 2026
        </a>
      </nav>
      <a href="<?= BASE_URL ?>/login" class="pub-login-btn">🔐 Admin</a>
    </div>
  </header>
  <main class="pub-main">
    <?= $content ?>
  </main>
  <footer class="footer" style="text-align:center;padding:20px;font-size:.76rem;color:var(--pub-muted);border-top:1px solid var(--pub-border)">
    <p>&copy; <?= date('Y') ?> Resultados Fútbol Amateur - Liga San Francisco de Borja, Quijos.</p>
    <p><small>Desarrollado para la pasión del deporte local. Powered by SOLINTEEC DEVS & TECH</small></p>
  </footer>

  <script>
    // Registro del PWA Service Worker
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
          .then(registration => {
            console.log('SW Registrado correctamente:', registration.scope);
          })
          .catch(err => {
            console.log('Fallo al registrar SW:', err);
          });
      });
    }
  </script>
</body>
</html>
