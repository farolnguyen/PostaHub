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
<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h1 class="h3 mb-2">Bảng điều khiển Admin</h1>
            <p class="text-muted mb-0">Quản lý nội dung hệ thống, media, bình luận và phân quyền người dùng từ một nơi.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Bài viết</h2>
                    <p class="text-muted flex-grow-1 mb-3">Xem danh sách, tạo mới, chỉnh sửa và xóa bài viết.</p>
                    <a href="{{ route('admin.post.index') }}" class="btn btn-primary btn-sm">Mở quản lý bài viết</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Media</h2>
                    <p class="text-muted flex-grow-1 mb-3">Theo dõi hình ảnh, cập nhật thông tin file và dọn media lỗi.</p>
                    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-primary btn-sm">Mở quản lý media</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Bình luận</h2>
                    <p class="text-muted flex-grow-1 mb-3">Kiểm soát bình luận, chỉnh sửa nội dung và quản lý reply.</p>
                    <a href="{{ route('admin.comment.index') }}" class="btn btn-outline-primary btn-sm">Mở quản lý bình luận</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Quyền User</h2>
                    <p class="text-muted flex-grow-1 mb-3">Bật/tắt quyền đăng bài và bình luận cho từng người dùng.</p>
                    <a href="{{ route('admin.rule.index') }}" class="btn btn-outline-primary btn-sm">Mở phân quyền</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Export / Import</h2>
                    <p class="text-muted flex-grow-1 mb-3">Xuất user/post ra CSV hoặc Excel; import user từ CSV có validate theo dòng.</p>
                    <a href="{{ route('admin.import.users.form') }}" class="btn btn-outline-success btn-sm">Mở import user</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
