<?php $__env->startSection('title', 'Request Detail - ' . $documentRequest->model); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="<?php echo e(route('supplier.dashboard')); ?>" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Documents Table -->
    <div id="documents-container">
        <?php echo $__env->make('supplier.partials.documents-table', ['part' => $firstPart], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/supplier/request-detail.blade.php ENDPATH**/ ?>