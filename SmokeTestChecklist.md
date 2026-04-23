# Smoke Test Checklist (FW3)

Ngay test: ____________________
Nguoi test: ___________________
Moi truong: local / staging / production (khoanh tron)

## A. Ket qua smoke tu dong da chay

- [x] `GET /` => `200`
- [x] `GET /post/non-existing-slug` => `404` (khong vo trang loi 500)
- [x] `GET /login` => `200`
- [x] `GET /register` => `200`
- [x] `GET /admin/login` => `200`
- [x] `GET /admin/register` => `200`
- [x] `GET /mypage` (guest) => `302` redirect auth
- [x] `GET /admin` (guest) => `302` redirect auth admin
- [x] `php artisan route:list --except-vendor` hien day du route public/mypage/admin theo sitemap

## B. Smoke test tay theo sitemap (can tick PASS)

### B1. Public + User auth
- [ ] Dang ky user moi thanh cong.
- [ ] Dang nhap user thanh cong.
- [ ] Forgot/reset password user chay du luong.
- [ ] User logout thanh cong.
- [ ] Trang chu feed load, phan trang hien thi dung (khong loi icon mui ten).
- [ ] Vao chi tiet post bat ky khong loi.

### B2. Mypage (user)
- [ ] `/mypage/post` list du lieu dung theo user.
- [ ] Tao/sua/xoa post thanh cong.
- [ ] Like/unlike post cap nhat dung.
- [ ] `/mypage/like` hien danh sach da like.
- [ ] `/mypage/profile` hien comment cua user + nguoi da like bai cua user.

### B3. Admin auth + dashboard
- [ ] Dang ky admin (neu can) / dang nhap admin thanh cong.
- [ ] Admin verify email (neu chua verify) va vao duoc dashboard.
- [ ] Forgot/reset password admin chay du luong.
- [ ] Admin logout thanh cong.

### B4. Admin CRUD
- [ ] `admin/post` CRUD thanh cong (create/edit/delete/detail).
- [ ] `admin/comment` CRUD thanh cong.
- [ ] `admin/media` upload/edit/delete/detail thanh cong.
- [ ] `admin/rule` bat/tat `can_post`, `can_comment` va user bi anh huong dung.

### B5. Export / Import
- [ ] Export users CSV/XLSX mo duoc file, bo 2 cot created/updated.
- [ ] Export posts CSV/XLSX dung content (plain text) + URL day du.
- [ ] Import users CSV thanh cong voi file hop le.
- [ ] Import users CSV bao loi dung dong voi file sai dinh dang.

### B6. Logging / Queue / Scheduler
- [ ] Loi khu vuc mypage duoc ghi vao `storage/logs/log_mypage*.log`.
- [ ] `php artisan app:backup-users` tao file backup duong dan dung.
- [ ] Queue worker gui email thong bao backup cho admin.
- [ ] `php artisan schedule:list` hien command backup 01:00.
- [ ] Cron `schedule:run` moi phut da cau hinh va co log hoac ket qua xac nhan.

### B7. Cache
- [ ] Homepage feed, post detail, mypage (post/like/profile) load on dinh khi co du lieu lon.
- [ ] Sau CRUD post/comment/like, du lieu moi hien thi dung (cache invalidation OK).

## C. Ghi chu loi / ket qua

- Loi phat hien:
  - _________________________________
  - _________________________________
- Huong xu ly:
  - _________________________________
  - _________________________________

