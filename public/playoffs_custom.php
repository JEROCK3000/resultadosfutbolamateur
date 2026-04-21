<?php
/**
 * playoffs_custom.php — Llaves Personalizadas Temporales
 * Solicitud especial: Ida y Vuelta, Final Única, Cruces exactos de imagen.
 */
declare(strict_types=1);
session_start();

$isAdmin = isset($_SESSION['user_id']);

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . (str_contains($_SERVER['HTTP_HOST'], 'localhost') ? '/resultadosfutbol/public' : '')
);
require_once dirname(__DIR__) . '/helpers/functions.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/app/Controllers/StandingsController.php';

$league_id = filter_input(INPUT_GET, 'league_id', FILTER_VALIDATE_INT) ?: 2; // Default 2 (Copa Valle)

// Equipos literales de la imagen proporcionada por el usuario (1 al 16)
$teamsByPos = [
    1 => 'DEPORTIVO COSANGA',
    2 => 'ANDES ORIENTAL',
    3 => 'T 12 F.C',
    4 => 'BORJA S.C',
    5 => 'BAEZA CENTRAL',
    6 => 'ORELLANA',
    7 => 'VASCO',
    8 => 'INDEPENDIENTE DE SUMACO',
    9 => 'REAL MADRID',
    10 => 'LIVERPOOL',
    11 => 'JUVENTUS',
    12 => 'LEONARDO MURIALDO',
    13 => 'AMERICA',
    14 => 'LAS VEGAS',
    15 => 'ANDES JUNIOR',
    16 => 'ATLETICO DEL VALLE'
];

// Configuración de los cruces octavos de final según la imagen enviada
// Las llaves se definieron visualmente así:
$octavos_layout = [
    'O1' => [1, 16],
    'O2' => [5, 10],
    'O3' => [3, 14],
    'O4' => [7, 12],
    'O5' => [2, 15],
    'O6' => [6, 9],
    'O7' => [4, 13],
    'O8' => [8, 11]
];

// Nodos del Bracket
$nodes = [
    'O1',
    'O2',
    'O3',
    'O4',
    'O5',
    'O6',
    'O7',
    'O8', // Octavos
    'Q1',
    'Q2',
    'Q3',
    'Q4',                         // Cuartos
    'S1',
    'S2',                                     // Semis
    'F1'                                            // Final
];

$file_path = BASE_PATH . "/storage/custom_bracket_{$league_id}.json";
$counter_path = BASE_PATH . "/storage/playoffs_visits_{$league_id}.txt";

// Lógica del contador de visitas (Solo incrementa al cargar normal por GET)
$visits = 0;
if (file_exists($counter_path)) {
    $visits = (int)file_get_contents($counter_path);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $visits++;
    @file_put_contents($counter_path, (string)$visits);
}

// Guardar resultados si se hace POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) die("Acceso denegado.");
    $data = [];
    foreach ($nodes as $n) {
        $data[$n] = [
            'ida_h' => $_POST["{$n}_ida_h"] ?? '',
            'ida_a' => $_POST["{$n}_ida_a"] ?? '',
            'vue_h' => $_POST["{$n}_vue_h"] ?? '',
            'vue_a' => $_POST["{$n}_vue_a"] ?? '',
            'override' => $_POST["{$n}_override"] ?? ''
        ];
    }
    @file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
    header("Location: playoffs_custom.php?league_id={$league_id}&saved=1");
    exit;
}

// Cargar estado
$state = [];
if (file_exists($file_path)) {
    $state = json_decode(file_get_contents($file_path), true) ?: [];
}

