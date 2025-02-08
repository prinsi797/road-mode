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
                                        <label for="area_name">Name</label>
                                        <input type="text" name="area_name" class="form-control k-input" id="area_name"
                                            @if ($edit) value="{{ $data->area_name }}" @else value="{{ old('area_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="distance_from_branch">Distance From Branch</label>
                                        <input type="text" name="distance_from_branch" class="form-control k-input"
                                            id="distance_from_branch"
                                            @if ($edit) value="{{ $data->distance_from_branch }}" @else value="{{ old('distance_from_branch') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city_id">City</label>
                                        <select name="city_id" class="form-control k-input" id="city_id">
                                            <option value="">Select City</option>
                                            @foreach ($citys as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ $edit && $data->city_id == $city->id ? 'selected' : '' }}>
                                                    {{ $city->city_name }}
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
                                    Update Changes
                                @else
                                    Add City
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
