# PostaHub

PostaHub là project Laravel theo đề bài tổng hợp, tập trung vào hệ thống đăng bài, thảo luận, like và quản lý nội dung cho cả user và admin.

## Mục tiêu chính
- Tách riêng hệ thống đăng nhập `user` và `admin` (không dùng chung bảng).
- Hỗ trợ `post`, `comment`, reply comment, `like`.
- Quản lý `media` cho cả post và comment (polymorphic).
- Có import/export, phân quyền, logging, queue, schedule, cache theo yêu cầu đề bài.

## Công nghệ
- Laravel 13
- PHP 8.3
- MySQL
- Bootstrap 4.5 (Blade + CDN; không dùng Node/npm hay Vite)

## Trạng thái project
- Phase A (khởi tạo nền tảng): **Đã hoàn thành**
- Kế hoạch chi tiết:
  - `progess.md` (tổng quan các task)
  - `../progess_task2.md` (Task 2 semantic search)

## Cài đặt local

### 1) Clone project
```bash
git clone https://github.com/farolnguyen/PostaHub
cd PostaHub
```

### 2) Cài dependencies
```bash
composer install
```

### 3) Tạo file env
```bash
cp .env.example .env
```

Cập nhật các biến quan trọng trong `.env`:
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

### 4) Tạo APP_KEY
```bash
php artisan key:generate
```

### 5) Migrate + seed
```bash
php artisan migrate:fresh --seed
```

Lệnh này sẽ seed:
- 1 admin mặc định (`admin@postahub.local` / `password`)
- Bộ dữ liệu semantic search (user random + post đa chủ đề, đa độ dài)

### 5b) Seed lại bộ dữ liệu semantic (tùy chỉnh số lượng)
```bash
php artisan db:seed --class=SemanticSearchDatasetSeeder
```

Mặc định:
- `SEMANTIC_SEED_USERS=1000`
- `SEMANTIC_SEED_POSTS_PER_USER=100` (tương ứng `100000` posts tổng)
- Có thể override trực tiếp tổng posts bằng `SEMANTIC_SEED_POSTS`.

User semantic được tạo random tên và email theo domain `@semantic.postahub.local`, mật khẩu `password`.

Test nhanh (ít user/post):
```bash
SEMANTIC_SEED_USERS=20 SEMANTIC_SEED_POSTS_PER_USER=10 php artisan db:seed --class=SemanticSearchDatasetSeeder
```

### 6) Chạy app
```bash
php artisan storage:link
php artisan serve
```

Mở trình duyệt: `http://127.0.0.1:8000`

## Tài khoản seed mặc định
- Admin:
  - Email: `admin@postahub.local`
  - Password: `password`
- Users:
  - Sinh random khi seed, domain email: `@semantic.postahub.local`
  - Password: `password`

## Lệnh hay dùng
- Clear config/cache:
  - `php artisan config:clear`
  - `php artisan cache:clear`
  - `php artisan optimize:clear`
- Kiểm tra migration:
  - `php artisan migrate:status`
- Chạy queue worker:
  - `php artisan queue:work`
- Chạy backup users thủ công:
  - `php artisan app:backup-users`
- Xem danh sách schedule:
  - `php artisan schedule:list`

## Ghi chú về Node/Vite
- Project hiện tại không còn phụ thuộc pipeline Vite cho UI runtime.
- Không cần `npm install`, `npm run dev`, `npm run build` để chạy web.
- Nếu trước đó đang mở terminal `npm run dev`, có thể dừng tiến trình đó.

## Scheduler và crontab (Phase F)
- Đã cấu hình scheduler: command `app:backup-users` chạy hằng ngày lúc `01:00`.
- Trên Linux server, thêm crontab để Laravel scheduler được kích hoạt mỗi phút:
  - `* * * * * cd /duong-dan/PostaHub && php artisan schedule:run >> /dev/null 2>&1`

## Chiến lược cache (FW2)
- Đã áp dụng cache cho các luồng đọc nhiều:
  - Trang chủ feed (`/`)
  - Chi tiết bài viết (`/post/{url}`)
  - Mypage: danh sách post/like và thống kê profile
- Các thao tác CRUD/like liên quan sẽ gọi version bump (`App\Support\SiteCache::bumpAll()`) để key cache mới được sinh ngay.
- Chủ động **không cache trực tiếp HTML** với:
  - Trang auth (login/register/forgot/reset) do có token/session/validation theo request.
  - Trang admin CRUD/list để ưu tiên dữ liệu realtime và tránh stale data khi quản trị.

## Ghi chú upload media lớn (video/audio)
- Hệ thống hỗ trợ upload media `image/*`, `video/*`, `audio/*` cho post/comment (user + admin).
- Nếu gặp lỗi `The POST data is too large`, cần tăng giới hạn PHP (`upload_max_filesize`, `post_max_size`) trong file `php.ini`, sau đó restart service tương ứng.
- Gợi ý local khi chạy `php artisan serve`: `upload_max_filesize=64M`, `post_max_size=80M`.

## Ghi chú bảo mật cơ bản (FW4)
- Đã bổ sung sanitize HTML cho nội dung CKEditor (post/comment) bằng `mews/purifier`.
- Đã bổ sung throttle cho login và các endpoint upload/comment để giảm spam/bruteforce.
- Đã bổ sung security headers cơ bản qua middleware global (`nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`).

