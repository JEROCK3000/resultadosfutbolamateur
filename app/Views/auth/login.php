<?php /** app/Views/auth/login.php */ ?>
<div class="auth-logo">
  <h1>⚽ <span>SOLINTEEC</span> ⚽</h1>
  <h1>⚽ <span>Fútbol</span> ⚽</h1>
  <p>Sistema Multiligas de la Amazonía</p>
</div>

<form action="<?= url('login/authenticate') ?>" method="POST" novalidate>
  <div class="form-group">
    <label class="form-label" for="email">Correo electrónico</label>
    <input type="email" id="email" name="email" class="form-control" required placeholder="usuario@ejemplo.ec"
      autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label" for="password">Contraseña</label>
    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••"
      autocomplete="current-password">
  </div>
  <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;padding:12px;font-size:1rem">
    🔐 Iniciar Sesión
  </button>
</form>

<p style="text-align:center;margin-top:20px;font-size:.78rem;color:var(--color-text-muted)">
  Sistema de gestión privado — Campeonatos de Fútbol - Ecuador
</p>
<p style="text-align:center;margin-top:8px;font-size:.78rem">
  <a href="<?= url('principal') ?>" style="color:var(--color-primary-light)">→ Ver resultados públicos</a>
</p>