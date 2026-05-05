<?php $__env->startSection('page-title', 'Security Permissions Configuration'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .role-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .role-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .role-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        background: #fff;
        border-radius: 16px 16px 0 0;
    }

    .role-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .role-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .role-desc {
        font-size: 0.825rem;
        color: #718096;
        line-height: 1.5;
    }

    .permissions-area {
        flex: 1;
        padding: 0;
    }

    .perm-group {
        border-bottom: 1px solid #f7fafc;
    }

    .perm-group-header {
        background: #f8fafc;
        padding: 0.6rem 1.5rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
    }

    .perm-row {
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.2s;
    }

    .perm-row:hover {
        background: #fdfdfd;
    }

    .perm-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .perm-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #cbd5e0;
    }

    .perm-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
    }

    /* Modern Toggle Slider */
    .switch-modern {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 20px;
    }

    .switch-modern input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider-modern {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 20px;
    }

    .slider-modern:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    input:checked + .slider-modern {
        background-color: #0d6efd;
    }

    input:checked + .slider-modern:before {
        transform: translateX(18px);
    }

    input:disabled + .slider-modern {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .card-actions {
        padding: 1.25rem 1.5rem;
        background: #fff;
        border-top: 1px solid #f0f0f0;
        border-radius: 0 0 16px 16px;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .bg-soft-primary { background: #eef2ff; color: #4338ca; }
    .bg-soft-success { background: #ecfdf5; color: #059669; }
    .bg-soft-warning { background: #fffbeb; color: #d97706; }
    .bg-soft-danger { background: #fef2f2; color: #dc2626; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h4 class="fw-bold text-dark mb-1">Access Control Management</h4>
            <p class="text-muted small mb-0">Define organizational roles and granular system permissions</p>
        </div>
        <div class="col-sm-6 text-sm-end">
            <a href="<?php echo e(route('admin.admins.index')); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 shadow-sm mt-3 mt-sm-0">
                <i class="fas fa-users-cog me-2"></i>User Registry
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div><?php echo e(session('success')); ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Roles Grid -->
    <div class="row g-4">
        <?php $__currentLoopData = $rolesPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleKey => $roleData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $colors = match($roleKey) {
                'super_admin' => ['icon' => 'fa-shield-alt', 'class' => 'bg-soft-danger'],
                'admin' => ['icon' => 'fa-user-shield', 'class' => 'bg-soft-primary'],
                'manager' => ['icon' => 'fa-user-edit', 'class' => 'bg-soft-success'],
                'accountant' => ['icon' => 'fa-file-invoice-dollar', 'class' => 'bg-soft-warning'],
                'saler' => ['icon' => 'fa-shopping-cart', 'class' => 'bg-soft-info'],
                default => ['icon' => 'fa-user-tag', 'class' => 'bg-soft-secondary'],
            };
        ?>
        <div class="col-12 col-xl-6">
            <div class="role-card">
                <div class="role-card-header">
                    <div class="role-icon-box <?php echo e($colors['class']); ?>">
                        <i class="fas <?php echo e($colors['icon']); ?>"></i>
                    </div>
                    <div class="role-name">
                        <?php echo e($roleData['name']); ?>

                        <span class="status-badge <?php echo e($colors['class']); ?>"><?php echo e($roleCounts[$roleKey] ?? 0); ?> Users</span>
                    </div>
                    <div class="role-desc"><?php echo e($roleData['description']); ?></div>
                </div>

                <div class="permissions-area">
                    <form method="POST" action="<?php echo e(route('admin.roles-permissions.update', $roleKey)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <?php
                            $categories = [
                                'Administrative' => ['manage_admins', 'manage_roles', 'view_audit_logs', 'reset_passwords'],
                                'Financial Services' => ['manage_finance', 'view_reports', 'manage_orders'],
                                'Operational Ops' => ['manage_products', 'manage_inventory', 'manage_customers', 'manage_leads'],
                                'Specialized Tools' => []
                            ];
                            
                            $categorized = [];
                            foreach($roleData['permissions'] as $p => $v) {
                                $found = false;
                                foreach($categories as $cat => $keys) {
                                    if(in_array($p, $keys)) {
                                        $categorized[$cat][$p] = $v;
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) $categorized['Specialized Tools'][$p] = $v;
                            }
                        ?>

                        <?php $__currentLoopData = $categorized; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catName => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(count($perms) > 0): ?>
                            <div class="perm-group">
                                <div class="perm-group-header"><?php echo e($catName); ?></div>
                                <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission => $hasPermission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="perm-row">
                                    <div class="perm-info">
                                        <div class="perm-dot" style="background: <?php echo e($hasPermission ? '#0d6efd' : '#cbd5e0'); ?>"></div>
                                        <span class="perm-name"><?php echo e(ucwords(str_replace('_', ' ', $permission))); ?></span>
                                    </div>
                                    <label class="switch-modern">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo e($permission); ?>]" 
                                               value="1" 
                                               <?php echo e($hasPermission ? 'checked' : ''); ?>

                                               <?php echo e($roleKey === 'super_admin' ? 'disabled' : ''); ?>>
                                        <span class="slider-modern"></span>
                                    </label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="card-actions d-flex align-items-center justify-content-between">
                            <?php if($roleKey !== 'super_admin'): ?>
                                <span class="text-muted x-small fw-bold"><?php echo e(strtoupper($roleKey)); ?> PROTOCOL</span>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm shadow-sm">
                                    Apply Configuration
                                </button>
                            <?php else: ?>
                                <div class="text-danger small fw-bold d-flex align-items-center">
                                    <i class="fas fa-lock me-2"></i>ROOT SYSTEM ACCESS SECURED
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/roles-permissions/index.blade.php ENDPATH**/ ?>