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
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h1 class="h4 mb-2">
                Xin chào, {{ auth('web')->user()->name ?? ('Admin '.(auth('admin')->user()->name ?? '')) }}
            </h1>
            <p class="text-muted mb-0">Đây là khu vực cá nhân của bạn. Chọn một mục bên dưới để quản lý nội dung.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Bài viết của tôi</h2>
                    <p class="text-muted flex-grow-1 mb-3">Quản lý danh sách bài đã đăng, tạo mới hoặc chỉnh sửa nội dung.</p>
                    <a href="{{ route('mypage.post.index') }}" class="btn btn-primary btn-sm">Vào trang bài viết</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Bài đã thích</h2>
                    <p class="text-muted flex-grow-1 mb-3">Xem lại các bài bạn đã thích và truy cập nhanh nội dung quan tâm.</p>
                    <a href="{{ route('mypage.like.index') }}" class="btn btn-outline-primary btn-sm">Xem bài đã thích</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Hồ sơ cá nhân</h2>
                    <p class="text-muted flex-grow-1 mb-3">Theo dõi bình luận của bạn và các tương tác trên bài viết đã đăng.</p>
                    <a href="{{ route('mypage.profile.index') }}" class="btn btn-outline-secondary btn-sm">Mở hồ sơ</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
