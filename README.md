# PostaHub

PostaHub la project Laravel theo de bai tong hop, tap trung vao he thong dang bai, thao luan, like va quan ly noi dung cho ca user va admin.

## Muc tieu chinh
- Tach rieng he thong dang nhap `user` va `admin` (khong dung chung bang).
- Ho tro `post`, `comment`, reply comment, `like`.
- Quan ly `media` cho ca post va comment (polymorphic).
- Co import/export, phan quyen, logging, queue, schedule, cache theo yeu cau de bai.

## Cong nghe
- Laravel 13
- PHP 8.3
- MySQL
- Bootstrap 4.5
- Node.js + npm (build frontend assets)

## Trang thai project
- Phase A (khoi tao nen tang): **Da hoan thanh**
- Ke hoach chi tiet: xem file `progess.md`

## Cai dat local

### 1) Clone project
```bash
git clone https://github.com/farolnguyen/PostaHub
cd PostaHub
```

### 2) Cai dependencies
```bash
composer install
npm install
```

### 3) Tao file env
```bash
cp .env.example .env
```

Cap nhat cac bien quan trong trong `.env`:
```env
APP_NAME=PostaHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=postahub_db
DB_USERNAME=postahub_user
DB_PASSWORD=StrongPass!123

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4) Tao APP_KEY
```bash
php artisan key:generate
```

### 5) Migrate + seed
```bash
php artisan migrate:fresh --seed
```

### 5b) Seed dummy (1000 user x 100 post)
Khong chay tu dong trong `DatabaseSeeder`. Lenh:
```bash
php artisan db:seed --class=DummyBulkSeeder
```
User: email `bulkdummy-seed-000000@seed.postahub.local` ... `bulkdummy-seed-000999@...`, mat khau `password`.


Test nhanh (it user/post):
```bash
DUMMY_SEED_USERS=5 DUMMY_SEED_POSTS_PER_USER=10 php artisan db:seed --class=DummyBulkSeeder
```

### 6) Build frontend va chay app
```bash
npm run build
php artisan serve
```

Mo trinh duyet: `http://127.0.0.1:8000`

## Tai khoan seed mac dinh
- Admin:
  - Email: `admin@postahub.local`
  - Password: `password`
- Users:
  - `derrick@example.com` / `password`
  - `alice@example.com` / `password`
  - `bob@example.com` / `password`

## Lenh hay dung
- Clear cache config:
  - `php artisan config:clear`
  - `php artisan cache:clear`
- Kiem tra migration:
  - `php artisan migrate:status`
- Xoa cache tong:
  - `php artisan optimize:clear`
- Chay queue worker (khi can):
  - `php artisan queue:work`
- Chay backup users thu cong:
  - `php artisan app:backup-users`
- Xem danh sach schedule:
  - `php artisan schedule:list`

## Scheduler va crontab (Phase F)
- Da cau hinh scheduler: command `app:backup-users` chay hang ngay luc `01:00`.
- Tren Linux server, them crontab de Laravel scheduler duoc kich hoat moi phut:
  - `* * * * * cd /duong-dan/PostaHub && php artisan schedule:run >> /dev/null 2>&1`

## Chien luoc cache (FW2)
- Da ap dung cache cho cac luong doc nhieu:
  - Trang chu feed (`/`)
  - Chi tiet bai viet (`/post/{url}`)
  - Mypage: danh sach post/like va thong ke profile
- Cac thao tac CRUD/like lien quan se goi version bump (`App\\Support\\SiteCache::bumpAll()`) de key cache moi duoc sinh ngay.
- Chu dong **khong cache truc tiep HTML** voi:
  - Trang auth (login/register/forgot/reset) do co token/session/validation theo request.
  - Trang admin CRUD/list de uu tien du lieu realtime va tranh stale data khi quan tri.
- Cach tiep can nay dap ung yeu cau cache theo huong an toan van hanh: toi uu phan doc lon, giu tinh nhat quan du lieu dong.

## Ghi chu upload media lon (video/audio)
- He thong da ho tro upload media `image/*`, `video/*`, `audio/*` cho post/comment (user + admin).
- Neu gap loi `The POST data is too large`, can tang gioi han PHP (`upload_max_filesize`, `post_max_size`) trong file `php.ini` cua moi truong dang chay (CLI/FPM), sau do restart service tuong ung.
- Goi y local khi chay `php artisan serve`: `upload_max_filesize=64M`, `post_max_size=80M`.

## Cau truc du lieu hien tai
- `users`, `admins`
- `posts`
- `comments` (polymorphic qua `commentable`)
- `media` (polymorphic qua `mediable`)
- `likes`
- `user_rules`
- He thong: `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`

## Sitemap muc tieu (tom tat)
- Public: `/`, `/login`, `/register`, `/forget_password`, `/logout`, `/post/detail`
- User: `/mypage/post`, `/mypage/like`, `/mypage/profile`
- Admin: `/admin/login`, `/admin/register`, `/admin/forget_password`, `/admin/logout`, `/admin/post/*`, `/admin/media/*`, `/admin/comment/*`, `/admin/export`, `/admin/import`, `/admin/rule`

## Ghi chu
- Ke hoach implementation theo phase duoc cap nhat trong `progess.md`.

