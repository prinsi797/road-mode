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
                                        <label for="br_address">Branch Address</label>
                                        <input type="text" name="br_address" class="form-control k-input" id="br_address"
                                            @if ($edit) value="{{ $data->br_address }}" @else value="{{ old('br_address') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_owner_name">Owner Name</label>
                                        <input type="text" name="br_owner_name" class="form-control k-input"
                                            id="br_owner_name"
                                            @if ($edit) value="{{ $data->br_owner_name }}" @else value="{{ old('br_owner_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_photo">Photo</label>
                                        <input type="file" name="br_photo" class="form-control k-input" id="br_photo">
                                        @if ($edit && $data->br_photo)
                                            <small>Current Photo: <a href="{{ asset($data->br_photo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_sign">Sign</label>
                                        <input type="file" name="br_sign" class="form-control k-input" id="br_sign">
                                        @if ($edit && $data->br_sign)
                                            <small>Current Photo: <a href="{{ asset($data->br_sign) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_owner_email">Owner Email</label>
                                        <input type="email" name="br_owner_email" class="form-control k-input"
                                            id="br_owner_email"
                                            @if ($edit) value="{{ $data->br_owner_email }}" @else value="{{ old('br_owner_email') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_mobile">Owner Mobile</label>
                                        <input type="text" name="br_mobile" class="form-control k-input" id="br_mobile"
                                            @if ($edit) value="{{ $data->br_mobile }}" @else value="{{ old('br_mobile') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="mb-3">
                                        <label for="br_city_id">City</label>
                                        <select name="br_city_id" class="form-control k-input" id="br_city_id">
                                            <option value="">Select City</option>
                                            @foreach ($cities as $citie)
                                                <option value="{{ $citie->id }}"
                                                    {{ $edit && $data->br_city_id == $citie->id ? 'selected' : '' }}>
                                                    {{ $citie->city_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_state">State</label>
                                        <input type="text" name="br_state" class="form-control k-input" id="br_state"
                                            @if ($edit) value="{{ $data->br_state }}" @else value="{{ old('br_state') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_start_Date">Start Date</label>
                                        <input type="date" name="br_start_Date" class="form-control k-input"
                                            id="br_start_Date"
                                            @if ($edit) value="{{ $data->br_start_Date }}" @else value="{{ old('br_start_Date') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_end_date">End Date</label>
                                        <input type="date" name="br_end_date" class="form-control k-input"
                                            id="br_end_date"
                                            @if ($edit) value="{{ $data->br_end_date }}" @else value="{{ old('br_end_date') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_renew_year">Renew Year</label>
                                        <input type="text" name="br_renew_year" class="form-control k-input"
                                            id="br_renew_year"
                                            @if ($edit) value="{{ $data->br_renew_year }}" @else value="{{ old('br_renew_year') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_connection_link">Connection Link</label>
                                        <input type="text" name="br_connection_link" class="form-control k-input"
                                            id="br_connection_link"
                                            @if ($edit) value="{{ $data->br_connection_link }}" @else value="{{ old('br_connection_link') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_db_name">Database Name</label>
                                        <input type="text" name="br_db_name" class="form-control k-input"
                                            id="br_db_name"
                                            @if ($edit) value="{{ $data->br_db_name }}" @else value="{{ old('br_db_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_user_name">Database User Name</label>
                                        <input type="text" name="br_user_name" class="form-control k-input"
                                            id="br_user_name"
                                            @if ($edit) value="{{ $data->br_user_name }}" @else value="{{ old('br_user_name') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_password">Database Password</label>
                                        <input type="text" name="br_password" class="form-control k-input"
                                            id="br_password"
                                            @if ($edit) value="{{ $data->br_password }}" @else value="{{ old('br_password') }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_area_id">Area</label>
                                        <select name="br_area_id" id="br_area_id"
                                            class="form-control @error('br_area_id') is-invalid @enderror">
                                            <option value="">Select Area</option>
                                            @foreach ($areas as $area)
                                                <option value="{{ $area->id }}"
                                                    {{ old('br_area_id', $edit ? $data->br_area_id : '') == $area->id ? 'selected' : '' }}>
                                                    {{ $area->area_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('br_area_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="br_pin_code">Pin Code</label>
                                        <input type="text" name="br_pin_code" class="form-control k-input"
                                            id="br_pin_code"
                                            @if ($edit) value="{{ $data->br_pin_code }}" @else value="{{ old('br_pin_code') }}" @endif>
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
                                        <input type="text" name="created_by" class="form-control k-input"
                                            id="created_by"
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
                                    Update Branch
                                @else
                                    Add Branch
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
