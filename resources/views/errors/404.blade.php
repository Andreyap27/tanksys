<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Nunito', system-ui, sans-serif;
            background-color: #f8fafc;
            color: #0f1e33;
        }
        .error-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            line-height: 1;
            color: #1a5cb8;
            letter-spacing: -0.05em;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0.75rem;
            color: #0f1e33;
        }
        .error-divider {
            width: 3rem;
            height: 3px;
            background: linear-gradient(90deg, #1a5cb8, #f59e0b);
            border-radius: 2px;
            margin: 1.25rem auto;
        }
        .error-desc {
            font-size: 0.9375rem;
            color: #64748b;
            max-width: 22rem;
            line-height: 1.6;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.75rem;
            padding: 0.65rem 1.5rem;
            background-color: #1a5cb8;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background 0.15s;
        }
        .error-btn:hover { background-color: #1a3a6c; }
        .error-footer {
            position: absolute;
            bottom: 1.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <div class="error-title">Halaman Tidak Ditemukan</div>
        <div class="error-divider"></div>
        <p class="error-desc">Halaman yang Anda cari tidak ada atau telah dipindahkan. Periksa kembali URL atau kembali ke halaman utama.</p>
        <a href="{{ url('/') }}" class="error-btn">&#8592; Kembali ke Dashboard</a>
        <div class="error-footer">&copy; {{ date('Y') }} PT. Anugrah Energi Petrolum</div>
    </div>
</body>
</html>
