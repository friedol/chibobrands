<?php $__env->startSection('title', 'Movement Details - Gatekeeper'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 px-4 overflow-hidden">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">Movement Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('gatekeeper.movements.index')); ?>" class="text-decoration-none">Logs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#<?php echo e($movement->id); ?></li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('gatekeeper.movements.index')); ?>" class="btn btn-outline-secondary btn-sm px-4 rounded-pill fw-bold">
                <i class="fas fa-arrow-left me-2"></i>Back to Logs
            </a>
            <button onclick="window.print()" class="btn btn-dark btn-sm px-4 rounded-pill fw-bold shadow-sm">
                <i class="fas fa-print me-2"></i>Print Details
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Product Card -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-box me-2"></i>Product Information
                    </h6>
                    <?php if($movement->type === 'in'): ?>
                        <span class="badge bg-success rounded-pill px-3 py-1">INCOMING</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">OUTGOING</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h3 class="fw-bold text-dark mb-1"><?php echo e($movement->product_name); ?></h3>
                            <p class="text-muted mb-4">Reference: <?php echo e($movement->authorization_reference ?: 'N/A'); ?></p>
                            
                            <div class="d-flex gap-4 mb-2">
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold ls-1 mb-1">Quantity</small>
                                    <span class="fs-4 fw-bold text-dark"><?php echo e($movement->quantity); ?></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold ls-1 mb-1">Unit Price</small>
                                    <span class="fs-4 fw-bold text-dark"><?php echo e($movement->unit_price ? number_format($movement->unit_price, 2) . ' TZS' : '-'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded-4 border text-center">
                                <small class="text-muted d-block mb-1">Log Recorded On</small>
                                <div class="fw-bold text-dark fs-5"><?php echo e($movement->movement_date->format('M d, Y')); ?></div>
                                <div class="text-primary fw-medium"><?php echo e($movement->movement_date->format('h:i A')); ?></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    <div class="mb-0">
                        <h6 class="fw-bold mb-3">Additional Notes</h6>
                        <div class="p-3 bg-light rounded-3 text-muted border-start border-4 border-primary">
                            <?php echo e($movement->notes ?: 'No additional notes provided for this movement.'); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Context Details -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="fas fa-map-marker-alt me-2"></i><?php echo e($movement->type === 'in' ? 'Origin (Source)' : 'Destination (Recipient)'); ?>

                    </h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-white h-100">
                                <small class="text-muted d-block mb-1">Name</small>
                                <div class="fw-bold text-dark fs-5"><?php echo e($movement->type === 'in' ? ($movement->source_name ?: '-') : ($movement->recipient_name ?: '-')); ?></div>
                                <span class="badge bg-light text-secondary border mt-1"><?php echo e($movement->type === 'in' ? $movement->source_type : $movement->recipient_type); ?></span>
                            </div>
                        </div>
                        <?php if($movement->type === 'out' || ($movement->type === 'in' && $movement->source_identifier)): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-white h-100">
                                <small class="text-muted d-block mb-1">Identifier (Phone/ID)</small>
                                <div class="fw-bold text-dark fs-5"><?php echo e($movement->type === 'in' ? $movement->source_identifier : $movement->recipient_identifier); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($movement->type === 'out'): ?>
                        <div class="col-md-12">
                            <div class="p-3 border rounded-4 bg-white">
                                <small class="text-muted d-block mb-1">Delivery Method</small>
                                <div class="fw-bold text-dark"><i class="fas fa-truck me-2 text-muted"></i><?php echo e($movement->delivery_method ?: 'Direct Delivery'); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-12 col-lg-4">
            <!-- Handler Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="fas fa-user-check me-2"></i>Handled By
                    </h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="text-center mb-3">
                        <div class="avatar-circle bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?php echo e($movement->handler_name); ?></h5>
                        <p class="text-muted small mb-0"><?php echo e($movement->handler_type); ?></p>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">ID/Phone:</span>
                            <span class="fw-bold text-dark"><?php echo e($movement->handler_identifier ?: 'N/A'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Gatekeeper Info -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-secondary">
                        <i class="fas fa-user-shield me-2"></i>Recorded By
                    </h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                        <div class="avatar-circle bg-white shadow-sm text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="fas fa-id-badge"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-0"><?php echo e($movement->gatekeeper->name); ?></div>
                            <small class="text-muted">Gatekeeper</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 1px; }
    .border-dashed { border-style: dashed !important; }
    @media print {
        .btn, nav, .breadcrumb { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        body { background: white !important; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/gatekeeper/show.blade.php ENDPATH**/ ?>