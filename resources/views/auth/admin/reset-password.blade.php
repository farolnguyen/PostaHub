@extends('layouts.auth')

@section('title', 'Admin đặt lại mật khẩu')

@section('content')
<div class="card shadow-sm border-primary">
    <div class="card-body">
        <h1 class="h4 mb-3 text-center">Đặt lại mật khẩu (Admin)</h1>

        <x-form.error-alert />

        <form method="post" action="{{ route('admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-form.input name="email" label="Email admin" type="email" :value="$email" required />
            <x-form.input name="password" label="Mật khẩu mới" type="password" required />
            <x-form.input name="password_confirmation" label="Xác nhận mật khẩu" type="password" required />

            <button type="submit" class="btn btn-primary btn-block">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>
@endsection