<?php

namespace App\Http\Controllers;

use App\Models\DesignTask;
use App\Models\TaskUpdate;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Services\AuditLogService;
use App\Services\SmsApiService;
use App\Notifications\NewTaskCreatedNotification;
use App\Notifications\DeliveryTaskAssignedNotification;
use App\Notifications\DeliveryTaskUpdatedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskStatusUpdatedNotification;
use App\Notifications\TaskCommentedNotification;

class DesignTaskController extends Controller
{
    /**
     * Display a listing of tasks based on user role.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = DesignTask::with(['customer', 'receptionist', 'designer', 'latestUpdate', 'saler'])
            ->latest()
            ->orderBy('id', 'desc');

        // Filter based on role
        $query->forSaler($user);

        if ($user->role === 'receptionist') {
            $query->where('receptionist_id', $user->id);
        } elseif ($user->role === 'designer') {
            $query->where('designer_id', $user->id);
        } elseif ($user->role === 'operator') {
            $query->where(function($q) use ($user) {
                $q->where('operator_id', $user->id)
                  ->orWhere('receptionist_id', $user->id)
                  ->orWhere('designer_id', $user->id); // In case operator was assigned as designer
            });
        }
        // Super admin, admin, manager can see all tasks

        // Search by task title or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by designer if provided
        if ($request->filled('designer_id')) {
            $query->where('designer_id', $request->designer_id);
        }

        // Filter by receptionist if provided
        if ($request->filled('receptionist_id')) {
            $query->where('receptionist_id', $request->receptionist_id);
        }

        // Filter by customer if provided
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by specific date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by delivery status (tasks needing delivery)
        if ($request->filled('delivery_filter')) {
            if ($request->delivery_filter === 'pending') {
                $query->where('status', 'super_completed')
                      ->where(function($q) {
                          $q->whereNull('delivery_status')
                            ->orWhere('delivery_status', '!=', 'delivered');
                      });
            } elseif ($request->delivery_filter === 'delivered') {
                $query->where('delivery_status', 'delivered');
            }
        }

        // Filter by date period
        if ($request->filled('period')) {
            $period = $request->period;
            $now = now();
            
            if ($period === 'today') {
                $query->whereDate('created_at', $now->today());
            } elseif ($period === 'yesterday') {
                $query->whereDate('created_at', $now->subDay()->toDateString());
            } elseif ($period === 'week') {
                $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($period === 'last_week') {
                 $query->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            } elseif ($period === 'quarter') {
                $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            } elseif ($period === 'half_year') {
                $query->whereBetween('created_at', [$now->subMonths(6), $now]);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', $now->year);
            }
        }
        
        // Custom Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by Loss if provided
        if ($request->get('is_loss') == '1') {
            $query->where('is_loss', true);
        }

        // Stats Calculation (Clone query before pagination and status filter)
        $statsQuery = clone $query;
        // If status filter was applied, we might want stats to reflect global or filtered? 
        // Usually stats cards show breakdown, so we should typically NOT filter by status for the breakdown cards
        // But if we want to show stats for "Today", we want date filter applied.
        
        // Let's remove status filter from stats query so cards show full breakdown of the current selection (e.g. current designer/date)
        // Note: The status filter hasn't been applied to $query yet in the original code flow (it's applied below).
        // Wait, I am editing around line 80. The status filter is applied at line 66 in original code.
        
        // RE-READING ORIGINAL CODE: 
        // Status filter is at lines 66-68. 
        // I should insert my code BEFORE the status filter if I want stats to show breakdown of all statuses for the current filters.
        // OR I can clone it before status filter.
        
        // The ReplaceBlock targets line 80, which is AFTER status filter (66-68).
        // So $query already has status filter if applied.
        // If the user selects "Pending", and we show stats, usually we want to see "Pending: 5, Completed: 0" etc? 
        // Or do we want to see "Total tasks matching search"?
        // I will calculate stats based on the *current* query (so if filtered by 'Pending', stats show only Pending).
        // BUT, often "Stats Cards" are used *instead* of filters or to show context.
        // Let's create a separate query for stats that respects all filters EXCEPT status.
        
        $tasks = $query->paginate(15)->withQueryString();
        
        // Calculate stats for the cards (respecting all filters except status)
        $statsBaseQuery = DesignTask::with('customer');
        // Apply same Role filters
        if (in_array($user->role, ['receptionist', 'accountant'])) {
            // Receptionist sees all stats roughly, or should we keep filtering?
            // "receptionist should see for all" implies no filter by receptionist_id for them.
            // So we do NOTHING here for receptionist role, allowing full access to stats.
        } elseif ($user->role === 'designer') {
            $statsBaseQuery->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $statsBaseQuery->where('saler_id', $user->id);
        } elseif ($user->role === 'operator') {
             $statsBaseQuery->where(function($q) use ($user) {
                $q->where('operator_id', $user->id)
                  ->orWhere('receptionist_id', $user->id)
                  ->orWhere('designer_id', $user->id);
            });
        }
        
        // Apply Search
        if ($request->filled('search')) {
            $search = $request->search;
             $statsBaseQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        // Apply Designer/Receptionist Filters
         if ($request->filled('designer_id')) {
            $statsBaseQuery->where('designer_id', $request->designer_id);
        }
        if ($request->filled('receptionist_id')) {
            $statsBaseQuery->where('receptionist_id', $request->receptionist_id);
        }
        // Apply Date Filters
        if ($request->filled('period')) {
             $period = $request->period;
            $now = now();
            if ($period === 'today') $statsBaseQuery->whereDate('created_at', $now->today());
            elseif ($period === 'yesterday') $statsBaseQuery->whereDate('created_at', $now->subDay()->toDateString());
            elseif ($period === 'week') $statsBaseQuery->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            elseif ($period === 'last_week') $statsBaseQuery->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            elseif ($period === 'month') $statsBaseQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            elseif ($period === 'quarter') $statsBaseQuery->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            elseif ($period === 'half_year') $statsBaseQuery->whereBetween('created_at', [$now->subMonths(6), $now]);
            elseif ($period === 'year') $statsBaseQuery->whereYear('created_at', $now->year);
        }
        if ($request->filled('date_from')) $statsBaseQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $statsBaseQuery->whereDate('created_at', '<=', $request->date_to);


        $taskStats = [
            'total' => (clone $statsBaseQuery)->count(),
            'pending' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PENDING)->count(),
            'in_progress' => (clone $statsBaseQuery)->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_CONFIRMED])->count(),
            'in_review' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_IN_REVIEW)->count(),
            'printing' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTING)->count(),
            'printed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTED)->count(),
            'completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_COMPLETED)->count(),
            'super_completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_SUPER_COMPLETED)->count(),
            'delivered' => (clone $statsBaseQuery)->where('delivery_status', 'delivered')->count(),
            'rejected' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_REJECTED)->count(),
            'cancelled' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_CANCELLED)->count(),
            'loss' => (clone $statsBaseQuery)->where('is_loss', true)->count(),
        ];
        
        // Admin, SuperAdmin, and Accountant see revenue
        if (in_array($user->role, ['admin', 'super_admin', 'accountant'])) {
            $taskStats['revenue'] = (clone $statsBaseQuery)->sum('amount_paid');
        }

        $all_designers = User::where('role', 'designer')->where('verified', true)->orderBy('name')->select('id', 'name', 'role')->get();
        $operators = User::where('role', 'operator')->where('verified', true)->orderBy('name')->select('id', 'name', 'role')->get();
        $all_receptionists = User::whereIn('role', ['receptionist', 'operator', 'admin', 'super_admin', 'accountant'])->where('verified', true)->orderBy('name')->select('id', 'name', 'role')->get();
        $templates = \App\Models\MessageTemplate::active()->select('id', 'title')->get();
        $all_delivery = User::where('role', 'delivery')->where('verified', true)->orderBy('name')->select('id', 'name', 'role')->get();
        $salers = User::where('role', 'saler')->where('verified', true)->orderBy('name')->select('id', 'name', 'role')->get();
        $departments = \App\Models\Department::all();

        $statuses = [
            DesignTask::STATUS_PENDING => 'Pending',
            DesignTask::STATUS_IN_PROGRESS => 'In Progress',
            DesignTask::STATUS_IN_REVIEW => 'In Review',
            DesignTask::STATUS_PRINTING => 'Printing',
            DesignTask::STATUS_PRINTED => 'Printed',
            DesignTask::STATUS_COMPLETED => 'Completed',
            DesignTask::STATUS_SUPER_COMPLETED => 'Closed',
            DesignTask::STATUS_REJECTED => 'Rejected',
            DesignTask::STATUS_CANCELLED => 'Cancelled',
        ];

        return view('admin.design-tasks.index', compact('tasks', 'statuses', 'user', 'all_designers', 'all_receptionists', 'operators', 'templates', 'all_delivery', 'taskStats', 'salers', 'departments'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        $customers = Customer::forSaler(Auth::user())->verified()->orderBy('name')->get();
        $designers = User::where('role', 'designer')->where('verified', true)->viewableStaff()->get();
        $operators = User::where('role', 'operator')->where('verified', true)->viewableStaff()->get();
        $salers = User::where('role', 'saler')->where('verified', true)->orderBy('name')->get();
        
        // Get existing task titles for autocomplete
        $existingTitles = DesignTask::distinct()
            ->whereNotNull('title')
            ->pluck('title')
            ->toArray();
            
        $departments = \App\Models\Department::all();
        $taskTypes = \App\Models\DesignTaskType::orderBy('name')->get();
        
        return view('admin.design-tasks.create', compact('customers', 'designers', 'operators', 'salers', 'existingTitles', 'departments', 'taskTypes'));
    }

    /**
     * Store a newly created task(s).
     */
    
