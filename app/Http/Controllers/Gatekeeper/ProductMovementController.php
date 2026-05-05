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
    public function index(Request $request)
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

        $movements = $query->latest('movement_date')->paginate(20)->withQueryString();

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
        $staffUsers = User::whereIn('role', ['admin', 'manager', 'operator', 'receptionist', 'designer', 'saler', 'gatekeeper'])
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
        $query = ProductMovement::query();

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->filled('person_type')) {
            $query->where('handler_type', $request->person_type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $movements = $query->latest('movement_date')->get();

        if ($movements->isEmpty()) {
            return redirect()->back()->with('error', 'No records found to print.');
        }

        return view('gatekeeper.print-logs', compact('movements'));
    }
}