## Cấu trúc dữ liệu hiện tại
- `users`, `admins`
- `posts`
- `comments` (polymorphic qua `commentable`)
- `media` (polymorphic qua `mediable`)
- `likes`
- `user_rules`
- Hệ thống: `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`

## Sitemap mục tiêu (tóm tắt)
- Public: `/`, `/search`, `/login`, `/register`, `/forget_password`, `/logout`, `/post/detail`
- User: `/mypage/post`, `/mypage/like`, `/mypage/profile`
- Admin: `/admin/login`, `/admin/register`, `/admin/forget_password`, `/admin/logout`, `/admin/post/*`, `/admin/media/*`, `/admin/comment/*`, `/admin/export`, `/admin/import`, `/admin/rule`

---

## Setup semantic search (Task 2, Qdrant)

### 1) Chạy Qdrant bằng Docker
Trong thư mục project:
```bash
docker compose -f docker-compose.qdrant.yml up -d
```

Kiểm tra service:
```bash
curl http://127.0.0.1:6333/collections
```

Nếu cần stop:
```bash
docker compose -f docker-compose.qdrant.yml down
```

### 2) Cấu hình env (embedding local)
Thêm/đổi các biến trong `.env`:
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
SEMANTIC_EMBED_CHUNK_BATCH=32

SEMANTIC_QUEUE_INTERACTIVE=semantic
SEMANTIC_QUEUE_BULK=semantic-bulk
SEMANTIC_BULK_REINDEX_BATCH_POSTS=40
SEMANTIC_BULK_REINDEX_DELAY_SECONDS=12
SEMANTIC_SEED_QUEUE_BULK_REINDEX=true

SEMANTIC_SEARCH_VECTOR_HITS_LIMIT=80
SEMANTIC_SEARCH_PER_PAGE=15
SEMANTIC_SEARCH_PER_PAGE_MAX=50
SEMANTIC_SEARCH_QUERY_MAX=500
SEMANTIC_SEARCH_SNIPPET_CHARS=260
SEMANTIC_SEARCH_FALLBACK_ON_EMPTY=true
SEMANTIC_SEARCH_FALLBACK_ON_ERROR=true
```

Chạy embedding service (terminal riêng), từ thư mục `PostaHub/`:
```bash
cd embedding-service
source .venv/bin/activate
uvicorn main:app --host 127.0.0.1 --port 8001
```

### 3) Kiểm tra hạ tầng semantic
```bash
php artisan semantic:ensure-infrastructure
```

Tùy chọn: chỉ kiểm tra Qdrant
```bash
php artisan semantic:ensure-infrastructure --skip-embedding
```

Nếu collection `post_chunks` cũ có size 1536 nhưng hiện dùng E5 (384), xóa và tạo lại:
```bash
curl -X DELETE http://127.0.0.1:6333/collections/post_chunks
php artisan semantic:ensure-infrastructure
```

### 4) Index dữ liệu semantic
```bash
php artisan search:reindex-posts --post=1
php artisan search:reindex-posts
```

### 5) Queue semantic (Phase 4)
Worker xử lý hàng đợi `semantic` (bài đơn lẻ) và `semantic-bulk` (reindex theo lô sau seed):
```bash
php artisan queue:work database --queue=semantic,semantic-bulk,default
```

Script `composer run dev` đã thêm `--queue=semantic,semantic-bulk,default` cho `queue:listen`.

Tắt bulk job sau seed (chỉ insert DB, không xếp hàng index):
- Đặt `SEMANTIC_SEED_QUEUE_BULK_REINDEX=false` trong `.env`.

### 6) Search công khai
- `GET /search?q=...`  
  Semantic được dùng khi `SEMANTIC_SEARCH_ENABLED=true` + embedding + Qdrant hoạt động.
- Nếu semantic rỗng/lỗi sẽ fallback SQL LIKE (theo config fallback).
- Debug score: `APP_DEBUG=true` và thêm `&debug_scores=1`.

Sau khi đổi env:
```bash
php artisan config:clear
```

### 7) Bảng tracking chunk/index
```bash
php artisan migrate
```

Bảng mới: `post_semantic_chunks` (lưu chunk text, hash, trạng thái indexed, lỗi gần nhất, vector point id).

### 8) Flow chạy nhanh (end-to-end)
Từ thư mục `PostaHub/`:
```bash
php artisan config:clear
php artisan semantic:ensure-infrastructure
php artisan search:reindex-posts
php artisan serve
```

### 9) Resume reindex khi dừng giữa chừng
Lấy mốc hiện tại trong MySQL:
```sql
SELECT COUNT(DISTINCT post_id) AS posts_indexed FROM post_semantic_chunks;
SELECT MAX(post_id) AS max_post_id FROM post_semantic_chunks;
SELECT COUNT(*) AS total_posts FROM posts;
```

Chạy tiếp từ mốc:
```bash
php artisan search:reindex-posts --from-id=<max_post_id>
```

Kiểm tra còn bao nhiêu post chưa có chunk:
```sql
SELECT COUNT(*) AS posts_without_chunks
FROM posts p
WHERE NOT EXISTS (
  SELECT 1 FROM post_semantic_chunks c WHERE c.post_id = p.id
);
```

### 10) Benchmark chất lượng
File benchmark checklist:
- `../semantic_search_benchmark.md`

Test trên web:
- `http://127.0.0.1:8000/search?q=<query>`
- Thêm `&debug_scores=1` khi `APP_DEBUG=true` để xem score/chunk.

