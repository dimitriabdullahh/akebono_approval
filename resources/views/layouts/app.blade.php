<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Sistem Approval Dokumen Supplier') - PT Akebono Astra Indonesia</title>

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
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">

        @stack('styles')
    </head>
    <body>
        @if(auth()->guard('employee')->check() || auth()->check())
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Left side menu -->
                    <ul class="navbar-nav">
                        @php
                            $user = auth()->user();
                            $employee = auth()->guard('employee')->user();
                            $isSupplier = $user && method_exists($user, 'isSupplier') && $user->isSupplier();
                            $isInternalAdmin = ($user && method_exists($user, 'isInternalAdmin') && $user->isInternalAdmin()) || $employee;
                        @endphp
                        
                        @if($isSupplier)
                            <!-- Supplier Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}" href="{{ route('supplier.dashboard') }}">
                                    <i class="ti ti-home"></i> Dashboard
                                </a>
                            </li>
                            {{-- TEMPORARILY HIDDEN: Outstanding Documents Menu
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('supplier.outstanding') ? 'active' : '' }}" href="{{ route('supplier.outstanding') }}">
                                    <i class="ti ti-alert-circle"></i> Outstanding Documents
                                </a>
                            </li>
                            --}}
                        @elseif($isInternalAdmin)
                            <!-- Admin/Employee Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="ti ti-dashboard"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('review.*') ? 'active' : '' }}" href="{{ route('review.index') }}">
                                    <i class="ti ti-clipboard-check"></i> Review
                                </a>
                            </li>
                        @endif
                    </ul>
                    
                    <!-- Center brand -->
                    <div class="navbar-brand position-absolute start-50 translate-middle-x">
                        <i class="ti ti-building"></i> Akebono Approval System
                    </div>
                    
                    <!-- Right side user menu -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                @php
                                    $displayUser = auth()->user() ?? auth()->guard('employee')->user();
                                    $displayName = $displayUser->name ?? 'User';
                                    $isSupplierUser = auth()->check() && method_exists(auth()->user(), 'isSupplier') && auth()->user()->isSupplier();
                                @endphp
                                
                                <i class="ti ti-user-circle"></i> {{ $displayName }}
                                @if($isSupplierUser)
                                    <small class="text-muted">({{ auth()->user()->supplier->name }})</small>
                                @elseif(auth()->guard('employee')->check())
                                    <small class="text-muted">({{ auth()->guard('employee')->user()->npk }})</small>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @if(auth()->guard('employee')->check())
                                    @php $emp = auth()->guard('employee')->user(); @endphp
                                    <li><span class="dropdown-item-text">NPK: <strong>{{ $emp->npk }}</strong></span></li>
                                    <li><span class="dropdown-item-text">Division: <strong>{{ $emp->division }}</strong></span></li>
                                    <li><span class="dropdown-item-text">Role: <strong>{{ $emp->role }}</strong></span></li>
                                @elseif(auth()->check())
                                    <li><span class="dropdown-item-text">Role: <strong>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</strong></span></li>
                                    @if(method_exists(auth()->user(), 'isSupplier') && auth()->user()->isSupplier())
                                        <li><span class="dropdown-item-text text-truncate" style="max-width: 250px;">Supplier: <strong>{{ auth()->user()->supplier->name }}</strong></span></li>
                                    @endif
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
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
        @endif

        @if ($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i>
                <strong>Error!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        <main>
            @yield('content')
        </main>

        <!-- Enhanced Toast Container -->
        @include('partials.enhanced-toast-container')
        
        <!-- Loading Overlay -->
        @include('partials.loading-overlay')

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <!-- Custom JS -->
        <script src="{{ mix('js/app.js') }}"></script>

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
                @if (session('success'))
                    if (typeof showToast === 'function') {
                        showToast('success', '{{ session('success') }}', 4000, { showProgress: true });
                    }
                @endif

                @if (session('error'))
                    if (typeof showToast === 'function') {
                        showToast('error', '{{ session('error') }}', 6000, { showProgress: true });
                    }
                @endif

                @if (session('warning'))
                    if (typeof showToast === 'function') {
                        showToast('warning', '{{ session('warning') }}', 5000, { showProgress: true });
                    }
                @endif

                @if (session('info'))
                    if (typeof showToast === 'function') {
                        showToast('info', '{{ session('info') }}', 4000, { showProgress: true });
                    }
                @endif
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

        @stack('scripts')
    </body>
</html>
