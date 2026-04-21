@extends('layouts.auth')

@section('title', 'Admin quên mật khẩu')

@section('content')
<div class="card shadow-sm border-primary">
    <div class="card-body">
        <h1 class="h4 mb-3 text-center">Quên mật khẩu (Admin)</h1>
        <p class="text-muted small">Nhập email admin để nhận link đặt lại mật khẩu.</p>

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

        <form method="post" action="{{ route('admin.password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email admin</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Gửi link reset</button>
        </form>

        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('admin.login.form') }}">Quay lại admin login</a>
        </p>
    </div>
</div>
@endsection