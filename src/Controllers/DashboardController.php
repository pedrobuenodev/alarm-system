<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Controller, Request, Response};
use App\Models\{Alarm, AuditLog, Equipment};

final class DashboardController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $db        = \App\Config\Database::getInstance();
        $equipment = new Equipment();
        $alarm     = new Alarm();
        $auditLog  = new AuditLog();

        $totalEquipments = (int) $db->query('SELECT COUNT(*) FROM equipments WHERE deleted_at IS NULL')->fetchColumn();
        $totalAlarms     = (int) $db->query('SELECT COUNT(*) FROM alarms WHERE deleted_at IS NULL')->fetchColumn();
        $activeAlarms    = (int) $db->query('SELECT COUNT(*) FROM alarms WHERE status = "on" AND deleted_at IS NULL')->fetchColumn();
        $totalEvents     = (int) $db->query('SELECT COUNT(*) FROM alarm_events')->fetchColumn();

        $top3   = $alarm->topActuated(3);
        $recent = $auditLog->recent(10);

        $response->view('dashboard/index', compact(
            'totalEquipments',
            'totalAlarms',
            'activeAlarms',
            'totalEvents',
            'top3',
            'recent'
        ));
    }
}