    public function store(Request $request): RedirectResponse
    {
        $customerId = null;
        
        // Handle customer creation if new customer
        if ($request->input('customer_id') === 'new') {
            $customerData = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'nullable|email|unique:customers,email',
                'customer_phone' => 'required|string|unique:customers,phone',
                'customer_company' => 'nullable|string|max:255',
                'customer_business_type' => 'nullable|string',
                'customer_address' => 'nullable|string',
                'saler_id' => 'nullable|exists:users,id',
            ]);
            
            // Create new customer
            $customer = Customer::create([
                'name' => $customerData['customer_name'],
                'email' => $customerData['customer_email'],
                'phone' => $customerData['customer_phone'],
                'company_name' => $customerData['customer_company'] ?? null,
                'business_type' => $customerData['customer_business_type'] ?? null,
                'address' => $customerData['customer_address'] ?? null,
                'password' => Hash::make(str()->random(12)), // Random password
                'verified' => true, // Auto-verify customer created by receptionist
                'is_active' => true,
                'added_by' => $customerData['saler_id'] ?? auth()->id(),
            ]);
            
            // Store saler info in notes if provided
            if (!empty($customerData['saler_id'])) {
                $saler = User::find($customerData['saler_id']);
                if ($saler) {
                    $customer->notes = 'Referred by saler: ' . $saler->name . ' (' . ($saler->phone ?? 'No phone') . ')';
                    $customer->save();
                }
            }
            
