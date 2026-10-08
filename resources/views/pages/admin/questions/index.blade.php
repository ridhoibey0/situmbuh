@extends('layouts.admin')
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Pertanyaan KPSP</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Questions</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Basic Tables start -->
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <a href="{{route('questions.create')}}" class="btn btn-primary mb-3">
                        + Tambah Pertanyaan Baru
                    </a>

                    <div class="mt-3 mb-3">
                        <strong>Filter</strong>
                        <div class="row">
                             <div class="col-md-3">
                                <label for="category">Kategori Umur</label>
                                <select name="ageCategory" id="ageCategory" class="form-select">
                                    <option value="">--- Select Kategori ---</option>
                                    @foreach ($ageCategory as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category">Kategori Pertanyaan</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">--- Select Kategori ---</option>
                                    @foreach ($category as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="table1">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori Umur</th>
                                    <th>Kategori Pertanyaan</th>
                                    <th>Question</th>
                                    <th>Deskripsi</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </section>
        <!-- Basic Tables end -->

    </div>
@endsection
@push('addon-script')
    <script>
        // AJAX DataTable
        var datatable = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            ajax: {
                url: '{!! url()->current() !!}',
                data: function(d){
                    d.category = $("#category").val();
                    d.ageCategory = $("#ageCategory").val();
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'age_category.name',
                    name: 'age_category.name'
                },
                {
                    data: 'category.name',
                    name: 'category.name'
                },
                {
                    data: 'question',
                    name: 'question'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'image',
                    name: 'image'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                },
            ]
        });

        $("#category").change(function() {
            datatable.draw();
        });

        $("#ageCategory").change(function() {
            datatable.draw();
        });
    </script>
@endpush
