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
                                    <h5>Basic Details</h5>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sc_name">Name</label>
                                        <input type="text" name="sc_name" class="form-control k-input" id="sc_name"
                                            @if ($edit) value="{{ $data->sc_name }}" @else value="{{ old('sc_name') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sc_bike_car">Bike Car</label>
                                        <input type="text" name="sc_bike_car" class="form-control k-input"
                                            id="sc_bike_car"
                                            @if ($edit) value="{{ $data->sc_bike_car }}" @else value="{{ old('sc_bike_car') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sc_photo">Photo</label>
                                        <input type="file" name="sc_photo" class="form-control k-input" id="sc_photo">
                                        @if ($edit && $data->sc_photo)
                                            <small>Current Photo: <a href="{{ asset('storage/' . $data->sc_photo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sc_description">Description</label>
                                        <input type="text" name="sc_description" class="form-control k-input"
                                            id="sc_description"
                                            @if ($edit) value="{{ $data->sc_description }}" @else value="{{ old('sc_description') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_status">Status</label>
                                        <select name="is_status" class="form-control k-input" id="is_status">
                                            @foreach (getStatusOptions() as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (isset($data) && $data->is_status == $key) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="created_by">Created By</label>
                                        <input type="text" name="created_by" class="form-control k-input" id="created_by"
                                            @if ($edit) value="{{ $data->created_by }}" @else value="{{ old('created_by') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modified_by">Modified By</label>
                                        <input type="text" name="modified_by" class="form-control k-input"
                                            id="modified_by"
                                            @if ($edit) value="{{ $data->modified_by }}" @else value="{{ old('modified_by') }}" @endif>
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
                                    Update Changes
                                @else
                                    Add Service
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
