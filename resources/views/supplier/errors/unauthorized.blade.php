<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Portal Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F2942 0%, #1e40af 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }

        .error-container {
            text-align: center;
            color: white;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .error-message {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }

        .error-details {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-details p {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .btn-home {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background-color: white;
            color: #0F2942;
        }

        .footer-text {
            margin-top: 2rem;
            opacity: 0.7;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="ti ti-lock"></i>
        </div>
        <h1 class="error-title">401 - Akses Ditolak</h1>
        <p class="error-message">{{ $message ?? 'Anda tidak memiliki akses ke portal ini' }}</p>

        <div class="error-details">
            <p>
                <i class="ti ti-info-circle me-2"></i>
                <strong>Kemungkinan penyebab:</strong>
            </p>
            <ul class="text-start d-inline-block" style="font-size: 0.95rem;">
                <li>Token akses tidak valid atau sudah kadaluarsa</li>
                <li>URL akses salah atau tidak lengkap</li>
                <li>Akses portal telah ditutup oleh sistem</li>
                <li>Sesi telah berakhir</li>
            </ul>
        </div>

        <div>
            <p style="margin-bottom: 1rem; font-size: 0.95rem;">
                <i class="ti ti-mail me-1"></i>
                Jika Anda merasa ini adalah kesalahan, silakan hubungi:
            </p>
            <p style="font-size: 0.9rem;">
                <strong>Tim QA Akebono</strong><br>
                Email: <a href="mailto:qa@akebono.local" style="color: white; text-decoration: underline;">qa@akebono.local</a>
            </p>
        </div>

        <div class="footer-text">
            <p>PT Akebono Astra Indonesia - Sistem Approval Dokumen Supplier</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
