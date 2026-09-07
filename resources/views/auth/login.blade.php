<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Akebono Approval System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabler-icons@latest/tabler-icons.css">
    <style>
        :root {
            --primary-color: #0F2942;
            --accent-color: #2563EB;
        }

        * {
            font-family: 'Inter', 'Plus Jakarta Sans', 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, #1d4ed8 100%);
            border: none;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .card-header h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .card-header .text-white-50 {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .btn-login {
            background-color: var(--accent-color);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border: none;
            border-radius: 12px;
            border-left: 4px solid;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            border-left-color: #ef4444;
            background-color: #fef2f2;
            color: #991b1b;
        }

        .error-icon {
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #cbd5e1;
            pointer-events: none;
        }

        .form-control.error-input {
            border-color: #ef4444 !important;
        }

        .error-text {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.3rem;
            display: none;
        }

        .footer-text {
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 1.5rem;
        }

        .footer-text a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo/Header -->
        <div style="text-align: center; margin-bottom: 2rem; color: white;">
            <i class="ti ti-building" style="font-size: 3rem;"></i>
            <h2 style="margin-top: 0.5rem; font-weight: 700;">Akebono Approval System</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h1>Login</h1>
                <p class="text-white-50">Sistem Approval Dokumen Supplier</p>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-circle error-icon"></i>
                        <strong>Login Gagal</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="ti ti-mail me-1"></i>Email Address
                        </label>
                        <div class="input-icon">
                            <input type="email" class="form-control @error('email') error-input @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="admin@akebono.local" required autofocus>
                            <i class="ti ti-mail"></i>
                        </div>
                        <div class="error-text" id="emailError">{{ $errors->first('email') }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="ti ti-lock me-1"></i>Password
                        </label>
                        <div class="input-icon">
                            <input type="password" class="form-control @error('password') error-input @enderror" 
                                   id="password" name="password" 
                                   placeholder="Masukkan password" required>
                            <i class="ti ti-lock"></i>
                        </div>
                        <div class="error-text" id="passwordError">{{ $errors->first('password') }}</div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="ti ti-login me-2"></i>Login Sekarang
                    </button>
                </form>

                <div class="footer-text">
                    <i class="ti ti-info-circle me-1"></i>
                    Hubungi admin jika Anda lupa password
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show error toasts
        @if ($errors->any())
            showToast('error', 'Silakan periksa kembali email dan password Anda', 4000);
        @endif

        function showToast(type, message, duration = 3000) {
            const typeMap = {
                'error': { bgClass: 'bg-danger', icon: 'ti-alert-circle' }
            };

            const config = typeMap[type] || { bgClass: 'bg-info', icon: 'ti-info-circle' };

            const toastHTML = `
                <div class="toast align-items-center text-white ${config.bgClass} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="ti ${config.icon} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            const toastContainer = document.getElementById('toastContainer') || (() => {
                const container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(container);
                return container;
            })();

            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHTML;
            toastContainer.appendChild(toastElement.firstElementChild);

            const toastBs = new bootstrap.Toast(toastContainer.lastElementChild);
            toastBs.show();
        }

        // Form validation with micro-interactions
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            let isValid = true;

            // Email validation
            if (!email || !email.includes('@')) {
                document.getElementById('email').classList.add('error-input');
                document.getElementById('emailError').textContent = 'Masukkan email yang valid';
                document.getElementById('emailError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('email').classList.remove('error-input');
                document.getElementById('emailError').style.display = 'none';
            }

            // Password validation
            if (!password || password.length < 3) {
                document.getElementById('password').classList.add('error-input');
                document.getElementById('passwordError').textContent = 'Password harus diisi';
                document.getElementById('passwordError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('password').classList.remove('error-input');
                document.getElementById('passwordError').style.display = 'none';
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Clear error on input
        ['email', 'password'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', function() {
                this.classList.remove('error-input');
                document.getElementById(id + 'Error').style.display = 'none';
            });
        });
    </script>
</body>
</html>
