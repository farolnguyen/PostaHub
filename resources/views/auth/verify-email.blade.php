<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Xác thực email</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Xác thực email</h1>
                    <p class="text-muted">
                        Vui lòng kiểm tra email để xác thực tài khoản trước khi tiếp tục sử dụng hệ thống.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="post" action="{{ auth('admin')->check() ? route('admin.verification.send') : route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Gửi lại email xác thực</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

