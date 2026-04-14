<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Próximos Encuentros —
    <?= htmlspecialchars($league['name'])?>
  </title>
  <style>
    @page {
      margin: 15mm;
    }

    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .no-print {
        display: none;
      }
    }

    body {
      font-family: 'Arial', sans-serif;
      font-size: 12px;
      color: #1a1a1a;
      margin: 0;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 24px;
      border-bottom: 3px solid #166534;
      padding-bottom: 12px;
    }

    .header h1 {
      margin: 0;
      font-size: 18px;
      color: #166534;
    }

    .header p {
      margin: 4px 0 0;
      color: #555;
      font-size: 11px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background: #166534;
      color: #fff;
      padding: 8px 10px;
      text-align: left;
      font-size: 11px;
    }

    td {
      padding: 8px 10px;
      border-bottom: 1px solid #e5e7eb;
    }

    tr:nth-child(even) td {
      background: #f0fdf4;
    }

    .vs {
      text-align: center;
      color: #888;
      font-size: 11px;
    }

    .footer-note {
      font-size: 10px;
      color: #888;
      text-align: right;
      margin-top: 12px;
    }

    .btn-print {
      display: inline-block;
      padding: 8px 20px;
      background: #166534;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Próximos Encuentros</h1>
    <p>
      <?= htmlspecialchars($league['name'])?> · Temporada
      <?= htmlspecialchars($league['season'])?>
    </p>
    <p>Generado:
      <?= date('d/m/Y H:i')?>
    </p>
  </div>
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Local</th>
        <th class="vs">vs</th>
        <th>Visitante</th>
        <th>Estadio</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($matches)): ?>
      <tr>
        <td colspan="6" style="text-align:center;color:#888;padding:20px">Sin encuentros registrados</td>
      </tr>
      <?php else:
        $groupedMatches = [];
        foreach ($matches as $m) {
            $r = (int)($m['round_number'] ?? 0);
            if (!isset($groupedMatches[$r])) $groupedMatches[$r] = [];
            $groupedMatches[$r][] = $m;
        }
        ksort($groupedMatches);
      ?>
      <?php foreach ($groupedMatches as $round => $roundMatches): ?>
      <tr>
        <td colspan="6" style="background:#e5e7eb; font-weight:bold; text-align:center; color:#333;">
          FECHA <?= $round > 0 ? $round : 'Sin Jornada' ?>
        </td>
      </tr>
      <?php foreach ($roundMatches as $m): ?>
      <tr>
        <?php if($m['status'] === 'unscheduled'): ?>
          <td colspan="2" style="text-align:center; color:#888; font-style:italic">Logística por definir</td>
        <?php else: ?>
          <td><strong>
              <?= date('d/m/Y', strtotime($m['match_date']))?>
            </strong><br>
            <span style="font-size:10px;color:#888">
              <?= date('l', strtotime($m['match_date']))?>
            </span>
          </td>
          <td><strong>
              <?= htmlspecialchars(substr($m['match_time'], 0, 5))?>h
            </strong></td>
        <?php endif; ?>
        <td>
          <?= htmlspecialchars($m['home_team'] ?? '')?>
        </td>
        <td class="vs">—</td>
        <td>
          <?= htmlspecialchars($m['away_team'] ?? '')?>
        </td>
        <td style="font-size:11px;color:#555">
          <?= htmlspecialchars($m['stadium'] ?? 'Por definir')?>
        </td>
      </tr>
      <?php endforeach; endforeach; endif; ?>
    </tbody>
  </table>
  <p class="footer-note">Campeonato de Fútbol · Sistema Multiligas · Borja, Quijos, Amazonía Ecuatoriana</p>
</body>
</html>