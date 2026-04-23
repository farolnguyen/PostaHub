<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>THÔNG BÁO BACKUP USER</title>
</head>
<body>
<p>Backup bảng <strong>users</strong> vừa được tạo.</p>
<ul>
    <li>Thời gian tạo: {{ $generatedAt }}</li>
    <li>Số dòng dữ liệu: {{ $rowCount }}</li>
    <li>Đường dẫn (storage/app): {{ $relativePath }}</li>
</ul>
<p>Mail được gửi từ hệ thống PostaHub.</p>
</body>
</html>

