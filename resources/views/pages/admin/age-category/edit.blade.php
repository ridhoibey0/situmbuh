@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <h3>Ubah kategori umur</h3>
    </div>
    <section id="basic-vertical-layouts">
        <div class="row match-height">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Create Age Category</h4>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                            @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form form-vertical" method="POST"
                                action="{{ route('age-category.update', $item->id) }}">
                                @method('PUT')
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="first-name-vertical">Name</label>
                                                <input type="text" id="first-name-vertical" class="form-control"
                                                    name="name" placeholder="Name" value="{{$item->name}}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Min Age</label>
                                                <input type="number" id="email-id-vertical" class="form-control"
                                                    name="min_age" placeholder="Min Age" value="{{$item->min_age}}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Max Age</label>
                                                <input type="number" id="email-id-vertical" class="form-control"
                                                    name="max_age" placeholder="Max Age" value="{{$item->max_age}}">
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
