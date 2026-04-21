# PostaHub — Kế hoạch tiến độ dự án

## 1) Mục tiêu tổng quan
- Xây dựng website đăng bài, thảo luận, thích bài viết theo đúng sitemap đề bài.
- Tách riêng auth cho `admin` và `user` (không dùng chung bảng users).
- Hoàn thành các yêu cầu Laravel: Routing, Controllers, Validation, Form Requests, Polymorphic, Policies/Gates, Import/Export, Logging/Exception, Queue, Scheduler, Cache.

## 2) Phạm vi chức năng cần có
- Public: xem bài viết, chi tiết bài, đăng nhập/đăng ký/quên mật khẩu/đăng xuất cho user.
- Mypage user: quản lý bài viết, bài đã thích, profile (bình luận của mình + ai thích bài của mình).
- Admin: đăng nhập/đăng ký/quên mật khẩu/đăng xuất; quản lý post/comment/media; import/export; rule phân quyền user.
- Nghiệp vụ chính: post, comment, trả lời comment, thích post, media cho post/comment.

## 3) Kiến trúc dữ liệu (database)
- `users`: tài khoản user frontend.
- `admins`: tài khoản admin backend.
- `posts`: `user_id`, `title`, `url`, `content`, `thumbnail`.
- `comments`: `user_id`, `content`, `image`, `commentable_type`, `commentable_id` (polymorphic).
- `media`: `path`, `type`, `size`, `mediable_type`, `mediable_id` (polymorphic).
- `likes`: `user_id`, `post_id`, unique (`user_id`, `post_id`).
- `user_rules`: `user_id`, `can_post`, `can_comment`.
- Bảng hệ thống: `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`.

## 4) Kế hoạch triển khai theo phase

### Phase A — Khởi tạo nền tảng (hoàn thành)
Mục tiêu:
- Cài và cấu hình môi trường Laravel + MySQL + Node.
- Khởi tạo project `PostaHub`.
- Kết nối DB thành công, migrate thành công.
- Có migration/model nền cho các bảng nghiệp vụ.

Việc cần hoàn tất:
- [x] Tạo DB `postahub_db` + user `postahub_user`.
- [x] Cấu hình `.env` (DB, APP_NAME, SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION).
- [x] Tạo `APP_KEY` và clear config/cache.
- [x] Migrate bảng mặc định (`users`, `sessions`, `cache`, `jobs`, `failed_jobs`…).
- [x] Tạo migration/model: `Admin`, `Post`, `Comment`, `Media`, `Like`, `UserRule`.
- [x] Hoàn chỉnh schema chi tiết các migration custom.
- [x] Chạy `php artisan migrate` không lỗi cho tất cả migration.
- [x] Hoàn chỉnh seeders (`AdminSeeder`, `UserSeeder`) và `php artisan db:seed`.

Output Phase A:
- App chạy được tại `http://127.0.0.1:8000`.
- DB đủ bảng và seed dữ liệu test.

### Phase B — Auth tách riêng User/Admin
Mục tiêu:
- User và Admin có đăng nhập/đăng ký/quên mật khẩu/đăng xuất riêng.

Việc cần làm:
- [x] Cấu hình guard/provider trong `config/auth.php`.
- [x] Tạo controller auth cho user (login/register).
- [x] Tạo controller auth cho admin (`/admin/*`) (login/register).
- [x] Khai báo route login/register cho user và admin.
- [x] Tạo view auth riêng cho user/admin.
- [x] Thêm luồng forgot/reset password cho user và admin.
- [x] Cập nhật model và hoàn thiện auth flow cần thiết.
- [x] Hoàn thiện redirect sau login/logout cho từng guard.
- [x] Test tay + smoke test luồng user/admin auth.

Trạng thái hiện tại:
- Đã hoàn thành login/register/logout/forgot-password cho user và admin.
- Đã tạo trang `mypage` tạm và `admin dashboard` tạm để test redirect.
- Đã test pass các luồng register/login cho user và admin.

Output:
- Đăng nhập user/admin độc lập, không nhầm quyền.

### Phase C — Core business: Post, Comment, Like, Media
Mục tiêu:
- Hoàn thành luồng đăng bài, thảo luận, thích và quản lý media.

Việc cần làm:
- CRUD post cho user (mypage) và admin (quản trị).
- Dùng CKEditor cho content post/comment.
- Comment cho post + reply cho comment (polymorphic).
- Like/unlike post.
- Media upload nhiều ảnh cho post/comment.
- Dùng Blade Component cho form input.
- Dùng Form Request cho validate.

Output:
- Người dùng tạo bài, bình luận, trả lời, thích đầy đủ.

