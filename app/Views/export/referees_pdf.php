<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Árbitros</title>
  <style>
    @page { margin: 15mm; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
    .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #b91c1c; padding-bottom: 12px; }
    .header h1 { margin: 0; font-size: 18px; color: #b91c1c; }
    .header p { margin: 4px 0 0; color: #555; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #b91c1c; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
    td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
    tr:nth-child(even) td { background: #fef2f2; }
    .footer-note { font-size: 10px; color: #888; text-align: right; margin-top: 12px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Cuerpo Arbitral Registrado</h1>
    <p>Generado: <?= date('d/m/Y H:i') ?></p>
  </div>
  <table>
    <thead>
      <tr>
        <th>Nombre Completo</th>
        <th>Rol Principal</th>
        <th>Nivel / Experiencia</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($referees)): ?>
      <tr>
        <td colspan="3" style="text-align:center;color:#888;padding:20px">Sin árbitros registrados</td>
      </tr>
      <?php else: foreach ($referees as $r): ?>
      <tr>
        <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
        <td><?= htmlspecialchars(ucfirst($r['role'] ?? '-')) ?></td>
        <td><?= htmlspecialchars($r['experience_level'] ?? '-') ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  <p class="footer-note">Sistema Multiligas de Fútbol</p>
</body>
</html>
