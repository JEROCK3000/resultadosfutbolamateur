<?php
/**
 * app/Views/layouts/app.php — Layout base del sistema
 * Todas las vistas incluyen este layout.
 *
 * Variables disponibles al incluir:
 *   $pageTitle  — Título de la página
 *   $content    — Contenido HTML de la vista (via ob_start())
 */
$flash = getFlash();
?><!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistema Multiligas de Fútbol — Resultados, posiciones y encuentros">
  <title><?= e($pageTitle ?? 'Resultados Fútbol') ?> | Resultados Fútbol</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>

<body>

  <!-- ── Overlay sidebar ─────────────────────────────────────── -->
  <div class="sidebar-overlay" id="sidebar-overlay"></div>

  <!-- ── Sidebar ────────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="icon">⚽</span>
      <span>Resultados Fútbol</span>
    </div>

    <nav class="sidebar-nav">

      <div class="nav-section">
        <span class="nav-section-label">Principal</span>
      </div>
      <ul>
        <li class="nav-item">
          <a href="<?= url('/') ?>">
            <span class="nav-icon">🏠</span> Dashboard
          </a>
        </li>
      </ul>

      <div class="nav-section" style="margin-top:8px">
        <span class="nav-section-label">Configuración</span>
      </div>
      <ul>
        <li class="nav-item">
          <a href="<?= url('ligas') ?>">
            <span class="nav-icon">🏆</span> Campeonatos
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('estadios') ?>">
            <span class="nav-icon">🏟️</span> Estadios
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('equipos') ?>">
            <span class="nav-icon">👕</span> Equipos
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('arbitros') ?>">
            <span class="nav-icon">🟨</span> Árbitros
          </a>
        </li>
      </ul>

      <div class="nav-section" style="margin-top:8px">
        <span class="nav-section-label">Competencia</span>
      </div>
      <ul>
        <li class="nav-item">
          <a href="<?= url('encuentros') ?>">
            <span class="nav-icon">📅</span> Encuentros
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('posiciones') ?>">
            <span class="nav-icon">📊</span> Tabla de Posiciones
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('torneos') ?>">
            <span class="nav-icon">🎯</span> Fases Finales
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= url('programacion') ?>">
            <span class="nav-icon">📅</span> Programación / Sorteo Semanal
          </a>
        </li>
      </ul>

      <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <div class="nav-section" style="margin-top:8px">
          <span class="nav-section-label">Administración</span>
        </div>
        <ul>
          <li class="nav-item">
            <a href="<?= url('usuarios') ?>">
              <span class="nav-icon">👥</span> Usuarios
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= url('auditoria') ?>">
              <span class="nav-icon">📋</span> Auditoría
            </a>
          </li>
        </ul>
      <?php endif; ?>

      <div class="nav-section" style="margin-top:8px">
        <span class="nav-section-label">Sitio Público</span>
      </div>
      <ul>
        <li class="nav-item">
          <a href="<?= url('principal') ?>" target="_blank">
            <span class="nav-icon">🌐</span> Ver sitio público
          </a>
        </li>
      </ul>

    </nav>
  </aside>


  <!-- ── Topbar ──────────────────────────────────────────────── -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="btn-menu" id="btn-menu" aria-label="Abrir menú">☰</button>
      <span class="topbar-title">SOLINTEEC - Fútbol</span>
    </div>
    <div class="topbar-right" style="display:flex;align-items:center;gap:12px">
      <a href="<?= url('principal') ?>" target="_blank"
        style="font-size:.78rem;color:var(--color-text-muted);text-decoration:none;border:1px solid currentColor;padding:3px 8px;border-radius:6px">
        🌐 Ver sitio
      </a>
      <span style="font-size:.82rem;color:var(--color-text-muted)">
        <?= e($_SESSION['user_name'] ?? 'Invitado') ?>
        <span style="opacity:.5;font-size:.7rem">(<?= e($_SESSION['user_role'] ?? '') ?>)</span>
      </span>
      <a href="<?= url('logout') ?>" style="font-size:.78rem;color:var(--color-danger);text-decoration:none">↩ Salir</a>
    </div>
  </header>


  <!-- ── Contenido principal ────────────────────────────────── -->
  <main class="main-content">

    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-hide role="alert">
        <?php
        $icon = match ($flash['type']) {
          'success' => '✅', 'danger' => '❌', 'warning' => '⚠️', default => 'ℹ️'
        };
        ?>
        <?= $icon ?>   <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?= $content ?? '' ?>

  </main>

  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= asset('js/app.js') ?>"></script>
</body>

</html>