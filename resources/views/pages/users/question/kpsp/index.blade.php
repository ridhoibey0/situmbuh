@extends('layouts.users')

@section('title', $questions[0]['category']['name'] . ' - Situmbuh')

@push('addon-style')
    <style>
        .kq { margin-bottom: 12px; }
        .kq .no { font-size: 12.5px; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
        .kq .q { color: var(--ink); font-weight: 600; line-height: 1.45; }
        .kq .q p { margin-bottom: .4rem; }
        .kq .q img { max-width: 100%; height: auto; border-radius: 8px; margin-top: 6px; }
        .kq .hint { font-size: 13px; color: var(--muted); margin: 6px 0 12px; }
        .kq .opts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .kq .opt { position: relative; }
        .kq .opt input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
        .kq .opt span { display: flex; align-items: center; justify-content: center; min-height: 46px; border: 1px solid var(--line); border-radius: 8px; font-weight: 700; color: var(--ink-soft); background: #fff; }
        .kq .opt input:checked + span { background: var(--accent); border-color: var(--accent); color: #fff; box-shadow: 0 6px 14px rgba(22, 163, 74, .28); }
        .kq .opt input:focus-visible + span { outline: 3px solid rgba(22, 163, 74, .4); outline-offset: 1px; }
        .kq-bar { position: sticky; bottom: 56px; z-index: 5; background: var(--bg); padding: 10px 0 12px; margin-top: 8px; border-top: 1px solid var(--line); }
    </style>
@endpush

@section('content')
    <div class="u-head">
        <a href="{{ route('questioner.index') }}" class="u-back">&larr; Skrining</a>
        <h1 class="u-h1" style="font-size: 20px; line-height: 1.3">{{ $questions[0]['category']['name'] }}</h1>
        <p class="u-lead">Jawab sesuai kondisi anak saat ini. Semua pertanyaan wajib diisi.</p>
    </div>

    <form method="POST" action="{{ route('kpsp.store') }}" id="kpspForm">
        @csrf
        <input type="hidden" name="age_category_id" value="{{ $questions[0]['ageCategory']['id'] }}">
        <input type="hidden" name="category_id" value="{{ $questions[0]['category']['id'] }}">

        @foreach ($questions as $index => $question)
            <article class="u-panel kq">
                <div class="no">Pertanyaan {{ $index + 1 }} dari {{ count($questions) }}</div>
                <div class="q">{!! $question->question !!}</div>
                @if ($question->description)
                    <div class="hint">{{ $question->description }}</div>
                @else
                    <div class="mb-3"></div>
                @endif
                <div class="opts">
                    <label class="opt" for="q{{ $question->id }}-yes">
                        <input id="q{{ $question->id }}-yes" type="radio" name="answers[{{ $question->id }}]" value="true" required>
                        <span>Ya</span>
                    </label>
                    <label class="opt" for="q{{ $question->id }}-no">
                        <input id="q{{ $question->id }}-no" type="radio" name="answers[{{ $question->id }}]" value="false" required>
                        <span>Tidak</span>
                    </label>
                </div>
            </article>
        @endforeach

        <div class="kq-bar">
            <div class="u-small u-muted mb-2"><span id="kpspDone">0</span> dari {{ count($questions) }} terjawab</div>
            <button type="submit" class="btn btn-primary w-100">Lihat hasil skrining</button>
        </div>
    </form>
@endsection

@push('addon-script')
    <script>
        (function () {
            var form = document.getElementById('kpspForm'), out = document.getElementById('kpspDone');
            function count() {
                var names = {};
                form.querySelectorAll('input[type=radio]:checked').forEach(function (r) { names[r.name] = 1; });
                out.textContent = Object.keys(names).length;
            }
            // Gambar soal yang tidak dapat dimuat disembunyikan agar tidak tampil sebagai ikon rusak.
            document.querySelectorAll('.kq .q img').forEach(function (img) { img.addEventListener('error', function () { img.style.display = 'none'; }); });
            form.addEventListener('change', count);
            count();
        })();
    </script>
@endpush
