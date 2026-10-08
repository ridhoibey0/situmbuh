@extends('layouts.auth')

@section('title', 'Daftar')
@section('heading', 'Buat akun')
@section('lead', 'Mulai pantau pertumbuhan anak Anda')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="u-field">
            <label for="name">Nama Anda</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                placeholder="Nama lengkap" autocomplete="name" autofocus required>
        </div>
        <div class="u-field">
            <label for="phone">No. HP</label>
            <input type="tel" inputmode="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                placeholder="081234567890" autocomplete="tel" required>
        </div>
        <div class="u-field">
            <label for="password">Kata sandi</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                <button type="button" onclick="togglePassword(this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
            </div>
        </div>
        <div class="u-field">
            <label for="password_confirmation">Ulangi kata sandi</label>
            <div class="pw-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                    placeholder="Ulangi kata sandi" autocomplete="new-password" required>
                <button type="button" onclick="togglePassword(this)" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-submit w-100">Daftar</button>
    </form>

    <hr class="u-divider">
    <p class="text-center mb-0 u-small">Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold" style="color: var(--accent)">Masuk di sini</a></p>
@endsection
