@extends('layouts.auth')

@section('title', 'Đăng ký')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-4 text-center">Đăng ký (User)</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('user.register.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Họ tên</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
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
                <div class="form-group">
                    <label for="password_confirmation">Nhập lại mật khẩu</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('user.login.form') }}">Đã có tài khoản? Đăng nhập</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('home') }}">Về trang chủ</a>
            </p>
        </div>
    </div>
@endsection