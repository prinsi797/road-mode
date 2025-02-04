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
                                        <label for="category_id">Category</label>
                                        <select name="category_id" class="form-control k-input" id="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $edit && $data->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control k-input" id="name"
                                            @if ($edit) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo">Photo</label>
                                        <input type="file" name="photo" class="form-control k-input" id="photo">
                                        @if ($edit && $data->photo)
                                            <small>Current Photo: <a href="{{ asset('storage/' . $data->photo) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price">Price</label>
                                        <input type="text" name="price" class="form-control k-input" id="price"
                                            @if ($edit) value="{{ $data->price }}" @else value="{{ old('price') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sell_price">Sell Price</label>
                                        <input type="text" name="sell_price" class="form-control k-input" id="sell_price"
                                            @if ($edit) value="{{ $data->sell_price }}" @else value="{{ old('sell_price') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="suggestion">Suggestion</label>
                                        <input type="text" name="suggestion" class="form-control k-input" id="suggestion"
                                            @if ($edit) value="{{ $data->suggestion }}" @else value="{{ old('suggestion') }}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description">Description</label>
                                        <textarea name="description" class="form-control k-input" id="description">{{ $edit ? $data->description : old('description') }}</textarea>
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
