# PostaHub

PostaHub la project Laravel theo de bai tong hop, tap trung vao he thong dang bai, thao luan, like va quan ly noi dung cho ca user va admin.

## Muc tieu chinh
- Tach rieng he thong dang nhap `user` va `admin` (khong dung chung bang).
- Ho tro `post`, `comment`, reply comment, `like`.
- Quan ly `media` cho ca post va comment (polymorphic).
- Co import/export, phan quyen, logging, queue, schedule, cache theo yeu cau de bai.

## Cong nghe
- Laravel
- PHP 8.2+ / 8.3
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

