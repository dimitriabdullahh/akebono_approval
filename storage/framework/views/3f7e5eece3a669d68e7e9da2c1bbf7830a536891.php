<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo $__env->yieldContent('title', 'Sistem Approval Dokumen Supplier'); ?> - PT Akebono Astra Indonesia</title>

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
        
        <!-- Custom CSS -->
        <link href="<?php echo e(mix('css/app.css')); ?>" rel="stylesheet">

        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body>
        <?php if(auth()->guard()->check()): ?>
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Left side menu -->
                    <ul class="navbar-nav">
                        <?php if(auth()->user()->isSupplier()): ?>
                            <!-- Supplier Menu -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('supplier.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('supplier.dashboard')); ?>">
                                    <i class="ti ti-home"></i> Dashboard
                                </a>
                            </li>
                            
                        <?php elseif(auth()->user()->isInternalAdmin()): ?>
                            <!-- Admin Menu -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('dashboard*') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                                    <i class="ti ti-dashboard"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('review.*') ? 'active' : ''); ?>" href="<?php echo e(route('review.index')); ?>">
                                    <i class="ti ti-clipboard-check"></i> Review
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Center brand -->
                    <div class="navbar-brand position-absolute start-50 translate-middle-x">
                        <i class="ti ti-building"></i> Akebono Approval System
                    </div>
                    
                    <!-- Right side user menu -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="ti ti-user-circle"></i> <?php echo e(auth()->user()->name); ?>

                                <?php if(auth()->user()->isSupplier()): ?>
                                    <small class="text-muted">(<?php echo e(auth()->user()->supplier->name); ?>)</small>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><span class="dropdown-item-text">Role: <strong><?php echo e(ucfirst(str_replace('_', ' ', auth()->user()->role))); ?></strong></span></li>
                                <?php if(auth()->user()->isSupplier()): ?>
                                    <li><span class="dropdown-item-text text-truncate" style="max-width: 250px;">Supplier: <strong><?php echo e(auth()->user()->supplier->name); ?></strong></span></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-logout"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php endif; ?>

        <?php if($errors->any()): ?>
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
        
        <!-- Custom JS -->
        <script src="<?php echo e(mix('js/app.js')); ?>"></script>

        <script>
            // Auto-hide alerts after 5 seconds with enhanced animation
            document.querySelectorAll('.alert').forEach((alert, index) => {
                // Stagger the appearance of multiple alerts
                setTimeout(() => {
                    alert.classList.add('fade-in');
                }, index * 100);
                
                // Auto-hide after delay
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000 + (index * 100));
            });

            // Show flash messages as enhanced toasts
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

            // Global error handling with user-friendly messages
            window.addEventListener('error', (e) => {
                console.error('Application error:', e.error);
                
                // Skip error toast for specific cases
                if (e.message && (
                    e.message.includes('ResizeObserver') || 
                    e.message.includes('Bootstrap') ||
                    e.message.includes('Dropdown') ||
                    e.message.includes('showToast') ||
                    e.filename && e.filename.includes('bootstrap')
                )) {
                    return; // Ignore benign errors
                }
            });

            // Network error handling
            window.addEventListener('online', () => {
                if (typeof showToast === 'function') {
                    showToast('success', 'Koneksi internet tersambung kembali.');
                }
            });

            window.addEventListener('offline', () => {
                if (typeof showToast === 'function') {
                    showToast('warning', 'Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi.', 0);
                }
            });

            // Enhanced keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + K for quick search (if search input exists)
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchInput = document.querySelector('input[type="search"], input[placeholder*="Cari"]');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }

                // Escape to clear notifications
                if (e.key === 'Escape') {
                    if (window.clearAllToasts) {
                        window.clearAllToasts();
                    }
                }
            });
        </script>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH E:\KERJAAN\akebono-approval\resources\views/layouts/app.blade.php ENDPATH**/ ?>