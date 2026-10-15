<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#16a34a">
    <meta name="robots" content="noindex">
    <title>@yield('code') @yield('title') - Situmbuh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; font-family: 'Nunito', system-ui, -apple-system, 'Segoe UI', sans-serif; color: #0f172a; background: linear-gradient(180deg, #f0fdf4 0%, #f8fafc 55%); position: relative; overflow-x: hidden; }
        body::before { content: ''; position: absolute; width: 360px; height: 360px; border-radius: 50%; background: #dcfce7; right: -120px; top: -140px; opacity: .7; z-index: 0; }
        body::after { content: ''; position: absolute; width: 220px; height: 220px; border-radius: 50%; background: #e0f2fe; left: -80px; bottom: -90px; opacity: .8; z-index: 0; }
        .brand { position: relative; z-index: 1; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: #16a34a; text-decoration: none; margin-bottom: 22px; }
        .card { position: relative; z-index: 1; width: 100%; max-width: 440px; background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 36px 28px 30px; text-align: center; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .code { font-size: 84px; line-height: 1; font-weight: 800; letter-spacing: -.04em; color: #16a34a; margin: 0; }
        h1 { margin: 14px 0 8px; font-size: 22px; font-weight: 800; letter-spacing: -.01em; }
        p { margin: 0; color: #475569; line-height: 1.6; font-size: 15px; }
        .actions { display: flex; flex-direction: column; gap: 10px; margin-top: 26px; }
        .btn { display: block; padding: 13px 18px; border-radius: 14px; font: inherit; font-weight: 700; font-size: 15px; text-decoration: none; cursor: pointer; border: 2px solid transparent; }
        .btn-primary { background: #16a34a; color: #fff; }
        .btn-primary:hover { background: #15803d; }
        .btn-ghost { background: #fff; color: #334155; border-color: #e2e8f0; }
        .btn-ghost:hover { background: #f8fafc; }
        .btn:focus-visible { outline: 3px solid #86efac; outline-offset: 2px; }
        .help { margin-top: 20px; font-size: 13px; color: #64748b; }
        @media (min-width: 480px) { .actions { flex-direction: row; } .btn { flex: 1; } }
    </style>
</head>

<body>
    <a class="brand" href="{{ url('/') }}">Situmbuh</a>

    <main class="card">
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">Ke beranda</a>
            <button class="btn btn-ghost" type="button" onclick="if (history.length > 1) { history.back(); } else { location.href = '{{ url('/') }}'; }">Kembali</button>
        </div>

        @hasSection('help')
            <p class="help">@yield('help')</p>
        @endif
    </main>
</body>

</html>