/** Calcula el ganador basado en goles o desempate manual */
function calcWinner($nodeId, $homeName, $awayName)
{
    global $state;
    if (empty($homeName) || empty($awayName))
        return "";

    $override = $state[$nodeId]['override'] ?? '';
    if ($override === 'H')
        return $homeName . " (P)";
    if ($override === 'A')
        return $awayName . " (P)";

    // Requiere que ida y vuelta tengan datos para calcular automático
    $idaH = $state[$nodeId]['ida_h'] ?? '';
    $idaA = $state[$nodeId]['ida_a'] ?? '';
    $vueH = $state[$nodeId]['vue_h'] ?? '';
    $vueA = $state[$nodeId]['vue_a'] ?? '';

    if ($idaH === '' || $idaA === '' || $vueH === '' || $vueA === '') {
        return ""; // Falta registrar algún partido (Ida o Vuelta)
    }

    $ih = (int) $idaH;
    $ia = (int) $idaA;
    $vh = (int) $vueH;
    $va = (int) $vueA;

    $th = $ih + $vh;
    $ta = $ia + $va;

    if ($th > $ta)
        return $homeName;
    if ($ta > $th)
        return $awayName;

    return "Empate (Desempatar)";
}

// NOMBRES CALCULADOS AUTOMÁTICAMENTE
$names = [];

// Octavos de final
foreach ($octavos_layout as $node => $posarr) {
    $names[$node]['h'] = $posarr[0] . '° ' . ($teamsByPos[$posarr[0]] ?? 'Pendiente');
    $names[$node]['a'] = $posarr[1] . '° ' . ($teamsByPos[$posarr[1]] ?? 'Pendiente');
}

// Cuartos de final
$names['Q1']['h'] = calcWinner('O1', $names['O1']['h'], $names['O1']['a']);
$names['Q1']['a'] = calcWinner('O2', $names['O2']['h'], $names['O2']['a']);
$names['Q2']['h'] = calcWinner('O3', $names['O3']['h'], $names['O3']['a']);
$names['Q2']['a'] = calcWinner('O4', $names['O4']['h'], $names['O4']['a']);
$names['Q3']['h'] = calcWinner('O5', $names['O5']['h'], $names['O5']['a']);
$names['Q3']['a'] = calcWinner('O6', $names['O6']['h'], $names['O6']['a']);
$names['Q4']['h'] = calcWinner('O7', $names['O7']['h'], $names['O7']['a']);
$names['Q4']['a'] = calcWinner('O8', $names['O8']['h'], $names['O8']['a']);

// Semifinales
$names['S1']['h'] = calcWinner('Q1', $names['Q1']['h'], $names['Q1']['a']);
$names['S1']['a'] = calcWinner('Q2', $names['Q2']['h'], $names['Q2']['a']);
$names['S2']['h'] = calcWinner('Q3', $names['Q3']['h'], $names['Q3']['a']);
$names['S2']['a'] = calcWinner('Q4', $names['Q4']['h'], $names['Q4']['a']);

// Final
$names['F1']['h'] = calcWinner('S1', $names['S1']['h'], $names['S1']['a']);
$names['F1']['a'] = calcWinner('S2', $names['S2']['h'], $names['S2']['a']);

function getVal($node, $field)
{
    global $state;
    return $state[$node][$field] ?? '';
}

