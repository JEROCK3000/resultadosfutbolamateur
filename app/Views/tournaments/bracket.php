<?php /** app/Views/tournaments/bracket.php — Llave visual */ ?>
<nav class="breadcrumb">
  <a href="<?= url() ?>">Dashboard</a><span class="breadcrumb-sep">›</span>
  <a href="<?= url('torneos') ?>">Fases Finales</a><span class="breadcrumb-sep">›</span>
  <span><?= e($tournament['name']) ?></span>
</nav>

<div class="card-header" style="margin-bottom:20px">
  <div>
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.2rem,4vw,1.6rem);font-weight:700">
      🏆 <?= e($tournament['name']) ?>
    </h1>
    <p style="color:var(--color-text-muted);font-size:.85rem">
      <?= e($tournament['league_name']) ?>
      &mdash;
      <span class="badge <?= $tournament['type']==='seeded' ? 'badge-warning' : 'badge-info' ?>">
        <?= $tournament['type']==='seeded' ? '🔀 Cruces por posición' : '⚡ Llaves estándar' ?>
      </span>
    </p>
  </div>
  <form action="<?= url("torneos/eliminar/{$tournament['id']}") ?>" method="POST" style="display:inline">
    <button type="submit" class="btn btn-danger btn-sm"
            data-confirm="¿Eliminar este torneo y sus llaves?">🗑️ Eliminar</button>
  </form>
</div>

<?php if (empty($rounds)): ?>
  <div class="empty-state card">
    <div class="empty-icon">🏆</div>
    <p>No se encontraron rondas para este torneo</p>
  </div>
<?php else: ?>

<!-- Bracket de llaves -->
<div class="card">
  <div class="card-header"><h2 class="card-title">Llaves del Torneo</h2></div>
  <div class="bracket-wrapper">
    <div class="bracket">
      <?php foreach ($rounds as $round): ?>
      <div class="bracket-round">
        <div class="bracket-round-title"><?= e($round['round_name']) ?></div>

        <?php if (empty($round['matches'])): ?>
          <div style="text-align:center;padding:16px;color:var(--color-text-muted);font-size:.8rem">
            Pendiente
          </div>
        <?php else: foreach ($round['matches'] as $m): ?>
          <?php
            $homeGoals = $m['home_goals'] ?? null;
            $awayGoals = $m['away_goals'] ?? null;
            $homeWin   = $homeGoals !== null && $awayGoals !== null && $homeGoals > $awayGoals;
            $awayWin   = $homeGoals !== null && $awayGoals !== null && $awayGoals > $homeGoals;
          ?>
          <?php
            $canEdit = !empty($_SESSION['user_id']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_league'] == $tournament['league_id']);
            $hasTeams = !empty($m['home_team_id']) && !empty($m['away_team_id']);
          ?>
          <div class="bracket-match">
            <?php if ($canEdit && $hasTeams): ?>
            <form action="<?= url("torneos/marcador/{$m['id']}") ?>" method="POST" style="display:flex;flex-direction:column;gap:4px">
            <?php endif; ?>
            <div class="bracket-team <?= $homeWin ? 'winner' : '' ?>">
              <span style="display:flex;align-items:center">
                <?php if ($m['position_home'] ?? null): ?>
                  <span style="font-size:.7rem;color:var(--color-text-muted);margin-right:4px"><?= $m['position_home'] ?>°</span>
                <?php endif; ?>
                <?php if (!empty($m['home_logo'])): ?>
                  <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['home_logo']) ?>" width="16" height="16" style="object-fit:contain;vertical-align:middle;margin-right:4px;border-radius:50%">
                <?php endif; ?>
                <?= e($m['home_team'] ?? 'Por definir') ?>
              </span>
              <span class="score">
                <?php if ($canEdit && $hasTeams): ?>
                  <input type="number" name="home_goals" value="<?= $homeGoals !== null ? $homeGoals : '' ?>" style="width:36px;text-align:center;padding:2px;border:1px solid #ccc;border-radius:4px" min="0" required>
                <?php else: ?>
                  <?= $homeGoals !== null ? $homeGoals : '—' ?>
                <?php endif; ?>
              </span>
            </div>
            <div class="bracket-team <?= $awayWin ? 'winner' : '' ?>">
              <span style="display:flex;align-items:center">
                <?php if ($m['position_away'] ?? null): ?>
                  <span style="font-size:.7rem;color:var(--color-text-muted);margin-right:4px"><?= $m['position_away'] ?>°</span>
                <?php endif; ?>
                <?php if (!empty($m['away_logo'])): ?>
                  <img src="<?= BASE_URL ?>/assets/img/teams/<?= e($m['away_logo']) ?>" width="16" height="16" style="object-fit:contain;vertical-align:middle;margin-right:4px;border-radius:50%">
                <?php endif; ?>
                <?= e($m['away_team'] ?? 'Por definir') ?>
              </span>
              <span class="score">
                <?php if ($canEdit && $hasTeams): ?>
                  <input type="number" name="away_goals" value="<?= $awayGoals !== null ? $awayGoals : '' ?>" style="width:36px;text-align:center;padding:2px;border:1px solid #ccc;border-radius:4px" min="0" required>
                <?php else: ?>
                  <?= $awayGoals !== null ? $awayGoals : '—' ?>
                <?php endif; ?>
              </span>
            </div>
            <?php if ($canEdit && $hasTeams): ?>
              <button type="submit" class="btn btn-sm btn-primary" style="margin-top:2px;padding:2px 0;font-size:.7rem;border-radius:4px">💾 Guardar y Avanzar</button>
            </form>
            <?php endif; ?>
          </div>
        <?php endforeach; endif; ?>

      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php endif; ?>

<div class="form-actions mt-3">
  <a href="<?= url('torneos') ?>" class="btn btn-secondary">← Volver a Torneos</a>
  <a href="<?= url("posiciones/{$tournament['league_id']}") ?>" class="btn btn-outline">📊 Ver Posiciones</a>
</div>
