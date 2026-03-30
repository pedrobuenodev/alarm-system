<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Equipment
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
            'SELECT * FROM equipments WHERE deleted_at IS NULL ORDER BY created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM equipments WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function existsBySerial(string $serial, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM equipments WHERE serial = :serial AND deleted_at IS NULL';
        $params = [':serial' => $serial];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO equipments (name, serial, type, created_at)
             VALUES (:name, :serial, :type, NOW())'
        );
        $stmt->execute([
            ':name'   => $data['name'],
            ':serial' => $data['serial'],
            ':type'   => $data['type'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipments
             SET name = :name, serial = :serial, type = :type
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            ':name'   => $data['name'],
            ':serial' => $data['serial'],
            ':type'   => $data['type'],
            ':id'     => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipments SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hasActiveAlarms(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM alarms
             WHERE equipment_id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
