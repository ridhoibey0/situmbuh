@extends('layouts.users')

@section('title', 'Akun - Situmbuh')

@php
    $user = Auth::user();
    $selectedProvinceId = old('province_id', $user->province_id);
    $selectedRegencyId = old('regency_id', $user->regency_id);
    $selectedDistrictId = old('district_id', $user->district_id);
    $selectedVillageId = old('village_id', $user->village_id);
    $support = \App\Support\Phone::whatsappUrl(config('services.support_whatsapp'), 'Halo, saya butuh bantuan menggunakan Situmbuh.');
@endphp

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Akun</h1>
        <p class="u-lead">{{ $user->phone }}</p>
    </div>

    @if (session('message'))
        <div class="alert alert-warning">{{ session('message') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update', $user->id) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        <section class="u-panel">
            <div class="d-flex align-items-center gap-3 mb-3">
                <img id="profileImagePreview"
                    src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://m.atlantic-pedia.co.id/assets/images/user/default_image.jpg' }}"
                    alt="Foto profil" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line)">
                <div>
                    <label for="profileImageInput" class="btn btn-outline-secondary btn-sm mb-0">Ganti foto</label>
                    <input type="file" id="profileImageInput" name="avatar" accept="image/*" class="d-none">
                    @error('avatar')<div class="u-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="u-field">
                <label for="name">Nama akun</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="u-error">{{ $message }}</div>@enderror
            </div>
            <div class="u-field">
                <label for="parent_name">Nama orang tua</label>
                <input type="text" class="form-control" id="parent_name" name="parent_name" value="{{ old('parent_name', $user->parent_name) }}" required>
                @error('parent_name')<div class="u-error">{{ $message }}</div>@enderror
            </div>
            <div class="u-field mb-0">
                <label for="email">Email <span class="u-muted fw-normal">(opsional)</span></label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
                @error('email')<div class="u-error">{{ $message }}</div>@enderror
            </div>
        </section>

        <p class="u-section">Wilayah</p>
        <section class="u-panel">
            <div class="u-field">
                <label for="province_id">Provinsi</label>
                <select id="province_id" name="province_id" class="form-select">
                    <option value="">Pilih provinsi</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" {{ $selectedProvinceId == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="u-field">
                <label for="regency_id">Kabupaten/Kota</label>
                <select id="regency_id" name="regency_id" class="form-select" @if (!$selectedProvinceId) disabled @endif>
                    <option value="">Pilih kabupaten/kota</option>
                </select>
            </div>
            <div class="u-field">
                <label for="district_id">Kecamatan</label>
                <select id="district_id" name="district_id" class="form-select" @if (!$selectedRegencyId) disabled @endif>
                    <option value="">Pilih kecamatan</option>
                </select>
            </div>
            <div class="u-field">
                <label for="village_id">Desa</label>
                <select id="village_id" name="village_id" class="form-select" @if (!$selectedDistrictId) disabled @endif>
                    <option value="">Pilih desa</option>
                </select>
            </div>
            <div class="u-field mb-0">
                <label for="address_detail">Alamat lengkap</label>
                <textarea id="address_detail" name="address_detail" class="form-control">{{ old('address_detail', $user->address_detail) }}</textarea>
            </div>
        </section>

        <button type="submit" class="btn btn-primary w-100 mt-3">Simpan</button>
    </form>

    <p class="u-section">Lainnya</p>
    <div class="u-list">
        @if ($support)
            <a class="u-row" href="{{ $support }}" target="_blank" rel="noopener">
                <i class="bi bi-whatsapp lead-icon"></i>
                <div class="grow"><div class="t">Bantuan via WhatsApp</div><div class="d">Hubungi pengelola Situmbuh</div></div>
                <i class="bi bi-box-arrow-up-right chev"></i>
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="u-row w-100 border-0 text-start" style="background: none">
                <i class="bi bi-box-arrow-right lead-icon" style="background: var(--high-wash); color: var(--high)"></i>
                <div class="grow"><div class="t">Keluar</div></div>
            </button>
        </form>
    </div>
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function resetSelect(selector, placeholder) {
            $(selector).html('<option value="">' + placeholder + '</option>').prop('disabled', true);
        }

        const initialRegencyId = @json($selectedRegencyId);
        const initialDistrictId = @json($selectedDistrictId);
        const initialVillageId = @json($selectedVillageId);

        function fillSelect(selector, data, placeholder, selectedId) {
            const select = $(selector);
            select.html('<option value="">' + placeholder + '</option>');

            $.each(data, function(i, item) {
                const isSelected = String(item.id) === String(selectedId) ? 'selected' : '';
                select.append('<option value="' + item.id + '" ' + isSelected + '>' + item.name + '</option>');
            });

            select.prop('disabled', false);
        }

        function loadRegencies(provinceId, callback, selectedId = null) {
            if (!provinceId) {
                resetSelect('#regency_id', '-- Pilih Kabupaten/Kota --');
                resetSelect('#district_id', '-- Pilih Kecamatan --');
                resetSelect('#village_id', '-- Pilih Desa --');
                return;
            }

            $.getJSON('/api/regencies/' + provinceId, function(data) {
                fillSelect('#regency_id', data, '-- Pilih Kabupaten/Kota --', selectedId);
                if (typeof callback === 'function') {
                    callback();
                }
            });
        }

        function loadDistricts(regencyId, callback, selectedId = null) {
            if (!regencyId) {
                resetSelect('#district_id', '-- Pilih Kecamatan --');
                resetSelect('#village_id', '-- Pilih Desa --');
                return;
            }

            $.getJSON('/api/districts/' + regencyId, function(data) {
                fillSelect('#district_id', data, '-- Pilih Kecamatan --', selectedId);
                if (typeof callback === 'function') {
                    callback();
                }
            });
        }

        function loadVillages(districtId, selectedId = null) {
            if (!districtId) {
                resetSelect('#village_id', '-- Pilih Desa --');
                return;
            }

            $.getJSON('/api/villages/' + districtId, function(data) {
                fillSelect('#village_id', data, '-- Pilih Desa --', selectedId);
            });
        }

        $('#province_id').change(function() {
            let provinceId = $(this).val();
            resetSelect('#regency_id', '-- Pilih Kabupaten/Kota --');
            resetSelect('#district_id', '-- Pilih Kecamatan --');
            resetSelect('#village_id', '-- Pilih Desa --');

            loadRegencies(provinceId);
        });

        $('#regency_id').change(function() {
            let regencyId = $(this).val();
            resetSelect('#district_id', '-- Pilih Kecamatan --');
            resetSelect('#village_id', '-- Pilih Desa --');

            loadDistricts(regencyId);
        });

        $('#district_id').change(function() {
            let districtId = $(this).val();
            resetSelect('#village_id', '-- Pilih Desa --');

            loadVillages(districtId);
        });

        $(function() {
            if ($('#province_id').val()) {
                loadRegencies($('#province_id').val(), function() {
                    if (initialRegencyId) {
                        loadDistricts(initialRegencyId, function() {
                            if (initialDistrictId) {
                                loadVillages(initialDistrictId, initialVillageId);
                            }
                        }, initialDistrictId);
                    }
                }, initialRegencyId);
            }
        });
    </script>
    <script>
        (function () {
            var input = document.getElementById('profileImageInput'), img = document.getElementById('profileImagePreview');
            input.addEventListener('change', function (e) {
                var file = e.target.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (ev) { img.src = ev.target.result; };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endpush
