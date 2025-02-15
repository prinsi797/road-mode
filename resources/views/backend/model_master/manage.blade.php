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
                                        <label for="model_name">Model Name</label>
                                        <input type="text" name="model_name" class="form-control k-input" id="model_name"
                                            @if ($edit) value="{{ $data->model_name }}" @else value="{{ old('model_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="model_photo">Photo</label>
                                        <input type="file" name="model_photo" class="form-control k-input"
                                            id="model_photo">
                                        @if ($edit && $data->model_photo)
                                            <small>Current Photo: <a href="{{ asset($data->model_photo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="model_description">Model Description</label>
                                        <input type="text" name="model_description" class="form-control k-input"
                                            id="model_description"
                                            @if ($edit) value="{{ $data->model_description }}" @else value="{{ old('model_description') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label for="com_id">Company</label>
                                        <select name="com_id" class="form-control k-input" id="com_id">
                                            <option value="">Select Company</option>
                                            @foreach ($company as $companies)
                                                <option value="{{ $companies->id }}"
                                                    {{ $edit && $data->com_id == $companies->id ? 'selected' : '' }}>
                                                    {{ $companies->com_name }}
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
                                    Update Model
                                @else
                                    Add Model
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
