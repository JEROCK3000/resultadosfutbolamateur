<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Equipos</title>
  <style>
    @page { margin: 15mm; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
    .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #1e40af; padding-bottom: 12px; }
    .header h1 { margin: 0; font-size: 18px; color: #1e40af; }
    .header p { margin: 4px 0 0; color: #555; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e40af; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
    td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
    tr:nth-child(even) td { background: #f8fafc; }
    .footer-note { font-size: 10px; color: #888; text-align: right; margin-top: 12px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Listado de Equipos Registrados</h1>
    <p>Generado: <?= date('d/m/Y H:i') ?></p>
  </div>
  <table>
    <thead>
      <tr>
        <th>Campeonato Asignado</th>
        <th>Nombre del Equipo</th>
        <th>Siglas</th>
        <th>Fundación</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($teams)): ?>
      <tr>
        <td colspan="4" style="text-align:center;color:#888;padding:20px">Sin equipos registrados</td>
      </tr>
      <?php else: foreach ($teams as $t): ?>
      <tr>
        <td style="font-size:11px;color:#555"><?= htmlspecialchars($t['league_name'] ?? 'General') ?></td>
        <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
        <td><?= htmlspecialchars($t['short_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars((string)($t['founded_year'] ?? '-')) ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  <p class="footer-note">Sistema Multiligas de Fútbol</p>
</body>
</html>
