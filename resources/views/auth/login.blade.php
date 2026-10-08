@extends('layouts.auth')

@section('title', 'Masuk')
@section('heading', 'Selamat datang kembali')
@section('lead', 'Masuk untuk memantau tumbuh kembang anak')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="u-field">
            <label for="phone">No. HP</label>
            <input type="tel" inputmode="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                placeholder="081234567890" autocomplete="tel" autofocus required>
        </div>
        <div class="u-field">
            <label for="password">Kata sandi</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                <button type="button" onclick="togglePassword(this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="u-small fw-bold" style="color: var(--accent)">Lupa kata sandi?</a>
            @endif
        </div>
        <button type="submit" class="btn btn-primary btn-submit w-100">Masuk</button>
    </form>

    <hr class="u-divider">
    <p class="text-center mb-0 u-small">Belum punya akun? <a href="{{ route('register') }}" class="fw-bold" style="color: var(--accent)">Daftar sekarang</a></p>
@endsection
