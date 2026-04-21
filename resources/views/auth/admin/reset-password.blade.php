@extends('layouts.auth')

@section('title', 'Admin đặt lại mật khẩu')

@section('content')
<div class="card shadow-sm border-primary">
    <div class="card-body">
        <h1 class="h4 mb-3 text-center">Đặt lại mật khẩu (Admin)</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label>Email admin</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>
@endsection