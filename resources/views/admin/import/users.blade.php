<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Import User (CSV)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Import/Export User </h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php($result = session('import_result'))
    @if (is_array($result))
        <div class="alert alert-info">
            <p class="mb-1"><strong>Đã import thành công:</strong> {{ (int) ($result['imported'] ?? 0) }} dòng.</p>
            @if (! empty($result['errors']))
                <p class="mb-2"><strong>Các dòng lỗi:</strong></p>
                <ul class="mb-0 pl-3 small">
                    @foreach ($result['errors'] as $err)
                        <li>
                            Dòng {{ (int) ($err['line'] ?? 0) }}:
                            {{ implode(' ', $err['messages'] ?? []) }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.import.users.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="csv_file">Chọn file CSV</label>
                    <input type="file" name="csv_file" id="csv_file" class="form-control-file @error('csv_file') is-invalid @enderror" accept=".csv,.txt,text/csv" required>
                    @error('csv_file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Tải lên và import</button>
                <a href="{{ route('admin.import.users.template') }}" class="btn btn-outline-secondary ml-2">Tải file mẫu CSV</a>
                
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h5">Export dữ liệu</h2>
            <div class="d-flex flex-wrap">
                <a href="{{ route('admin.export.users.csv') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">User — CSV</a>
                <a href="{{ route('admin.export.users.xlsx') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">User — Excel</a>
                <a href="{{ route('admin.export.posts.csv') }}" class="btn btn-outline-success btn-sm mr-2 mb-2">Post — CSV</a>
                <a href="{{ route('admin.export.posts.xlsx') }}" class="btn btn-outline-success btn-sm mb-2">Post — Excel</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
