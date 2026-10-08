@extends('layouts.users')

@section('title', 'Tindak lanjut - Situmbuh')

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Tindak lanjut</h1>
        <p class="u-lead">Diurutkan berdasarkan tenggat</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @error('status')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <form method="GET" class="u-filter align-items-center">
        <select name="status" class="form-select" aria-label="Status" onchange="this.form.submit()">
            @foreach (['active' => 'Belum selesai', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-check mb-0 d-flex align-items-center gap-2 text-nowrap" style="padding-left: 0">
            <input class="form-check-input m-0" type="checkbox" name="mine" value="1" id="mine" @checked($mine) onchange="this.form.submit()" style="width: 20px; height: 20px">
            <label class="form-check-label mb-0" for="mine">Tugas saya</label>
        </div>
    </form>

    @forelse ($followUps as $followUp)
        @include('pages.kader.follow-ups._card', ['followUp' => $followUp, 'showChild' => true])
    @empty
        <div class="u-panel"><p class="u-muted mb-0">Tidak ada tindak lanjut.</p></div>
    @endforelse

    <div class="mt-3 d-flex justify-content-center">{{ $followUps->links() }}</div>
@endsection
