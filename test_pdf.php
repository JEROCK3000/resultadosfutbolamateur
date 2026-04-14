<?php
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/resultadosfutbol/public');

require_once BASE_PATH . '/helpers/functions.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/vendor/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $lModel = new LeagueModel();
    $mModel = new MatchModel();

    $league = $lModel->getById(1);
    if (!$league) die("League not found.");
    $matches = $mModel->getByLeague(1);

    ob_start();
    require BASE_PATH . '/app/Views/export/matches_pdf.php';
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    echo "SUCCESS: " . strlen($dompdf->output()) . " bytes\n";
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
