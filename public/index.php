<?php

declare(strict_types=1);

// ── Autoloader ────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/src/';
    $file = $base . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Environment ───────────────────────────────────────────────
$_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'db';
$_ENV['DB_PORT'] = getenv('DB_PORT') ?: '3306';
$_ENV['DB_NAME'] = getenv('DB_NAME') ?: 'alarm_system';
$_ENV['DB_USER'] = getenv('DB_USER') ?: 'alarm_user';
$_ENV['DB_PASS'] = getenv('DB_PASS') ?: 'alarm_pass';
$_ENV['MAIL_HOST'] = getenv('MAIL_HOST') ?: 'mailhog';
$_ENV['MAIL_PORT'] = getenv('MAIL_PORT') ?: '1025';

// ── Error handling ────────────────────────────────────────────
ini_set('display_errors', getenv('APP_ENV') === 'production' ? '0' : '1');
error_reporting(E_ALL);

// ── Security headers ──────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Bootstrap ─────────────────────────────────────────────────
use App\Core\{Router, Request};
use App\Controllers\{AlarmController, DashboardController, EquipmentController};

$router  = new Router();
$request = new Request();

// ── Routes ────────────────────────────────────────────────────

// Dashboard
$router->get('/',          fn($req, $res)           => (new DashboardController())->index($req, $res));
$router->get('/dashboard', fn($req, $res)           => (new DashboardController())->index($req, $res));

// Equipments
$router->get('/equipments',                   fn($req, $res)             => (new EquipmentController())->index($req, $res));
$router->get('/equipments/create',            fn($req, $res)             => (new EquipmentController())->create($req, $res));
$router->post('/equipments',                  fn($req, $res)             => (new EquipmentController())->store($req, $res));
$router->get('/equipments/{id}/edit',         fn($req, $res, $p)         => (new EquipmentController())->edit($req, $res, $p));
$router->put('/equipments/{id}',              fn($req, $res, $p)         => (new EquipmentController())->update($req, $res, $p));
$router->delete('/equipments/{id}',           fn($req, $res, $p)         => (new EquipmentController())->destroy($req, $res, $p));

// Alarms
$router->get('/alarms',                       fn($req, $res)             => (new AlarmController())->index($req, $res));
$router->get('/alarms/events',                fn($req, $res)             => (new AlarmController())->events($req, $res));
$router->get('/alarms/create',                fn($req, $res)             => (new AlarmController())->create($req, $res));
$router->post('/alarms',                      fn($req, $res)             => (new AlarmController())->store($req, $res));
$router->get('/alarms/{id}/edit',             fn($req, $res, $p)         => (new AlarmController())->edit($req, $res, $p));
$router->put('/alarms/{id}',                  fn($req, $res, $p)         => (new AlarmController())->update($req, $res, $p));
$router->delete('/alarms/{id}',               fn($req, $res, $p)         => (new AlarmController())->destroy($req, $res, $p));
$router->post('/alarms/{id}/activate',        fn($req, $res, $p)         => (new AlarmController())->activate($req, $res, $p));
$router->post('/alarms/{id}/deactivate',      fn($req, $res, $p)         => (new AlarmController())->deactivate($req, $res, $p));

// ── Dispatch ──────────────────────────────────────────────────
$router->dispatch($request);
