<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Alarm
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, e.name AS equipment_name, e.serial AS equipment_serial
             FROM alarms a
             INNER JOIN equipments e ON e.id = a.equipment_id AND e.deleted_at IS NULL
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, e.name AS equipment_name
             FROM alarms a
             INNER JOIN equipments e ON e.id = a.equipment_id
             WHERE a.id = :id AND a.deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO alarms (description, classification, equipment_id, status, created_at)
             VALUES (:description, :classification, :equipment_id, "off", NOW())'
        );
        $stmt->execute([
            ':description'    => $data['description'],
            ':classification' => $data['classification'],
            ':equipment_id'   => $data['equipment_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE alarms
             SET description = :description, classification = :classification,
                 equipment_id = :equipment_id
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            ':description'    => $data['description'],
            ':classification' => $data['classification'],
            ':equipment_id'   => $data['equipment_id'],
            ':id'             => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE alarms SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL AND status = "off"'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE alarms SET status = :status WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':status' => $status, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** @return array<int, array<string, mixed>> Top 3 alarmes que mais atuaram */
    public function topActuated(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.description, a.classification,
                    e.name AS equipment_name,
                    COUNT(ae.id) AS activation_count
             FROM alarms a
             INNER JOIN equipments e ON e.id = a.equipment_id
             LEFT JOIN alarm_events ae ON ae.alarm_id = a.id AND ae.status = "on"
             WHERE a.deleted_at IS NULL
             GROUP BY a.id, a.description, a.classification, e.name
             ORDER BY activation_count DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
