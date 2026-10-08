@props(['show' => false])
<div class="modal fade" id="testimoniModal" tabindex="-1" aria-labelledby="testimoniModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('testimoni.create') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5" id="testimoniModalLabel">Bagikan pengalaman Anda</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="u-small u-muted">Ceritakan bagaimana Situmbuh membantu Anda memantau tumbuh kembang anak.</p>
                    <div class="u-field">
                        <label for="rating">Penilaian</label>
                        <select name="rating" id="rating" class="form-select" required>
                            <option value="" disabled selected>Pilih 1 sampai 5</option>
                            @foreach ([5 => '5 - Sangat baik', 4 => '4 - Baik', 3 => '3 - Cukup', 2 => '2 - Kurang', 1 => '1 - Buruk'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="u-field mb-0">
                        <label for="isi">Testimoni</label>
                        <textarea name="message" id="isi" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Nanti</button>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($show)
    @push('addon-script')
        <script>
            window.addEventListener('load', function () {
                try { if (sessionStorage.getItem('situba-testimoni-closed')) return; } catch (e) {}
                var el = document.getElementById('testimoniModal');
                el.addEventListener('hidden.bs.modal', function () {
                    try { sessionStorage.setItem('situba-testimoni-closed', '1'); } catch (e) {}
                });
                setTimeout(function () { new bootstrap.Modal(el).show(); }, 1800);
            });
        </script>
    @endpush
@endif
