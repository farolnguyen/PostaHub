@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-4 text-center">Đăng nhập (User)</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('user.login.submit') }}">
                @csrf
                <x-form.input name="email" label="Email" type="email" required autofocus />
                <x-form.input name="password" label="Mật khẩu" type="password" required />
                <x-form.checkbox name="remember" label="Ghi nhớ đăng nhập" />
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('user.register.form') }}">Chưa có tài khoản? Đăng ký</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('admin.login.form') }}">Đăng nhập bằng tài khoản Admin</a>
            </p>

        </div>
    </div>
@endsection