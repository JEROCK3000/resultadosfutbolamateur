<?php
declare(strict_types=1);

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/app/Models/LeagueModel.php';
require_once BASE_PATH . '/app/Models/MatchModel.php';
require_once BASE_PATH . '/app/Controllers/StandingsController.php';

/**
 * ExportController.php — Exportaciones a PDF y Excel
 * PDF: Generado con Dompdf
 * Excel: SpreadsheetML XML compatible con Excel y LibreOffice
 */
require_once BASE_PATH . '/vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController extends Controller
{
    private LeagueModel        $leagueModel;
    private MatchModel         $matchModel;
    private StandingsController $standingsCtrl;
    private $teamModel;
    private $refereeModel;
    private $matchResultModel;

    public function __construct()
    {
        $this->leagueModel   = new LeagueModel();
        $this->matchModel    = new MatchModel();
        $this->standingsCtrl = new StandingsController();
        
        require_once BASE_PATH . '/app/Models/TeamModel.php';
        $this->teamModel = new TeamModel();
        require_once BASE_PATH . '/app/Models/RefereeModel.php';
        $this->refereeModel = new RefereeModel();
        require_once BASE_PATH . '/app/Models/MatchResultModel.php';
        $this->matchResultModel = new MatchResultModel();
    }

    /** GET /exportar/posiciones/pdf/{league_id} */
    public function standingsPdf(string $leagueId): void
    {
        $this->requireAuth();
        $league    = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger','Campeonato no encontrado.'); $this->redirect('/posiciones'); return; }
        $standings = $this->standingsCtrl->getStandingsPublic((int) $leagueId);

        ob_start();
        $isPdfExport = true;
        require BASE_PATH . '/app/Views/export/standings_pdf.php';
        $html = ob_get_clean();

        $this->generatePdf($html, 'posiciones_' . preg_replace('/\s+/', '_', strtolower($league['name'])));
    }

    /** GET /exportar/posiciones/excel/{league_id} */
    public function standingsExcel(string $leagueId): void
    {
        $this->requireAuth();
        $league    = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger','Campeonato no encontrado.'); $this->redirect('/posiciones'); return; }
        $standings = $this->standingsCtrl->getStandingsPublic((int) $leagueId);

        $filename = 'posiciones_' . preg_replace('/\s+/', '_', strtolower($league['name'])) . '.xlsx';
        $this->sendExcelHeaders($filename);
        echo $this->buildStandingsXml($league, $standings);
        exit;
    }

    /** GET /exportar/encuentros/pdf/{league_id}?estadio=N */
    public function matchesPdf(string $leagueId): void
    {
        $this->requireAuth();
        $league  = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger','Campeonato no encontrado.'); $this->redirect('/encuentros'); return; }

        $stadiumFilter = filter_input(INPUT_GET, 'estadio', FILTER_VALIDATE_INT) ?: 0;
        $all     = $this->matchModel->getByLeague((int) $leagueId);
        $matches = array_values(array_filter($all, function ($m) use ($stadiumFilter) {
            if ($m['status'] === 'finished') return false;
            if ($stadiumFilter && (int)$m['stadium_id'] !== $stadiumFilter) return false;
            return true;
        }));

        ob_start();
        $isPdfExport = true;
        require BASE_PATH . '/app/Views/export/matches_pdf.php';
        $html = ob_get_clean();

        $this->generatePdf($html, 'encuentros_' . preg_replace('/\s+/', '_', strtolower($league['name'])));
    }

    /** GET /exportar/encuentros/excel/{league_id}?estadio=N */
    public function matchesExcel(string $leagueId): void
    {
        $this->requireAuth();
        $league  = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger','Campeonato no encontrado.'); $this->redirect('/encuentros'); return; }

        $stadiumFilter = filter_input(INPUT_GET, 'estadio', FILTER_VALIDATE_INT) ?: 0;
        $all     = $this->matchModel->getByLeague((int) $leagueId);
        $matches = array_values(array_filter($all, function ($m) use ($stadiumFilter) {
            if ($m['status'] === 'finished') return false;
            if ($stadiumFilter && (int)$m['stadium_id'] !== $stadiumFilter) return false;
            return true;
        }));

        $filename = 'encuentros_' . preg_replace('/\s+/', '_', strtolower($league['name'])) . '.xlsx';
        $this->sendExcelHeaders($filename);
        echo $this->buildMatchesXml($league, $matches);
        exit;
    }

    /** GET /exportar/resultados/pdf/{league_id} */
    public function resultsPdf(string $leagueId): void
    {
        $league = $this->leagueModel->getById((int) $leagueId);
        if (!$league) { $this->setFlash('danger','Campeonato no encontrado.'); $this->redirect('/principal'); return; }

        $allMatches = $this->matchModel->getByLeague((int) $leagueId);
        $results = [];
        foreach ($allMatches as $m) {
            if ($m['status'] === 'finished') {
                $res = $this->matchResultModel->getByMatch((int)$m['id']);
                if ($res) {
                    $m['home_goals']    = $res['home_goals'];
                    $m['away_goals']    = $res['away_goals'];
                    $m['result_status'] = $res['status'];
                    $results[] = $m;
                }
            }
        }

        ob_start();
        require BASE_PATH . '/app/Views/export/results_pdf.php';
        $html = ob_get_clean();

        $this->generatePdf($html, 'resultados_' . preg_replace('/\s+/', '_', strtolower($league['name'])));
    }

    /** GET /exportar/resultados/excel/{league_id} */
    public function resultsExcel(string $leagueId): void
    {
        $league = $this->leagueModel->getById((int) $leagueId);
        if (!$league) return;

        $allMatches = $this->matchModel->getByLeague((int) $leagueId);
        $results = [];
        foreach ($allMatches as $m) {
            if ($m['status'] === 'finished') {
                $res = $this->matchResultModel->getByMatch((int)$m['id']);
                if ($res) {
                    $m['home_goals'] = $res['home_goals'];
                    $m['away_goals'] = $res['away_goals'];
                    $m['result_status'] = $res['status'];
                    $results[] = $m;
                }
            }
        }

        $filename = 'resultados_' . preg_replace('/\s+/', '_', strtolower($league['name'])) . '.xlsx';
        $this->sendExcelHeaders($filename);
        echo $this->buildResultsXml($league, $results);
        exit;
    }

    /** GET /exportar/equipos/pdf/{league_id} */
    public function teamsPdf(string $leagueId): void
    {
        $this->requireAuth();
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) { $this->redirect('/equipos'); return; }

        $teams = $this->teamModel->getByLeague($id);
        ob_start();
        require BASE_PATH . '/app/Views/export/teams_pdf.php';
        $html = ob_get_clean();
        $this->generatePdf($html, 'listado_equipos_' . preg_replace('/\s+/', '_', strtolower($league['name'])));
    }

    /** GET /exportar/equipos/excel/{league_id} */
    public function teamsExcel(string $leagueId): void
    {
        $this->requireAuth();
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) { $this->redirect('/equipos'); return; }

        $teams = $this->teamModel->getByLeague($id);
        $filename = 'listado_equipos_' . preg_replace('/\s+/', '_', strtolower($league['name'])) . '.xlsx';
        $this->sendExcelHeaders($filename);
        echo $this->buildTeamsXml($teams);
        exit;
    }

    /** GET /exportar/arbitros/pdf */
    public function refereesPdf(): void
    {
        $this->requireAuth();
        $referees = $this->refereeModel->getAll();
        ob_start();
        require BASE_PATH . '/app/Views/export/referees_pdf.php';
        $html = ob_get_clean();
        $this->generatePdf($html, 'listado_arbitros');
    }

    /** GET /exportar/arbitros/excel */
    public function refereesExcel(): void
    {
        $this->requireAuth();
        $referees = $this->refereeModel->getAll();
        $this->sendExcelHeaders('listado_arbitros.xlsx');
        echo $this->buildRefereesXml($referees);
        exit;
    }

    /** GET /exportar/encuentros-admin/pdf/{league_id} */
    public function adminMatchesPdf(string $leagueId): void
    {
        $this->requireAuth();
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) { $this->redirect('/encuentros'); return; }

        $matches = $this->matchModel->getByLeague($id);
        ob_start();
        $isPdfExport = true;
        require BASE_PATH . '/app/Views/export/matches_pdf.php';
        $html = ob_get_clean();
        $this->generatePdf($html, 'encuentros_admin_' . preg_replace('/\s+/', '_', strtolower($league['name'])));
    }

    /** GET /exportar/encuentros-admin/excel/{league_id} */
    public function adminMatchesExcel(string $leagueId): void
    {
        $this->requireAuth();
        $id = (int)$leagueId;
        $league = $this->leagueModel->getById($id);
        if (!$league) { $this->redirect('/encuentros'); return; }

        $matches = $this->matchModel->getByLeague($id);
        $filename = 'encuentros_admin_' . preg_replace('/\s+/', '_', strtolower($league['name'])) . '.xlsx';
        $this->sendExcelHeaders($filename);
        echo $this->buildMatchesXml($league, $matches);
        exit;
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    private function generatePdf(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Limpiar cualquier buffer previo que pueda generar BOM o espacios y quebrar los headers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Sanitizar estrictamente el nombre para evitar cabeceras Content-Disposition malformadas
        $unwanted = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N'];
        $safeFilename = strtr($filename, $unwanted);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $safeFilename);
        if (empty($safeFilename)) {
            $safeFilename = 'export_'.time(); 
        }
        
        $pdfContent = $dompdf->output();

        header('Content-Type: application/pdf', true);
        header('Content-Disposition: inline; filename="' . $safeFilename . '.pdf"', true);
        header('Cache-Control: private, max-age=0, must-revalidate', true);
        header('Pragma: public', true);
        header('Content-Length: ' . strlen($pdfContent), true);

        echo $pdfContent;
        exit;
    }

    private function sendExcelHeaders(string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
    }

    private function buildStandingsXml(array $league, array $standings): string
    {
        $title = htmlspecialchars($league['name'] . ' — Tabla de Posiciones');
        $rows  = '';
        foreach ($standings as $i => $r) {
            $rows .= '<Row>'
                . $this->cell((string)($i+1))
                . $this->cell($r['name'])
                . $this->cell((string)$r['PJ'], 'Number')
                . $this->cell((string)$r['PG'], 'Number')
                . $this->cell((string)$r['PE'], 'Number')
                . $this->cell((string)$r['PP'], 'Number')
                . $this->cell((string)$r['GF'], 'Number')
                . $this->cell((string)$r['GC'], 'Number')
                . $this->cell((string)$r['DG'], 'Number')
                . $this->cell((string)$r['TA'], 'Number')
                . $this->cell((string)$r['TR'], 'Number')
                . $this->cell((string)$r['PTS'], 'Number')
                . '</Row>';
        }

        $headers = $this->headerRow(['#','Equipo','PJ','PG','PE','PP','GF','GC','DG','TA','TR','PTS']);

        return $this->wrapXml($title, $headers . $rows);
    }

    private function buildMatchesXml(array $league, array $matches): string
    {
        $title = htmlspecialchars($league['name'] . ' — Fixture Consolidado');
        $rows  = '';
        foreach ($matches as $m) {
            $date = $m['match_date'] ? date('d/m/Y', strtotime($m['match_date'])) : 'Pendiente';
            $time = $m['match_time'] ? substr($m['match_time'], 0, 5) : '—';
            $stadium = $m['stadium'] ?? 'Por definir';
            $round = $m['round_number'] ? "Fecha {$m['round_number']}" : '-';

            $rows .= '<Row>'
                . $this->cell($round)
                . $this->cell($date)
                . $this->cell($time)
                . $this->cell($m['home_team'])
                . $this->cell($m['away_team'])
                . $this->cell($stadium)
                . $this->cell(ucfirst($m['status'] === 'unscheduled' ? 'Por programar' : $m['status']))
                . '</Row>';
        }

        $headers = $this->headerRow(['Jornada','Fecha','Hora','Local','Visitante','Estadio','Estado']);
        return $this->wrapXml($title, $headers . $rows);
    }

    private function buildResultsXml(array $league, array $results): string
    {
        $title = htmlspecialchars($league['name'] . ' — Resultados');
        $rows  = '';
        foreach ($results as $r) {
            $rows .= '<Row>'
                . $this->cell(date('d/m/Y', strtotime($r['match_date'])))
                . $this->cell($r['home_team'])
                . $this->cell((string)$r['home_goals'], 'Number')
                . $this->cell((string)$r['away_goals'], 'Number')
                . $this->cell($r['away_team'])
                . $this->cell(ucfirst($r['result_status']))
                . '</Row>';
        }
        $headers = $this->headerRow(['Fecha','Local','Goles Local','Goles Visita','Visitante','Estado']);
        return $this->wrapXml($title, $headers . $rows);
    }

    private function buildTeamsXml(array $teams): string
    {
        $title = 'Listado_Equipos';
        $rows  = '';
        foreach ($teams as $t) {
            $rows .= '<Row>'
                . $this->cell($t['league_name'] ?? 'General')
                . $this->cell($t['name'])
                . $this->cell($t['short_name'] ?? '-')
                . $this->cell((string)($t['founded_year'] ?? '-'))
                . '</Row>';
        }
        $headers = $this->headerRow(['Liga','Equipo','Siglas','Fundación']);
        return $this->wrapXml($title, $headers . $rows);
    }

    private function buildRefereesXml(array $referees): string
    {
        $title = 'Listado_Arbitros';
        $rows  = '';
        foreach ($referees as $r) {
            $rows .= '<Row>'
                . $this->cell($r['name'])
                . $this->cell($r['role'] ?? '-')
                . $this->cell($r['experience_level'] ?? '-')
                . '</Row>';
        }
        $headers = $this->headerRow(['Nombre','Rol Principal', 'Nivel/Experiencia']);
        return $this->wrapXml($title, $headers . $rows);
    }

    private function cell(string $value, string $type = 'String'): string
    {
        $val = htmlspecialchars($value, ENT_XML1, 'UTF-8');
        return "<Cell><Data ss:Type=\"{$type}\">{$val}</Data></Cell>";
    }

    private function headerRow(array $cols): string
    {
        $cells = '';
        foreach ($cols as $c) {
            $cells .= "<Cell ss:StyleID=\"header\"><Data ss:Type=\"String\">" . htmlspecialchars($c) . "</Data></Cell>";
        }
        return "<Row>{$cells}</Row>";
    }

    private function wrapXml(string $title, string $rows): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<?mso-application progid="Excel.Sheet"?>'
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            . '<Styles>'
            . '<Style ss:ID="header"><Font ss:Bold="1"/><Interior ss:Color="#1e3a5f" ss:Pattern="Solid"/><Font ss:Color="#FFFFFF" ss:Bold="1"/></Style>'
            . '</Styles>'
            . '<Worksheet ss:Name="' . htmlspecialchars($title) . '">'
            . '<Table>' . $rows . '</Table>'
            . '</Worksheet></Workbook>';
    }
}