            $customerId = $customer->id;
        } else {
            // Validate customer exists
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
            ]);
            $customerId = $validated['customer_id'];
        }
        
        // Validate tasks array and global payment
        $validated = $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.designer_instructions' => 'nullable|string',
            'tasks.*.priority' => 'required|in:1,3,5',
            'tasks.*.designer_id' => 'nullable|exists:users,id',
            'tasks.*.operator_id' => 'nullable|exists:users,id',
            'tasks.*.saler_id' => 'nullable|exists:users,id',
            'tasks.*.department_id' => 'required|exists:departments,id',
            'tasks.*.qty' => 'required|numeric|min:0',
            'tasks.*.rate' => 'required|numeric|min:0',
            'tasks.*.price' => 'required|numeric|min:0',
            'tasks.*.task_type_id' => 'nullable|exists:design_task_types,id',
            'tasks.*.delivery_cost' => 'nullable|numeric|min:0',
            'tasks.*.delivery_discount' => 'nullable|numeric|min:0',
            'tasks.*.deadline' => 'nullable|date|after:today',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'requires_receipt' => 'nullable|boolean',
            'auto_print_instructions' => 'nullable|boolean',
        ]);
        
        // Validate file uploads separately
        $request->validate([
            'tasks.*.reference_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        $receptionistId = Auth::id();
        $tasksCreated = [];
        
        // Check if receipt is required
        $requiresReceipt = $request->has('requires_receipt') && $request->input('requires_receipt');
        
        // Create each task and apportion global payment
        $remainingGlobalPaid = floatval($validated['amount_paid'] ?? 0);
        
        foreach ($validated['tasks'] as $index => $taskData) {
            $qty = floatval($taskData['qty'] ?? 1);
            $rate = floatval($taskData['rate'] ?? 0);
            
            // Store base price (VAT will be calculated at receipt/display time)
            $basePrice = $qty * $rate;
            $deliveryCost = floatval($taskData['delivery_cost'] ?? 0);
            $deliveryDiscount = floatval($taskData['delivery_discount'] ?? 0);
            
            $taskPriceWithVat = $requiresReceipt ? ($basePrice * 1.18) + $deliveryCost - $deliveryDiscount : $basePrice + $deliveryCost - $deliveryDiscount;
            
            // Apportion payment to this task
            $apportionedPaid = min($remainingGlobalPaid, $taskPriceWithVat);
            $remainingGlobalPaid -= $apportionedPaid;
            
            $taskValidated = [
                'title' => $taskData['title'],
                'task_code' => DesignTask::generateTaskCode(),
                'description' => $taskData['description'] ?? null,
                'designer_instructions' => $taskData['designer_instructions'] ?? null,
                'customer_id' => $customerId,
                'receptionist_id' => $receptionistId,
                'designer_id' => $taskData['designer_id'] ?? null,
                'operator_id' => $taskData['operator_id'] ?? null,
                'saler_id' => $taskData['saler_id'] ?? null,
                'department_id' => $taskData['department_id'],
                'priority' => $taskData['priority'],
                'deadline' => $taskData['deadline'] ?? null,
                'price' => $basePrice,
                'qty' => $qty,
                'rate' => $rate,
                'amount_paid' => $apportionedPaid,
                'balance' => $taskPriceWithVat - $apportionedPaid,
                'requires_receipt' => $requiresReceipt,
                'design_task_type_id' => $taskData['task_type_id'] ?? null,
                'delivery_cost' => $deliveryCost,
                'delivery_discount' => $deliveryDiscount,
                'status' => DesignTask::STATUS_PENDING,
            ];
            
            // Handle image uploads for this task
            $uploadedImages = [];
            if ($request->hasFile("tasks.{$index}.reference_images")) {
                foreach ($request->file("tasks.{$index}.reference_images") as $image) {
                    if ($image && $image->isValid()) {
                        $path = $image->store('design-tasks/references', 'public');
                        $uploadedImages[] = [
                            'path' => $path,
                            'original_name' => $image->getClientOriginalName(),
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                    }
                }
            }
            
            if (!empty($uploadedImages)) {
                $taskValidated['reference_images'] = $uploadedImages;
            }
            
            $task = DesignTask::create($taskValidated);
            $tasksCreated[] = $task;

            // Record Payment in Finance module
            if ($task->amount_paid > 0) {
                \App\Models\Payment::create([
                    'design_task_id' => $task->id,
                    'customer_id' => $task->customer_id,
                    'amount' => $task->amount_paid,
                    'payment_method' => $validated['payment_method'],
                    'date' => now(),
                    'seller_id' => auth()->id(),
                    'department_id' => $task->department_id,
                ]);

                // Trigger Customer Analytics Recalculation
                try {
                    $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                    $analyticsService->recalculateCustomerAnalytics($task->customer_id);
                } catch (\Exception $e) {
                    Log::error('Failed to update customer analytics on design task creation', ['error' => $e->getMessage()]);
                }
            }
            
            // Load customer relationship for SMS and audit
            $task->load('customer');
            
            // Log audit for design task creation
            try {
                if ($task->customer) {
                    AuditLogService::created($task, 'Created design task: ' . $task->title . ' for customer: ' . $task->customer->name);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for design task creation: ' . $e->getMessage());
            }

            // Notify operators and admins about the new task
            $staffToNotify = User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
            foreach ($staffToNotify as $staff) {
                try {
                    $staff->notify(new NewTaskCreatedNotification($task, Auth::user()));
                } catch (\Exception $e) {
                    \Log::error('Failed to send NewTaskCreatedNotification: ' . $e->getMessage());
                }
            }

            // If a designer was assigned during creation, notify them too
            if ($task->designer_id) {
                $designer = User::find($task->designer_id);
                if ($designer) {
                    try {
                        $designer->notify(new TaskAssignedNotification($task));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send TaskAssignedNotification: ' . $e->getMessage());
                    }
                }
            }
        }
        
        // Send SMS notification to customer (Batch)
        try {
            if (!empty($tasksCreated)) {
                $firstTask = $tasksCreated[0];
                if ($firstTask->customer && $firstTask->customer->phone) {
                    \Log::info('Attempting to send Batch SMS for task creation', [
                        'count' => count($tasksCreated),
                        'customer_id' => $firstTask->customer->id,
                        'customer_phone' => $firstTask->customer->phone,
                    ]);
                    
                    $smsService = app(SmsApiService::class);
                    $result = $smsService->sendBatchTaskReceipt($tasksCreated);
                    
                    if ($result['success']) {
                        \Log::info('Batch Receipt SMS sent successfully for task creation');
                    } else {
                        \Log::warning('Batch SMS sending failed for task creation', [
                            'error' => $result['message'] ?? 'Unknown error',
                        ]);
                    }
                } else {
                    \Log::warning('Customer missing or has no phone number for batch SMS');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Exception while sending Batch SMS notification for task creation', [
                'error' => $e->getMessage(),
            ]);
        }
        
        $taskCount = count($tasksCreated);
        $message = $taskCount === 1 
            ? 'Design task created successfully.' 
            : "{$taskCount} design tasks created successfully.";
        
        // Check if auto-print instructions was requested
        $autoPrint = $request->has('auto_print_instructions') && $request->input('auto_print_instructions');
        $hasDesignerAssigned = collect($tasksCreated)->contains(function($task) {
            return !is_null($task->designer_id);
        });
        
        // Store tasks data for printing (Thermal Receipt)
        if (!empty($tasksCreated)) {
            // Calculate totals for the receipt
            $subtotal = collect($tasksCreated)->sum('price');
            $totalAmountPaid = $validated['amount_paid'] ?? 0;
            $deliveryDiff = collect($tasksCreated)->sum(function ($t) {
                return (float) ($t->delivery_cost ?? 0) - (float) ($t->delivery_discount ?? 0);
            });
            $vat = $requiresReceipt ? $subtotal * 0.18 : 0;
            $totalWithVat = $subtotal + $vat + $deliveryDiff;
            $totalBalance = $totalWithVat - $totalAmountPaid;

            $receiptData = [
                'customerData' => [
                    'name' => $tasksCreated[0]->customer->name,
                    'phone' => $tasksCreated[0]->customer->phone,
                    'email' => $tasksCreated[0]->customer->email,
                    'company_name' => $tasksCreated[0]->customer->company_name,
                ],
                'tasks' => collect($tasksCreated)->map(function($t) {
                    return [
                        'title' => $t->title,
                        'qty' => $t->qty,
                        'rate' => $t->rate,
                        'price' => $t->price, // Base price (qty * rate)
                        'delivery_cost' => $t->delivery_cost ?? 0,
                        'delivery_discount' => $t->delivery_discount ?? 0,
                        'saler' => $t->saler ? $t->saler->name : null,
                        'salerPhone' => $t->saler ? $t->saler->phone : null,
                    ];
                })->toArray(),
                'subtotal' => $subtotal,
                'vat' => $vat,
                'total' => $totalWithVat,
                'totalPaid' => $totalAmountPaid,
                'totalBalance' => $totalBalance,
                'requires_receipt' => $requiresReceipt,
                'paymentMethod' => $validated['payment_method'] ?? 'Cash',
            ];

            session()->flash('print_receipt_data', $receiptData);
            
            // Also keep the existing auto_print_instructions if needed (for designer tasks specifically)
            if ($autoPrint && $hasDesignerAssigned) {
                session()->flash('auto_print_instructions', true);
                $tasksWithRelations = collect($tasksCreated)->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'designer_instructions' => $task->designer_instructions,
                        'description' => $task->description,
                        'priority' => $task->priority_label,
                        'deadline' => $task->deadline ? $task->deadline->format('M d, Y h:i A') : null,
                        'designer' => $task->designer ? $task->designer->name : null,
                        'customer' => [
                            'name' => $task->customer->name,
                            'email' => $task->customer->email,
                            'phone' => $task->customer->phone,
                        ],
                    ];
                })->toArray();
                session()->flash('print_tasks_data', $tasksWithRelations);
            }
        }
        
        // Trigger Customer Analytics Recalculation
        try {
            if ($customerId) {
                $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                $analyticsService->recalculateCustomerAnalytics($customerId);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update customer analytics after batch task creation', ['error' => $e->getMessage()]);
        }
        
        return redirect()->route('admin.design-tasks.create')
            ->with('success', $message);
    }
    
    /**
     * Get existing tasks for a customer (AJAX endpoint).
     */
    public function getCustomerTasks($customerId)
    {
        $tasks = DesignTask::forSaler(Auth::user())
            ->where('customer_id', $customerId)
            ->with(['designer', 'receptionist'])
            ->latest()
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'status_label' => $task->status_label,
                    'created_at' => $task->created_at->format('M d, Y'),
                    'show_url' => route('admin.design-tasks.show', $task),
                ];
            });
        
        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(DesignTask $designTask): View
    {
        $this->authorizeTaskAccess($designTask);
        
        $customers = Customer::forSaler(Auth::user())->verified()->orderBy('name')->get();
        $designers = User::where('role', 'designer')->where('verified', true)->viewableStaff()->get();
        $operators = User::where('role', 'operator')->where('verified', true)->viewableStaff()->get();
        $salers = User::where('role', 'saler')->where('verified', true)->orderBy('name')->get();
        $departments = \App\Models\Department::all();
        $taskTypes = \App\Models\DesignTaskType::orderBy('name')->get();

        // Fix for legacy tasks: If rate is 0 but price is set, calculate rate derived from price/qty
        if ((!$designTask->rate || $designTask->rate == 0) && $designTask->price > 0 && $designTask->qty > 0) {
            $designTask->rate = $designTask->price / $designTask->qty;
        }
        
        return view('admin.design-tasks.edit', compact('designTask', 'customers', 'designers', 'operators', 'salers', 'departments', 'taskTypes'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'designer_instructions' => 'nullable|string',
            'priority' => 'required|in:1,3,5',
            'designer_id' => 'nullable|exists:users,id',
            'operator_id' => 'nullable|exists:users,id',
            'saler_id' => 'nullable|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'qty' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'design_task_type_id' => 'nullable|exists:design_task_types,id',
            'delivery_cost' => 'nullable|numeric|min:0',
            'delivery_discount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'edit_reason' => 'required|string|min:5|max:1000',
        ]);

        $oldValues = $designTask->getOriginal();
        
        // Calculate new balance if price/qty/rate changed
        $qty = floatval($validated['qty']);
        $rate = floatval($validated['rate']);
        $deliveryCost = floatval($validated['delivery_cost'] ?? 0);
        $deliveryDiscount = floatval($validated['delivery_discount'] ?? 0);
        $basePrice = $qty * $rate;
        $taskPriceWithVat = $designTask->requires_receipt ? ($basePrice * 1.18) + $deliveryCost - $deliveryDiscount : $basePrice + $deliveryCost - $deliveryDiscount;
        
        $designTask->fill($validated);
        $designTask->price = $basePrice;
        $designTask->balance = $taskPriceWithVat - $designTask->amount_paid;
        
        $changes = [];
        foreach ($designTask->getDirty() as $field => $newValue) {
            if ($field === 'edit_reason') continue;
            $oldValue = $oldValues[$field] ?? 'N/A';
            $changes[] = ucfirst(str_replace('_', ' ', $field)) . " changed from '{$oldValue}' to '{$newValue}'";
        }

        $designTask->save();

        // Create task update record for history
        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_TASK_EDITED,
            'content' => "Task edited. Reason: " . $validated['edit_reason'] . "\n\nChanges:\n" . implode("\n", $changes),
            'metadata' => [
                'reason' => $validated['edit_reason'],
                'changes' => $changes
            ],
        ]);

        // Trigger Customer Analytics Recalculation
        try {
            $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
            $analyticsService->recalculateCustomerAnalytics($designTask->customer_id);
        } catch (\Exception $e) {
            \Log::error('Failed to update customer analytics on design task update', ['error' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(DesignTask $designTask): View
    {
        $this->authorizeTaskAccess($designTask);
        
        $designTask->load(['customer', 'receptionist', 'designer', 'saler', 'operator', 'delivery', 'department', 'recordedBy', 'updates.admin']);
        $user = Auth::user();
        $templates = \App\Models\MessageTemplate::active()->select('id', 'title')->get();
        $operators = \App\Models\User::where('role', 'operator')->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        
        $departments = \App\Models\Department::all();
        $salers = \App\Models\User::where('role', 'saler')->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $all_designers = \App\Models\User::where('role', 'designer')->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        
        return view('admin.design-tasks.show', compact('designTask', 'user', 'templates', 'operators', 'departments', 'salers', 'all_designers'));
    }

    /**
     * Assign task to a designer.
     */
    public function assign(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask, 'receptionist');

        $validated = $request->validate([
            'designer_id' => 'nullable|exists:users,id',
            'operator_id' => 'nullable|exists:users,id',
        ]);

        if (!$validated['designer_id'] && !$validated['operator_id']) {
            return redirect()->back()->with('error', 'Please select a designer or an operator.');
        }

        $updateData = [];
        $assignedNames = [];

        if ($validated['designer_id']) {
            $updateData['designer_id'] = $validated['designer_id'];
            $designer = User::find($validated['designer_id']);
            $assignedNames[] = "Designer ({$designer->name})";
        }

        if ($validated['operator_id']) {
            $updateData['operator_id'] = $validated['operator_id'];
            $operator = User::find($validated['operator_id']);
            $assignedNames[] = "Operator ({$operator->name})";
        }

        $updateData['status'] = DesignTask::STATUS_IN_PROGRESS;

        $designTask->update($updateData);

        // Refresh the model to load relationships
        $designTask->refresh();
        $designTask->load(['designer', 'operator']);

        $assignmentMessage = "Task assigned to: " . implode(' and ', $assignedNames);

        // Create task update
        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_STATUS_UPDATE,
            'content' => $assignmentMessage,
            'metadata' => [
                'from' => 'pending',
                'to' => 'in_progress',
            ],
        ]);

        // Notify the designer
        if ($designTask->designer) {
            try {
                $designTask->designer->notify(new TaskAssignedNotification($designTask));
            } catch (\Exception $e) {
                \Log::error('Failed to send TaskAssignedNotification: ' . $e->getMessage());
            }
        }

        // Notify operators and admins about the assignment
        $staffToNotify = User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
        foreach ($staffToNotify as $staff) {
            try {
                $staff->notify(new TaskStatusUpdatedNotification($designTask, Auth::user(), 'pending'));
            } catch (\Exception $e) {
                \Log::error('Failed to send TaskStatusUpdatedNotification (Assignment): ' . $e->getMessage());
            }
        }

        // Send SMS notification to customer
        try {
            $designTask->load('customer');
            $smsService = app(SmsApiService::class);
            if ($designTask->customer && $designTask->customer->phone) {
                $smsService->sendTaskNotification($designTask->customer, $designTask, 'assigned');
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send SMS notification for task assignment: ' . $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Task assigned successfully.');
    }

    /**
     * Assign task to a delivery person.
     */
    public function assignDelivery(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask, 'receptionist');

        if ($designTask->delivery_status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot assign delivery to an already delivered task.');
        }

        $validated = $request->validate([
            'delivery_method' => 'required|in:pickup,delivery',
            'delivery_id' => 'required_if:delivery_method,delivery|nullable|exists:users,id',
            'delivery_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'delivery_method' => $validated['delivery_method'],
            'delivery_notes' => $validated['delivery_notes'],
            'delivery_status' => $validated['delivery_method'] === 'pickup' ? 'ready_for_pickup' : 'assigned',
        ];

        if ($validated['delivery_method'] === 'delivery') {
            $updateData['delivery_id'] = $validated['delivery_id'];
        }

        $designTask->update($updateData);

        $deliveryName = 'None (Pickup)';
        $deliveryUser = null;

        if ($validated['delivery_method'] === 'delivery') {
            $deliveryUser = User::find($validated['delivery_id']);
            $deliveryName = $deliveryUser ? $deliveryUser->name : 'Unknown';
        }

        // Create task update
        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_STATUS_UPDATE,
            'content' => $validated['delivery_method'] === 'pickup' 
                ? "Task marked as Ready for Pickup. Note: " . ($validated['delivery_notes'] ?? 'N/A')
                : "Task assigned to delivery person: {$deliveryName}. Note: " . ($validated['delivery_notes'] ?? 'N/A'),
        ]);

        // Notify the delivery person
        if ($deliveryUser) {
            try {
                $deliveryUser->notify(new DeliveryTaskAssignedNotification($designTask));
            } catch (\Exception $e) {
                \Log::error('Failed to send DeliveryTaskAssignedNotification: ' . $e->getMessage());
            }
        }

        // Notify customer about delivery assignment
        try {
            $designTask->load('customer');
            $smsService = app(SmsApiService::class);
            if ($designTask->customer && $designTask->customer->phone) {
                // If it's assigned to delivery, it usually means it's out for delivery or scheduled
                // We can use a specific 'delivery_assigned' status key if we defined it in SmsApiService
                // For now, let's assume we added it or fallback to 'status_changed'
                $smsService->sendTaskNotification($designTask->customer, $designTask, 'delivery_assigned');
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send SMS notification for delivery assignment: ' . $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Task assigned to delivery person successfully.');
    }

    /**
     * Update delivery status.
     */
    public function updateDeliveryStatus(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask, 'delivery');

        if ($designTask->delivery_status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot update status of an already delivered task.');
        }

        $validated = $request->validate([
            'delivery_status' => 'required|string',
            'delivery_notes' => 'nullable|string',
            'delivery_payment_method' => 'nullable|required_if:delivery_status,delivered|string|in:cash,mobile,bank,card',
        ]);

        $updates = [
            'delivery_status' => $validated['delivery_status'],
            'delivery_notes' => $validated['delivery_notes'],
        ];

        if ($validated['delivery_status'] === 'delivered') {
            $updates['delivered_at'] = now();
            if (isset($validated['delivery_payment_method'])) {
                $updates['delivery_payment_method'] = $validated['delivery_payment_method'];
            }
            // Sync overall task status to delivered
            $updates['status'] = DesignTask::STATUS_DELIVERED;
        }

        $designTask->update($updates);

        $paymentInfo = '';
        if (isset($validated['delivery_payment_method'])) {
            $paymentInfo = ". Payment: " . ucfirst($validated['delivery_payment_method']);
        }

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_STATUS_UPDATE,
            'content' => "Delivery status updated to: {$validated['delivery_status']}. Notes: " . ($validated['delivery_notes'] ?? 'None') . $paymentInfo,
        ]);

        // Notify customer about delivery status update (e.g. delivered)
        if ($validated['delivery_status'] === 'delivered') {
            try {
                $designTask->load('customer');
                $smsService = app(SmsApiService::class);
                if ($designTask->customer && $designTask->customer->phone) {
                    $smsService->sendTaskNotification($designTask->customer, $designTask, 'delivered');
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send SMS notification for delivery update: ' . $e->getMessage());
            }
        }

        // Notify admins and receptionist about the delivery update
        $staffToNotify = User::whereIn('role', ['receptionist', 'admin', 'super_admin', 'manager', 'operator', 'accountant'])->get();
        
        if ($designTask->delivery_id && $designTask->delivery_id !== Auth::id()) {
            $deliveryMan = User::find($designTask->delivery_id);
            if ($deliveryMan && !$staffToNotify->contains($deliveryMan->id)) {
                $staffToNotify->push($deliveryMan);
            }
        }

        foreach ($staffToNotify as $staff) {
            try {
                $staff->notify(new DeliveryTaskUpdatedNotification($designTask, Auth::user()));
            } catch (\Exception $e) {
                \Log::error('Failed to send DeliveryTaskUpdatedNotification: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Delivery status updated successfully.');
    }

    /**
     * Mark a task as a loss (incorrect work).
     */
    public function markAsLoss(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask, 'receptionist');

        $validated = $request->validate([
            'loss_reason' => 'required|string|max:1000',
            'loss_amount' => 'nullable|numeric|min:0',
        ]);

        $designTask->update([
            'is_loss' => true,
            'loss_reason' => $validated['loss_reason'],
            'loss_amount' => $validated['loss_amount'] ?? $designTask->price,
            'loss_recorded_at' => now(),
            'loss_recorded_by' => Auth::id(),
        ]);

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_COMMENT,
            'content' => "Task marked as LOSS. Reason: {$validated['loss_reason']}. Amount: TZS " . number_format($validated['loss_amount'] ?? $designTask->price),
        ]);

        // Audit log
        AuditLogService::log('updated', 'Task marked as loss: ' . $designTask->title, $designTask);

        return redirect()->back()->with('success', 'Task has been recorded as a loss.');
    }

    /**
     * Cancel a task.
     */
    public function cancel(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask, 'receptionist');

        if (in_array($designTask->status, [DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_DELIVERED])) {
            return redirect()->back()->with('error', 'Cannot cancel a closed or delivered task.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        $designTask->update([
            'status' => DesignTask::STATUS_CANCELLED,
        ]);

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_COMMENT,
            'content' => "Task CANCELLED. Reason: {$validated['cancel_reason']}",
        ]);

        // Audit log
        AuditLogService::log('updated', 'Task cancelled: ' . $designTask->title, $designTask);

        return redirect()->back()->with('success', 'Task has been cancelled.');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask);

        if ($designTask->delivery_status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot update status of an already delivered task.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,in_review,completed,confirmed,printing,printed,super_completed,rejected',
            'operator_id' => 'nullable|exists:users,id',
        ]);

        $oldStatus = $designTask->status;
        $oldValues = $designTask->only(['status', 'completed_at']);
        
        // If status is super_completed or printed (approaching final stages), set completed_at
        $isFinishing = in_array($validated['status'], [DesignTask::STATUS_PRINTED, DesignTask::STATUS_SUPER_COMPLETED]);
        
        $designTask->status = $validated['status'];
        $designTask->completed_at = $isFinishing ? ($designTask->completed_at ?: now()) : $designTask->completed_at;
        
        // If status is moving to super_completed, mark as ready for pickup and generate code if not already set
        // If status is moving to super_completed, auto-assign to delivery pool or mark as ready for pickup
        if ($validated['status'] === DesignTask::STATUS_SUPER_COMPLETED) {
            if ($designTask->delivery_method === 'delivery') {
                $designTask->delivery_status = 'assigned'; // Auto-move to Boda
            } else {
                $designTask->delivery_status = 'ready_for_pickup';
            }
            
            if (!$designTask->pickup_code) {
                $designTask->pickup_code = str_pad(rand(1111, 9999), 4, '0', STR_PAD_LEFT);
                \Log::info("Generated pickup code {$designTask->pickup_code} for task {$designTask->id}");
            }
        }
        
        if (isset($validated['operator_id'])) {
            $designTask->operator_id = $validated['operator_id'];
        }

        $designTask->save();

        // Trigger Customer Analytics Recalculation
        try {
            if ($designTask->customer_id) {
                $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                $analyticsService->recalculateCustomerAnalytics($designTask->customer_id);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update customer analytics on design task status update', ['error' => $e->getMessage()]);
        }

        // Refresh to get updated status_label
        $designTask->refresh();

        // Log audit for status change
        try {
            AuditLogService::updated($designTask, $oldValues, 'Changed design task status: ' . $designTask->title . ' from ' . $oldStatus . ' to ' . $validated['status']);
        } catch (\Exception $e) {
            \Log::warning('Failed to log audit for status change: ' . $e->getMessage());
        }

        // Create task update
        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_STATUS_UPDATE,
            'content' => "Status changed from {$oldStatus} to {$validated['status']}",
            'metadata' => [
                'from' => $oldStatus,
                'to' => $validated['status'],
            ],
        ]);

        // Notify relevant staff about the status change
        $staffToNotify = User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
        
        // Also notify the receptionist if they aren't already in the list
        $receptionist = User::find($designTask->receptionist_id);
        if ($receptionist && !$staffToNotify->contains($receptionist->id)) {
            $staffToNotify->push($receptionist);
        }
        
        // Also notify the designer if they aren't already in the list and didn't make the change
        if ($designTask->designer_id && $designTask->designer_id !== Auth::id()) {
            $designer = User::find($designTask->designer_id);
            if ($designer && !$staffToNotify->contains($designer->id)) {
                $staffToNotify->push($designer);
            }
        }

        // Also notify the saler if they are assigned to the task
        if ($designTask->saler_id) {
            $saler = User::find($designTask->saler_id);
            if ($saler && !$staffToNotify->contains($saler->id)) {
                $staffToNotify->push($saler);
            }
        }

        if ($designTask->delivery_id && $designTask->delivery_id !== Auth::id()) {
            $deliveryMan = User::find($designTask->delivery_id);
            if ($deliveryMan && !$staffToNotify->contains($deliveryMan->id)) {
                $staffToNotify->push($deliveryMan);
            }
        }

        foreach ($staffToNotify as $staff) {
            if ($staff->id !== Auth::id()) { // Don't notify the person who made the change
                try {
                    $staff->notify(new TaskStatusUpdatedNotification($designTask, Auth::user(), $oldStatus));
                } catch (\Exception $e) {
                    \Log::error('Failed to send TaskStatusUpdatedNotification (Status Change): ' . $e->getMessage());
                }
            }
        }

        // Send SMS notification to customer based on status and user role
        // Designers should NOT trigger SMS notifications
        $user = Auth::user();
        $isDesigner = $user->role === 'designer';
        
        if (!$isDesigner) {
            try {
                $designTask->load('customer');
                $smsService = app(SmsApiService::class);
                if ($designTask->customer && $designTask->customer->phone) {
                    $statusKey = $validated['status'];
                    
                    if ($statusKey === 'in_progress') {
                        $smsService->sendTaskNotification($designTask->customer, $designTask, 'in_progress');
                    } elseif ($statusKey === 'super_completed') {
                        // Only send completion SMS on SUPER COMPLETED
                        $smsService->sendTaskCompletion($designTask);
                    } elseif (in_array($statusKey, ['pending', 'rejected'])) {
                        $smsService->sendTaskNotification($designTask->customer, $designTask, 'status_changed');
                    }
                    // 'completed' and 'confirmed' are intermediate and silent
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send SMS notification for status change: ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Task status updated successfully.');
    }

    /**
     * Add comment/feedback to task.
     */
    public function addComment(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_COMMENT,
            'content' => $validated['content'],
        ]);

        // Notify relevant staff about the new comment
        $staffToNotify = User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
        
        $receptionist = User::find($designTask->receptionist_id);
        if ($receptionist && !$staffToNotify->contains($receptionist->id)) {
            $staffToNotify->push($receptionist);
        }
        
        if ($designTask->designer_id) {
            $designer = User::find($designTask->designer_id);
            if ($designer && !$staffToNotify->contains($designer->id)) {
                $staffToNotify->push($designer);
            }
        }
        
        if ($designTask->delivery_id) {
            $deliveryMan = User::find($designTask->delivery_id);
            if ($deliveryMan && !$staffToNotify->contains($deliveryMan->id)) {
                $staffToNotify->push($deliveryMan);
            }
        }

        foreach ($staffToNotify as $staff) {
            if ($staff->id !== Auth::id()) {
                try {
                    $staff->notify(new TaskCommentedNotification($designTask, Auth::user(), $validated['content']));
                } catch (\Exception $e) {
                    \Log::error('Failed to send TaskCommentedNotification: ' . $e->getMessage());
                }
            }
        }

        return redirect()->back()
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Display paid design tasks.
     */
    public function paid()
    {
        $user = Auth::user();
        $query = DesignTask::with(['customer', 'receptionist', 'designer', 'saler'])
            ->where('balance', '<=', 0)
            ->where('price', '>', 0);

        // Filter based on role
        if ($user->role === 'receptionist') {
            $query->where('receptionist_id', $user->id);
        } elseif ($user->role === 'designer') {
            $query->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $query->where('saler_id', $user->id);
        }
        // Operators, Super admin, admin, manager can see all tasks

        // Apply Search (Title or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // Apply Filters (Designer or Receptionist)
        if (request()->filled('designer_id')) {
            $query->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $query->where('receptionist_id', request('receptionist_id'));
        }

        // Filter by date period
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            
            if ($period === 'today') {
                $query->whereDate('created_at', $now->today());
            } elseif ($period === 'yesterday') {
                $query->whereDate('created_at', $now->subDay()->toDateString());
            } elseif ($period === 'week') {
                $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($period === 'last_week') {
                 $query->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            } elseif ($period === 'quarter') {
                $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            } elseif ($period === 'half_year') {
                $query->whereBetween('created_at', [$now->subMonths(6), $now]);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', $now->year);
            }
        }
        
        // Custom Date Range
        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
            
        $tasks = $query->latest()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        
        // Calculate stats (respecting all filters except status)
        $statsBaseQuery = DesignTask::where('balance', '<=', 0)->where('price', '>', 0);
        
        if ($user->role === 'designer') {
            $statsBaseQuery->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $statsBaseQuery->where('saler_id', $user->id);
        }
        
        if (request()->filled('search')) {
            $search = request('search');
            $statsBaseQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if (request()->filled('designer_id')) {
            $statsBaseQuery->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $statsBaseQuery->where('receptionist_id', request('receptionist_id'));
        }
        
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            if ($period === 'today') $statsBaseQuery->whereDate('created_at', $now->today());
            elseif ($period === 'yesterday') $statsBaseQuery->whereDate('created_at', $now->subDay()->toDateString());
            elseif ($period === 'week') $statsBaseQuery->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            elseif ($period === 'last_week') $statsBaseQuery->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            elseif ($period === 'month') $statsBaseQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            elseif ($period === 'quarter') $statsBaseQuery->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            elseif ($period === 'half_year') $statsBaseQuery->whereBetween('created_at', [$now->subMonths(6), $now]);
            elseif ($period === 'year') $statsBaseQuery->whereYear('created_at', $now->year);
        }
        if (request()->filled('date_from')) $statsBaseQuery->whereDate('created_at', '>=', request('date_from'));
        if (request()->filled('date_to')) $statsBaseQuery->whereDate('created_at', '<=', request('date_to'));

        $taskStats = [
            'total' => (clone $statsBaseQuery)->count(),
            'pending' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PENDING)->count(),
            'in_progress' => (clone $statsBaseQuery)->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_CONFIRMED])->count(),
            'in_review' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_IN_REVIEW)->count(),
            'printing' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTING)->count(),
            'printed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTED)->count(),
            'completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_COMPLETED)->count(),
            'super_completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_SUPER_COMPLETED)->count(),
            'delivered' => (clone $statsBaseQuery)->where('delivery_status', 'delivered')->count(),
            'rejected' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_REJECTED)->count(),
        ];
        
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $taskStats['revenue'] = (clone $statsBaseQuery)->sum('amount_paid');
        }
        
        $statuses = DesignTask::getStatusOptions();
        
        $all_designers = User::whereIn('role', ['designer', 'operator'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $all_receptionists = User::whereIn('role', ['receptionist', 'operator', 'admin', 'super_admin', 'accountant'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $templates = \App\Models\MessageTemplate::active()->select('id', 'title')->get();
        $salers = User::where('role', 'saler')->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $departments = \App\Models\Department::all();
        $viewType = 'paid';

        return view('admin.design-tasks.index', compact('tasks', 'statuses', 'user', 'all_designers', 'all_receptionists', 'templates', 'all_delivery', 'taskStats', 'salers', 'departments', 'viewType'));
    }

    /**
     * Display pending/partial payment design tasks.
     */
    public function pending()
    {
        $user = Auth::user();
        $query = DesignTask::with(['customer', 'receptionist', 'designer', 'saler'])
            ->where('balance', '>', 0);

        // Filter based on role
        if ($user->role === 'receptionist') {
            $query->where('receptionist_id', $user->id);
        } elseif ($user->role === 'designer') {
            $query->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $query->where('saler_id', $user->id);
        }
        // Operators, Super admin, admin, manager can see all tasks

        // Apply Search (Title or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // Apply Filters (Designer or Receptionist)
        if (request()->filled('designer_id')) {
            $query->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $query->where('receptionist_id', request('receptionist_id'));
        }

        // Filter by date period
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            
            if ($period === 'today') {
                $query->whereDate('created_at', $now->today());
            } elseif ($period === 'yesterday') {
                $query->whereDate('created_at', $now->subDay()->toDateString());
            } elseif ($period === 'week') {
                $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($period === 'last_week') {
                 $query->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            } elseif ($period === 'quarter') {
                $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            } elseif ($period === 'half_year') {
                $query->whereBetween('created_at', [$now->subMonths(6), $now]);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', $now->year);
            }
        }
        
        // Custom Date Range
        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
            
        $tasks = $query->latest()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        
        // Calculate stats
        $statsBaseQuery = DesignTask::where('balance', '>', 0);
        
        if ($user->role === 'designer') {
            $statsBaseQuery->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $statsBaseQuery->where('saler_id', $user->id);
        }
        
        if (request()->filled('search')) {
            $search = request('search');
            $statsBaseQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if (request()->filled('designer_id')) {
            $statsBaseQuery->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $statsBaseQuery->where('receptionist_id', request('receptionist_id'));
        }
        
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            if ($period === 'today') $statsBaseQuery->whereDate('created_at', $now->today());
            elseif ($period === 'yesterday') $statsBaseQuery->whereDate('created_at', $now->subDay()->toDateString());
            elseif ($period === 'week') $statsBaseQuery->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            elseif ($period === 'last_week') $statsBaseQuery->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            elseif ($period === 'month') $statsBaseQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            elseif ($period === 'quarter') $statsBaseQuery->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            elseif ($period === 'half_year') $statsBaseQuery->whereBetween('created_at', [$now->subMonths(6), $now]);
            elseif ($period === 'year') $statsBaseQuery->whereYear('created_at', $now->year);
        }
        if (request()->filled('date_from')) $statsBaseQuery->whereDate('created_at', '>=', request('date_from'));
        if (request()->filled('date_to')) $statsBaseQuery->whereDate('created_at', '<=', request('date_to'));

        $taskStats = [
            'total' => (clone $statsBaseQuery)->count(),
            'pending' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PENDING)->count(),
            'in_progress' => (clone $statsBaseQuery)->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_CONFIRMED])->count(),
            'in_review' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_IN_REVIEW)->count(),
            'printing' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTING)->count(),
            'printed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTED)->count(),
            'completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_COMPLETED)->count(),
            'super_completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_SUPER_COMPLETED)->count(),
            'delivered' => (clone $statsBaseQuery)->where('delivery_status', 'delivered')->count(),
            'rejected' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_REJECTED)->count(),
        ];
        
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $taskStats['revenue'] = (clone $statsBaseQuery)->sum('amount_paid');
        }
        
        $statuses = DesignTask::getStatusOptions();
        
        $all_designers = User::whereIn('role', ['designer', 'operator'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $all_receptionists = User::whereIn('role', ['receptionist', 'operator', 'admin', 'super_admin', 'accountant'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $templates = \App\Models\MessageTemplate::active()->select('id', 'title')->get();
        $salers = User::where('role', 'saler')->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $departments = \App\Models\Department::all();
        $viewType = 'pending';

        return view('admin.design-tasks.index', compact('tasks', 'statuses', 'user', 'all_designers', 'all_receptionists', 'templates', 'all_delivery', 'taskStats', 'salers', 'departments', 'viewType'));
    }

    /**
     * Display all design task invoices.
     */
    public function invoices()
    {
        $user = Auth::user();
        $query = DesignTask::with(['customer', 'receptionist', 'designer', 'saler']);

        // Filter based on role
        if ($user->role === 'receptionist') {
            $query->where('receptionist_id', $user->id);
        } elseif ($user->role === 'designer') {
            $query->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $query->where('saler_id', $user->id);
        }
        // Operators, Super admin, admin, manager can see all tasks

        // Apply Search (Title or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // Apply Filters (Designer or Receptionist)
        if (request()->filled('designer_id')) {
            $query->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $query->where('receptionist_id', request('receptionist_id'));
        }

        // Filter by date period
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            
            if ($period === 'today') {
                $query->whereDate('created_at', $now->today());
            } elseif ($period === 'yesterday') {
                $query->whereDate('created_at', $now->subDay()->toDateString());
            } elseif ($period === 'week') {
                $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($period === 'last_week') {
                 $query->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            } elseif ($period === 'quarter') {
                $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            } elseif ($period === 'half_year') {
                $query->whereBetween('created_at', [$now->subMonths(6), $now]);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', $now->year);
            }
        }
        
        // Custom Date Range
        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
            
        $tasks = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Calculate stats
        $statsBaseQuery = DesignTask::query();
        
        if ($user->role === 'designer') {
            $statsBaseQuery->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $statsBaseQuery->where('saler_id', $user->id);
        }
        
        if (request()->filled('search')) {
            $search = request('search');
            $statsBaseQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if (request()->filled('designer_id')) {
            $statsBaseQuery->where('designer_id', request('designer_id'));
        }
        if (request()->filled('receptionist_id')) {
            $statsBaseQuery->where('receptionist_id', request('receptionist_id'));
        }
        
        if (request()->filled('period')) {
            $period = request('period');
            $now = now();
            if ($period === 'today') $statsBaseQuery->whereDate('created_at', $now->today());
            elseif ($period === 'yesterday') $statsBaseQuery->whereDate('created_at', $now->subDay()->toDateString());
            elseif ($period === 'week') $statsBaseQuery->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            elseif ($period === 'last_week') $statsBaseQuery->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            elseif ($period === 'month') $statsBaseQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            elseif ($period === 'quarter') $statsBaseQuery->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            elseif ($period === 'half_year') $statsBaseQuery->whereBetween('created_at', [$now->subMonths(6), $now]);
            elseif ($period === 'year') $statsBaseQuery->whereYear('created_at', $now->year);
        }
        if (request()->filled('date_from')) $statsBaseQuery->whereDate('created_at', '>=', request('date_from'));
        if (request()->filled('date_to')) $statsBaseQuery->whereDate('created_at', '<=', request('date_to'));

        $taskStats = [
            'total' => (clone $statsBaseQuery)->count(),
            'pending' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PENDING)->count(),
            'in_progress' => (clone $statsBaseQuery)->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_CONFIRMED])->count(),
            'in_review' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_IN_REVIEW)->count(),
            'printing' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTING)->count(),
            'printed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_PRINTED)->count(),
            'completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_COMPLETED)->count(),
            'super_completed' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_SUPER_COMPLETED)->count(),
            'delivered' => (clone $statsBaseQuery)->where('delivery_status', 'delivered')->count(),
            'rejected' => (clone $statsBaseQuery)->where('status', DesignTask::STATUS_REJECTED)->count(),
        ];
        
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $taskStats['revenue'] = (clone $statsBaseQuery)->sum('amount_paid');
        }
        
        $statuses = DesignTask::getStatusOptions();
        
        $all_designers = User::whereIn('role', ['designer', 'operator'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $all_receptionists = User::whereIn('role', ['receptionist', 'operator', 'admin', 'super_admin', 'accountant'])->where('verified', true)->orderBy('name')->select('id', 'name')->get();
        $templates = \App\Models\MessageTemplate::active()->select('id', 'title')->get();
        $all_delivery = User::where('role', 'delivery')->where('verified', true)->orderBy('name')->select('id', 'name')->get();

        return view('admin.design-tasks.invoices', compact('tasks', 'statuses', 'user', 'all_designers', 'all_receptionists', 'templates', 'all_delivery', 'taskStats'));
    }

    /**
     * Get design task data for invoice modal.
     */
    public function getInvoiceData($id)
    {
        $task = DesignTask::with(['customer', 'receptionist', 'designer', 'latestUpdate'])
            ->findOrFail($id);
            
        $this->authorizeTaskAccess($task);

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'priority_label' => $task->priority_label,
            'deadline' => $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline',
            'amount_paid' => $task->amount_paid,
            'price' => $task->price,
            'balance' => $task->balance,
            'requires_receipt' => $task->requires_receipt,
            'created_at' => $task->created_at->format('M d, Y'),
            'customer' => [
                'id' => $task->customer_id,
                'name' => $task->customer->name ?? 'N/A',
                'phone' => $task->customer->phone ?? 'N/A',
                'address' => $task->customer->address ?? 'N/A',
            ],
            'designer' => $task->designer->name ?? 'Unassigned',
            'receptionist' => $task->receptionist->name ?? 'N/A',
            'description' => $task->description,
            'designer_instructions' => $task->designer_instructions,
        ]);
    }

    public function getTaskData($id)
    {
        $task = DesignTask::with(['customer', 'receptionist', 'designer', 'latestUpdate'])
            ->findOrFail($id);
            
        $this->authorizeTaskAccess($task);

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'deadline' => $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : null,
            'qty' => $task->qty,
            'rate' => $task->rate,
            'department_id' => $task->department_id,
            'saler_id' => $task->saler_id,
            'designer_id' => $task->designer_id,
            'operator_id' => $task->operator_id,
            'description' => $task->description,
            'designer_instructions' => $task->designer_instructions,
            'task_code' => $task->task_code,
            'status_label' => $task->status_label,
            'delivery_cost' => $task->delivery_cost ?? 0,
            'delivery_discount' => $task->delivery_discount ?? 0,
        ]);
    }


    /**
     * Display printable invoice for a design task.
     */
    public function printInvoice(DesignTask $designTask): View
    {
        $this->authorizeTaskAccess($designTask);
        $designTask->load(['customer', 'receptionist', 'designer']);
        
        $tasks = collect([$designTask]);
        $customer = $designTask->customer;
        $isSingle = true;
        
        return view('admin.design-tasks.print-invoice', compact('tasks', 'customer', 'isSingle'));
    }

    /**
     * Display printable invoice for all customer tasks.
     */
    public function printCustomerInvoice($customerId): View
    {
        $customer = Customer::findOrFail($customerId);
        $tasks = DesignTask::where('customer_id', $customerId)
            ->with(['receptionist', 'designer'])
            ->latest()
            ->get();

        if ($tasks->isEmpty()) {
            abort(404, 'No tasks found for this customer.');
        }

        // Authorize access (check if user can access at least one task of this customer)
        $this->authorizeTaskAccess($tasks->first());

        $isSingle = false;
        
        return view('admin.design-tasks.print-invoice', compact('tasks', 'customer', 'isSingle'));
    }

    /**
     * Display printable invoice for filtered tasks.
     */
    public function printFiltered(Request $request): View
    {
        $user = Auth::user();
        $query = DesignTask::with(['customer', 'receptionist', 'designer'])
            ->latest();

        // Apply exactly the same filters as the index/listing pages
        if ($user->role === 'receptionist') {
            $query->where('receptionist_id', $user->id);
        } elseif ($user->role === 'designer') {
            $query->where('designer_id', $user->id);
        } elseif ($user->role === 'saler') {
            $query->where('saler_id', $user->id);
        } elseif ($user->role === 'operator') {
            $query->where(function($q) use ($user) {
                $q->where('operator_id', $user->id)
                  ->orWhere('receptionist_id', $user->id)
                  ->orWhere('designer_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('receptionist_id')) {
            $query->where('receptionist_id', $request->receptionist_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by delivery status (tasks needing delivery)
        if ($request->filled('delivery_filter')) {
            if ($request->delivery_filter === 'pending') {
                $query->where('status', 'super_completed')
                      ->where(function($q) {
                          $q->whereNull('delivery_status')
                            ->orWhere('delivery_status', '!=', 'delivered');
                      });
            } elseif ($request->delivery_filter === 'delivered') {
                $query->where('delivery_status', 'delivered');
            }
        }

        // Custom balance filter (for pending/paid views)
        if ($request->filled('type')) {
            if ($request->type === 'pending') {
                $query->where('balance', '>', 0);
            } elseif ($request->type === 'paid') {
                $query->where('balance', '<=', 0);
            }
        }

        if ($request->filled('period')) {
            $period = $request->period;
            $now = now();
            if ($period === 'today') $query->whereDate('created_at', $now->today());
            elseif ($period === 'yesterday') $query->whereDate('created_at', $now->subDay()->toDateString());
            elseif ($period === 'week') $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
            elseif ($period === 'last_week') $query->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
            elseif ($period === 'month') $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            elseif ($period === 'quarter') $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
            elseif ($period === 'half_year') $query->whereBetween('created_at', [$now->subMonths(6), $now]);
            elseif ($period === 'year') $query->whereYear('created_at', $now->year);
        }
        
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        $tasks = $query->get();
        
        if ($tasks->count() === 0) {
            abort(404, 'No tasks found for current filters.');
        }

        // Use a generic customer object for the title if it's a mixed result
        $customer = (object)[
            'name' => 'Filtered Results',
            'phone' => 'Multiple',
            'email' => 'Multiple',
            'address' => 'N/A'
        ];
        
        // If it's filtered for only ONE customer, use their name
        if ($tasks->pluck('customer_id')->unique()->count() === 1) {
            $customer = $tasks->first()->customer;
        }

        $isSingle = false;

        return view('admin.design-tasks.print-invoice', compact('tasks', 'customer', 'isSingle'));
    }

    /**
     * Update payment for a design task.
     */
    public function updatePayment(Request $request, DesignTask $designTask): RedirectResponse
    {
        $this->authorizeTaskAccess($designTask);
        
        // Restriction: allow accounting/admin and receptionist/operator to enter payments.
        if (!in_array(Auth::user()->role, ['accountant', 'admin', 'super_admin', 'manager', 'receptionist', 'operator'])) {
            abort(403, 'Only accounting staff and administrators can update payments.');
        }

        if ($designTask->delivery_status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot update payment of an already delivered task.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            // Some screens use `notes`, others use `note`
            'note' => 'nullable|string',
            'notes' => 'nullable|string',
            // Optional additional cost while completing (adds to delivery_cost, with reason in history)
            'additional_cost' => 'nullable|numeric|min:0',
            'additional_cost_reason' => 'nullable|string|max:1000',
        ]);

        $newAmount = $request->amount;
        $note = $request->input('notes') ?? $request->input('note');

        $additionalCost = floatval($request->input('additional_cost') ?? 0);
        $additionalReason = trim((string) ($request->input('additional_cost_reason') ?? ''));

        if ($additionalCost > 0 && $additionalReason === '') {
            return redirect()->back()->withErrors([
                'additional_cost_reason' => 'Reason is required when additional cost is entered.'
            ])->withInput();
        }

        // Add the additional cost to the amount being paid so it clears it immediately without leaving a balance
        $newAmount += $additionalCost;

        // Add payment
        $designTask->amount_paid += $newAmount;

        // If the user entered an additional cost, add it to the delivery_cost so it affects the total/grand total.
        if ($additionalCost > 0) {
            $designTask->delivery_cost = floatval($designTask->delivery_cost ?? 0) + $additionalCost;
        }
        
        // Calculate total price with VAT if required (VAT only applies to base price)
        $basePrice = $designTask->requires_receipt ? $designTask->price * 1.18 : $designTask->price;
        $deliveryCost = floatval($designTask->delivery_cost ?? 0);
        $deliveryDiscount = floatval($designTask->delivery_discount ?? 0);
        $totalPrice = $basePrice + $deliveryCost - $deliveryDiscount;
        $designTask->balance = $totalPrice - $designTask->amount_paid;

        $designTask->save();

        // Record Payment in Finance module
        \App\Models\Payment::create([
            'design_task_id' => $designTask->id,
            'customer_id' => $designTask->customer_id,
            'amount' => $newAmount,
            'payment_method' => $request->payment_method,
            'date' => now(),
            'seller_id' => auth()->id(),
            'department_id' => $designTask->department_id,
            'notes' => $note,
            'is_debt' => true,
        ]);

        // Trigger Customer Analytics Recalculation
        try {
            if ($designTask->customer_id) {
                $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                $analyticsService->recalculateCustomerAnalytics($designTask->customer_id);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update customer analytics on design task payment', ['error' => $e->getMessage()]);
        }

        // Create task update for payment
        $content = "Payment received: TZS " . number_format($newAmount) . " via " . $request->payment_method . ".";
        if ($note) {
            $content .= " Note: " . $note . ".";
        }
        if ($additionalCost > 0) {
            $content .= " Additional cost: TZS " . number_format($additionalCost) . ". Reason: " . $additionalReason . ".";
        }

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => Auth::id(),
            'type' => TaskUpdate::TYPE_COMMENT,
            'content' => $content,
        ]);

        return redirect()->back()->with('success', 'Payment updated successfully!');
    }

    /**
     * Authorize task access based on role.
     */
    private function authorizeTaskAccess(DesignTask $task, ?string $requiredRole = null): void
    {
        $user = Auth::user();

        // Super admin and admin can access all
        if (in_array($user->role, ['super_admin', 'admin', 'manager', 'accountant', 'gatekeeper'])) {
            return;
        }

        // Receptionist and operator can access all tasks (for POS/reception management)
        if (in_array($user->role, ['receptionist', 'operator'])) {
            return;
        }

        // Designer can access tasks assigned to them
        if ($user->role === 'designer' && $task->designer_id === $user->id) {
            return;
        }

        // Saler can access tasks they created/assigned
        if ($user->role === 'saler' && $task->saler_id === $user->id) {
            return;
        }

        // Anyone assigned as delivery person can access
        if ($task->delivery_id === $user->id) {
            return;
        }

        // If specific role required, check it
        if ($requiredRole) {
            if ($requiredRole === 'designer' && ($user->role === 'designer' || $user->role === 'operator')) {
                if ($task->designer_id === $user->id) {
                    return;
                }
            } elseif ($user->role !== $requiredRole) {
                // Allow delivery person to update status if required role is delivery
                if ($requiredRole === 'delivery' && $task->delivery_id === $user->id) {
                    return;
                }
                abort(403, 'Unauthorized access.');
            }
        }

        // If we get here and no specific role was required, check if user has any access
        abort(403, 'Unauthorized access to this task.');
    }

    /**
     * Send delivery notification for super completed task
     */
    public function sendDeliveryNotification(DesignTask $designTask)
    {
        try {
            // Check if task is super completed
            if ($designTask->status !== 'super_completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Task must be super completed before sending delivery notification.'
                ], 400);
            }

            // Check if customer has phone number
            if (!$designTask->customer || !$designTask->customer->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer phone number not found.'
                ], 400);
            }

            // Send SMS notification
            $smsService = app(\App\Services\SmsApiService::class);
            
            $customerName = $designTask->customer->name;
            $taskTitle = $designTask->title;
            $taskCode = $designTask->task_code ?? '#' . $designTask->id;
            
            $message = "Hello {$customerName}!\n\n" .
                      "Good news! Your design task ({$taskCode}) is now ready for delivery/pickup.\n\n" .
                      "Task: {$taskTitle}\n\n" .
                      "Please contact us to arrange delivery or pickup.\n" .
                      "CHIBO BRANDS\n" .
                      "Tel: 0655392319";

            $result = $smsService->sendSMS($designTask->customer->phone, $message);

            if ($result['success']) {
                // Log the delivery notification
                \Log::info('Delivery notification sent', [
                    'task_id' => $designTask->id,
                    'customer_id' => $designTask->customer_id,
                    'phone' => $designTask->customer->phone
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Delivery notification sent successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send SMS notification.'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending delivery notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the notification.'
            ], 500);
        }
    }
}








