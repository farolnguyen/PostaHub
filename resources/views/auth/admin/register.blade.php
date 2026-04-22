@extends('layouts.auth')

@section('title', 'Admin đăng ký')

@section('content')
    <div class="card shadow-sm border-primary">
        <div class="card-body">
            <h1 class="h4 mb-4 text-center">Đăng ký (Admin)</h1>

            <x-form.error-alert />

            <form method="post" action="{{ route('admin.register.submit') }}">
                @csrf
                <x-form.input name="name" label="Tên" required />
                <x-form.input name="email" label="Email" type="email" required />
                <x-form.input name="password" label="Mật khẩu" type="password" required />
                <x-form.input name="password_confirmation" label="Nhập lại mật khẩu" type="password" required />
                <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('admin.login.form') }}">Quay lại đăng nhập</a>
            </p>

        </div>
    </div>
@endsection