function renderMatchBox($nodeId, $title, $isFinal = false, $customClass = '')
{
    global $names, $isAdmin;
    
    $disAttr = $isAdmin ? '' : 'disabled';
    $rdAttr = $isAdmin ? '' : 'readonly';

    $idaH = getVal($nodeId, 'ida_h');
    $idaA = getVal($nodeId, 'ida_a');
    $vueH = getVal($nodeId, 'vue_h');
    $vueA = getVal($nodeId, 'vue_a');

    $th = $names[$nodeId]['h'] ?: 'Esperando rival...';
    $ta = $names[$nodeId]['a'] ?: 'Esperando rival...';

    $override = getVal($nodeId, 'override');
    $selH = ($override === 'H') ? 'selected' : '';
    $selA = ($override === 'A') ? 'selected' : '';
    $auto = ($override === '') ? 'selected' : '';

    $html = "<div class='match-box {$customClass}'>";
    $html .= "<div class='match-title' style='display:flex; justify-content:space-between; align-items:center;'>";
    $html .= "<span>$title</span>";
    $html .= "<select name='{$nodeId}_override' style='font-size:9px; border:1px solid var(--border); border-radius:3px; background:var(--box-bg); color:var(--text);' title='Desempate manual' {$disAttr}>
                <option value='' $auto>Auto</option>
                <option value='H' $selH>+P L</option>
                <option value='A' $selA>+P V</option>
              </select>";
    $html .= "</div>";

    // Cálculo del marcador global
    $globalH = '';
    $globalA = '';
    if ($idaH !== '' && $idaH !== null) {
        $globalH = (int)$idaH + ($isFinal ? 0 : (int)($vueH ?? 0));
    }
    if ($idaA !== '' && $idaA !== null) {
        $globalA = (int)$idaA + ($isFinal ? 0 : (int)($vueA ?? 0));
    }

    // Encabezados de columnas
    $marginStyle = "margin-top:-2px; margin-bottom:2px;";
    if ($isFinal) {
        // En la final añadimos la misma caja pero invisible para no perder la altura y que las líneas cuadren
        $html .= "<div style='display:flex; justify-content:flex-end; gap:4px; padding-right:4px; {$marginStyle} visibility:hidden;'>";
        $html .= "<span style='width:24px; font-size:7px;'>IDA</span>";
        $html .= "</div>";
    } else {
        $html .= "<div style='display:flex; justify-content:flex-end; gap:4px; padding-right:4px; {$marginStyle}'>";
        $html .= "<span style='width:24px; text-align:center; font-size:7px; font-weight:bold; color:var(--title);'>IDA</span>";
        $html .= "<span style='width:24px; text-align:center; font-size:7px; font-weight:bold; color:var(--title);'>VTA</span>";
        $html .= "<span style='width:26px; text-align:center; font-size:7px; font-weight:bold; color:var(--btn-bg);'>GLB</span>";
        $html .= "</div>";
    }

    // HOME TEAM
    $html .= "<div class='team-row'>";
    $html .= "<div class='team-name' title='" . htmlspecialchars($th) . "'>" . htmlspecialchars($th) . "</div>";
    if ($isFinal) {
        $html .= "<input type='text' name='{$nodeId}_ida_h' value='{$idaH}' class='score-input hue' title='Final' {$rdAttr}>";
    } else {
        $html .= "<input type='text' name='{$nodeId}_ida_h' value='{$idaH}' class='score-input' title='Ida' {$rdAttr}>";
        $html .= "<input type='text' name='{$nodeId}_vue_h' value='{$vueH}' class='score-input hue' title='Vuelta' {$rdAttr}>";
        $html .= "<div class='score-global' title='Marcador Global'>{$globalH}</div>";
    }
    $html .= "</div>";

    // AWAY TEAM
    $html .= "<div class='team-row'>";
    $html .= "<div class='team-name' title='" . htmlspecialchars($ta) . "'>" . htmlspecialchars($ta) . "</div>";
    if ($isFinal) {
        $html .= "<input type='text' name='{$nodeId}_ida_a' value='{$idaA}' class='score-input hue' title='Final' {$rdAttr}>";
    } else {
        $html .= "<input type='text' name='{$nodeId}_ida_a' value='{$idaA}' class='score-input' title='Ida' {$rdAttr}>";
        $html .= "<input type='text' name='{$nodeId}_vue_a' value='{$vueA}' class='score-input hue' title='Vuelta' {$rdAttr}>";
        $html .= "<div class='score-global' title='Marcador Global'>{$globalA}</div>";
    }
    $html .= "</div>";

    $html .= "</div>";
    return $html;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Generador Temporal de Llaves</title>
    <style>
        :root {
            --bg: #eef2f5;
            --box-bg: #fff;
            --text: #333;
            --border: #cbd5e1;
            --title: #64748b;
            --score-bg: #fff;
            --score-hue-bg: #eff6ff;
            --score-hue-border: #93c5fd;
            --btn-bg: #1e40af;
        }

        [data-theme="dark"] {
            --bg: #0d1117;
            --box-bg: #161b22;
            --text: #e6edf3;
            --border: #30363d;
            --title: #8b949e;
            --score-bg: #161b22;
            --score-hue-bg: rgba(96, 165, 250, 0.1);
            --score-hue-border: #3b82f6;
            --btn-bg: #3b82f6;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 20px;
            color: var(--text);
            transition: background 0.3s, color 0.3s;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .theme-toggle {
            position: absolute;
            top: 0;
            right: 20px;
            background: var(--box-bg);
            border: 2px solid var(--border);
            color: var(--text);
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn {
            padding: 10px 24px;
            background: rgba(255, 255, 255, 0.05);
            /* Glass base */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .bracket-container {
            display: flex;
            justify-content: center;
            overflow-x: auto;
            padding: 40px 0 60px;
            /* espacio para la firma */
            gap: 30px;
            position: relative;
        }

        .col {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 220px;
            position: relative;
        }

        /* Conexiones CSS */
        .match-pair {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            flex: 1;
            position: relative;
            padding: 10px 0;
        }

        .line-out-left::after {
            content: "";
            position: absolute;
            right: -15px;
            top: 25%;
            bottom: 25%;
            border: 2px solid var(--border);
            border-left: none;
            width: 15px;
            border-radius: 0 4px 4px 0;
            z-index: 0;
        }

        .line-out-right::before {
            content: "";
            position: absolute;
            left: -15px;
            top: 25%;
            bottom: 25%;
            border: 2px solid var(--border);
            border-right: none;
            width: 15px;
            border-radius: 4px 0 0 4px;
            z-index: 0;
        }

        .line-in-left::before {
            content: "";
            position: absolute;
            left: -17px;
            top: 50%;
            width: 15px;
            border-top: 2px solid var(--border);
            z-index: 0;
        }

        .line-in-right::after {
            content: "";
            position: absolute;
            right: -17px;
            top: 50%;
            width: 15px;
            border-top: 2px solid var(--border);
            z-index: 0;
        }

        .line-out-straight-left::after {
            content: "";
            position: absolute;
            right: -17px;
            top: 50%;
            width: 15px;
            border-top: 2px solid var(--border);
            z-index: 0;
        }

        .line-out-straight-right::before {
            content: "";
            position: absolute;
            left: -17px;
            top: 50%;
            width: 15px;
            border-top: 2px solid var(--border);
            z-index: 0;
        }

        .match-box {
            background: var(--box-bg);
            border: 2px solid var(--border);
            border-radius: 6px;
            padding: 5px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            margin: 10px 0;
        }

        .match-title {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--title);
            margin-bottom: 5px;
            font-weight: bold;
        }

        .team-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px;
            border-bottom: 1px solid var(--border);
            gap: 4px;
        }

        .team-row:last-child {
            border-bottom: none;
        }

        .team-name {
            font-size: 10px;
            font-weight: 700;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.3px;
        }

        .score-input {
            width: 22px;
            height: 18px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid var(--border);
            border-radius: 3px;
            background: var(--score-bg);
            color: var(--text);
        }

        .score-input.hue {
            border-color: var(--score-hue-border);
            background: var(--score-hue-bg);
        }

        .score-global {
            width: 26px;
            height: 18px;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            background: var(--btn-bg);
            color: #ffffff;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            font-weight: bold;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease-out, transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <button class="theme-toggle" type="button" onclick="toggleTheme()">🌙 Oscuro</button>
        <h1 style="margin:0 0 10px 0; color:var(--text);">🏆 LLAVES FASE FINAL CAMPEONATO 2026 🏆</h1>
        <h3 style="margin:0 0 10px 0; color:var(--text);">LIGA DEPORTIVA PARROQUIAL SAN FRANCISCO DE BORJA</h3>
        <p style="margin:0 0 20px 0; color:var(--title);">Sistema de ida y vuelta.</p>
        <div style="display:flex; justify-content:center; flex-wrap:wrap; gap:15px; margin-top:20px;">
            <a href="<?= BASE_URL ?>" class="btn">Volver</a>
            <button type="button" onclick="exportPNG()" class="btn">Guardar PNG</button>
            <button type="button" onclick="exportPDF()" class="btn">Guardar PDF</button>
            <?php if($isAdmin): ?>
                <button type="submit" form="bracketForm" class="btn" style="background:var(--btn-bg);color:#fff;border-color:var(--btn-bg);">Actualizar Resultados</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div id="flash-toast" class="toast-notification">✅ ¡Resultados guardados con éxito!</div>
    <?php endif; ?>

    <form id="bracketForm" method="POST">

        <div class="bracket-container">
            <!-- IZQUIERDA: OCTAVOS -->
            <div class="col">
                <div class="match-pair line-out-left">
                    <?= renderMatchBox('O1', "Octavos - O1") ?>
                    <?= renderMatchBox('O2', "Octavos - O2") ?>
                </div>
                <div class="match-pair line-out-left">
                    <?= renderMatchBox('O3', "Octavos - O3") ?>
                    <?= renderMatchBox('O4', "Octavos - O4") ?>
                </div>
            </div>

            <!-- IZQUIERDA: CUARTOS -->
            <div class="col">
                <div class="match-pair line-out-left">
                    <?= renderMatchBox('Q1', "Cuartos - Q1", false, 'line-in-left') ?>
                    <?= renderMatchBox('Q2', "Cuartos - Q2", false, 'line-in-left') ?>
                </div>
            </div>

            <!-- IZQUIERDA: SEMIS -->
            <div class="col">
                <div class="match-pair">
                    <?= renderMatchBox('S1', "Semifinal - S1", false, 'line-in-left line-out-straight-left') ?>
                </div>
            </div>

            <!-- CENTRO: FINAL -->
            <div class="col" style="justify-content:center;">
                <div style="text-align:center; height:100px; margin-bottom:20px; display:flex; align-items:flex-end; justify-content:center; animation: bounce 3s infinite;">
                    <img src="<?= BASE_URL ?>/assets/img/trophy.png" alt="Trofeo" style="max-height: 100px; filter: drop-shadow(0 10px 15px rgba(212, 175, 55, 0.4));" />
                </div>
                <?= renderMatchBox('F1', "👑 GRAN FINAL", true, 'line-in-left line-in-right') ?>
                <div style="height:60px;"></div>
            </div>

            <!-- DERECHA: SEMIS -->
            <div class="col">
                <div class="match-pair">
                    <?= renderMatchBox('S2', "Semifinal - S2", false, 'line-in-right line-out-straight-right') ?>
                </div>
            </div>

            <!-- DERECHA: CUARTOS -->
            <div class="col">
                <div class="match-pair line-out-right">
                    <?= renderMatchBox('Q3', "Cuartos - Q3", false, 'line-in-right') ?>
                    <?= renderMatchBox('Q4', "Cuartos - Q4", false, 'line-in-right') ?>
                </div>
            </div>

            <!-- DERECHA: OCTAVOS -->
            <div class="col">
                <div class="match-pair line-out-right">
                    <?= renderMatchBox('O5', "Octavos - O5") ?>
                    <?= renderMatchBox('O6', "Octavos - O6") ?>
                </div>
                <div class="match-pair line-out-right">
                    <?= renderMatchBox('O7', "Octavos - O7") ?>
                    <?= renderMatchBox('O8', "Octavos - O8") ?>
                </div>
            </div>

            <div
                style="position: absolute; bottom: 185px; left: 50%; transform: translateX(-50%); font-size: 13px; font-weight: bold; color: var(--text); opacity: 0.15; font-family: monospace; letter-spacing: 1px; pointer-events: none;">
                Powered by SOLINTEEC DEVS & TECH
            </div>
        </div>

    </form>

    <!-- Contador de visitas (ignorado al exportar con html2canvas) -->
    <div data-html2canvas-ignore="true" style="position: fixed; bottom: 15px; right: 15px; font-family: monospace; font-size: 11px; font-weight: bold; color: var(--text); opacity: 0.2; pointer-events: none; z-index: 100;">
        👁 Visitas: <?= number_format($visits, 0, ',', '.') ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        // Ocultar toast automáticamente
        const toast = document.getElementById('flash-toast');
        if (toast) {
            // Empieza a ocultarse tras 2.5s
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -20px)';
                // Se remueve completamente al terminar la transición
                setTimeout(() => toast.remove(), 500);
            }, 2500);
        }

        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');

        function toggleTheme() {
            const bg = document.documentElement.getAttribute('data-theme');
            if (bg === 'dark') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Lógica de exportación
        async function getCanvas() {
            const el = document.querySelector('.bracket-container');
            el.style.position = 'relative';

            // 1. Agregar Título Oficial Dinámico
            const titleLayer = document.createElement('div');
            titleLayer.style.position = 'absolute';
            titleLayer.style.top = '15px';
            titleLayer.style.left = '50%';
            titleLayer.style.transform = 'translateX(-50%)';
            titleLayer.style.textAlign = 'center';
            titleLayer.style.width = '100%';
            titleLayer.style.zIndex = '5';
            titleLayer.innerHTML = `
                <h2 style="margin:0; font-family:'Arial', sans-serif; color:var(--text); font-size:24px; text-transform:uppercase; letter-spacing:2px; font-weight:800; text-shadow:0 2px 10px rgba(0,0,0,0.1);">FASE FINAL CAMPEONATO 2026</h2>
                <h3 style="margin:8px 0 0; font-family:'Arial', sans-serif; color:var(--title); font-size:15px; letter-spacing:1px; font-weight:600;">LIGA DEPORTIVA PARROQUIAL SAN FRANCISCO DE BORJA</h3>
            `;
            el.appendChild(titleLayer);

            // Ocultar temporalmente los selects de desempate
            const selects = el.querySelectorAll('select');
            const originalDisplays = [];
            selects.forEach(s => {
                originalDisplays.push(s.style.display);
                if (s.value === '') s.style.display = 'none';
            });

            // Expandir los bordes temporales para que quepa todo el texto hermoso
            const origPadding = el.style.padding;
            const origOverflow = el.style.overflow;
            const origMinWidth = el.style.minWidth;

            el.style.padding = '100px 30px 60px 30px';
            el.style.overflow = 'visible';
            el.style.minWidth = 'max-content';

            const bgColors = window.getComputedStyle(document.body).backgroundColor;

            // Escala 4x = 400% de calidad!
            const canvas = await html2canvas(el, {
                backgroundColor: bgColors,
                scale: 4,
                windowWidth: el.scrollWidth,
                width: el.scrollWidth
            });

            // Limpieza total
            titleLayer.remove();
            el.style.padding = origPadding;
            el.style.overflow = origOverflow;
            el.style.minWidth = origMinWidth;
            selects.forEach((s, idx) => {
                s.style.display = originalDisplays[idx];
            });

            return canvas;
        }

        async function exportPNG() {
            const canvas = await getCanvas();
            const link = document.createElement('a');
            link.download = 'fase-final-resultados.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        async function exportPDF() {
            const canvas = await getCanvas();
            const imgData = canvas.toDataURL('image/png');

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: [canvas.width, canvas.height]
            });

            pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
            pdf.save('fase-final-resultados.pdf');
        }
    </script>

</body>

</html>