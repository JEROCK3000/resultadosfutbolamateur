<?php
declare(strict_types=1);

/**
 * public/index.php — Front Controller
 * Único punto de entrada del sistema. Carga configuración, rutas y despacha.
 */

// ─── Constantes del sistema ─────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . (str_contains($_SERVER['HTTP_HOST'], 'localhost') ? '/resultadosfutbol/public' : '')
);

// ─── Helpers y variables de entorno ─────────────────────────────────────────
require_once BASE_PATH . '/helpers/functions.php';
loadEnv(BASE_PATH . '/.env');

// ─── Sesión ──────────────────────────────────────────────────────────────────
session_start();

// ─── Core ────────────────────────────────────────────────────────────────────
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';

$router = new Router();

// ══════════════════════════════════════════════════════════
//  RUTAS PÚBLICAS (sin login)
// ══════════════════════════════════════════════════════════
$router->get('/login',              'AuthController@login');
$router->post('/login/authenticate','AuthController@authenticate');
$router->get('/logout',             'AuthController@logout');

// Sitio público para visitantes
$router->get('/principal',                      'PublicController@home');
$router->get('/principal/liga/{id}',            'PublicController@league');
$router->get('/principal/liga/{id}/encuentros', 'PublicController@matches');
$router->get('/principal/liga/{id}/resultados', 'PublicController@results');

// ══════════════════════════════════════════════════════════
//  PANEL ADMIN (requieren login — validado en cada controller)
// ══════════════════════════════════════════════════════════

// Dashboard
$router->get('/',          'HomeController@index');
$router->get('/dashboard', 'HomeController@index');

// ── Estadios ──
$router->get('/estadios',                   'StadiumController@index');
$router->get('/estadios/crear',             'StadiumController@create');
$router->post('/estadios/guardar',          'StadiumController@store');
$router->get('/estadios/editar/{id}',       'StadiumController@edit');
$router->post('/estadios/actualizar/{id}',  'StadiumController@update');
$router->post('/estadios/eliminar/{id}',    'StadiumController@destroy');

// ── Ligas ──
$router->get('/ligas',                      'LeagueController@index');
$router->get('/ligas/crear',                'LeagueController@create');
$router->post('/ligas/guardar',             'LeagueController@store');
$router->get('/ligas/editar/{id}',          'LeagueController@edit');
$router->post('/ligas/actualizar/{id}',     'LeagueController@update');
$router->post('/ligas/eliminar/{id}',       'LeagueController@destroy');

// ── Equipos ──
$router->get('/equipos',                    'TeamController@index');
$router->get('/equipos/liga/{id}',          'TeamController@show');
$router->get('/equipos/crear',              'TeamController@create');
$router->post('/equipos/guardar',           'TeamController@store');
$router->get('/equipos/importar/{league_id}', 'TeamController@importShow');
$router->post('/equipos/importar/{league_id}', 'TeamController@importStore');
$router->get('/equipos/editar/{id}',        'TeamController@edit');
$router->post('/equipos/actualizar/{id}',   'TeamController@update');
$router->post('/equipos/eliminar/{id}',     'TeamController@destroy');
$router->get('/equipos/por-liga/{id}',      'TeamController@porLiga');

// ── Calendario y Fixtures ──
$router->get('/calendario/generar/{id}',     'ScheduleController@create');
$router->post('/calendario/generar/{id}',    'ScheduleController@store');

// ── Sorteo Semanal Logístico ──
$router->get('/programacion',                'WeeklyScheduleController@index');
$router->get('/programacion/{league_id}',    'WeeklyScheduleController@show');
$router->post('/programacion/{league_id}',   'WeeklyScheduleController@store');

// ── Encuentros ──
$router->get('/encuentros',                  'MatchController@index');
$router->get('/encuentros/liga/{id}',        'MatchController@show');
$router->get('/encuentros/crear',            'MatchController@create');
$router->post('/encuentros/guardar',         'MatchController@store');
$router->get('/encuentros/editar/{id}',      'MatchController@edit');
$router->post('/encuentros/actualizar/{id}', 'MatchController@update');
$router->post('/encuentros/eliminar/{id}',   'MatchController@destroy');
$router->get('/encuentros/ver/{id}',         'MatchController@showMatch');

// ── Resultados ──
$router->post('/resultados/guardar',               'ResultController@store');
$router->post('/resultados/evento/guardar',        'ResultController@storeEvent');
$router->post('/resultados/evento/eliminar/{id}',  'ResultController@destroyEvent');

// ── Tabla de Posiciones ──
$router->get('/posiciones',             'StandingsController@index');
$router->get('/posiciones/{league_id}', 'StandingsController@show');

// ── Sorteos Rápidos Generales ──
$router->get('/sorteos',                'DrawController@index');
$router->get('/sorteos/{league_id}',    'DrawController@show');

// ── Fases Finales ──
$router->get('/torneos',                   'TournamentController@index');
$router->get('/torneos/crear/{league_id}', 'TournamentController@create');
$router->post('/torneos/generar',          'TournamentController@generate');
$router->get('/torneos/{id}/llave',        'TournamentController@bracket');
$router->post('/torneos/marcador/{id}',    'TournamentController@saveScore');
$router->post('/torneos/eliminar/{id}',    'TournamentController@destroy');

// ── Árbitros ──
$router->get('/arbitros',                  'RefereeController@index');
$router->get('/arbitros/crear',            'RefereeController@create');
$router->post('/arbitros/guardar',         'RefereeController@store');
$router->get('/arbitros/editar/{id}',      'RefereeController@edit');
$router->post('/arbitros/actualizar/{id}', 'RefereeController@update');
$router->post('/arbitros/eliminar/{id}',   'RefereeController@destroy');

// ── Usuarios (solo admin) ──
$router->get('/usuarios',                  'UserController@index');
$router->get('/usuarios/crear',            'UserController@create');
$router->post('/usuarios/guardar',         'UserController@store');
$router->get('/usuarios/editar/{id}',      'UserController@edit');
$router->post('/usuarios/actualizar/{id}', 'UserController@update');
$router->post('/usuarios/eliminar/{id}',   'UserController@destroy');

// ── Auditoría (solo admin) ──
$router->get('/auditoria', 'AuditController@index');

// ── Exportaciones ──
$router->get('/exportar/posiciones/pdf/{league_id}',    'ExportController@standingsPdf');
$router->get('/exportar/posiciones/excel/{league_id}',  'ExportController@standingsExcel');
$router->get('/exportar/encuentros/pdf/{league_id}',    'ExportController@matchesPdf');
$router->get('/exportar/encuentros/excel/{league_id}',  'ExportController@matchesExcel');
$router->get('/exportar/resultados/pdf/{id}',    'ExportController@resultsPdf');
$router->get('/exportar/resultados/excel/{id}',  'ExportController@resultsExcel');
$router->get('/exportar/equipos/pdf/{id}',       'ExportController@teamsPdf');
$router->get('/exportar/equipos/excel/{id}',     'ExportController@teamsExcel');
$router->get('/exportar/arbitros/pdf',           'ExportController@refereesPdf');
$router->get('/exportar/arbitros/excel',                'ExportController@refereesExcel');
$router->get('/exportar/encuentros-admin/pdf/{id}',     'ExportController@adminMatchesPdf');
$router->get('/exportar/encuentros-admin/excel/{id}',   'ExportController@adminMatchesExcel');

// ─── Despachar petición ──────────────────────────────────────────────────────
$router->dispatch();
