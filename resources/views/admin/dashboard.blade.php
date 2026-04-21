<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Admin Dashboard</h1>
            <p class="text-muted mb-4">Trang quản trị.</p>
            <a href="{{ route('admin.post.index') }}" class="btn btn-primary mr-2">Quản lý bài viết</a>
            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-primary mr-2">Quản lý media</a>
            <a href="{{ route('admin.comment.index') }}" class="btn btn-outline-primary mr-2">Quản lý bình luận</a>
            <a href="{{ route('admin.rule.index') }}" class="btn btn-outline-primary mr-2">Quyền user (rule)</a>
            <form action="{{ route('admin.logout') }}" method="post" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">Đăng xuất admin</button>
            </form>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary ml-2">Về trang chủ</a>
        </div>
    </div>
</div>
</body>
</html>
