<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can view audit logs.');
        }

        $query = AuditLog::with('user');

        // Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', "%{$request->ip_address}%");
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $validColumns = ['created_at', 'action', 'severity', 'ip_address'];
        $validDirections = ['asc', 'desc'];

        if (in_array($sortColumn, $validColumns) && in_array($sortDirection, $validDirections)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = $request->get('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100]) ? $perPage : 25;

        $logs = $query->paginate($perPage);

        // Summary statistics
        $summary = [
            'total_events' => (clone $query)->count(),
            'failed_logins' => (clone $query)->where('action', 'like', '%FAIL%')->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count(),
            'critical_events' => (clone $query)->where('severity', 'CRITICAL')->count(),
        ];

        return view('audit-logs.index', compact('logs', 'summary'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can export audit logs.');
        }

        $query = AuditLog::with('user');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-logs-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date/Time', 'User', 'Email', 'Action', 'Severity', 'Description', 'IP Address', 'User Agent', 'Request Method', 'Request Path']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'N/A',
                    $log->user?->email ?? 'N/A',
                    $log->action,
                    $log->severity,
                    $log->description ?? '—',
                    $log->ip_address ?? '—',
                    $log->user_agent ?? '—',
                    $log->request_method ?? '—',
                    $log->request_path ?? '—',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can view audit log details.');
        }

        $auditLog = AuditLog::with('user')->findOrFail($id);

        // Find related events (same user within 5 minutes)
        $relatedEvents = AuditLog::with('user')
            ->where('user_id', $auditLog->user_id)
            ->where('id', '!=', $auditLog->id)
            ->whereBetween('created_at', [
                $auditLog->created_at->subMinutes(5),
                $auditLog->created_at->addMinutes(5),
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'log' => $auditLog,
            'related_events' => $relatedEvents,
        ]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can delete audit logs.');
        }

        $auditLog = AuditLog::findOrFail($id);
        $auditLog->delete();

        return response()->json(['success' => true, 'message' => 'Audit log deleted successfully.']);
    }

    public function destroyBatch(Request $request)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can delete audit logs.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $count = AuditLog::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('admin.dashboard.audit-logs.index')
            ->with('success', "Deleted {$count} audit log(s).");
    }

    public function destroyOld(Request $request)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can delete audit logs.');
        }

        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $count = AuditLog::where('created_at', '<', now()->subDays($request->days))->delete();

        return redirect()
            ->route('admin.dashboard.audit-logs.index')
            ->with('success', "Deleted {$count} audit log(s) older than {$request->days} days.");
    }
}
