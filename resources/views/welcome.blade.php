<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'PostaHub') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="{{ url('/') }}">{{ config('app.name', 'PostaHub') }}</a>
        <div class="navbar-nav ml-auto flex-row align-items-center">
            @auth('web')
                <span class="navbar-text text-white mr-3 d-none d-sm-inline small">Xin chào, {{ Auth::guard('web')->user()->name }}</span>
                <a class="btn btn-light btn-sm mr-2" href="{{ route('user.index') }}">Mypage</a>
                <form action="{{ route('user.logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Đăng xuất</button>
                </form>
            @else
                <a class="btn btn-light btn-sm mr-2" href="{{ route('user.login.form') }}">Đăng nhập</a>
                <a class="btn btn-outline-light btn-sm" href="{{ route('user.register.form') }}">Đăng ký</a>
            @endauth
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold text-dark">Chào mừng đến {{ config('app.name', 'PostaHub') }}</h1>
                <p class="lead text-muted mb-0">
                    Nơi chia sẻ bài viết, thảo luận và tương tác. Đăng nhập thành viên để tham gia cộng đồng,
                    hoặc vào khu vực quản trị nếu bạn là admin.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 font-weight-bold mb-3">Thành viên</h2>
                        <p class="text-muted mb-4">
                            Tạo tài khoản hoặc đăng nhập để đăng bài, bình luận và quản lý trang cá nhân (mypage).
                        </p>
                        @guest('web')
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('user.login.form') }}" class="btn btn-primary btn-lg mr-2 mb-2">Đăng nhập</a>
                                <a href="{{ route('user.register.form') }}" class="btn btn-outline-primary btn-lg mb-2">Đăng ký</a>
                            </div>
                        @else
                            <p class="text-success mb-3 small">Bạn đã đăng nhập bằng tài khoản thành viên.</p>
                            <a href="{{ route('user.index') }}" class="btn btn-primary mb-2">Vào Mypage</a>
                            <form action="{{ route('user.logout') }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Đăng xuất</button>
                            </form>
                        @endguest
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-primary">
                    <div class="card-body p-4">
                        <h2 class="h4 font-weight-bold mb-3">Quản trị</h2>
                        <p class="text-muted mb-4">
                            Khu vực dành cho admin: đăng nhập hoặc đăng ký tài khoản quản trị (tách biệt với thành viên).
                        </p>
                        @guest('admin')
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('admin.login.form') }}" class="btn btn-primary btn-lg mr-2 mb-2">Admin đăng nhập</a>
                                <a href="{{ route('admin.register.form') }}" class="btn btn-outline-primary btn-lg mb-2">Admin đăng ký</a>
                            </div>
                        @else
                            <p class="text-success mb-3 small">Bạn đã đăng nhập admin.</p>
                            <form action="{{ route('admin.logout') }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Đăng xuất admin</button>
                            </form>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary ml-2">Vào bảng điều khiển</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4">
            <p class="mb-0">PostaHub · Laravel · Bootstrap 4.5</p>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
</body>
</html>
