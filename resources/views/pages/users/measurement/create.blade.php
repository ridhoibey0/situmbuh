@extends('layouts.users')

@section('title', 'Catat pengukuran - Situmbuh')

@php
    $fields = [
        ['weight', 'Berat badan', 'kg'],
        ['height', 'Tinggi badan', 'cm'],
        ['head_circumference', 'Lingkar kepala', 'cm'],
        ['arm_circumference', 'Lingkar lengan', 'cm'],
    ];
@endphp

@section('content')
    <div class="u-head">
        <a href="{{ route('growth.index') }}" class="u-back">&larr; Pertumbuhan</a>
        <h1 class="u-h1">Catat pengukuran</h1>
        <p class="u-lead">{{ $child->name }} &middot; {{ $child->ageInMonths() }} bulan</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('measurement.store') }}">
        @csrf
        <div class="u-panel">
            <div class="u-field">
                <label for="measured_at">Tanggal pengukuran</label>
                <input type="date" class="form-control" id="measured_at" name="measured_at"
                    value="{{ old('measured_at', now()->toDateString()) }}" min="{{ $child->bod->toDateString() }}"
                    max="{{ now()->toDateString() }}" required>
                @error('measured_at')<div class="u-error">{{ $message }}</div>@enderror
            </div>
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
