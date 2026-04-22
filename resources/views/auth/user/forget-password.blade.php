@extends('layouts.auth')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3 text-center">Quên mật khẩu (User)</h1>
        <p class="text-muted small">Nhập email để nhận link đặt lại mật khẩu.</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('password.email') }}">
            @csrf
            <x-form.input name="email" label="Email" type="email" required />
            <button type="submit" class="btn btn-primary btn-block">Gửi link reset</button>
        </form>

        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('user.login.form') }}">Quay lại đăng nhập</a>
        </p>
    </div>
</div>
@endsection