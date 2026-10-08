@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <h3>Ubah pertanyaan</h3>
    </div>
    <section id="basic-vertical-layouts">
        <div class="row match-height">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Update Questions</h4>
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
                            <form class="form form-vertical" method="POST" action="{{ route('questions.update', $item->id) }}" enctype="multipart/form-data">
                                @method('PUT')
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="first-name-vertical">Question</label>
                                               <textarea name="question" id="question" class="form-control" >{{$item->question}}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Description</label>
                                                <input type="text" value="{{$item->description}}" id="email-id-vertical" class="form-control"
                                                    name="description" placeholder="Description">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Image</label>
                                                <input type="file" id="email-id-vertical" class="form-control"
                                                    name="image" placeholder="Image">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Age Category</label>
                                                <select class="form-control" name="age_category_id" id="age_category_id">
                                                    @foreach ($category as $ctg)
                                                        <option value="{{ $ctg->id }}">{{ $ctg->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email-id-vertical">Kategori Pertanyaan</label>
                                                <select class="form-control" name="category_id" id="age_category_id">
                                                    @foreach ($questionCategory as $qctg)
                                                        <option value="{{ $qctg->id }}">{{ $qctg->name }}</option>
                                                    @endforeach
                                                </select>
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

@push('addon-script')
      <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
  <script>
CKEDITOR.replace('question', {
    on: {
        instanceReady: function () {
            // Seleksi elemen alert CKEditor
            const notifications = document.getElementById('#cke_notifications_area_content');
            notifications.style.display = 'none';
        }
    }
});

  </script>
@endpush
