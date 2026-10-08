@extends('layouts.users')

@section('title', 'Daftarkan anak - Situmbuh')

@section('content')
    <div class="u-head">
        <a href="{{ route('kader.children.index') }}" class="u-back">&larr; Daftar anak</a>
        <h1 class="u-h1">Daftarkan anak</h1>
    </div>

    <form method="POST" action="{{ route('kader.children.store') }}">
        @csrf
        <div class="u-panel">
            <div class="u-field">
                <label for="name">Nama anak</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autocomplete="off">
                @error('name')<div class="u-error">{{ $message }}</div>@enderror
            </div>
            <div class="u-field">
                <label for="gender">Jenis kelamin</label>
                <select name="gender" id="gender" class="form-select" required>
                    <option value="" disabled @selected(!old('gender'))>Pilih</option>
                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                </select>
                @error('gender')<div class="u-error">{{ $message }}</div>@enderror
            </div>
            <div class="u-field">
                <label for="bod">Tanggal lahir</label>
                <input type="date" class="form-control" id="bod" name="bod" value="{{ old('bod') }}" max="{{ now()->toDateString() }}" required>
                @error('bod')<div class="u-error">{{ $message }}</div>@enderror
            </div>
            <div class="u-field mb-0">
                <label for="parent_phone">No. HP orang tua <span class="u-muted fw-normal">(opsional)</span></label>
                <input type="tel" inputmode="tel" class="form-control" id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}">
                <div class="u-small u-muted mt-1">Anak otomatis tertaut ke akun orang tua dengan nomor ini, sekarang atau saat mereka mendaftar.</div>
                @error('parent_phone')<div class="u-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">Simpan</button>
    </form>
@endsection
