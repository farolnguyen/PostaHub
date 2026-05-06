# PostaHub embedding service (Task 2 — Phase 1)

Local HTTP API dùng `intfloat/multilingual-e5-small` (384 chiều). Laravel (phase sau) sẽ gọi service này.

## Tiền đề (Ubuntu / Debian)

Nếu lỗi *ensurepip is not available* khi tạo venv, cài gói venv cho đúng phiên bản Python (ví dụ 3.12):

```bash
sudo apt update
sudo apt install -y python3.12-venv
```

(Tùy máy có thể là `python3.11-venv` — chạy `python3 --version` rồi chọn gói tương ứng.)

## Cài đặt

Từ thư mục gốc repo (có `PostaHub/`):

```bash
cd PostaHub/embedding-service
python3 -m venv .venv
source .venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

(Lần đầu cài `torch` + `sentence-transformers` có thể vài phút và tải model khi chạy lần đầu.)

## Chạy

```bash
source .venv/bin/activate
uvicorn main:app --host 127.0.0.1 --port 8001
```

Trình duyệt:

- `http://127.0.0.1:8001/` — JSON mô tả service (không còn 404).
- `http://127.0.0.1:8001/docs` — Swagger UI (thử `/health`, `/v1/embed`).
- `http://127.0.0.1:8001/health` — kiểm tra model đã load.

Biến môi trường tùy chọn:

| Biến | Mặc định | Ý nghĩa |
|------|-----------|---------|
| `EMBEDDING_MODEL` | `intfloat/multilingual-e5-small` | Tên model Hugging Face |
| `EMBEDDING_MAX_BATCH` | `32` | Tối đa số chuỗi mỗi request `/v1/embed` |

## API

### `GET /health`

Trả JSON: `status`, `model`, `dimensions` (sau khi model load xong).

### `POST /v1/embed`

Body JSON:

```json
{
  "inputs": ["câu 1", "câu 2"],
  "input_type": "query"
}
```

- `input_type`: `"query"` → tiền tố E5 `query: ` (dùng khi embed câu tìm kiếm).
- `input_type`: `"document"` (mặc định) → tiền tố `passage: ` (dùng khi index nội dung bài).

Response:

```json
{
  "model": "intfloat/multilingual-e5-small",
  "dimensions": 384,
  "vectors": [[...], [...]]
}
```

Ví dụ `curl`:

```bash
curl -sS http://127.0.0.1:8001/health
curl -sS http://127.0.0.1:8001/v1/embed \
  -H 'Content-Type: application/json' \
  -d '{"inputs":["Đoạn nội dung cần index"],"input_type":"document"}'
```

Client gửi **nội dung thuần**; server tự thêm prefix `query:` / `passage:` theo `input_type`.
