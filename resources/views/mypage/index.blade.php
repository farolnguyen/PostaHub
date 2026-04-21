<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mypage — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('user.index') }}">Mypage</a>
        <div>
            <a class="btn btn-outline-light btn-sm" href="{{ route('home') }}">Trang chủ</a>
            <form action="{{ route('user.logout') }}" method="post" class="d-inline ml-2">
                @csrf
                <button type="submit" class="btn btn-light btn-sm">Đăng xuất</button>
            </form>
        </div>
    </div>
</nav>
<div class="container">
    <h1 class="h3">Xin chào, {{ Auth::guard('web')->user()->name }}</h1>
    <p class="text-muted">Mypage da co 3 khu chinh theo sitemap: post, like va profile.</p>
    <a href="{{ route('mypage.post.index') }}" class="btn btn-primary">Post cua toi</a>
    <a href="{{ route('mypage.like.index') }}" class="btn btn-outline-primary ml-2">Bai da like</a>
    <a href="{{ route('mypage.profile.index') }}" class="btn btn-outline-secondary ml-2">Profile</a>
</div>
</body>
</html>
