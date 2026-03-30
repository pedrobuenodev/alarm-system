<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class AlarmEvent
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lista alarmes atuados com filtros e ordenação dinâmica segura.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listActuated(
        string $search = '',
        string $sortBy = 'entered_at',
        string $sortDir = 'DESC'
    ): array {
        // Mapa de alias para coluna qualificada — evita ambiguidade no ORDER BY
        $columnMap = [
            'entered_at'        => 'ae.entered_at',
            'exited_at'         => 'ae.exited_at',
            'status'            => 'ae.status',
            'alarm_description' => 'a.description',
            'equipment_name'    => 'e.name',
        ];
        $allowedDirs = ['ASC', 'DESC'];

        $orderColumn = $columnMap[$sortBy] ?? 'ae.entered_at';
        $sortDir     = in_array($sortDir, $allowedDirs, true) ? $sortDir : 'DESC';

        $sql = 'SELECT ae.id,
                       ae.entered_at,
                       ae.exited_at,
                       ae.status,
                       a.description  AS alarm_description,
                       a.classification,
                       e.name         AS equipment_name
                FROM alarm_events ae
                INNER JOIN alarms a     ON a.id = ae.alarm_id
                INNER JOIN equipments e ON e.id = a.equipment_id
                WHERE 1=1';

        $params = [];

        if ($search !== '') {
            $sql .= ' AND a.description LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY {$orderColumn} {$sortDir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function activate(int $alarmId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO alarm_events (alarm_id, entered_at, status)
             VALUES (:alarm_id, NOW(), "on")'
        );
        $stmt->execute([':alarm_id' => $alarmId]);
        return (int) $this->db->lastInsertId();
    }

    public function deactivate(int $alarmId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE alarm_events
             SET exited_at = NOW(), status = "off"
             WHERE alarm_id = :alarm_id AND exited_at IS NULL AND status = "on"'
        );
        $stmt->execute([':alarm_id' => $alarmId]);
        return $stmt->rowCount() > 0;
    }
}
