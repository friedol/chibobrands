<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AutoFollowupController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Customer::query()
            ->whereNotNull('phone')
            ->where(function ($q) {
                $q->whereNotNull('next_expected_order_date')
                  ->orWhereNotNull('manual_follow_up_date');
            });

        // Salers see only their own customers
        if ($user->role === 'saler') {
            $query->forSaler($user);
        }

        // Filter: due date range
        $range = $request->get('range', 'overdue_and_today');
        $today = Carbon::today();

        switch ($range) {
            case 'today':
                $query->where(function ($q) use ($today) {
                    $q->whereDate('manual_follow_up_date', $today)
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('manual_follow_up_date')
                             ->whereDate('next_expected_order_date', $today);
                      });
                });
                break;
            case 'this_week':
                $query->where(function ($q) use ($today) {
                    $q->whereBetween('manual_follow_up_date', [$today, $today->copy()->endOfWeek()])
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('manual_follow_up_date')
                             ->whereBetween('next_expected_order_date', [$today, $today->copy()->endOfWeek()]);
                      });
                });
                break;
            case 'upcoming_7':
                $query->where(function ($q) use ($today) {
                    $q->whereBetween('manual_follow_up_date', [$today, $today->copy()->addDays(7)])
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('manual_follow_up_date')
                             ->whereBetween('next_expected_order_date', [$today, $today->copy()->addDays(7)]);
                      });
                });
                break;
            default: // overdue_and_today
                $query->where(function ($q) use ($today) {
                    $q->where('manual_follow_up_date', '<=', $today)
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('manual_follow_up_date')
                             ->where('next_expected_order_date', '<=', $today);
                      });
                });
                break;
        }

        // Filter by seller (admin only)
        if (in_array($user->role, ['admin', 'super_admin', 'manager']) && $request->filled('saler_id')) {
            $query->where('added_by', $request->saler_id);
        }

        // Filter by follow_up_status
        if ($request->filled('follow_up_status')) {
            $query->where('follow_up_status', $request->follow_up_status);
        }

        $customers = $query
            ->orderByRaw('COALESCE(manual_follow_up_date, next_expected_order_date) ASC')
            ->paginate(30)
            ->withQueryString();

        $salers = User::where('role', 'saler')->orderBy('name')->get();

        $stats = [
            'overdue'    => (clone $query->getQuery())->whereRaw('COALESCE(manual_follow_up_date, next_expected_order_date) < CURDATE()')->count(),
            'due_today'  => (clone $query->getQuery())->whereRaw('DATE(COALESCE(manual_follow_up_date, next_expected_order_date)) = CURDATE()')->count(),
            'this_week'  => (clone $query->getQuery())->whereRaw('COALESCE(manual_follow_up_date, next_expected_order_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)')->count(),
        ];

        return view('admin.auto-followup.index', compact('customers', 'salers', 'stats', 'range'));
    }
}
