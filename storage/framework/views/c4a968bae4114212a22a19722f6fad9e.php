<?php $__env->startSection('title', 'Customer Management - CHIBO BRAND Admin'); ?>
<?php $__env->startSection('description', 'Manage customer accounts and verification'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }
    
    .btn-view {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .btn-view:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-edit {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
    }
    
    .btn-edit:hover {
        background-color: #0dcaf0;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-more {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .btn-more:hover, .btn-more[aria-expanded="true"] {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-delete {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .btn-delete:hover {
        background-color: #dc3545;
        color: white;
        transform: translateY(-2px);
    }

    .x-small { font-size: 11px !important; }
    .ls-1 { letter-spacing: 0.5px; }
    
    /* Font size refinements */
    body { font-size: 13px !important; }
    .table thead th { font-size: 11px !important; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #495057; }
    .table td { font-size: 13px !important; }
    .badge { font-weight: 600; padding: 0.5em 0.8em; font-size: 11px !important; }
    .form-control, .form-select, .btn, .modal-body label { font-size: 13px !important; }
    .modal-title { font-size: 15px !important; }
    h4, .h4 { font-size: 14px !important; }
    .text-muted.small { font-size: 12px !important; }
    .avatar-circle { font-size: 13px !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <!-- Header & Stats -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-2 text-dark">Customer Management</h4>
        </div>
        <?php if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant'): ?>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="fas fa-plus"></i>
                <span>Add Customer</span>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <?php if($errors->any()): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-danger-subtle mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <ul class="mb-0 list-unstyled small fw-bold">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width: 48px; height: 48px;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">+<?php echo e($stats['new_this_week']); ?> this week</span>
                    </div>
                    <h3 class="mb-1 fw-bold"><?php echo e($stats['total']); ?></h3>
                    <div class="text-muted small fw-medium">Total Customers</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 48px; height: 48px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div class="small text-muted"><?php echo e($stats['verified_percent']); ?>% Verified</div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?php echo e($stats['verified']); ?></h3>
                    <div class="text-muted small fw-medium">Verified Accounts</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-info-subtle text-info" style="width: 48px; height: 48px;">
                            <i class="fas fa-briefcase fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?php echo e($stats['wholesale']); ?></h3>
                    <div class="text-muted small fw-medium">Wholesale Partners</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-clock fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?php echo e($stats['pending']); ?></h3>
                    <div class="text-muted small fw-medium">Pending Verification</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-4">
                    <form method="GET" class="position-relative">
                        <i class="fas fa-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input type="text" name="search" class="form-control form-control-solid ps-5 rounded-pill bg-light border-0" 
                               placeholder="Search..." value="<?php echo e(request('search')); ?>">
                    </form>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2 text-nowrap overflow-auto pb-1 pb-md-0">
                    <a href="<?php echo e(route('admin.customers.index')); ?>" 
                       class="btn btn-sm rounded-pill px-3 <?php echo e(!request('status') ? 'btn-dark' : 'btn-light border'); ?>">
                        All
                    </a>
                    <a href="<?php echo e(route('admin.customers.index', ['status' => 'verified'])); ?>" 
                       class="btn btn-sm rounded-pill px-3 <?php echo e(request('status') == 'verified' ? 'btn-dark' : 'btn-light border'); ?>">
                        Verified
                    </a>
                    <a href="<?php echo e(route('admin.customers.index', ['status' => 'unverified'])); ?>" 
                       class="btn btn-sm rounded-pill px-3 <?php echo e(request('status') == 'unverified' ? 'btn-dark' : 'btn-light border'); ?>">
                        Unverified
                    </a>
                    <a href="<?php echo e(route('admin.customers.index', ['status' => 'wholesale'])); ?>" 
                       class="btn btn-sm rounded-pill px-3 <?php echo e(request('status') == 'wholesale' ? 'btn-dark' : 'btn-light border'); ?>">
                        Wholesale
                    </a>
                    <a href="<?php echo e(route('admin.customers.index', ['status' => 'active'])); ?>" 
                       class="btn btn-sm rounded-pill px-3 <?php echo e(request('status') == 'active' ? 'btn-dark' : 'btn-light border'); ?>">
                        Active
                    </a>
                    <?php if(request()->has('status') || request()->has('search')): ?>
                        <a href="<?php echo e(route('admin.customers.index')); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-2">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if($customers->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-secondary text-uppercase x-small fw-bold border-0" style="width: 25%;">Customer</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 20%;">Contact Info</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 20%;">Business</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 15%;">Status</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 10%;">Joined</th>
                                <th class="pe-4 text-end text-secondary text-uppercase x-small fw-bold border-0" style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <?php
                                                $customerProfileImage = $customer->profile_image ?? null;
                                                $avatarSrc = !empty($customerProfileImage) ? asset('storage/' . $customerProfileImage) : asset('img/avatars/placeholder.png');
                                            ?>
                                            <img
                                                src="<?php echo e($avatarSrc); ?>"
                                                alt="<?php echo e($customer->name); ?>"
                                                class="rounded-circle shadow-sm me-3"
                                                style="width: 40px; height: 40px; min-width: 40px; object-fit: cover;"
                                                onerror="this.onerror=null; this.src='<?php echo e(asset('img/avatars/placeholder.png')); ?>';"
                                            >
                                            <div>
                                                <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 200px;"><?php echo e($customer->name); ?></div>
                                                <?php if($customer->is_wholesale): ?>
                                                    <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill x-small mt-1">Wholesale</span>
                                                <?php else: ?>
                                                    <span class="text-muted x-small">Retail Customer</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-envelope text-muted small" style="width: 16px;"></i>
                                                <span class="text-muted small text-truncate" style="max-width: 150px;" title="<?php echo e($customer->email); ?>"><?php echo e($customer->email); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-phone text-muted small" style="width: 16px;"></i>
                                                <span class="text-muted small"><?php echo e($customer->phone); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($customer->company_name): ?>
                                            <div class="fw-medium text-dark text-truncate" style="max-width: 150px;"><?php echo e($customer->company_name); ?></div>
                                            <?php if($customer->business_type): ?>
                                                <div class="text-muted x-small"><?php echo e($customer->business_type); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small fs-italic">Individual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if($customer->verified): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill w-fit-content">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill w-fit-content">Unverified</span>
                                            <?php endif; ?>
                                            
                                            <?php if(!$customer->is_active): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill w-fit-content">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark small fw-medium"><?php echo e($customer->created_at->format('M d, Y')); ?></span>
                                            <span class="text-muted x-small"><?php echo e($customer->created_at->diffForHumans()); ?></span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?php echo e(route('admin.customers.show', $customer)); ?>" 
                                               class="btn-action btn-view" 
                                               title="View Details" 
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant'): ?>
                                            <button type="button" 
                                               class="btn-action btn-edit" 
                                               title="Edit Profile" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#editCustomerModal<?php echo e($customer->id); ?>"
                                               data-no-global-handler>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>

                                            <?php if((auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant') && (!$customer->verified || $templates->count() > 0)): ?>
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn-action btn-more" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-no-global-handler>
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg small">
                                                        <?php if(!$customer->verified): ?>
                                                        <li>
                                                            <form method="POST" action="<?php echo e(route('admin.customers.verify', $customer)); ?>">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning" data-no-global-handler>
                                                                    <i class="fas fa-check fa-fw"></i> Verify Manually
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($templates->count() > 0): ?>
                                                            <?php if(!$customer->verified): ?>
                                                                <li><hr class="dropdown-divider my-1"></li>
                                                            <?php endif; ?>
                                                            <li class="dropdown-header text-uppercase x-small fw-bold text-muted">Send Message</li>
                                                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li>
                                                                    <form action="<?php echo e(route('admin.message-templates.send')); ?>" method="POST">
                                                                        <?php echo csrf_field(); ?>
                                                                        <input type="hidden" name="template_id" value="<?php echo e($template->id); ?>">
                                                                        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
                                                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2" data-no-global-handler>
                                                                            <i class="fas fa-paper-plane text-info fa-fw opacity-50"></i> <?php echo e(\Illuminate\Support\Str::limit($template->title, 20)); ?>

                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>

                                            <?php if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.customers.destroy', $customer)); ?>" id="deleteForm<?php echo e($customer->id); ?>" class="d-inline-block">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="button" class="btn-action btn-delete" 
                                                        title="Delete Customer"
                                                        data-bs-toggle="tooltip"
                                                        onclick="modernConfirm('Delete Customer?', 'Are you sure you want to delete this customer? This action cannot be undone.', () => document.getElementById('deleteForm<?php echo e($customer->id); ?>').submit(), { title: 'Delete Customer', type: 'danger', icon: 'fa-trash', confirmText: 'Delete' })">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users text-muted opacity-50" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="text-dark fw-bold">No Customers Found</h5>
                    <p class="text-muted small mb-4">
                        <?php if(request('search') || request('status')): ?>
                            No customers match your current filters.
                        <?php else: ?>
                            No customers have registered yet.
                        <?php endif; ?>
                    </p>
                    <?php if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant'): ?>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus me-2"></i>Add New Customer
                    </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($customers->hasPages()): ?>
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Showing <span class="fw-bold"><?php echo e($customers->firstItem()); ?></span> to <span class="fw-bold"><?php echo e($customers->lastItem()); ?></span> of <span class="fw-bold"><?php echo e($customers->total()); ?></span> results
                    </div>
                    <div class="pagination-wrapper">
                        <?php echo e($customers->appends(request()->query())->links('pagination::bootstrap-4')); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
<?php $__env->stopPush(); ?>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addCustomerModalLabel">Create New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('admin.customers.store')); ?>" method="POST" data-no-global-handler>
                <?php echo csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('name')); ?>" required placeholder="Enter full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('email')); ?>" placeholder="customer@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="phone_country_code" class="form-select border-0 bg-light rounded-start-3" style="max-width: 100px;">
                                    <option value="+255" selected>🇹🇿 +255</option>
                                    <option value="+254">🇰🇪 +254</option>
                                    <option value="+256">🇺🇬 +256</option>
                                    <option value="+250">🇷🇼 +250</option>
                                    <option value="+257">🇧🇮 +257</option>
                                    <option value="+243">🇨🇩 +243</option>
                                    <option value="+27">🇿🇦 +27</option>
                                </select>
                                <input type="text" name="phone" class="form-control border-0 bg-light rounded-end-3" value="<?php echo e(old('phone')); ?>" placeholder="7XX XXX XXX" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">WhatsApp (Optional)</label>
                            <div class="input-group">
                                <select name="whatsapp_country_code" class="form-select border-0 bg-light rounded-start-3" style="max-width: 100px;">
                                    <option value="+255" selected>🇹🇿 +255</option>
                                    <option value="+254">🇰🇪 +254</option>
                                    <option value="+256">🇺🇬 +256</option>
                                </select>
                                <input type="text" name="whatsapp_number" class="form-control border-0 bg-light rounded-end-3" value="<?php echo e(old('whatsapp_number')); ?>" placeholder="7XX XXX XXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Password (Optional)</label>
                            <input type="password" name="password" class="form-control border-0 bg-light rounded-3" placeholder="Enter password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-3" placeholder="Confirm password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Company Name</label>
                            <input type="text" name="company_name" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('company_name')); ?>" placeholder="Company name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Business Type</label>
                            <select name="business_type" class="form-select border-0 bg-light rounded-3">
                                <option value="">Select Business Type</option>
                                <option value="retail">Retail Store</option>
                                <option value="wholesale">Wholesale Distributor</option>
                                <option value="printing">Printing Company</option>
                                <option value="advertising">Advertising Agency</option>
                                <option value="corporate">Corporate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <?php if(auth()->user()->role !== 'saler'): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Brought By (Saler)</label>
                            <select name="added_by" class="form-select border-0 bg-light rounded-3">
                                <option value="">Select Saler (Optional)</option>
                                <?php $__currentLoopData = $salers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($saler->id); ?>"><?php echo e($saler->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Business Address</label>
                            <textarea name="address" rows="2" class="form-control border-0 bg-light rounded-3" placeholder="Physical address"><?php echo e(old('address')); ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="verified" id="modal_verified" checked>
                                    <label class="form-check-label small" for="modal_verified">Verified Account</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="modal_is_active" checked>
                                    <label class="form-check-label small" for="modal_is_active">Active Status</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_wholesale" id="modal_is_wholesale" value="1">
                                    <label class="form-check-label small" for="modal_is_wholesale">Wholesale Partner</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" data-no-global-handler>Create Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant'): ?>
    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal<?php echo e($customer->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Customer: <?php echo e($customer->name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('admin.customers.update', $customer)); ?>" method="POST" data-no-global-handler>
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('name', $customer->name)); ?>" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email (Optional)</label>
                                <input type="email" name="email" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('email', $customer->email)); ?>" placeholder="customer@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="phone_country_code" class="form-select border-0 bg-light rounded-start-3" style="max-width: 100px;">
                                        <option value="+255" <?php echo e(str_contains($customer->phone, '+255') ? 'selected' : ''); ?>>🇹🇿 +255</option>
                                        <option value="+254" <?php echo e(str_contains($customer->phone, '+254') ? 'selected' : ''); ?>>🇰🇪 +254</option>
                                        <option value="+256" <?php echo e(str_contains($customer->phone, '+256') ? 'selected' : ''); ?>>🇺🇬 +256</option>
                                    </select>
                                    <input type="text" name="phone" class="form-control border-0 bg-light rounded-end-3" value="<?php echo e(old('phone', preg_replace('/^\+\d+ /', '', $customer->phone))); ?>" placeholder="7XX XXX XXX" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">WhatsApp (Optional)</label>
                                <div class="input-group">
                                    <select name="whatsapp_country_code" class="form-select border-0 bg-light rounded-start-3" style="max-width: 100px;">
                                        <option value="+255" <?php echo e(!$customer->whatsapp_number || str_contains($customer->whatsapp_number, '+255') ? 'selected' : ''); ?>>🇹🇿 +255</option>
                                        <option value="+254" <?php echo e($customer->whatsapp_number && str_contains($customer->whatsapp_number, '+254') ? 'selected' : ''); ?>>🇰🇪 +254</option>
                                    </select>
                                    <input type="text" name="whatsapp_number" class="form-control border-0 bg-light rounded-end-3" value="<?php echo e(old('whatsapp_number', $customer->whatsapp_number ? preg_replace('/^\+\d+ /', '', $customer->whatsapp_number) : '')); ?>" placeholder="7XX XXX XXX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Password (Optional)</label>
                                <input type="password" name="password" class="form-control border-0 bg-light rounded-3" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-3" placeholder="Confirm password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Company Name</label>
                                <input type="text" name="company_name" class="form-control border-0 bg-light rounded-3" value="<?php echo e(old('company_name', $customer->company_name)); ?>" placeholder="Company name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Business Type</label>
                                <select name="business_type" class="form-select border-0 bg-light rounded-3">
                                    <option value="">Select Business Type</option>
                                    <option value="retail" <?php echo e($customer->business_type == 'retail' ? 'selected' : ''); ?>>Retail Store</option>
                                    <option value="wholesale" <?php echo e($customer->business_type == 'wholesale' ? 'selected' : ''); ?>>Wholesale Distributor</option>
                                    <option value="printing" <?php echo e($customer->business_type == 'printing' ? 'selected' : ''); ?>>Printing Company</option>
                                    <option value="advertising" <?php echo e($customer->business_type == 'advertising' ? 'selected' : ''); ?>>Advertising Agency</option>
                                    <option value="corporate" <?php echo e($customer->business_type == 'corporate' ? 'selected' : ''); ?>>Corporate</option>
                                    <option value="other" <?php echo e($customer->business_type == 'other' ? 'selected' : ''); ?>>Other</option>
                                </select>
                            </div>
                            <?php if(auth()->user()->role !== 'saler'): ?>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Brought By (Saler)</label>
                                <select name="added_by" class="form-select border-0 bg-light rounded-3">
                                    <option value="">Select Saler (Optional)</option>
                                    <?php $__currentLoopData = $salers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($saler->id); ?>" <?php echo e($customer->added_by == $saler->id ? 'selected' : ''); ?>><?php echo e($saler->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Business Address</label>
                                <textarea name="address" rows="2" class="form-control border-0 bg-light rounded-3" placeholder="Physical address"><?php echo e(old('address', $customer->address)); ?></textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="verified" id="edit_verified<?php echo e($customer->id); ?>" <?php echo e($customer->verified ? 'checked' : ''); ?>>
                                        <label class="form-check-label small" for="edit_verified<?php echo e($customer->id); ?>">Verified Account</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active<?php echo e($customer->id); ?>" <?php echo e($customer->is_active ? 'checked' : ''); ?>>
                                        <label class="form-check-label small" for="edit_is_active<?php echo e($customer->id); ?>">Active Status</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_wholesale" id="edit_is_wholesale<?php echo e($customer->id); ?>" value="1" <?php echo e($customer->is_wholesale ? 'checked' : ''); ?>>
                                        <label class="form-check-label small" for="edit_is_wholesale<?php echo e($customer->id); ?>">Wholesale Partner</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" data-no-global-handler>Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/customers/index.blade.php ENDPATH**/ ?>