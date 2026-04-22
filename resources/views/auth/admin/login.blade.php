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
                <x-form.input name="email" label="Email" type="email" required autofocus />
                <x-form.input name="password" label="Mật khẩu" type="password" required />
                <x-form.checkbox name="remember" label="Ghi nhớ đăng nhập" />
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('admin.register.form') }}">Tạo tài khoản admin</a>
            </p>
            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('admin.password.request') }}">Quên mật khẩu?</a>
            </p>
        </div>
    </div>
@endsection