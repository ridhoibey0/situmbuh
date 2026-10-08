@extends('layouts.admin')
@push('addon-style')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
    <section class="sections">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <form method="POST" action="{{ route('export') }}">
                                    @csrf
                                    <div class="mb-3"><label>Periode User</label>
                                        <input type="text" name="periode" value="{{ request('periode') }}"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="village_id">Cari Desa</label>
                                        <select name="village_id" id="village_id" class="form-control select2-village"
                                            data-placeholder="Ketik nama desa..."></select>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-4 offset-md-3 pr-0"><button type="submit"
                                                class="btn btn-info btn-block">Generate</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6"><!----></div>
        </div>
    </section>
@endsection

@push('addon-script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {
            $('input[name="periode"]').daterangepicker({
                opens: 'right',
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#village_id').select2({
                placeholder: '-- Ketik nama desa --',
                ajax: {
                    url: '/api/search-villages',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // query pencarian
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.id,
                                text: `${item.name} - ${item.district_name}, ${item.regency_name}`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });
        });
    </script>
@endpush
