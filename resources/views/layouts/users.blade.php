<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#16a34a">
    <title>@yield('title', 'Situmbuh')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/situba-base.css') }}?v={{ filemtime(public_path('css/situba-base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}?v={{ filemtime(public_path('css/user.css')) }}">
    @stack('addon-style')
</head>

@php
    $user = auth()->user();
    $isStaff = $user?->isStaff();
    $isNakes = $user?->roles?->value === 'nakes';
    $nav = $isStaff
        ? array_filter([
            ['/kader', 'Anak', 'bi-people', fn() => request()->is('kader', 'kader/children/*') && !request()->is('kader/children/create')],
            ['/kader/follow-ups', 'Tindak lanjut', 'bi-list-check', fn() => request()->is('kader/follow-ups*')],
            $isNakes ? null : ['/kader/children/create', 'Daftar', 'bi-person-plus', fn() => request()->is('kader/children/create')],
        ])
        : [
            ['/users', 'Beranda', 'bi-house-door', fn() => request()->is('users')],
            ['/users/growth-monitoring', 'Pertumbuhan', 'bi-graph-up', fn() => request()->is('users/growth-monitoring', 'users/measurement')],
            ['/users/questioner', 'KPSP', 'bi-clipboard2-pulse', fn() => request()->is('users/questioner*')],
            ['/users/children', 'Anak', 'bi-people', fn() => request()->is('users/children*')],
            ['/users/profile', 'Akun', 'bi-person', fn() => request()->is('users/profile*')],
        ];
@endphp

<body class="usr-body">
    <div class="usr-shell">
        <header class="usr-top">
            <a class="usr-brand" href="{{ $isStaff ? '/kader' : '/users' }}">Situmbuh</a>
            @if ($isStaff)
                <span class="usr-role">{{ $user->roles->label() }}</span>
            @elseif (!empty($activeChild))
                <a class="usr-chip" href="{{ route('children.index') }}" aria-label="Ganti anak">
                    <x-child-avatar :gender="$activeChild->gender" />
                    <span>{{ $activeChild->name }}</span><i class="bi bi-chevron-down"></i>
                </a>
            @endif
        </header>

        @hasSection('hero')
            @yield('hero')
        @endif

        <main class="usr-main {{ View::hasSection('hero') ? 'has-hero' : '' }}">
            @yield('content')
        </main>

        @yield('fab')

        @hasSection('footer')
            @yield('footer')
        @else
            <nav class="usr-nav" aria-label="Navigasi utama">
                @foreach ($nav as [$href, $label, $icon, $active])
                    <a href="{{ $href }}" class="{{ $active() ? 'is-active' : '' }}" @if ($active()) aria-current="page" @endif>
                        <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                    </a>
                @endforeach
                @if ($isStaff)
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></button>
                    </form>
                @endif
            </nav>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('addon-script')
</body>

</html>
