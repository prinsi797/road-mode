@extends('backend.layouts.public-app')
@section('content')
    <section class="new-password-content py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-4 py-3">
                                @if (session()->has('message'))
                                    <div class="alert alert-info">{{ session()->get('message') }}</div>
                                @endif
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                <div class="card login-card">
                                    <div class="card-body">
                                        <div class="logo text-center">
                                            <img class="img-fluid logo-img"
                                                src="{{ asset('backend/assets/images/Logo.png') }}" alt="Kethod" />
                                        </div>
                                        <h5 class="mb-4 text-center">
                                            {{ __('Reset Password') }}
                                        </h5>
                                        <form method="POST" action="{{ route('password.update') }}">
                                            @csrf

                                            <input type="hidden" name="token" value="{{ $token }}">

                                            <div class="mb-3">
                                                <label class="form-label" for="email">{{ __('E-Mail Address') }}</label>
                                                <input id="email" type="email"
                                                    class="form-control k-input @error('email') is-invalid @enderror"
                                                    name="email" value="{{ $email ?? old('email') }}" required
                                                    autocomplete="email" autofocus>

                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label" for="password">{{ __('Password') }}</label>
                                                <input id="password" type="password"
                                                    class="form-control k-input @error('password') is-invalid @enderror"
                                                    name="password" required autocomplete="new-password">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="password-confirm">{{ __('Confirm Password') }}</label>
                                                <input id="password-confirm" type="password" class="form-control k-input"
                                                    name="password_confirmation" required autocomplete="new-password">
                                            </div>

                                            <div class="mb-3  d-grid gap-2">
                                                <button type="submit" class="btn k-btn k-btn-primary">
                                                    {{ __('Reset Password') }}
                                                </button>
                                            </div>
                                            <div class="mt-4 text-center">
                                                <p class="semibold-text mb-2"><a href="{{ route('login') }}"
                                                        data-toggle="flip" class="k-link">{{ __('Back to login?') }}</a>
                                                </p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
