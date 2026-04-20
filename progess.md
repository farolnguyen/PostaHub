# PostaHub - Project Progress Plan

## 1) Muc tieu tong quan
- Xay dung website dang bai, thao luan, like bai viet theo dung sitemap de bai.
- Tach rieng auth cho `admin` va `user` (khong dung chung bang users).
- Hoan thanh cac yeu cau Laravel: Routing, Controllers, Validation, Form Requests, Polymorphic, Policies/Gates, Import/Export, Logging/Exception, Queue, Scheduler, Cache.

## 2) Pham vi chuc nang can co
- Public: xem bai viet, chi tiet bai, login/register/forget password/logout cho user.
- Mypage user: quan ly bai viet, bai da like, profile (comment cua minh + ai like bai cua minh).
- Admin: login/register/forget password/logout; quan ly post/comment/media; import/export; rule phan quyen user.
- Nghiep vu chinh: post, comment, reply comment, like post, media cho post/comment.

## 3) Kien truc du lieu (database)
- `users`: tai khoan user frontend.
- `admins`: tai khoan admin backend.
- `posts`: `user_id`, `title`, `url`, `content`, `thumbnail`.
- `comments`: `user_id`, `content`, `image`, `commentable_type`, `commentable_id` (polymorphic).
- `media`: `path`, `type`, `size`, `mediable_type`, `mediable_id` (polymorphic).
- `likes`: `user_id`, `post_id`, unique (`user_id`, `post_id`).
- `user_rules`: `user_id`, `can_post`, `can_comment`.
- Bang he thong: `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`.

## 4) Ke hoach trien khai theo phase

### Phase A - Khoi tao nen tang (hoan thanh)
Muc tieu:
- Cai va cau hinh moi truong Laravel + MySQL + Node.
- Khoi tao project `PostaHub`.
- Ket noi DB thanh cong, migrate thanh cong.
- Co migration/model nen cho cac bang nghiep vu.

Viec can hoan tat:
- [x] Tao DB `postahub_db` + user `postahub_user`.
- [x] Cau hinh `.env` (DB, APP_NAME, SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION).
- [x] Tao `APP_KEY` va clear config/cache.
- [x] Migrate bang mac dinh (`users`, `sessions`, `cache`, `jobs`, `failed_jobs`...).
- [x] Tao migration/model: `Admin`, `Post`, `Comment`, `Media`, `Like`, `UserRule`.
- [x] Hoan chinh schema chi tiet cac migration custom.
- [x] Chay `php artisan migrate` khong loi cho tat ca migration.
- [x] Hoan chinh seeders (`AdminSeeder`, `UserSeeder`) va `php artisan db:seed`.

Output Phase A:
- App chay duoc tai `http://127.0.0.1:8000`.
- DB du bang va seed du lieu test.

### Phase B - Auth tach rieng User/Admin
Muc tieu:
- User va Admin co login/register/forgot password/logout rieng.

Viec can lam:
- [x] Cau hinh guard/provider trong `config/auth.php`.
- [x] Tao controller auth cho user (login/register).
- [x] Tao controller auth cho admin (`/admin/*`) (login/register).
- [x] Khai bao route login/register cho user va admin.
- [ ] Tao view auth rieng cho user/admin.
- [ ] Cap nhat model va hoan thien auth flow can thiet.
- [ ] Hoan thien redirect sau login/logout cho tung guard.
- [ ] Test tay toan bo luong user/admin auth.

Trang thai hien tai:
- Da xong phan Controller + Router cho login/register.
- Chua code: View, cap nhat model bo sung, redirect/login-logout day du, test tay.

Output:
- Dang nhap user/admin doc lap, khong nham quyen.

### Phase C - Core business: Post, Comment, Like, Media
Muc tieu:
- Hoan thanh luong dang bai, thao luan, like va quan ly media.

Viec can lam:
- CRUD post cho user (mypage) va admin (quan tri).
- Dung CKEditor cho content post/comment.
- Comment cho post + reply cho comment (polymorphic).
- Like/unlike post.
- Media upload nhieu anh cho post/comment.
- Dung Blade Component cho form input.
- Dung Form Request cho validate.

Output:
- Nguoi dung tao bai, binh luan, tra loi, like day du.

### Phase D - Mypage, Rule, Policy/Gate
Muc tieu:
- Hoan chinh dashboard user va phan quyen nghiep vu.

Viec can lam:
- `/mypage/post`: danh sach bai cua user.
- `/mypage/like`: danh sach bai da like.
- `/mypage/profile`: comment cua user + danh sach ai like bai cua user.
- `/admin/rule`: bat/tat quyen `can_post`, `can_comment`.
- Ap dung Policy/Gate cho cac hanh dong CRUD.

Output:
- User chi thao tac duoc dung quyen, admin quan tri rule de dang.

### Phase E - Export/Import
Muc tieu:
- Xuat user + post ra CSV/Excel, import user tu CSV.

Viec can lam:
- Export user (bo `created_at`, `updated_at`).
- Export post (bo `created_at`, `updated_at`).
- Import user tu CSV, co validate va thong bao loi dong.
- Giao dien admin cho export/import.

Output:
- Du lieu xuat/nhap dung format va on dinh.

### Phase F - Error handling, logging, exception, queue, scheduler
Muc tieu:
- Co co che bat loi, ghi log rieng cho mypage, va backup tu dong.

Viec can lam:
- Tao custom Exception hoac custom Logging channel `log_mypage`.
- Ghi log co context (user_id, route, payload toi gian) cho loi mypage.
- Tao Artisan command backup bang user hang ngay ra CSV.
- Gui email cho admin khi co backup moi (queue job).
- Cau hinh scheduler va crontab Linux moi phut.

Output:
- He thong co log ro rang + backup tu dong + thong bao email.

### Phase G - Cache va toi uu
Muc tieu:
- Tang toc do tai trang va truy van.

Viec can lam:
- Cache homepage, post detail, thong ke mypage.
- Invalidate cache khi create/update/delete post/comment/like.
- Kiem thu tinh nhat quan du lieu sau invalidate.

Output:
- Site nhanh hon, du lieu cap nhat dung.

## 5) Tieu chuan hoan thanh (Definition of Done)
- Tat ca route trong sitemap truy cap dung.
- Auth admin/user tach biet, hoat dong on.
- Post/comment/reply/like/media hoat dong day du.
- Rule phan quyen user hoat dong dung.
- Export/import pass voi file thuc te.
- Log mypage ghi dung file rieng.
- Scheduler + backup + mail thong bao hoat dong.
- Cache hoat dong va invalidation dung.

## 6) Thu tu uu tien thuc hien (de tranh loi)
1. Hoan tat migration + seeder (Phase A).
2. Auth tach guard (Phase B).
3. Core business Post/Comment/Like/Media (Phase C).
4. Rule + Policy/Gate + Mypage (Phase D).
5. Export/Import (Phase E).
6. Logging/Exception + Command/Queue/Scheduler (Phase F).
7. Cache va toi uu (Phase G).

## 7) Lenh kiem tra nhanh moi ngay
- `php artisan migrate:status`
- `php artisan route:list`
- `php artisan test` (neu da co test)
- `php artisan optimize:clear`

## 8) Ghi chu
- File nay la ke hoach va checklist. Cap nhat dau `[ ]` -> `[x]` sau moi muc hoan thanh.
- Neu doi pham vi hoac doi uu tien, cap nhat truc tiep vao cac Phase.
