<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    public static function log(string $action, string $description = null, array $oldValues = null, array $newValues = null): void
    {
        if (! auth()->check()) {
            return;
        }

        $eventId = self::generateEventId($action, auth()->id());

        // Idempotency check - prevent duplicate logs
        if (AuditLog::where('event_id', $eventId)->exists()) {
            return;
        }

        AuditLog::create([
            'event_id' => $eventId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'severity' => self::determineSeverity($action),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_id' => session()->getId(),
            'request_method' => Request::method(),
            'request_path' => Request::path(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    private static function generateEventId(string $action, int $userId): string
    {
        // Generate deterministic event_id based on action, user, and current second
        // This prevents duplicate entries from rapid repeated calls
        $timestamp = now()->format('Y-m-d H:i:s');
        $unique = md5($action . $userId . $timestamp);
        return substr($unique, 0, 32);
    }

    private static function determineSeverity(string $action): string
    {
        $action = strtoupper($action);

        if (str_contains($action, 'DELETE') || str_contains($action, 'PERMISSION')) {
            return 'CRITICAL';
        }

        if (str_contains($action, 'UPDATE') || str_contains($action, 'FAIL')) {
            return 'WARNING';
        }

        return 'INFO';
    }
}
