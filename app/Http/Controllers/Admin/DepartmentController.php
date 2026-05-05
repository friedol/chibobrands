<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'all'); // Default to all for CRUD view
        
        // Handle date range based on period
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()],
            'custom' => [
                $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [null, null]
        };

        $dateFrom = $dateRange[0];
        $dateTo = $dateRange[1];

        $query = Department::query();

        // Constrained counts
        $query->withCount([
            'designTasks' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            },
            'orders' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            },
            'expenses' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) $q->whereBetween('date', [$dateFrom, $dateTo]);
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->paginate(20)->withQueryString();
        $dateFromStr = $dateFrom ? $dateFrom->format('Y-m-d') : null;
        $dateToStr = $dateTo ? $dateTo->format('Y-m-d') : null;

        return view('admin.departments.index', compact('departments', 'period', 'dateFromStr', 'dateToStr'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_finance') && auth()->user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Department::create($validated);

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        if (!auth()->user()->hasPermission('manage_finance') && auth()->user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $department->update($validated);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if (!auth()->user()->hasPermission('manage_finance') && auth()->user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        if ($department->designTasks()->exists() || $department->orders()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete department with associated records.');
        }

        $department->delete();

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
