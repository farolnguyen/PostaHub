<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="{{ route('home') }}">{{ config('app.name', 'PostaHub') }}</a>

        <div class="d-flex align-items-center flex-grow-1 justify-content-center">
            <a class="btn btn-outline-light btn-sm mr-2" href="{{ route('home') }}">Trang chủ</a>
            <a class="btn btn-outline-light btn-sm mr-2" href="{{ route('user.index') }}">Trang cá nhân</a>
            @if (auth('admin')->check())
                <a class="btn btn-outline-light btn-sm" href="{{ route('admin.dashboard') }}">Dashboard</a>
            @endif
        </div>

        <div class="d-flex align-items-center">
            @if (auth('web')->check())
                <span class="navbar-text text-white mr-2 small">Xin chào {{ auth('web')->user()->name }}</span>
                <form action="{{ route('user.logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Đăng xuất</button>
                </form>
            @elseif (auth('admin')->check())
                <span class="navbar-text text-white mr-2 small">Xin chào Admin {{ auth('admin')->user()->name }}</span>
                <form action="{{ route('admin.logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Đăng xuất</button>
                </form>
            @else
                <a class="btn btn-light btn-sm mr-2" href="{{ route('user.register.form') }}">Đăng ký</a>
                <a class="btn btn-outline-light btn-sm" href="{{ route('user.login.form') }}">Đăng nhập</a>
            @endif
        </div>
    </div>
</nav>
