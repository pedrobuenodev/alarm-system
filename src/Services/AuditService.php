<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Core\Request;

final class AuditService
{
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->auditLog = new AuditLog();
    }

    public function log(
        Request $request,
        string  $entity,
        ?int    $entityId,
        string  $action,
        mixed   $payload = null
    ): void {
        $this->auditLog->record(
            entity:    $entity,
            entityId:  $entityId,
            action:    $action,
            payload:   $payload,
            ip:        $request->ip(),
            userAgent: $request->userAgent()
        );
    }
}
