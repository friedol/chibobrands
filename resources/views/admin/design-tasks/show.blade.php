@extends('layouts.admin')

@section('title', 'Design Task #' . $designTask->task_code . ' - CHIBO BRAND')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --indigo-600: #4f46e5;
        }

        body {
            font-family: 'Outfit', 'Nunito Sans', sans-serif;
            background-color: #f1f5f9;
        }

        .animate-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .premium-card {
            background: white;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .card {
            border-radius: 10px !important;
            border: 1px solid var(--slate-100) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .task-lifecycle-item {
            position: relative;
            flex: 1;
            text-align: center;
            padding: 1rem 0.5rem;
        }

        .lifecycle-dot {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--slate-100);
            color: var(--slate-400);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            position: relative;
            z-index: 2;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .lifecycle-dot.active {
            background: var(--primary-gradient);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .lifecycle-dot.completed {
            background: #dcfce7;
            color: #16a34a;
            border-color: #bbf7d0;
        }

        .lifecycle-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .lifecycle-label.active {
            color: #16a34a;
        }

        .lifecycle-line {
            position: absolute;
            top: 30px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: var(--slate-100);
            z-index: 1;
        }

        .lifecycle-line.completed {
            background: #86efac;
        }

        .btn-premium-action {
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-premium-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
        }

        .btn-premium-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-premium-outline {
            background: white;
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
        }

        .btn-premium-outline:hover {
            background: var(--slate-50);
            border-color: var(--slate-300);
            transform: translateY(-1px);
        }

        .bento-sidebar-item {
            background: var(--slate-50);
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid var(--slate-100);
            margin-bottom: 1rem;
        }

        .activity-item {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .activity-item:hover {
            background: white;
            border-color: var(--slate-100);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
    </style>
    
    <div class="container-fluid px-4 py-4 animate-in">
        <!-- Breadcrumb & Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.design-tasks.index') }}" class="text-decoration-none text-muted">Design Tasks</a></li>
                    <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">{{ $designTask->task_code }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                @if($user->role === 'gatekeeper')
                    <a href="{{ route('gatekeeper.dashboard') }}" class="btn btn-premium-outline">
                        <i class="fas fa-grid-2 me-2"></i>Dashboard
                    </a>
                @elseif($user->role === 'delivery')
                    <a href="{{ $designTask->delivery_status === 'delivered' ? route('admin.delivery.completed') : route('admin.delivery.incoming') }}"
                        class="btn btn-premium-outline">
                        <i class="fas fa-truck-moving me-2"></i>My Jobs
                    </a>
                @else
                    <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-premium-outline">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                @endif
                
                @if(in_array($user->role, ['admin', 'super_admin', 'manager', 'receptionist', 'operator', 'accountant']) && $designTask->delivery_status !== 'delivered')
                    <div class="dropdown">
                        <button class="btn btn-premium-outline px-3" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
                            <li><a class="dropdown-item rounded-3 py-2" href="#" onclick="openEditTaskModal({{ $designTask->id }})"><i class="fas fa-edit me-2"></i> Edit Task</a></li>
                            @if($designTask->status !== 'cancelled')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger rounded-3 py-2" href="#" data-bs-toggle="modal" data-bs-target="#cancelTaskModal"><i class="fas fa-ban me-2"></i> Cancel Task</a></li>
                                <li><a class="dropdown-item text-warning rounded-3 py-2" href="#" data-bs-toggle="modal" data-bs-target="#markLossModal"><i class="fas fa-heart-crack me-2"></i> Mark as Loss</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Header Hero Section -->
        <div class="premium-card p-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0 !important;">
            <div class="row align-items-center g-4">
                <div class="col-md-auto">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-success" 
                         style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border: 2px solid rgba(34, 197, 94, 0.2);">
                        <i class="fas fa-paint-brush fs-2"></i>
                    </div>
                </div>
                <div class="col-md">
                    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <span class="status-badge" style="background: {{ [
                            'pending' => '#fef3c7',
                            'in_progress' => '#e0e7ff',
                            'in_review' => '#dcfce7',
                            'completed' => '#dcfce7',
                            'confirmed' => '#cffafe',
                            'printing' => '#fef9c3',
                            'printed' => '#ccfbf1',
                            'super_completed' => '#dcfce7',
                            'rejected' => '#fee2e2',
                            'cancelled' => '#f1f5f9'
                        ][$designTask->status] ?? '#f8fafc' }}; color: {{ [
                            'pending' => '#92400e',
                            'in_progress' => '#3730a3',
                            'in_review' => '#166534',
                            'completed' => '#166534',
                            'confirmed' => '#155e75',
                            'printing' => '#854d0e',
                            'printed' => '#115e59',
                            'super_completed' => '#166534',
                            'rejected' => '#991b1b',
                            'cancelled' => '#475569'
                        ][$designTask->status] ?? '#64748b' }};">
                            <i class="fas fa-circle fs-xs" style="font-size: 6px;"></i>
                            {{ $designTask->status_label }}
                        </span>
                        <span class="text-success small fw-bold opacity-75"><i class="fas fa-clock me-1"></i> Added {{ $designTask->created_at->diffForHumans() }}</span>
                    </div>
                    <h2 class="text-dark fw-bold mb-1">{{ $designTask->title }}</h2>
                    <p class="text-muted mb-0">Project ID: <span class="text-dark fw-bold">{{ $designTask->task_code }}</span> | Type: <span class="text-dark fw-bold">{{ $designTask->type->name ?? 'N/A' }}</span></p>
                </div>
                <div class="col-md-auto ms-auto text-md-end">
                    <div class="px-4 py-3 rounded-3" style="background: white; border: 1px solid #bbf7d0;">
                        <div class="text-muted x-small fw-bold text-uppercase mb-1">Total Bill</div>
                        <div class="text-success h3 mb-0 fw-bold">TZS {{ number_format($designTask->total_amount ?? 0) }}</div>
                        @if(($designTask->balance ?? 0) > 0)
                            <div class="badge bg-danger text-white rounded-pill px-2 py-1 mt-2 small">
                                Due: TZS {{ number_format($designTask->balance) }}
                            </div>
                        @else
                            <div class="badge bg-success text-white rounded-pill px-2 py-1 mt-2 small">
                                <i class="fas fa-check-circle me-1"></i> Fully Paid
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content Column -->
            <div class="col-12 col-xl-8">

                <!-- Delivery Management (Delivery Person & Admins) -->
                @if($designTask->delivery_id === $user->id || ($designTask->status !== 'pending' && in_array($user->role, ['super_admin', 'admin', 'receptionist', 'accountant', 'manager'])))
                    <div class="card border-0 shadow-sm mb-4 bg-info text-dark">
                        @if($designTask->delivery_status !== 'delivered')
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3"><i
                                        class="fas fa-truck me-2"></i>{{ $designTask->delivery_id === $user->id ? 'Delivery Actions' : 'Delivery Management' }}
                                </h6>

                                <form method="POST" action="{{ route('admin.design-tasks.update-delivery-status', $designTask) }}"
                                    data-no-global-handler>
                                    @csrf
                                    <label class="form-label small fw-bold">Update Status</label>
                                    <select name="delivery_status" id="deliveryStatusSelect" class="form-select form-select-sm mb-2"
                                        onchange="togglePaymentField()">
                                        <option value="assigned" {{ $designTask->delivery_status == 'assigned' ? 'selected' : '' }}>
                                            Assigned / Pending</option>
                                        <option value="received" {{ $designTask->delivery_status == 'received' ? 'selected' : '' }}>
                                            Received / Picked up</option>
                                        <option value="delivered" {{ $designTask->delivery_status == 'delivered' ? 'selected' : '' }}>
                                            Delivered Successfully</option>
                                        <option value="failed" {{ $designTask->delivery_status == 'failed' ? 'selected' : '' }}>
                                            Delivery Failed</option>
                                    </select>

                                    <div id="paymentMethodField" class="mb-2"
                                        style="display: {{ $designTask->delivery_status == 'delivered' ? 'block' : 'none' }};">
                                        <label class="form-label small fw-bold text-success">Payment Method</label>
                                        <select name="delivery_payment_method" class="form-select form-select-sm">
                                            <option value="">Select Payment Method...</option>
                                            <option value="cash" {{ $designTask->delivery_payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="mobile" {{ $designTask->delivery_payment_method == 'mobile' ? 'selected' : '' }}>Mobile Payment (M-Pesa/Airtel/Tigo)</option>
                                            <option value="bank" {{ $designTask->delivery_payment_method == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="card" {{ $designTask->delivery_payment_method == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                                        </select>
                                    </div>

                                    <label class="form-label small fw-bold">Feedback / Notes</label>
                                    <textarea name="delivery_notes" class="form-control form-control-sm mb-3" rows="2"
                                        placeholder="e.g. Customer happy, paid in full">{{ $designTask->delivery_notes }}</textarea>

                                    <button type="submit" class="btn btn-light text-primary w-100 fw-bold shadow-sm"
                                        data-no-global-handler>Update Delivery Status</button>
                                </form>
                            </div>
                        @else
                            <div class="card-body p-4 text-center">
                                <div class="mb-2">
                                    <i class="fas fa-check-circle text-success fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-success mb-1">Delivered Successfully</h6>
                                <p class="small text-muted mb-0">This task is closed and cannot be modified.</p>
                                @if($designTask->delivered_at)
                                    <div class="mt-2 small text-muted">
                                        <strong>Date:</strong> {{ $designTask->delivered_at->format('M d, Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        <script>
                            function togglePaymentField() {
                                var status = document.getElementById('deliveryStatusSelect').value;
                                var paymentField = document.getElementById('paymentMethodField');
                                if (status === 'delivered') {
                                    paymentField.style.display = 'block';
                                    paymentField.querySelector('select').required = true;
                                } else {
                                    paymentField.style.display = 'none';
                                    paymentField.querySelector('select').required = false;
                                }
                            }
                        </script>
                    </div>
                @endif

        <!-- Task Lifecycle Tracker -->
        <div class="premium-card p-4 mb-4 border-0">
            <h6 class="fw-bold text-dark mb-4"><i class="fas fa-route me-2 text-primary"></i>Task Production Lifecycle</h6>
            <div class="d-flex justify-content-between position-relative">
                @php
                    $stages = [
                        'pending' => ['label' => 'New', 'icon' => 'asterisk'],
                        'in_progress' => ['label' => 'Design', 'icon' => 'pen-nib'],
                        'in_review' => ['label' => 'Review', 'icon' => 'eye'],
                        'completed' => ['label' => 'Printing', 'icon' => 'print'],
                        'super_completed' => ['label' => 'Super Done', 'icon' => 'flag-checkered'],
                        'delivered' => ['label' => 'Delivered', 'icon' => 'shipping-fast']
                    ];
                    $currentStatus = $designTask->status;
                    if ($designTask->delivery_status === 'delivered') $currentStatus = 'delivered';
                    
                    $order = ['pending', 'in_progress', 'in_review', 'completed', 'confirmed', 'printing', 'printed', 'super_completed', 'delivered'];
                    $currentIndex = array_search($currentStatus, $order);
                @endphp

                @foreach($stages as $statusKey => $stage)
                    @php
                        $thisIndex = array_search($statusKey, $order);
                        $isCompleted = ($currentIndex !== false && $currentIndex > $thisIndex) || ($currentStatus === $statusKey);
                        $isActive = ($currentStatus === $statusKey);
                        
                        // Treat middle statuses (confirmed, printing, printed) as 'completed' stage
                        if ($statusKey === 'completed' && in_array($currentStatus, ['confirmed', 'printing', 'printed'])) {
                            $isCompleted = true;
                            $isActive = true;
                        }
                    @endphp
                    
                    <div class="task-lifecycle-item">
                        @if(!$loop->last)
                            <div class="lifecycle-line {{ ($currentIndex > array_search($statusKey, $order)) ? 'completed' : '' }}"></div>
                        @endif
                        <div class="lifecycle-dot {{ $isActive ? 'active' : ($isCompleted ? 'completed' : '') }}">
                            <i class="fas fa-{{ $isCompleted ? 'check' : $stage['icon'] }} small"></i>
                        </div>
                        <div class="lifecycle-label {{ $isActive ? 'active' : '' }} mt-2">{{ $stage['label'] }}</div>
                        @if($isActive)
                            <div class="x-small text-primary fw-bold mt-1 opacity-75">Current Stage</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

                <!-- Task Description & Images -->
                <div class="card border" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);" class="mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 12px;"><i
                                class="fas fa-align-left me-2"></i>Task Brief</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="text-muted x-small text-uppercase fw-bold mb-2">Description</label>
                            <div class="bg-light p-3 rounded border">
                                @if($designTask->description)
                                    <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $designTask->description }}</p>
                                @else
                                    <span class="text-muted fst-italic">No description provided.</span>
                                @endif
                            </div>
                        </div>

                        @php
                            $referenceImages = is_array($designTask->reference_images)
                                ? $designTask->reference_images
                                : (is_string($designTask->reference_images)
                                    ? json_decode($designTask->reference_images, true)
                                    : []);
                        @endphp

                        @if(!empty($referenceImages))
                            <div>
                                <label class="text-muted x-small text-uppercase fw-bold mb-3">Reference Files</label>
                                <div class="row g-3">
                                    @foreach($referenceImages as $image)
                                        @if(isset($image['path']))
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <a href="{{ asset('storage/' . $image['path']) }}" target="_blank"
                                                    class="text-decoration-none">
                                                    <div class="card h-100 border-0 shadow-sm hover-lift">
                                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative"
                                                            style="height: 120px;">
                                                            <img src="{{ asset('storage/' . $image['path']) }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover"
                                                                style="object-fit: cover;"
                                                                onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=File';">
                                                            <div
                                                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-10 opacity-0 hover-opacity-100 transition-all">
                                                                <i class="fas fa-search-plus text-white fa-lg"></i>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-2 text-center bg-white border-top">
                                                            <small class="d-block text-truncate text-muted"
                                                                title="{{ $image['original_name'] ?? 'File' }}">
                                                                {{ $image['original_name'] ?? 'File' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline & Updates -->
                @if($user->role !== 'delivery')
                    <div class="card border" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);" class="mb-4">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 12px;"><i
                                    class="fas fa-history me-2"></i>Activity</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-4 bg-light border-bottom" style="max-height: 500px; overflow-y: auto;">
                                @if($designTask->updates->count() > 0)
                                    <div class="timeline">
                                        @foreach($designTask->updates->sortByDesc('created_at') as $update)
                                            <div class="d-flex gap-3 mb-4">
                                                <div class="flex-shrink-0">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                                        style="width: 40px; height: 40px; background: {{ $update->type === 'status_update' ? '#0dcaf0' : '#6c757d' }}">
                                                        <i
                                                            class="fas fa-{{ $update->type === 'status_update' ? 'sync' : 'comment' }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div
                                                        class="card border-0 shadow-sm {{ $update->type === 'status_update' ? 'bg-info-subtle border-info' : 'bg-white' }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span
                                                                    class="fw-bold text-dark small">{{ $update->admin->name ?? 'System' }}</span>
                                                                <span
                                                                    class="text-muted x-small">{{ $update->created_at->format('d/m/Y - h:iA') }}</span>
                                                            </div>

                                                            @if($update->type === 'status_update' && isset($update->metadata['from']))
                                                                <div
                                                                    class="d-inline-flex align-items-center gap-2 mb-2 p-1 px-2 bg-white rounded border border-info-subtle shadow-sm">
                                                                    <span
                                                                        class="badge bg-secondary text-white">{{ ucfirst(str_replace('_', ' ', $update->metadata['from'])) }}</span>
                                                                    <i class="fas fa-arrow-right text-muted x-small"></i>
                                                                    <span
                                                                        class="badge bg-primary text-white">{{ ucfirst(str_replace('_', ' ', $update->metadata['to'])) }}</span>
                                                                </div>
                                                            @endif

                                                            <p class="mb-0 text-secondary small">{{ $update->content }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3 text-muted opacity-25">
                                            <i class="fas fa-comments fa-3x"></i>
                                        </div>
                                        <p class="text-muted small">No activity recorded yet.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Comment Input -->
                            @if(
                                    (($user->role === 'designer' || $user->role === 'operator') && $designTask->designer_id === $user->id) ||
                                    in_array($user->role, ['receptionist', 'operator', 'admin', 'super_admin', 'manager', 'accountant'])
                                )
                                <div class="p-3 bg-white">
                                    <form method="POST" action="{{ route('admin.design-tasks.comment', $designTask) }}">
                                        @csrf
                                        <div class="input-group">
                                            <textarea name="content" class="form-control bg-light border-0"
                                                placeholder="Type a note or update..." rows="2" required
                                                style="resize: none;"></textarea>
                                            <button type="submit" class="btn btn-primary px-4" data-no-global-handler>
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Column -->
            <div class="col-12 col-xl-4">

                <!-- Quick Actions Panel (Role Based) -->
                @if(($user->role === 'designer' || $user->role === 'operator') && $designTask->designer_id === $user->id)
                    <div class="card border shadow-sm mb-4 bg-white text-dark">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-primary"></i>Your Actions</h6>

                            @if($designTask->status === 'pending')
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm"
                                        data-no-global-handler>Start Working</button>
                                </form>
                            @elseif($designTask->status === 'in_progress')
                                <div class="d-grid gap-2">
                                    <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                        @csrf <input type="hidden" name="status" value="in_review">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm"
                                            data-no-global-handler>Send for Review</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                        @csrf <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-outline-dark w-100" data-no-global-handler>Mark
                                            Completed</button>
                                    </form>
                                </div>
                            @elseif($designTask->status === 'in_review')
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success text-white w-100 fw-bold shadow-sm"
                                        data-no-global-handler>Complete Task</button>
                                </form>
                            @else
                                <div class="text-center bg-white bg-opacity-25 rounded p-3">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p class="mb-0 small">No pending actions.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif



                <!-- Receptionist/Admin Actions -->
                @if(in_array($user->role, ['receptionist', 'admin', 'super_admin', 'manager', 'operator', 'accountant']) && $designTask->delivery_status !== 'delivered')
                    @if($designTask->status === 'completed' && in_array($user->role, ['receptionist', 'admin', 'super_admin', 'manager', 'accountant']))
                        <div class="card border shadow-sm mb-4 bg-white text-dark">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-success">Approval Needed</h6>
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm"
                                        data-no-global-handler>Confirm Design</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($designTask->status === 'confirmed')
                        <div class="card border shadow-sm mb-4">
                            <div class="card-header bg-white text-dark fw-bold border-bottom py-2">
                                <i class="fas fa-print me-2 text-warning"></i>Production
                            </div>
                            <div class="card-body p-3 bg-white">
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="printing">
                                    <label class="small text-muted fw-bold mb-2">Assign Operator</label>
                                    <select class="form-select form-select-sm mb-3" name="operator_id" required>
                                        <option value="">Choose...</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-warning w-100 btn-sm shadow-sm" data-no-global-handler>Send
                                        to Print</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($designTask->status === 'printing' && in_array($user->role, ['operator', 'admin', 'super_admin', 'manager']))
                        <div class="card border shadow-sm mb-4">
                            <div class="card-header bg-white text-dark fw-bold border-bottom py-2">
                                <i class="fas fa-cog fa-spin me-2 text-info"></i>Printing in Progress
                            </div>
                            <div class="card-body p-3 bg-white">
                                <p class="small text-muted mb-3">Please update when printing is finished.</p>
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="printed">
                                    <button type="submit" class="btn btn-info text-white w-100 btn-sm shadow-sm"
                                        data-no-global-handler>Mark as Printed</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($designTask->status === 'printed' && in_array($user->role, ['operator', 'receptionist', 'admin', 'super_admin', 'manager', 'accountant']))
                        <div class="card border shadow-sm mb-4">
                            <div class="card-header bg-white text-dark fw-bold border-bottom py-2">
                                <i class="fas fa-box-open me-2 text-success"></i>Ready for Pickup
                            </div>
                            <div class="card-body p-3 bg-white">
                                <p class="small text-muted mb-3">Item is printed. Close the task to notify customer.</p>
                                <form method="POST" action="{{ route('admin.design-tasks.update-status', $designTask) }}">
                                    @csrf <input type="hidden" name="status" value="super_completed">
                                    <button type="submit" class="btn btn-success text-white w-100 btn-sm shadow-sm"
                                        onclick="return confirm('This will mark the task as fully closed and notify the customer. Continue?')"
                                        data-no-global-handler>
                                        <i class="fas fa-check-double me-2"></i>Complete & Close
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Task Info Card -->
                <div class="card border" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);" class="mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 12px;">Details</h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Price Section -->
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted" style="font-size: 12px;">Subtotal</span>
                                <span class="fw-bold text-dark" style="font-size: 14px;">TZS
                                    {{ number_format($designTask->price) }}</span>
                            </div>
                            @if($designTask->delivery_cost > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 12px;"><i
                                            class="fas fa-truck me-1 text-info"></i>Delivery</span>
                                    <span class="fw-bold text-info" style="font-size: 14px;">+ TZS
                                        {{ number_format($designTask->delivery_cost) }}</span>
                                </div>
                            @endif
                            @if($designTask->delivery_discount > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 12px;"><i
                                            class="fas fa-percent me-1 text-success"></i>Del. Discount</span>
                                    <span class="fw-bold text-success" style="font-size: 14px;">- TZS
                                        {{ number_format($designTask->delivery_discount) }}</span>
                                </div>
                            @endif
                            @if($designTask->requires_receipt)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 12px;">VAT (18%)</span>
                                    <span class="fw-bold text-warning" style="font-size: 14px;">TZS
                                        {{ number_format($designTask->price * 0.18) }}</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-2 pt-1 border-top">
                                <span class="text-dark fw-bold" style="font-size: 12px;">Grand Total</span>
                                <span class="fw-bold text-primary" style="font-size: 14px;">
                                    TZS
                                    {{ number_format($designTask->price + ($designTask->requires_receipt ? $designTask->price * 0.18 : 0) + $designTask->delivery_cost - $designTask->delivery_discount) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted" style="font-size: 12px;">Paid</span>
                                <span class="fw-bold text-success" style="font-size: 14px;">TZS
                                    {{ number_format($designTask->amount_paid) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 12px;">Balance</span>
                                <span class="fw-bold {{ $designTask->balance > 0 ? 'text-danger' : 'text-muted' }}"
                                    style="font-size: 14px;">
                                    TZS {{ number_format($designTask->balance) }}
                                </span>
                            </div>
                        </div>

                        <!-- Priority Section -->
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="text-dark text-uppercase fw-bold mb-2" style="font-size: 11px;">Priority</label>
                            <div>
                                <span
                                    class="badge {{ $designTask->priority === 3 ? 'bg-warning text-dark' : ($designTask->priority === 1 ? 'bg-danger' : 'bg-secondary') }}"
                                    style="font-size: 11px;">
                                    {{ $designTask->priority_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Dates Section -->
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="text-dark text-uppercase fw-bold mb-2" style="font-size: 11px;">Dates</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-calendar-plus text-muted" style="width: 16px; font-size: 11px;"></i>
                                    <span class="text-dark" style="font-size: 12px;">Created:
                                        {{ $designTask->created_at->format('M d, H:i') }}</span>
                                </div>
                                @if($designTask->deadline)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-hourglass-end text-danger" style="width: 16px; font-size: 11px;"></i>
                                        <span class="fw-medium text-danger" style="font-size: 12px;">Due:
                                            {{ $designTask->deadline->format('M d, H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Team Section -->
                        <div>
                            <label class="text-dark text-uppercase fw-bold mb-2 d-block"
                                style="font-size: 11px;">Team</label>

                            <!-- Designer -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted border"
                                    style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-user-edit" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 lh-1">
                                    <div class="fw-bold text-dark" style="font-size: 12px;">
                                        {{ $designTask->designer->name ?? 'Unassigned' }}</div>
                                    <div class="text-muted" style="font-size: 10px;">Designer</div>
                                </div>
                                @if(!$designTask->designer_id && in_array($user->role, ['receptionist', 'admin', 'accountant', 'manager']) && $designTask->delivery_status !== 'delivered')
                                    <button class="btn btn-dark btn-sm" style="font-size: 11px;" data-bs-toggle="collapse"
                                        data-bs-target="#assignDesignerCollapse">Assign</button>
                                @endif
                            </div>

                            <!-- Operator -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted border"
                                    style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-print" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 lh-1">
                                    <div class="fw-bold text-dark" style="font-size: 12px;">
                                        {{ $designTask->operator->name ?? 'Unassigned' }}</div>
                                    <div class="text-muted" style="font-size: 10px;">Operator</div>
                                </div>
                                @if(!$designTask->operator_id && in_array($user->role, ['receptionist', 'admin', 'accountant', 'manager']) && $designTask->delivery_status !== 'delivered')
                                    <button class="btn btn-dark btn-sm" style="font-size: 11px;" data-bs-toggle="collapse"
                                        data-bs-target="#assignDesignerCollapse">Assign</button>
                                @endif
                            </div>

                            <!-- Collapse for Team Assignment -->
                            <div class="collapse mb-3" id="assignDesignerCollapse">
                                <div class="card card-body bg-light border-0 p-3">
                                    <form method="POST" action="{{ route('admin.design-tasks.assign', $designTask) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="x-small fw-bold text-muted text-uppercase mb-1">Designer</label>
                                            <select name="designer_id" class="form-select form-select-sm"
                                                style="font-size: 12px;">
                                                <option value="">Select Designer...</option>
                                                @foreach($all_designers as $d)
                                                    <option value="{{ $d->id }}" {{ $designTask->designer_id == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ ucfirst($d->role) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="x-small fw-bold text-muted text-uppercase mb-1">Operator</label>
                                            <select name="operator_id" class="form-select form-select-sm"
                                                style="font-size: 12px;">
                                                <option value="">Select Operator...</option>
                                                @foreach($operators as $o)
                                                    <option value="{{ $o->id }}" {{ $designTask->operator_id == $o->id ? 'selected' : '' }}>{{ $o->name }} ({{ ucfirst($o->role) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-dark btn-sm w-100 mt-2" data-no-global-handler
                                            style="font-size: 12px;">Save Assignment</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Receptionist -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted border"
                                    style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-concierge-bell" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 lh-1">
                                    <div class="fw-bold text-dark" style="font-size: 12px;">
                                        {{ $designTask->receptionist->name ?? 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 10px;">Receptionist</div>
                                </div>
                            </div>

                            <!-- Delivery Person -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted border"
                                    style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-truck" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 lh-1">
                                    <div class="fw-bold text-dark" style="font-size: 12px;">
                                        {{ $designTask->delivery->name ?? 'Unassigned' }}</div>
                                    <div class="text-muted" style="font-size: 10px;">Delivery Person</div>
                                    @if($designTask->delivery_status)
                                                                <div class="mt-1 d-flex gap-1 flex-wrap">
                                                                    <span class="badge {{ 
                                                                                                        match ($designTask->delivery_status) {
                                            'delivered' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'assigned' => 'bg-warning text-dark',
                                            'ready_for_pickup' => 'bg-info text-dark',
                                            default => 'bg-secondary'
                                        } 
                                                                                                    }}" style="font-size: 10px;">
                                                                        {{ ucfirst(str_replace('_', ' ', $designTask->delivery_status)) }}
                                                                    </span>

                                                                    @if($designTask->pickup_code && in_array($user->role, ['super_admin', 'admin', 'receptionist', 'manager', 'accountant', 'gatekeeper', 'delivery']))
                                                                        <span class="badge bg-dark text-white border-warning border"
                                                                            style="font-size: 10px;" title="Verification Code">
                                                                            <i class="fas fa-key me-1 text-warning"></i> Code:
                                                                            {{ $designTask->pickup_code }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                    @endif
                                </div>
                                @if($designTask->status === 'super_completed' && in_array($user->role, ['receptionist', 'admin', 'operator', 'super_admin', 'manager', 'accountant']))
                                    @if(!$designTask->delivery_id && $designTask->delivery_status !== 'delivered')
                                        <button class="btn btn-dark btn-sm" style="font-size: 11px;" data-bs-toggle="collapse"
                                            data-bs-target="#assignDeliveryCollapse">Assign</button>
                                    @elseif($designTask->delivery_id && $designTask->delivery_status !== 'delivered')
                                        <button class="btn btn-secondary btn-sm disabled"
                                            style="font-size: 11px; opacity: 0.5; cursor: not-allowed;"
                                            onclick="alert('Delivery already assigned to {{ $designTask->delivery->name ?? 'a person' }}'); return false;">Assigned</button>
                                    @endif
                                @endif
                            </div>

                            <!-- Collapse for Delivery Assignment -->
                            <div class="collapse mt-2" id="assignDeliveryCollapse">
                                <div class="card card-body bg-light border-0 p-2">
                                    <form method="POST"
                                        action="{{ route('admin.design-tasks.assign-delivery', $designTask) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="fw-bold text-dark text-uppercase mb-1"
                                                style="font-size: 10px;">Method</label>
                                            <div class="d-flex gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="delivery_method"
                                                        id="showMethodPickup" value="pickup" checked
                                                        onchange="toggleShowDeliverySelection()">
                                                    <label class="form-check-label" for="showMethodPickup"
                                                        style="font-size: 11px;">Pickup</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="delivery_method"
                                                        id="showMethodDelivery" value="delivery"
                                                        onchange="toggleShowDeliverySelection()">
                                                    <label class="form-check-label" for="showMethodDelivery"
                                                        style="font-size: 11px;">Delivery</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="showDeliveryPersonSelection" class="mb-2 d-none">
                                            <label class="fw-bold text-dark text-uppercase mb-1"
                                                style="font-size: 10px;">Person</label>
                                            <select name="delivery_id" id="show_delivery_id"
                                                class="form-select form-select-sm" style="font-size: 12px;">
                                                <option value="">Select...</option>
                                                @foreach(\App\Models\User::where('role', 'delivery')->where('verified', true)->get() as $d)
                                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                                @endforeach
                                                {{-- Also show the assigned saler as an option for delivery --}}
                                                @if($designTask->saler && $designTask->saler->role === 'saler')
                                                    <option value="{{ $designTask->saler_id }}" class="text-primary fw-bold">
                                                        {{ $designTask->saler->name }} (Assigned Saler)</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="fw-bold text-dark text-uppercase mb-1"
                                                style="font-size: 10px;">Notes</label>
                                            <textarea name="delivery_notes" class="form-control form-control-sm" rows="2"
                                                placeholder="Optional..." style="font-size: 12px;"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-dark btn-sm w-100" data-no-global-handler
                                            style="font-size: 12px;">Confirm</button>
                                    </form>
                                </div>
                            </div>

                            <script>
                                function toggleShowDeliverySelection() {
                                    const isDelivery = document.getElementById('showMethodDelivery').checked;
                                    const selectionDiv = document.getElementById('showDeliveryPersonSelection');
                                    const selectEl = document.getElementById('show_delivery_id');
                                    if (isDelivery) {
                                        selectionDiv.classList.remove('d-none');
                                        selectEl.required = true;
                                    } else {
                                        selectionDiv.classList.add('d-none');
                                        selectEl.required = false;
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="card border" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);" class="mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold text-uppercase"
                                style="width: 48px; height: 48px; font-size: 1.2rem;">
                                {{ substr($designTask->customer->name, 0, 1) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $designTask->customer->name }}</h6>
                                <small class="text-muted">Customer</small>
                            </div>
                            <a href="{{ route('admin.customers.show', $designTask->customer->id) }}"
                                class="btn btn-sm btn-light border ms-auto"><i class="fas fa-external-link-alt"></i></a>
                        </div>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="fas fa-phone text-muted me-2"
                                    style="width: 20px;"></i>{{ $designTask->customer->phone }}</li>
                            @if($designTask->customer->email)
                                <li class="mb-2"><i class="fas fa-envelope text-muted me-2"
                                        style="width: 20px;"></i>{{ $designTask->customer->email }}</li>
                            @endif
                        </ul>
                    </div>
                    <!-- Quick Message Templates (Restricted for Salers) -->
                    @if($templates->count() > 0 && $designTask->customer->phone && $user->role !== 'saler')
                        <div class="card-footer bg-light p-3">
                            <div class="dropdown w-100">
                                <button
                                    class="btn btn-white border w-100 text-start d-flex justify-content-between align-items-center shadow-sm"
                                    type="button" data-bs-toggle="dropdown">
                                    <span class="small"><i class="fab fa-whatsapp text-success me-2"></i>Quick Message</span>
                                    <i class="fas fa-chevron-down small text-muted"></i>
                                </button>
                                <ul class="dropdown-menu w-100 shadow border-0 p-1">
                                    @foreach($templates as $template)
                                        <li>
                                            <form action="{{ route('admin.message-templates.send') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="template_id" value="{{ $template->id }}">
                                                <input type="hidden" name="customer_id" value="{{ $designTask->customer_id }}">
                                                <button type="submit" class="dropdown-item rounded small py-2"
                                                    data-no-global-handler>
                                                    {{ $template->title }}
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <style>
        /* Hover Effects */
        .hover-lift {
            transition: transform 0.2s;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
        }

        .hover-opacity-100:hover {
            opacity: 1 !important;
        }

        /* Timeline Dots */
        .timeline {
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        /* Custom Scrollbar for Timeline */
        .timeline-container::-webkit-scrollbar {
            width: 6px;
        }

        .timeline-container::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 4px;
        }

        /* Mobile Responsiveness & Font Size Reductions */
        @media (max-width: 768px) {

            /* General Headers */
            h2.h3 {
                font-size: 1.15rem !important;
            }

            .badge {
                font-size: 0.65rem !important;
            }

            /* Card Headers & Body */
            .card-header h6 {
                font-size: 0.8rem !important;
            }

            .card-body {
                padding: 1rem !important;
            }

            /* Labels and Text */
            label.x-small {
                font-size: 0.6rem !important;
            }

            .small {
                font-size: 0.75rem !important;
            }

            .x-small {
                font-size: 0.65rem !important;
            }

            /* Task Details */
            .fw-bold.text-dark {
                font-size: 0.85rem !important;
            }

            .text-muted.small {
                font-size: 0.7rem !important;
            }

            /* Team Section */
            .avatar-sm {
                width: 30px !important;
                height: 30px !important;
            }

            .lh-1 .small {
                font-size: 0.75rem !important;
            }

            /* Delivery Actions Form */
            .form-select-sm,
            .form-control-sm {
                font-size: 0.75rem !important;
            }

            .btn-light.text-primary {
                font-size: 0.8rem !important;
                padding: 0.5rem !important;
            }

            /* Timeline */
            .timeline::before {
                left: 15px;
            }

            .timeline .rounded-circle {
                width: 30px !important;
                height: 30px !important;
                font-size: 0.8rem;
            }

            .timeline .card-body {
                padding: 0.75rem !important;
            }
        }
    </style>
@endsection

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form id="editTaskForm" method="POST" data-no-global-handler>
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3">
                    <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 16px;"><i
                            class="fas fa-edit me-2"></i>Modify Task: <span id="editTaskCodeDisplay"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="row g-3">
                        <div class="col-12 text-center mb-2">
                            <span class="badge bg-light text-dark border px-3" style="font-size: 12px;"
                                id="editTaskStatusBadge"></span>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Task Title <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-heading text-muted"></i></span>
                                <input type="text" name="title" id="edit_title" class="form-control"
                                    placeholder="Task title..." required>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Priority <span
                                    class="text-danger">*</span></label>
                            <select name="priority" id="edit_priority" class="form-select form-select-sm" required>
                                <option value="1">High</option>
                                <option value="3">Medium</option>
                                <option value="5">Low</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Deadline</label>
                            <input type="datetime-local" name="deadline" id="edit_deadline"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Department <span
                                    class="text-danger">*</span></label>
                            <select name="department_id" id="edit_department_id" class="form-select form-select-sm"
                                required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Saler</label>
                            <select name="saler_id" id="edit_saler_id" class="form-select form-select-sm">
                                <option value="">Not specified</option>
                                @foreach($salers as $slr)
                                    <option value="{{ $slr->id }}">{{ $slr->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Designer</label>
                            <select name="designer_id" id="edit_designer_id" class="form-select form-select-sm">
                                <option value="">Awaiting assignment</option>
                                @foreach($all_designers as $dsnr)
                                    <option value="{{ $dsnr->id }}">{{ $dsnr->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Quantity <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="qty" id="modal_edit_qty" class="form-control form-control-sm"
                                step="0.01" min="0.01" required>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Rate (TZS) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="rate" id="modal_edit_rate" class="form-control form-control-sm"
                                step="0.01" min="0" required>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;"><i
                                    class="fas fa-truck me-1 text-info"></i>Delivery Cost</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-truck text-info"></i></span>
                                <input type="number" name="delivery_cost" id="modal_edit_delivery_cost"
                                    class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;"><i
                                    class="fas fa-percent me-1 text-success"></i>Del. Discount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i
                                        class="fas fa-percent text-success"></i></span>
                                <input type="number" name="delivery_discount" id="modal_edit_delivery_discount"
                                    class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Live Total Preview -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border d-flex flex-wrap gap-3"
                                style="background:#f8fafc; font-size:12px;">
                                <div>Subtotal: <strong id="modal_preview_subtotal">TZS 0</strong></div>
                                <div>Delivery: <strong class="text-info" id="modal_preview_delivery">TZS 0</strong>
                                </div>
                                <div>Discount: <strong class="text-success" id="modal_preview_discount">- TZS 0</strong>
                                </div>
                                <div class="ms-auto fw-bold">Total: <strong class="text-primary"
                                        id="modal_preview_total">TZS 0</strong></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Public Description</label>
                            <textarea name="description" id="edit_description" class="form-control form-control-sm"
                                rows="3" placeholder="Task description..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1"
                                style="font-size: 11px; letter-spacing: 0.5px;">Internal Instructions</label>
                            <textarea name="designer_instructions" id="edit_designer_instructions"
                                class="form-control form-control-sm" rows="3"
                                placeholder="Instructions for designer..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border-start border-4 border-danger rounded-3"
                                style="background-color: #fff1f2;">
                                <label class="form-label fw-bold text-danger x-small text-uppercase mb-1"
                                    style="font-size: 11px; letter-spacing: 0.5px;"><i
                                        class="fas fa-shield-alt me-1"></i>Reason for Edit <span
                                        class="text-danger">*</span></label>
                                <textarea name="edit_reason" class="form-control form-control-sm" id="edit_reason"
                                    rows="2" placeholder="Why are you changing this task? (Required for audit)"
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-dark px-4 shadow-sm" id="updateTaskBtn"
                        data-no-global-handler>
                        <i class="fas fa-save me-1"></i> Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Cancel Task Modal -->
    <div class="modal fade" id="cancelTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Cancel Task</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.design-tasks.cancel', $designTask) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-secondary small mb-4">Are you sure you want to cancel this task? This will stop the workflow and exclude it from financial reports.</p>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Reason for Cancellation</label>
                            <textarea name="cancel_reason" class="form-control border-light-subtle bg-light" rows="3" required placeholder="Describe why this task is being cancelled..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Keep Task</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mark as Loss Modal -->
    <div class="modal fade" id="markLossModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-heart-crack me-2"></i>Record as Loss (Hasara)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.design-tasks.mark-loss', $designTask) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-secondary small mb-4">Use this for tasks where material or time was wasted due to errors. This will be recorded in the Finance Loss Table.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Reason for Loss</label>
                            <textarea name="loss_reason" class="form-control border-light-subtle bg-light" rows="3" required placeholder="Describe what went wrong..."></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Cost of Loss (TZS)</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">TZS</span>
                                <input type="number" name="loss_amount" class="form-control border-light-subtle bg-light" value="{{ $designTask->price }}" required>
                            </div>
                            <div class="form-text x-small">Defaults to the task price. Adjust if materials were cheaper.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Record Loss</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        function openEditTaskModal(id) {
            const modalEl = document.getElementById('editTaskModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const form = document.getElementById('editTaskForm');

            // Reset form
            form.reset();
            form.action = `/admin/design-tasks/${id}`;

            // Show loading state or just show modal and then fill
            modal.show();

            // Fetch data
            fetch(`/admin/design-tasks/task-data/${id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('editTaskCodeDisplay').textContent = data.task_code;
                    document.getElementById('edit_title').value = data.title;
                    document.getElementById('edit_priority').value = data.priority;
                    document.getElementById('edit_deadline').value = data.deadline;
                    document.getElementById('edit_department_id').value = data.department_id;
                    document.getElementById('edit_saler_id').value = data.saler_id || '';
                    document.getElementById('edit_designer_id').value = data.designer_id || '';
                    document.getElementById('modal_edit_qty').value = data.qty;
                    document.getElementById('modal_edit_rate').value = data.rate;
                    document.getElementById('modal_edit_delivery_cost').value = data.delivery_cost || 0;
                    document.getElementById('modal_edit_delivery_discount').value = data.delivery_discount || 0;
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('edit_designer_instructions').value = data.designer_instructions || '';

                    updateModalPreview();

                    const statusBadge = document.getElementById('editTaskStatusBadge');
                    statusBadge.textContent = `Current Status: ${data.status_label}`;
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load task data.'
                    });
                    modal.hide();
                });
        }

        // Handle Edit Form Submission Loading State
        document.addEventListener('DOMContentLoaded', function () {
            const editForm = document.getElementById('editTaskForm');
            if (editForm) {
                editForm.addEventListener('submit', function () {
                    const btn = document.getElementById('updateTaskBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
                    }
                });
            }
        });

        // Modal live preview calculator
        function updateModalPreview() {
            const qty = parseFloat(document.getElementById('modal_edit_qty')?.value) || 0;
            const rate = parseFloat(document.getElementById('modal_edit_rate')?.value) || 0;
            const delivery = parseFloat(document.getElementById('modal_edit_delivery_cost')?.value) || 0;
            const discount = parseFloat(document.getElementById('modal_edit_delivery_discount')?.value) || 0;
            const subtotal = qty * rate;
            const total = subtotal + delivery - discount;
            const fmt = n => 'TZS ' + n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            const el = id => document.getElementById(id);
            if (el('modal_preview_subtotal')) el('modal_preview_subtotal').textContent = fmt(subtotal);
            if (el('modal_preview_delivery')) el('modal_preview_delivery').textContent = fmt(delivery);
            if (el('modal_preview_discount')) el('modal_preview_discount').textContent = '- ' + fmt(discount);
            if (el('modal_preview_total')) el('modal_preview_total').textContent = fmt(total);
        }

        document.addEventListener('DOMContentLoaded', function () {
            ['modal_edit_qty', 'modal_edit_rate', 'modal_edit_delivery_cost', 'modal_edit_delivery_discount'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', updateModalPreview);
            });
        });
    </script>
@endpush