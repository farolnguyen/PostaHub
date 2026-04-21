<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý quyền user</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Quyền đăng bài / bình luận theo user</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                <tr>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Quyền (user_rules)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    @php
                        $canPost = $user->rule?->can_post ?? true;
                        $canComment = $user->rule?->can_comment ?? true;
                    @endphp
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <form action="{{ route('admin.rule.update', $user) }}" method="post" class="d-flex flex-wrap align-items-center">
                                @csrf
                                @method('PUT')
                                <div class="form-check mr-4 mb-2 mb-md-0">
                                    <input type="hidden" name="can_post" value="0">
                                    <input class="form-check-input" type="checkbox" name="can_post" id="can_post_{{ $user->id }}" value="1" @checked($canPost)>
                                    <label class="form-check-label" for="can_post_{{ $user->id }}">can_post</label>
                                </div>
                                <div class="form-check mr-4 mb-2 mb-md-0">
                                    <input type="hidden" name="can_comment" value="0">
                                    <input class="form-check-input" type="checkbox" name="can_comment" id="can_comment_{{ $user->id }}" value="1" @checked($canComment)>
                                    <label class="form-check-label" for="can_comment_{{ $user->id }}">can_comment</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary ml-md-auto">Lưu</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>
</body>
</html>
