<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\TaskUpdate;
use App\Models\DesignTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Shared filtered query used by index(), printFiltered(), exportPdf() and
     * exportExcel() so every report format is built from the exact same data.
     */
    private function buildMovementsQuery(Request $request)
    {
        $query = ProductMovement::query();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->filled('person_type')) {
            // Allows filtering by "Who Handled It"
            $query->where('handler_type', $request->person_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $movements = $this->buildMovementsQuery($request)->latest('movement_date')->paginate(20)->withQueryString();

        return view('gatekeeper.index', compact('movements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch registered users for autocomplete/selection
        $deliveryPersonnel = User::where('role', 'delivery')->where('is_active', true)->orderBy('name')->get();
        // Broad definition of staff for the purpose of movement
        $staffUsers = User::whereIn('role', ['admin', 'manager', 'operator', 'receptionist', 'designer', 'saler', 'gatekeeper', 'accountant', 'marketing_manager', 'hr_officer'])
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();

        return view('gatekeeper.create', compact('deliveryPersonnel', 'staffUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'product_name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'purpose' => 'required|string',
            'authorization_reference' => 'nullable|string',
            
            // Handler
            'handler_type' => 'required|string',
            'handler_name' => 'required|string|max:255',
            'handler_identifier' => 'nullable|string|max:255',
            'handler_user_id' => 'nullable|exists:users,id', // If we want to link valid internal users
            
            // Context (Source/Recipient)
            'source_type' => 'nullable|string', // required_if:type,in
            'source_name' => 'nullable|string',
            'source_identifier' => 'nullable|string|max:255',
            'recipient_type' => 'nullable|string', // required_if:type,out
            'recipient_name' => 'nullable|string',
            'recipient_identifier' => 'nullable|string|max:255',
            'delivery_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'verification_code' => 'nullable|string',
        ]);

        if ($request->type === 'out' && $request->filled('authorization_reference')) {
             $taskCode = $request->authorization_reference;
             $task = DesignTask::where('task_code', $taskCode)
                 ->orWhere('id', is_numeric($taskCode) ? $taskCode : 0)
                 ->first();
             
             if ($task && $task->pickup_code && $request->filled('verification_code')) {
                 if ($task->pickup_code !== $request->verification_code) {
                     return back()->withInput()->with('error', 'Invalid Delivery Verification Code. Please cross-check with the customer or delivery person.');
                 }
             }
        }
        
        // Let's refine validation based on Type:
        if ($request->type === 'in') {
             $request->validate([
                 'source_type' => 'required',
                 'source_name' => 'required',
             ]);
        } else {
             $request->validate([
                 'recipient_type' => 'required',
                 'recipient_name' => 'required',
                 'delivery_method' => 'required',
             ]);

            if ($request->recipient_type === 'Customer') {
                $request->validate([
                    'recipient_identifier' => 'required|string|max:255',
                ]);
            }
        }

        $data = $request->all();
        if (empty($data['movement_date'])) {
            $data['movement_date'] = now();
        }
        
        $movement = new ProductMovement($data);
        $movement->gatekeeper_id = Auth::id();
        $movement->save();

        return redirect()->route('gatekeeper.movements.index')->with('success', 'Movement recorded successfully.');
    }

    /**
     * Search customers for the gatekeeper "Record Outgoing" form.
     * Returns JSON: { customers: [{id, name, phone, tax_id}] }
     */
    public function searchCustomers(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '') {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::query()
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('tax_id', 'like', '%' . $q . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'tax_id']);

        return response()->json([
            'customers' => $customers,
        ]);
    }

    /**
     * Search design tasks for pulling data into movement form.
     */
    public function searchTasks(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '') {
            return response()->json(['tasks' => []]);
        }

        $tasks = DesignTask::query()
            ->with(['customer', 'department'])
            ->where('status', '!=', DesignTask::STATUS_CANCELLED)
            ->where(function ($query) use ($q) {
                $query->where('task_code', 'like', '%' . $q . '%')
                    ->orWhere('title', 'like', '%' . $q . '%')
                    ->orWhereHas('customer', function($cq) use ($q) {
                        $cq->where('name', 'like', '%' . $q . '%')
                          ->orWhere('phone', 'like', '%' . $q . '%');
                    });
            })
            ->latest()
            ->limit(15)
            ->get();

        $formattedTasks = $tasks->map(function($task) {
            return [
                'id' => $task->id,
                'task_code' => $task->task_code,
                'title' => $task->title,
                'customer_name' => $task->customer->name ?? 'N/A',
                'customer_phone' => $task->customer->phone ?? 'N/A',
                'department' => $task->department->name ?? 'N/A',
                'description' => $task->description,
                'price' => $task->price,
            ];
        });

        return response()->json([
            'tasks' => $formattedTasks,
        ]);
    }

    /**
     * Delivery confirmation page — search super_completed tasks and mark delivered.
     */
    public function deliverIndex(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $tasks = collect();
        if ($q !== '') {
            $tasks = DesignTask::with(['customer', 'department'])
                ->where('status', DesignTask::STATUS_SUPER_COMPLETED)
                ->where(function ($query) use ($q) {
                    $query->where('task_code', 'like', '%' . $q . '%')
                        ->orWhere('title', 'like', '%' . $q . '%')
                        ->orWhereHas('customer', fn($cq) =>
                            $cq->where('name', 'like', '%' . $q . '%')
                               ->orWhere('phone', 'like', '%' . $q . '%')
                        );
                })
                ->latest()
                ->limit(30)
                ->get();
        } else {
            // Show all tasks ready for delivery by default
            $tasks = DesignTask::with(['customer', 'department'])
                ->where('status', DesignTask::STATUS_SUPER_COMPLETED)
                ->latest()
                ->limit(50)
                ->get();
        }

        return view('gatekeeper.deliver', compact('tasks', 'q'));
    }

    /**
     * Mark a design task as delivered (super_completed → delivered).
     */
    public function markDelivered(Request $request, DesignTask $task)
    {
        if ($task->status !== DesignTask::STATUS_SUPER_COMPLETED) {
            return back()->with('error', 'Task "' . $task->task_code . '" cannot be marked as delivered. Current status: ' . $task->status_label);
        }

        $task->status = DesignTask::STATUS_DELIVERED;
        $task->delivery_status = 'delivered';
        $task->delivered_at = now();
        $task->save();

        TaskUpdate::create([
            'task_id'  => $task->id,
            'admin_id' => Auth::id(),
            'type'     => TaskUpdate::TYPE_STATUS_UPDATE,
            'content'  => 'Task marked as Delivered by Gatekeeper.',
            'metadata' => ['from' => 'super_completed', 'to' => 'delivered'],
        ]);

        return back()->with('success', 'Task ' . $task->task_code . ' — "' . $task->title . '" has been marked as Delivered.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductMovement $movement)
    {
        return view('gatekeeper.show', compact('movement'));
    }

    /**
     * Print filtered gatekeeper records.
     */
    public function printFiltered(Request $request)
    {
        $movements = $this->buildMovementsQuery($request)->latest('movement_date')->get();

        if ($movements->isEmpty()) {
            return redirect()->back()->with('error', 'No records found to print.');
        }

        return view('gatekeeper.print-logs', compact('movements'));
    }

    /**
     * PDF export of the gatekeeper movement log — same filtered data as index()/printFiltered().
     */
    public function exportPdf(Request $request)
    {
        $movements = $this->buildMovementsQuery($request)->latest('movement_date')->get();

        $title    = 'Gatekeeper Movement Log';
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.product-movements', compact('movements', 'title', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('product-movements-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Excel export of the gatekeeper movement log — same filtered data as index()/printFiltered().
     */
    public function exportExcel(Request $request)
    {
        $movements = $this->buildMovementsQuery($request)->latest('movement_date')->get();

        $headings = ['Type', 'Date', 'Product', 'Qty', 'Unit Price', 'Handler', 'Handler Type', 'Source / Recipient', 'Context Type'];

        $rows = $movements->map(function ($movement) {
            $contextName = $movement->type === 'in' ? $movement->source_name : $movement->recipient_name;
            $contextType = $movement->type === 'in' ? $movement->source_type : $movement->recipient_type;

            return [
                strtoupper($movement->type),
                $movement->movement_date?->format('Y-m-d H:i'),
                $movement->product_name,
                $movement->quantity,
                $movement->unit_price ? (float) $movement->unit_price : null,
                $movement->handler_name,
                $movement->handler_type,
                $contextName,
                $contextType,
            ];
        })->toArray();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Product Movements'),
            'product-movements-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
