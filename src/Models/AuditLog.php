<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class AuditLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function record(
        string $entity,
        ?int   $entityId,
        string $action,
        mixed  $payload,
        string $ip,
        string $userAgent
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs (entity, entity_id, action, payload, ip, user_agent, created_at)
             VALUES (:entity, :entity_id, :action, :payload, :ip, :user_agent, NOW())'
        );
        $stmt->execute([
            ':entity'     => $entity,
            ':entity_id'  => $entityId,
            ':action'     => $action,
            ':payload'    => $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            ':ip'         => $ip,
            ':user_agent' => mb_substr($userAgent, 0, 255),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function recent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
