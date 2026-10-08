<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#16a34a">
    <title>@yield('title') - Situmbuh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/situba-base.css') }}?v={{ filemtime(public_path('css/situba-base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}?v={{ filemtime(public_path('css/user.css')) }}">
    <style>
        .auth-hero { padding: 34px 24px 90px; background: var(--accent); color: #fff; border-radius: 0 0 34px 34px; position: relative; overflow: hidden; }
        .auth-hero::before { content: ''; position: absolute; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.09); right: -60px; top: -70px; }
        .auth-hero::after { content: ''; position: absolute; width: 110px; height: 110px; border-radius: 50%; background: rgba(201,243,228,.2); left: -30px; bottom: -40px; }
        .auth-brand { display: inline-flex; align-items: center; gap: 9px; color: #fff; font-weight: 800; font-size: 22px; text-decoration: none; position: relative; z-index: 1; }
        .auth-brand::before { content: ''; width: 28px; height: 28px; border-radius: 9px 9px 9px 4px; background: linear-gradient(135deg, #22c55e, #0ea5e9); border: 2px solid #fff; }
        .auth-hero h1 { color: #fff; font-size: 28px; margin: 26px 0 4px; position: relative; z-index: 1; }
        .auth-hero p { color: #dcfce7; margin: 0; position: relative; z-index: 1; }
        .auth-card { margin: -56px 16px 0; position: relative; z-index: 2; }
        .auth-foot { text-align: center; color: var(--muted); font-size: 13px; padding: 18px 16px 28px; }
        .pw-wrap { position: relative; }
        .pw-wrap .form-control { padding-right: 48px; }
        .pw-wrap button { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: 0; background: none; color: var(--muted); width: 40px; height: 40px; font-size: 18px; }
    </style>
</head>

<body class="usr-body">
    <div class="usr-shell">
        <header class="auth-hero">
            <a class="auth-brand" href="{{ url('/') }}">Situmbuh</a>
            <h1>@yield('heading')</h1>
            <p>@yield('lead')</p>
        </header>

        <main class="auth-card u-panel" style="padding: 20px">
            @yield('content')
        </main>

        <p class="auth-foot">Berbasis standar pertumbuhan WHO. Bukan alat diagnosis.</p>
    </div>

    <script>
        function togglePassword(btn) {
            var input = btn.parentElement.querySelector('input');
            var hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            btn.querySelector('i').className = hidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    </script>
</body>

</html>