Tiến độ hiện tại (Phase C):
- [x] C1: Dựng khung route cho post detail (public), mypage post CRUD, admin post CRUD.
- [x] C2: Hoàn thành Post CRUD (user/admin) + Form Request validate.
- [x] C3: Tích hợp CKEditor cho form tạo/sửa post.
- [x] C4: Hoàn thành comment cho post + reply comment polymorphic + sửa/xóa comment của chính mình.
- [x] C5: Like/unlike post (toggle + hiển thị tổng like trên post detail).
- [x] C6: Mypage like/profile (`/mypage/like`, `/mypage/profile`).
- [x] C7: Media upload cho post/comment (upload file + lưu polymorphic media + hiển thị gallery).
- [x] Bổ sung admin/media theo sitemap: upload/edit/delete/detail + danh sách quản lý.
- [x] Bổ sung admin/comment theo sitemap: create/edit/delete + danh sách quản lý.
- [x] C8: Trang chủ (`/`) chuyển thành bảng tin bài đăng dạng feed (list bài mới nhất, thumbnail/media, thống kê like/comment, phân trang), tách khỏi giao diện login/register kép trước đó.
- [x] C9: URL slug của post tự sinh từ tiêu đề (auto-generate), có ô preview slug readonly cập nhật realtime khi gõ tiêu đề.
- [x] C10: Đồng bộ UX upload media đa ảnh cho post/comment: thêm dần input file, preview ảnh, nút xóa từng dòng, giới hạn tối đa 5 ảnh.
- [x] C11: Cải thiện hiển thị ảnh: render ảnh từ URL/upload trực tiếp và thêm fallback ảnh lỗi (`public/images/image-fallback.png`).
- [x] C12: Bỏ input URL ảnh riêng ở comment; hỗ trợ nhúng URL ảnh trực tiếp trong nội dung bình luận (auto render `<img>` khi lưu).

Lưu ý bảo mật:
- Hiện tại post/comment detail đang render HTML bằng cú pháp raw (`{!! ... !!}`) để hiển thị đúng nội dung từ CKEditor.
- Cách này có rủi ro XSS nếu user nhập script độc hại.
- Hướng xử lý an toàn hơn ở bước tiếp theo: sanitize HTML trước khi lưu (vd: mews/purifier) hoặc whitelist tag cho phép.

### Phase D — Mypage, Rule, Policy/Gate
Mục tiêu:
- Hoàn chỉnh dashboard user và phân quyền nghiệp vụ.

Việc cần làm:
- [x] `/mypage/post`: danh sách bài của user.
- [x] `/mypage/like`: danh sách bài đã thích.
- [x] `/mypage/profile`: comment của user + danh sách ai đã thích bài của user.
- [x] `/admin/rule`: bật/tắt quyền `can_post`, `can_comment` (bảng `user_rules`, đăng ký user tự động tạo rule mặc định).
- [x] Áp dụng Policy (`PostPolicy`, `CommentPolicy`) + đăng ký `Gate::policy` trong `AppServiceProvider`; `authorize()` trên mypage post + comment user; Blade `@can` ẩn form khi không được phép.
- [x] Header dùng chung toàn site (`partials/site-header`) với hành vi theo guard (user/admin/guest), admin hiển thị nút `Dashboard` thay cho `Trang cá nhân`.
- [x] Cải tiến giao diện dashboard/mypage để dễ thao tác hơn (card layout cho admin dashboard và mypage index).

Output:
- User chỉ thao tác đúng quyền, admin quản trị rule dễ dàng.

### Phase E — Export/Import
Mục tiêu:
- Xuất user + post ra CSV/Excel, import user từ CSV.

Việc cần làm:
- Export user (bỏ `created_at`, `updated_at`).
- Export post (bỏ `created_at`, `updated_at`).
- Import user từ CSV, có validate và thông báo lỗi dòng.
- Giao diện admin cho export/import.

Output:
- Dữ liệu xuất/nhập đúng format và ổn định.

### Phase F — Error handling, logging, exception, queue, scheduler
Mục tiêu:
- Có cơ chế bắt lỗi, ghi log riêng cho mypage, và backup tự động.

Việc cần làm:
- Tạo custom Exception hoặc custom Logging channel `log_mypage`.
- Ghi log có context (user_id, route, payload tối giản) cho lỗi mypage.
- Tạo Artisan command backup bảng user hằng ngày ra CSV.
- Gửi email cho admin khi có backup mới (queue job).
- Cấu hình scheduler và crontab Linux mỗi phút.

Output:
- Hệ thống có log rõ ràng + backup tự động + thông báo email.

### Phase G — Cache và tối ưu
Mục tiêu:
- Tăng tốc độ tải trang và truy vấn.

Việc cần làm:
- Cache homepage, post detail, thống kê mypage.
- Invalidate cache khi create/update/delete post/comment/like.
- Kiểm thử tính nhất quán dữ liệu sau invalidate.

Output:
- Site nhanh hơn, dữ liệu cập nhật đúng.

## 5) Tiêu chuẩn hoàn thành (Definition of Done)
- Tất cả route trong sitemap truy cập đúng.
- Auth admin/user tách biệt, hoạt động ổn.
- Post/comment/reply/like/media hoạt động đầy đủ.
- Rule phân quyền user hoạt động đúng.
- Export/import pass với file thực tế.
- Log mypage ghi đúng file riêng.
- Scheduler + backup + mail thông báo hoạt động.
- Cache hoạt động và invalidation đúng.

## 6) Thứ tự ưu tiên thực hiện (để tránh lỗi)
1. Hoàn tất migration + seeder (Phase A).
2. Auth tách guard (Phase B).
3. Core business Post/Comment/Like/Media (Phase C).
4. Rule + Policy/Gate + Mypage (Phase D).
5. Export/Import (Phase E).
6. Logging/Exception + Command/Queue/Scheduler (Phase F).
7. Cache và tối ưu (Phase G).

## 7) Lệnh kiểm tra nhanh mỗi ngày
- `php artisan migrate:status`
- `php artisan route:list`
- `php artisan test` (nếu đã có test)
- `php artisan optimize:clear`

## 8) Ghi chú
- File này là kế hoạch và checklist. Cập nhật dấu `[ ]` → `[x]` sau mỗi mục hoàn thành.
- Nếu đổi phạm vi hoặc đổi ưu tiên, cập nhật trực tiếp vào các Phase.
