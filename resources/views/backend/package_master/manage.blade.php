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
                                {{-- <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_id">Service Category</label> --}}
                                {{-- <select name="service_id" class="form-control k-input" id="service_id">
                                            <option value="">Select Service</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $edit && $data->service_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select> --}}
                                {{-- <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_id">service_id</label>
                                        <select name="service_id" class="form-control k-input" id="service_id">
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}"
                                                    @if (isset($data) && $data->service_id == $service->id) selected @endif>
                                                    {{ $service->sc_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_id">Services</label>
                                        <select name="service_id[]" class="form-control k-input" id="service_id" multiple>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}"
                                                    @if (isset($data) && in_array($service->id, explode(',', $data->service_id))) selected @endif>
                                                    {{ $service->sc_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- </div>
                                </div> --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pack_name">Pack Name</label>
                                        <input type="text" name="pack_name" class="form-control k-input" id="pack_name"
                                            @if ($edit) value="{{ $data->pack_name }}" @else value="{{ old('pack_name') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pack_other_faci">Pack other Faci</label>
                                        <input type="text" name="pack_other_faci" class="form-control k-input"
                                            id="pack_other_faci"
                                            @if ($edit) value="{{ $data->pack_other_faci }}" @else value="{{ old('pack_other_faci') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pack_description">Pack Description</label>
                                        <input type="text" name="pack_description" class="form-control k-input"
                                            id="pack_description"
                                            @if ($edit) value="{{ $data->pack_description }}" @else value="{{ old('pack_description') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pack_net_amt">Pack Net Amt</label>
                                        <input type="text" name="pack_net_amt" class="form-control k-input"
                                            id="pack_net_amt"
                                            @if ($edit) value="{{ $data->pack_net_amt }}" @else value="{{ old('pack_net_amt') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pack_duration">Pack Duration</label>
                                        <input type="text" name="pack_duration" class="form-control k-input"
                                            id="pack_duration"
                                            @if ($edit) value="{{ $data->pack_duration }}" @else value="{{ old('pack_duration') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="package_logo">Package logo</label>
                                        <input type="file" name="package_logo" class="form-control k-input"
                                            id="package_logo">
                                        @if ($edit && $data->package_logo)
                                            <small>Current Photo: <a href="{{ asset($data->package_logo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
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
                                    Add Product
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
