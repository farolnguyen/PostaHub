@extends('layouts.auth')

@section('title', 'Admin đăng nhập')

@section('content')
    <div class="card shadow-sm border-primary">
        <div class="card-body">
            <h1 class="h4 mb-4 text-center">Đăng nhập (Admin)</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="remember" id="remember" value="1"
                           class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('admin.register.form') }}">Tạo tài khoản admin</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('home') }}">Về trang chủ</a>
            </p>
        </div>
    </div>
@endsection