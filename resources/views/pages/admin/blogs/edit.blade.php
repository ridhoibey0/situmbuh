@extends('layouts.admin')
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Ubah artikel</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Blog</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('blogs.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $item->title }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="content">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required>{!! $item->content !!}</textarea>

                        </div>
                        <div class="form-group mb-3">
                            <label for="image">Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                            @if ($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="img-thumbnail mt-2" width="200">
                            @endif
                        </div>
                        <div class="form-group mb-3">
                            <label for="is_published">Published</label>
                            <select class="form-control" id="is_published" name="is_published">
                                <option value="0" {{ $item->is_published == 0 ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ $item->is_published == 1 ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Blog</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('addon-script')
  <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
  <script>
CKEDITOR.replace('content', {
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
