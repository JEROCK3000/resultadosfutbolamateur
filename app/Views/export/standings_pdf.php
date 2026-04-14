<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Tabla de Posiciones —
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
      border-bottom: 3px solid #1e3a8a;
      padding-bottom: 12px;
    }

    .header h1 {
      margin: 0;
      font-size: 18px;
      color: #1e3a8a;
    }

    .header p {
      margin: 4px 0 0;
      color: #555;
      font-size: 11px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th {
      background: #1e3a8a;
      color: #fff;
      padding: 8px 10px;
      text-align: center;
      font-size: 11px;
    }

    td {
      padding: 7px 10px;
      text-align: center;
      border-bottom: 1px solid #e5e7eb;
    }

    td:nth-child(2) {
      text-align: left;
      font-weight: 600;
    }

    tr:nth-child(even) td {
      background: #f8fafc;
    }

    tr:first-of-type td {
      background: #eff6ff;
      font-weight: bold;
    }

    .footer-note {
      font-size: 10px;
      color: #888;
      text-align: right;
      margin-top: 8px;
    }

    .btn-print {
      display: inline-block;
      padding: 8px 20px;
      background: #1e3a8a;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      margin-bottom: 16px;
    }

    .btn-print:hover {
      background: #1e40af;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>
      <?= htmlspecialchars($league['name'])?>
    </h1>
    <p>Temporada
      <?= htmlspecialchars($league['season'])?> ·
      <?= htmlspecialchars($league['country'])?>
    </p>
    <p>Tabla de Posiciones · Generado:
      <?= date('d/m/Y H:i')?>
    </p>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th style="text-align:left">Equipo</th>
        <th>PJ</th>
        <th>PG</th>
        <th>PE</th>
        <th>PP</th>
        <th>GF</th>
        <th>GC</th>
        <th>DG</th>
        <th>TA</th>
        <th>TR</th>
        <th>PTS</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($standings)): ?>
      <tr>
        <td colspan="12" style="text-align:center;color:#888;padding:20px">Sin resultados registrados</td>
      </tr>
      <?php
else:
  foreach ($standings as $i => $r): ?>
      <tr>
        <td>
          <?= $i + 1?>
        </td>
        <td style="text-align:left">
          <?= htmlspecialchars($r['name'])?>
        </td>
        <td>
          <?= $r['PJ']?>
        </td>
        <td>
          <?= $r['PG']?>
        </td>
        <td>
          <?= $r['PE']?>
        </td>
        <td>
          <?= $r['PP']?>
        </td>
        <td>
          <?= $r['GF']?>
        </td>
        <td>
          <?= $r['GC']?>
        </td>
        <td>
          <?= $r['DG'] >= 0 ? '+' . $r['DG'] : $r['DG']?>
        </td>
        <td>
          <?= $r['TA']?>
        </td>
        <td>
          <?= $r['TR']?>
        </td>
        <td><strong>
            <?= $r['PTS']?>
          </strong></td>
      </tr>
      <?php
  endforeach;
endif; ?>
    </tbody>
  </table>

  <p class="footer-note">Campeonato de Fútbol · Sistema Multiligas · Borja, Quijos, Amazonía Ecuatoriana</p>
</body>

</html>