@extends('layouts.users')

@section('title', 'Tambah anak - Situmbuh')

@php
    $fields = [
        ['weight', 'Berat badan lahir', 'kg'],
        ['height', 'Panjang badan lahir', 'cm'],
        ['head_circumference', 'Lingkar kepala lahir', 'cm'],
        ['arm_circumference', 'Lingkar lengan lahir', 'cm'],
    ];
@endphp

@section('content')
    <div class="u-head">
        <a href="{{ route('children.index') }}" class="u-back">&larr; Data anak</a>
        <h1 class="u-h1">Tambah anak</h1>
    </div>

    @if (session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
    @endif

    <form method="POST" action="{{ route('children.store') }}">
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
            <div class="u-field mb-0">
                <label for="bod">Tanggal lahir</label>
                <input type="date" class="form-control" id="bod" name="bod" value="{{ old('bod') }}"
                    max="{{ now()->toDateString() }}" required>
                @error('bod')<div class="u-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <p class="u-section">Data saat lahir</p>
        <div class="u-panel">
            <div class="row g-3">
                @foreach ($fields as [$key, $label, $unit])
                    <div class="col-6">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <div class="input-group">
                            <input type="number" step="0.1" inputmode="decimal" class="form-control" id="{{ $key }}" name="{{ $key }}"
                                value="{{ old($key) }}" required>
                            <span class="input-group-text">{{ $unit }}</span>
                        </div>
                        @error($key)<div class="u-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Simpan</button>
    </form>
@endsection
