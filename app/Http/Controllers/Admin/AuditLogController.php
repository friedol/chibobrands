<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Build query
        $query = AuditLog::with('user')->latest();

        // --- PERIOD LOGIC ---
        $period = $request->get('period');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($period && !$dateFrom && !$dateTo) {
            switch ($period) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek()->format('Y-m-d');
                    $dateTo = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo = now()->endOfYear()->format('Y-m-d');
                    break;
            }
        }

        // Super admin, admin, manager and accountant can see all logs
        // Other users can only see their own logs
        if (!in_array($user->role, ['super_admin', 'admin', 'manager', 'accountant'])) {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'accountant') {
            // Accountants ONLY see logs for users BELOW them
            $query->whereHas('user', function($q) {
                $q->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
            });
        }
        
        // Admins see everything except super_admin maybe? 
        // User didn't specify for admins, but let's stick to the request for accountant.

        // Filter by action if provided
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type if provided
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user if provided (only for admins/accountants)
        if ($request->filled('user_id') && in_array($user->role, ['super_admin', 'admin', 'manager', 'accountant'])) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range if provided
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(50)->withQueryString();

   
        try {
            $actions = AuditLog::distinct()->pluck('action')->sort()->values();
            $modelTypes = AuditLog::distinct()->whereNotNull('model_type')->pluck('model_type')->sort()->values();
        } catch (\Exception $e) {
            $actions = collect([]);
            $modelTypes = collect([]);
        }

        // Get users for filter (only for admins/accountants)
        $users = [];
        if (in_array($user->role, ['super_admin', 'admin', 'manager', 'accountant'])) {
            $usersQuery = \App\Models\User::whereIn('role', ['admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'accountant', 'delivery', 'gatekeeper', 'marketing_manager', 'hr_officer']);
            
            if ($user->role === 'accountant') {
                $usersQuery->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
            }
            
            $users = $usersQuery->orderBy('name')->get();
        }

        return view('admin.audit-logs.index', compact('auditLogs', 'actions', 'modelTypes', 'users', 'period', 'dateFrom', 'dateTo'));
    }
}
