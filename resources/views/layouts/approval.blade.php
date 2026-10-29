<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">
    
    <title>@yield('title', 'AAIJ Approval System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.css">

    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-bg: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            background-color: var(--light-bg);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .approval-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--success-color);
        }

        .approval-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .approval-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .approval-header .badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        /* Main content */
        .approval-container {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .approval-content {
            flex: 1;
            display: flex;
            gap: 1.5rem;
            padding: 2rem;
            overflow-y: auto;
        }

        .approval-preview {
            flex: 1.2;
            min-width: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .approval-preview-header {
            background-color: var(--light-bg);
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .approval-preview-header h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .approval-preview-body {
            flex: 1;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }

        .approval-preview-body iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .approval-preview-footer {
            background-color: var(--light-bg);
            padding: 1rem;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .approval-preview-footer a {
            display: inline-block;
            margin: 0 0.5rem;
        }

        .approval-sidebar {
            flex: 0.8;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .approval-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .approval-card-header {
            background-color: var(--light-bg);
            padding: 1rem;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .approval-card-header.success {
            background-color: #d4edda;
            border-bottom-color: var(--success-color);
            color: #155724;
        }

        .approval-card-body {
            padding: 1rem;
        }

        .approval-card-body dt {
            font-weight: 600;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            color: #666;
        }

        .approval-card-body dd {
            margin-left: 0;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .approval-card-body .badge {
            display: inline-block;
            margin-right: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .approval-status-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .approval-status-item:last-child {
            border-bottom: none;
        }

        .approval-status-label {
            font-weight: 500;
            flex: 1;
        }

        .approval-status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .approval-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .approval-actions .btn {
            padding: 0.75rem 1rem;
            font-weight: 500;
            border-radius: 4px;
        }

        .alert-custom {
            border-left: 4px solid var(--light-bg);
            margin-bottom: 1rem;
        }

        .alert-custom.info {
            border-left-color: var(--primary-color);
            background-color: #e7f3ff;
            color: #004085;
        }

        .alert-custom.success {
            border-left-color: var(--success-color);
            background-color: #d4edda;
            color: #155724;
        }

        .alert-custom.warning {
            border-left-color: var(--warning-color);
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-custom.danger {
            border-left-color: var(--danger-color);
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Footer */
        .approval-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }

        /* Loading spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner-overlay.active {
            display: flex;
        }

        .spinner-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 8px 8px 0 0;
        }

        .modal-header .close {
            color: white;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .approval-content {
                flex-direction: column;
            }

            .approval-preview {
                flex: 1;
                min-width: auto;
                min-height: 400px;
            }

            .approval-sidebar {
                flex: 1;
            }
        }

        @media (max-width: 768px) {
            .approval-header h1 {
                font-size: 1.2rem;
            }

            .approval-content {
                padding: 1rem;
                gap: 1rem;
            }

            .approval-preview-body {
                min-height: 300px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Header -->
    <div class="approval-header">
        <div class="container">
            <h1>
                <i class="ti ti-certificate"></i>
                AAIJ Approval System
            </h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="approval-container">
        <div class="approval-content">
            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    <div class="approval-footer">
        <p class="mb-0">
            © {{ date('Y') }} PT. Akebono Brake Astra Indonesia. Semua hak dilindungi.
        </p>
    </div>

    <!-- Loading Spinner Overlay -->
    <div class="spinner-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show/hide loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        // Prevent double submission
        function preventDoubleSubmit(formSelector) {
            let isSubmitting = false;
            document.querySelectorAll(formSelector).forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    isSubmitting = true;
                    showLoading();
                });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
