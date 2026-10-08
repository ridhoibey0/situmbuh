<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') - Situmbuh</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" crossorigin href="{{ asset('/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css" />
    <link rel="stylesheet" href="{{ asset('css/situba-base.css') }}?v={{ filemtime(public_path('css/situba-base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('addon-style')
</head>

@php
    $navGroups = [
        'Ringkasan' => [
            ['admin.dashboard', 'Dashboard', 'bi-grid-1x2', 'admin/dashboard'],
            ['admin.stunting.index', 'Laporan stunting', 'bi-bar-chart-line', 'admin/stunting*'],
        ],
        'Layanan' => [
            ['admin.children.index', 'Anak dan penugasan', 'bi-people', 'admin/children*'],
            ['users.index', 'Pengguna', 'bi-person-gear', 'admin/users*'],
        ],
        'Skrining KPSP' => [
            ['questions.index', 'Pertanyaan', 'bi-patch-question', 'admin/questions*'],
            ['age-category.index', 'Kategori umur', 'bi-tags', 'admin/age-category*'],
            ['answers.index', 'Jawaban', 'bi-card-checklist', 'admin/answers*'],
        ],
        'Konten dan data' => [
            ['blogs.index', 'Artikel', 'bi-newspaper', 'admin/blogs*'],
            ['admin.rekap', 'Unduh rekap', 'bi-file-earmark-arrow-down', 'admin/dashboard/rekap'],
        ],
    ];
@endphp

<body class="adm-body">
    <a class="adm-skip" href="#konten">Lewati ke konten</a>

    <aside class="adm-side" id="admSide" aria-label="Navigasi admin">
        <div class="adm-brand">
            <strong>Situmbuh</strong>
            <span>Panel pengelola layanan</span>
        </div>

        <nav class="adm-nav">
            @foreach ($navGroups as $label => $items)
                <div class="adm-nav-group">
                    <div class="adm-nav-label">{{ $label }}</div>
                    @foreach ($items as [$route, $text, $icon, $pattern])
                        <a href="{{ route($route) }}" class="{{ request()->is($pattern) ? 'is-active' : '' }}"
                            @if (request()->is($pattern)) aria-current="page" @endif>
                            <i class="bi {{ $icon }}"></i><span>{{ $text }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="adm-user">
            <div class="adm-user-name">
                {{ auth()->user()->name }}
                <small>{{ auth()->user()->roles?->label() }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </aside>
    <div class="adm-scrim" id="admScrim"></div>

    <div class="adm-main">
        <div class="adm-top">
            <button type="button" id="admToggle" aria-label="Buka menu"><i class="bi bi-list fs-5"></i></button>
            <strong class="text-dark">Situmbuh</strong>
        </div>

        <main class="adm-content" id="konten">
            @yield('content')
        </main>
    </div>

    @stack('prepend-script')
    <script src="{{ asset('/assets/extensions/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
    <script>
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data', infoFiltered: '(disaring dari _MAX_ data)', zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data', processing: 'Memuat...',
                paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
            }
        });
        $("#datatable").DataTable();

        (function () {
            var side = document.getElementById('admSide'), scrim = document.getElementById('admScrim'), btn = document.getElementById('admToggle');
            function toggle(open) { side.classList.toggle('is-open', open); scrim.classList.toggle('is-open', open); }
            btn.addEventListener('click', function () { toggle(!side.classList.contains('is-open')); });
            scrim.addEventListener('click', function () { toggle(false); });
        })();
    </script>
    @stack('addon-script')
</body>

</html>
