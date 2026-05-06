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
Lenh nay se seed:
- 1 admin mac dinh (`admin@postahub.local` / `password`)
- bo du lieu semantic search (user random + post da chu de, da do dai)

### 5b) Seed lai bo du lieu semantic (tuy chinh so luong)
```bash
php artisan db:seed --class=SemanticSearchDatasetSeeder
```
Mac dinh:
- `SEMANTIC_SEED_USERS=1000`
- `SEMANTIC_SEED_POSTS_PER_USER=100` (tuong ung `100000` posts tong)
- Co the override truc tiep tong posts bang `SEMANTIC_SEED_POSTS`.

User semantic duoc tao random ten va email theo domain `@semantic.postahub.local`, mat khau `password`.


Test nhanh (it user/post):
```bash
SEMANTIC_SEED_USERS=20 SEMANTIC_SEED_POSTS_PER_USER=10 php artisan db:seed --class=SemanticSearchDatasetSeeder
```

### 6) Build frontend va chay app
```bash
npm run build
php artisan storage:link
php artisan serve
```

Mo trinh duyet: `http://127.0.0.1:8000`

## Tai khoan seed mac dinh
- Admin:
  - Email: `admin@postahub.local`
  - Password: `password`
- Users:
  - Sinh random khi seed, domain email: `@semantic.postahub.local`
  - Password: `password`

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

## Ghi chu bao mat co ban (FW4)
- Da bo sung sanitize HTML cho noi dung CKEditor (post/comment) bang `mews/purifier` theo whitelist tag/attribute.
- Da bo sung throttle cho login va cac endpoint upload/comment de giam spam/bruteforce.
- Da bo sung security headers co ban qua middleware global (`nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`).

## Cau truc du lieu hien tai
- `users`, `admins`
- `posts`
- `comments` (polymorphic qua `commentable`)
- `media` (polymorphic qua `mediable`)
- `likes`
- `user_rules`
- He thong: `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`

## Sitemap muc tieu (tom tat)
- Public: `/`, `/search`, `/login`, `/register`, `/forget_password`, `/logout`, `/post/detail`
- User: `/mypage/post`, `/mypage/like`, `/mypage/profile`
- Admin: `/admin/login`, `/admin/register`, `/admin/forget_password`, `/admin/logout`, `/admin/post/*`, `/admin/media/*`, `/admin/comment/*`, `/admin/export`, `/admin/import`, `/admin/rule`

## Ghi chu
- Ke hoach implementation theo phase duoc cap nhat trong `progess.md`.

## Setup semantic search (Task 2, Qdrant)
### 1) Chay Qdrant bang Docker (lam tay)
Trong thu muc project:
```bash
docker compose -f docker-compose.qdrant.yml up -d
```

Kiem tra service:
```bash
curl http://127.0.0.1:6333/collections
```

Neu can stop:
```bash
docker compose -f docker-compose.qdrant.yml down
```

### 2) Cau hinh env (lam tay)
Them/doi cac bien trong `.env` (embedding local — Python service o `PostaHub/embedding-service`):
```env
SEMANTIC_SEARCH_ENABLED=true
QDRANT_URL=http://127.0.0.1:6333
QDRANT_API_KEY=
QDRANT_COLLECTION=post_chunks
QDRANT_TIMEOUT_SECONDS=10

EMBEDDING_PROVIDER=local_http
EMBEDDING_HTTP_BASE_URL=http://127.0.0.1:8001
EMBEDDING_MODEL=intfloat/multilingual-e5-small
EMBEDDING_DIMENSIONS=384
EMBEDDING_TIMEOUT_SECONDS=30

SEMANTIC_CHUNK_SIZE_CHARS=1200
SEMANTIC_CHUNK_OVERLAP_CHARS=200
SEMANTIC_INDEX_BATCH_SIZE=100
```

Chay embedding service (terminal rieng): `cd embedding-service && source .venv/bin/activate && uvicorn main:app --host 127.0.0.1 --port 8001` (tu thu muc `PostaHub/`)

Kiem tra Qdrant + embedding + tao collection neu chua co:
```bash
php artisan semantic:ensure-infrastructure
```

Tuy chon: `php artisan semantic:ensure-infrastructure --skip-embedding` (chi Qdrant, dung khi biet truoc kich thuoc vector khop config).

Neu collection `post_chunks` da tao truoc day voi size 1536 ma gio dung E5 (384), xoa collection roi chay lai ensure (dev):
```bash
curl -X DELETE http://127.0.0.1:6333/collections/post_chunks
php artisan semantic:ensure-infrastructure
```

Index semantic (can embedding-service + Qdrant dung kich thuoc):
```bash
php artisan search:reindex-posts --post=1
php artisan search:reindex-posts
```

### Queue semantic (Phase 4)
Worker can xu ly hang doi `semantic` (bai don le) va `semantic-bulk` (reindex theo lo sau seed):
```bash
php artisan queue:work database --queue=semantic,semantic-bulk,default
```
Script `composer run dev` da them `--queue=semantic,semantic-bulk,default` cho `queue:listen`.

Tat bulk job sau seed (chi insert DB, khong xep hang index): dat `SEMANTIC_SEED_QUEUE_BULK_REINDEX=false` trong `.env`.

Tim kiem cong khai: `GET /search?q=...` (semantic neu `SEMANTIC_SEARCH_ENABLED=true` + embedding + Qdrant; fallback SQL LIKE). Debug score: `APP_DEBUG=true` va them `&debug_scores=1`.

Sau do clear config khi doi env:
```bash
php artisan config:clear
```

### 3) Tao bang tracking chunk/index
```bash
php artisan migrate
```

Bang moi: `post_semantic_chunks` (luu chunk text, hash, trang thai indexed, loi gan nhat, vector point id).

