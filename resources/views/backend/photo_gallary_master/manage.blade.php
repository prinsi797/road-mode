@extends($app_layout)
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
            @include($theme_name . '.layouts.partial.breadcrumb')

            <div class="col-md-12 form_page">
                <form action="{{ $form_action }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @if ($edit)
                        <input type="hidden" value="{{ $data->id }}" name="id">
                    @endif

                    <div class="card">
                        <div class="card-body">
                            <div class="row form_sec">
                                <div class="col-12">
                                    <h5>Branch Details</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo_name">Photo Name</label>
                                        <input type="text" name="photo_name" class="form-control k-input" id="photo_name"
                                            @if ($edit) value="{{ $data->photo_name }}" @else value="{{ old('photo_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo_description">Photo Description</label>
                                        <input type="text" name="photo_description" class="form-control k-input"
                                            id="photo_description"
                                            @if ($edit) value="{{ $data->photo_description }}" @else value="{{ old('photo_description') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo">Photo</label>
                                        <input type="file" name="photo" class="form-control k-input" id="photo">
                                        @if ($edit && $data->photo)
                                            <small>Current photo: <a href="{{ asset($data->photo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo_for">photo_for</label>
                                        <select name="photo_for" class="form-control k-input" id="photo_for">
                                            @foreach (photoFor() as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (isset($data) && $data->photo_for == $key) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br />
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn k-btn k-btn-primary add_site">
                                @if ($edit)
                                    Update Gallery
                                @else
                                    Add Gallery
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
