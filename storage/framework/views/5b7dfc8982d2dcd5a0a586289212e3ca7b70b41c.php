<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo $__env->yieldContent('title', 'Portal Supplier'); ?> - PT Akebono Astra Indonesia</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Tabler Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabler-icons@latest/tabler-icons.css">
        
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Custom CSS - inline minimal styles for supplier portal -->
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fa;
            }
            .navbar-brand {
                font-weight: 600;
            }
            .animate-on-scroll {
                animation: fadeIn 0.5s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>

        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body style="background-color: #f8f9fa;">
        <!-- Simple header for supplier portal -->
        <nav class="navbar navbar-dark bg-primary mb-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">
                    <i class="ti ti-building"></i> PT Akebono Astra Indonesia - Portal Supplier
                </span>
            </div>
        </nav>

        <?php if(isset($errors) && $errors->any()): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i>
                <strong>Error!</strong>
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle"></i> <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i> <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <main>
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Enhanced Toast Container -->
        <?php echo $__env->make('partials.enhanced-toast-container', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        
        <!-- Loading Overlay -->
        <?php echo $__env->make('partials.loading-overlay', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <!-- Custom JS - inline essential functions -->
        <script>
            // Toast notification system
            function showToast(type, message, duration = 4000, options = {}) {
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: duration,
                    timerProgressBar: options.showProgress || false,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                toast.fire({
                    icon: type,
                    title: message
                });
            }

            // Loading overlay functions
            function showLoading(message                                                                                                                                                                                        
                    title: message,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            function hideLoading() {
                Swal.close();
            }
        </script>

        <script>
            // Auto-hide alerts after 5 seconds
            document.querySelectorAll('.alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('fade-in');
                }, index * 100);
                
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000 + (index * 100));
            });

            // Show flash messages as toasts
            document.addEventListener('DOMContentLoaded', () => {
                <?php if(session('success')): ?>
                    if (typeof showToast === 'function') {
                        showToast('success', '<?php echo e(session('success')); ?>', 4000, { showProgress: true });
                    }
                <?php endif; ?>

                <?php if(session('error')): ?>
                    if (typeof showToast === 'function') {
                        showToast('error', '<?php echo e(session('error')); ?>', 6000, { showProgress: true });
                    }
                <?php endif; ?>

                <?php if(session('warning')): ?>
                    if (typeof showToast === 'function') {
                        showToast('warning', '<?php echo e(session('warning')); ?>', 5000, { showProgress: true });
                    }
                <?php endif; ?>

                <?php if(session('info')): ?>
                    if (typeof showToast === 'function') {
                        showToast('info', '<?php echo e(session('info')); ?>', 4000, { showProgress: true });
                    }
                <?php endif; ?>
            });
        </script>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH E:\KERJAAN\akebono-approval\resources\views/layouts/supplier.blade.php ENDPATH**/ ?>