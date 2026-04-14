<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultados de la Jornada</title>
  <style>
    @page { margin: 15mm; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
    .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #059669; padding-bottom: 12px; }
    .header h1 { margin: 0; font-size: 18px; color: #059669; }
    .header p { margin: 4px 0 0; color: #555; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #059669; color: #fff; padding: 8px 10px; text-align: center; font-size: 11px; }
    td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: center; }
    tr:nth-child(even) td { background: #ecfdf5; }
    .vs { color: #888; font-size: 10px; text-transform: uppercase; }
    .score { font-size: 16px; font-weight: bold; background: #fff; border: 1px solid #d1d5db; border-radius: 4px; padding: 2px 8px; display: inline-block; min-width: 20px; }
    .footer-note { font-size: 10px; color: #888; text-align: right; margin-top: 12px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Resultados de Encuentros</h1>
    <p><?= htmlspecialchars($league['name'] ?? 'General') ?> · Generado: <?= date('d/m/Y H:i') ?></p>
  </div>
  <table>
    <thead>
      <tr>
        <th>Fecha / Hora</th>
        <th style="text-align:right">C. Local</th>
        <th>Marcador</th>
        <th style="text-align:left">C. Visitante</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($results)): ?>
      <tr>
        <td colspan="5" style="text-align:center;color:#888;padding:20px">No hay resultados registrados</td>
      </tr>
      <?php else: foreach ($results as $r): ?>
      <tr>
        <td style="font-size:10px">
          <strong><?= date('d/m/Y', strtotime($r['match_date'])) ?></strong><br>
          <?= substr($r['match_time'], 0, 5) ?>h
        </td>
        <td style="text-align:right"><strong><?= htmlspecialchars($r['home_team']) ?></strong></td>
        <td>
          <span class="score"><?= $r['home_goals'] ?? '-' ?></span>
          <span class="vs">vs</span>
          <span class="score"><?= $r['away_goals'] ?? '-' ?></span>
        </td>
        <td style="text-align:left"><strong><?= htmlspecialchars($r['away_team']) ?></strong></td>
        <td style="font-size:10px;color:<?= $r['result_status'] === 'official' ? '#059669' : '#d97706' ?>">
          <?= $r['result_status'] === 'official' ? 'Oficial' : 'Pendiente' ?>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  <p class="footer-note">Sistema Multiligas de Fútbol</p>
</body>
</html>
