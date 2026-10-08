@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <h3>Ubah pengguna</h3>
    </div>
    <section id="basic-vertical-layouts">
        <div class="row match-height">
            <div class="col">
                <div class="card">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="card-content">
                        <div class="card-body">
                            <form class="form form-vertical" method="POST" action="{{ route('users.update', $item->id) }}">
                                @method('PUT')
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="first-name-vertical">Name</label>
                                                <input type="text" id="first-name-vertical" class="form-control"
                                                    name="name" placeholder="Name" value="{{ $item->name }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">No Hp</label>
                                                <input type="number" id="email-id-vertical" class="form-control"
                                                    name="phone" placeholder="Phone" value="{{ $item->phone }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Nama Orang Tua</label>
                                                <input type="text" id="email-id-vertical" class="form-control"
                                                    name="parent_name" placeholder="Nama Orang Tua"
                                                    value="{{ $item->parent_name }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Tanggal Lahir</label>
                                                <input type="date" id="email-id-vertical" class="form-control"
                                                    name="bod" placeholder="Nama Orang Tua" value="{{ $item->bod }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">ROLES</label>
                                                <select name="roles" id="roles" class="form-control">
                                                    <option value="" selected disabled>Pilih Role</option>
                                                    @foreach (\App\Enums\UserRole::cases() as $role)
                                                        <option value="{{ $role->value }}"
                                                            {{ $item->roles === $role ? 'selected' : '' }}>
                                                            {{ $role->label() }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
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
