@extends('layouts.users')

@section('title', 'Tanya asisten - Situmbuh')

@php
    $first = $child ? \Illuminate\Support\Str::of($child->name)->before(' ') : null;
    $months = $child?->ageInMonths();
    $suggestions = $child
        ? [
            "Jelaskan kondisi pertumbuhan {$first} saat ini",
            "Apa arti Z-score {$first}?",
            "Makanan yang cocok untuk usia {$months} bulan",
            "Stimulasi apa untuk usia {$months} bulan?",
            "Kapan {$first} perlu diukur lagi?",
        ]
        : ['Apa itu Z-score?', 'Apa itu KPSP?', 'Tanda anak perlu diperiksa ke Posyandu'];
@endphp

@push('addon-style')
    <style>
        .bot { display: flex; gap: 12px; align-items: center; }
        .bot .face { width: 48px; height: 48px; border-radius: 16px; background: var(--pink); color: var(--pink-ink); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .chips { display: flex; gap: 8px; overflow-x: auto; padding: 4px 16px 8px; margin: 12px -16px 0; scrollbar-width: none; }
        .chips::-webkit-scrollbar { display: none; }
        .chips button { flex-shrink: 0; border: 0; border-radius: 999px; padding: 8px 14px; background: #fff; color: var(--accent-ink); font: inherit; font-size: 13.5px; font-weight: 700; box-shadow: var(--shadow); }
        .chat-log { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; padding-bottom: 8px; }
        .msg { max-width: 86%; padding: 10px 14px; border-radius: 20px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; font-size: 15px; }
        .msg.ai { align-self: flex-start; background: #fff; border-bottom-left-radius: 6px; color: var(--ink-soft); box-shadow: var(--shadow); }
        .msg.user { align-self: flex-end; background: var(--accent); color: #fff; border-bottom-right-radius: 6px; }
        .msg.note { align-self: center; background: none; box-shadow: none; color: var(--muted); font-size: 13px; text-align: center; }
        .msg.typing { display: inline-flex; gap: 5px; padding: 14px 16px; }
        .msg.typing i { width: 8px; height: 8px; border-radius: 50%; background: #b3c2de; animation: blink 1s infinite; }
        .msg.typing i:nth-child(2) { animation-delay: .2s; } .msg.typing i:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0%, 100% { opacity: .3; transform: translateY(0); } 50% { opacity: 1; transform: translateY(-3px); } }
        .chat-bar { position: sticky; bottom: 74px; z-index: 5; padding: 8px 0 10px; background: linear-gradient(to top, var(--bg) 70%, transparent); }
        .chat-bar .field { display: flex; gap: 8px; background: #fff; border-radius: 999px; padding: 6px 6px 6px 18px; box-shadow: 0 6px 20px rgba(20, 38, 74, .12); }
        .chat-bar input { flex: 1; border: 0; outline: 0; font: inherit; font-size: 15.5px; background: transparent; min-width: 0; }
        .chat-bar button { width: 44px; height: 44px; border: 0; border-radius: 50%; background: var(--accent); color: #fff; font-size: 18px; flex-shrink: 0; }
    </style>
@endpush

@section('content')
    <section class="u-panel is-mint">
        <div class="bot">
            <span class="face"><i class="bi bi-chat-heart"></i></span>
            <div>
                <div class="fw-bold" style="color: var(--ink); font-size: 17px">Asisten Situmbuh</div>
                <div class="u-small" style="color: var(--mint-ink)">
                    @if ($child)Tahu data {{ $first }}: usia, ukuran, dan statusnya.@else Informasi umum tumbuh kembang.@endif
                </div>
            </div>
        </div>
        <p class="mb-0 mt-2" style="font-size: 12.5px; color: var(--mint-ink)">
            @if ($child)Hanya nama depan, usia, ukuran, dan status {{ $first }} yang dikirim ke layanan AI.@endif
            Jawaban bukan diagnosis; hubungi kader atau tenaga kesehatan untuk kondisi anak.
        </p>
    </section>

    <div class="chips" id="chips">
        @foreach ($suggestions as $text)
            <button type="button" data-q="{{ $text }}">{{ $text }}</button>
        @endforeach
    </div>

    <div class="chat-log" id="chat-messages" aria-live="polite"></div>

    <form class="chat-bar" id="chat-form" autocomplete="off">
        <div class="field">
            <input type="text" id="chat-input" placeholder="Tulis pertanyaan Anda" aria-label="Pertanyaan" maxlength="1000" required>
            <button type="submit" id="send-button" aria-label="Kirim"><i class="bi bi-send-fill"></i></button>
        </div>
    </form>
@endsection

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        (function () {
            var sessionId = null;
            var box = document.getElementById('chat-messages');
            var input = document.getElementById('chat-input');
            var form = document.getElementById('chat-form');
            var typingEl = null;

            // Teks dipasang sebagai node teks (bukan innerHTML); **tebal** didukung secara aman.
            function fill(el, text) {
                String(text).split('**').forEach(function (part, i) {
                    if (i % 2 === 1) { var b = document.createElement('strong'); b.textContent = part; el.appendChild(b); }
                    else { el.appendChild(document.createTextNode(part)); }
                });
            }
            function add(kind, text) {
                var el = document.createElement('div');
                el.className = 'msg ' + kind;
                fill(el, text);
                box.appendChild(el);
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                return el;
            }
            function showTyping() {
                typingEl = document.createElement('div');
                typingEl.className = 'msg ai typing';
                typingEl.innerHTML = '<i></i><i></i><i></i>';
                box.appendChild(typingEl);
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            }
            function hideTyping() { if (typingEl) { typingEl.remove(); typingEl = null; } }

            function ask(text) {
                text = text.trim();
                if (!text) return;
                input.value = '';
                add('user', text);
                showTyping();
                axios.post('{{ route('chat.send') }}', { session_id: sessionId, message: text }).then(function (res) {
                    hideTyping();
                    sessionId = res.data.session_id;
                    add('ai', res.data.messages[0].message);
                }).catch(function () {
                    hideTyping();
                    add('note', 'Terjadi kesalahan, silakan coba lagi.');
                });
            }

            axios.get('{{ route('chat.session') }}').then(function (res) {
                var s = res.data.session;
                if (!s || !s.messages.length) { add('note', 'Pilih pertanyaan di atas atau tulis sendiri.'); return; }
                sessionId = s.id;
                s.messages.forEach(function (m) { add(m.sender === 'user' ? 'user' : 'ai', m.message); });
            }).catch(function () { add('note', 'Gagal memuat percakapan. Silakan coba lagi nanti.'); });

            form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
            document.getElementById('chips').addEventListener('click', function (e) {
                var b = e.target.closest('button[data-q]');
                if (b) ask(b.dataset.q);
            });
        })();
    </script>
@endpush
