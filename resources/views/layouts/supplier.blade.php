<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Portal Supplier') - PT Akebono Astra Indonesia</title>

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

        @stack('styles')
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

        @if (isset($errors) && $errors->any())
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
        </script>

        @stack('scripts')
    </body>
</html>
