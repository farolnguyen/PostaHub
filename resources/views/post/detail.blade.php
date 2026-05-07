<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $post->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary mb-3">Về trang chủ</a>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3">{{ $post->title }}</h1>
            <p class="text-muted mb-2">Tác giả: {{ $post->user->name ?? 'N/A' }}</p>
            <p><strong>URL:</strong> <code>{{ $post->url }}</code></p>
            <p><strong>Tổng lượt thích:</strong> {{ $post->likes_count }}</p>
            @if($post->thumbnail)
                <div class="mb-3">
                    <p class="mb-2"><strong>Ảnh đại diện (thumbnail):</strong></p>
                    <img src="{{ $post->thumbnail }}" alt="thumbnail {{ $post->title }}" class="img-fluid rounded border" style="max-width: 280px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                </div>
            @endif
            @if($post->media->count())
                <div class="row mb-3">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-2">
                            @include('partials.media-preview', ['media' => $media, 'alt' => 'post media', 'class' => 'img-fluid rounded border'])
                        </div>
                    @endforeach
                </div>
            @endif
            <hr>
            <div>{!! $post->content !!}</div>
            @if (auth('web')->check() || auth('admin')->check())
                <form action="{{ route('like.toggle', $post) }}" method="post" class="mt-3">
                    @csrf
                    <button type="submit" class="btn {{ $hasLiked ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                        {{ $hasLiked ? 'Bỏ thích' : 'Thích bài viết' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Thảo luận</h2>

            @if (auth('web')->check() || auth('admin')->check())
                @if (auth('admin')->check() || auth('web')->user()?->can('create', \App\Models\Comment::class))
                    <form action="{{ route('comment.store.post', $post) }}" method="post" class="mb-4" enctype="multipart/form-data">
                        @csrf
                        <x-form.textarea
                            id="new-comment-content"
                            name="content"
                            label="Nội dung bình luận"
                            rows="4"
                            class="js-comment-editor"
                        />
                        <div class="form-group">
                            <label>Tải media (ảnh/video/âm thanh) cho bình luận (tối đa 5 file)</label>
                            <div class="js-media-inputs" data-max-files="5">
                                <input type="file" name="media_images[]" class="form-control-file @error('media_images.*') is-invalid @enderror mb-2" accept="image/*,video/*,audio/*">
                            </div>
                            @error('media_images.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('media_images')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                    </form>
                @elseif(auth('web')->check())
                    <p class="text-muted mb-4">Tài khoản của bạn không được phép bình luận (can_comment = false).</p>
                @endif
            @else
                <p class="text-muted">Bạn cần <a href="{{ route('user.login.form') }}">đăng nhập</a> để bình luận.</p>
            @endif

            @include('post._comments-fragment', ['post' => $post])
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="https://unpkg.com/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://unpkg.com/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    function initDynamicMediaInputs(root) {
        root.querySelectorAll('.js-media-inputs').forEach(function (container) {
            var maxFiles = parseInt(container.dataset.maxFiles || '5', 10);
            var baseClass = 'form-control-file';

            function buildPreviewItem(input) {
                if (input.dataset.enhanced === '1') {
                    return;
                }

                var item = document.createElement('div');
                item.className = 'd-flex align-items-start mb-2 js-media-item';

                var left = document.createElement('div');
                left.className = 'mr-2';
                input.classList.remove('mb-2');
                input.classList.add(baseClass);
                left.appendChild(input);

                var preview = document.createElement('img');
                preview.alt = 'preview';
                preview.style.maxWidth = '64px';
                preview.style.maxHeight = '64px';
                preview.className = 'rounded border d-none';
                left.appendChild(preview);

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-outline-danger';
                removeBtn.textContent = 'Xóa';
                removeBtn.addEventListener('click', function () {
                    item.remove();
                    refresh();
                });

                input.addEventListener('change', function () {
                    if (input.files && input.files[0]) {
                        preview.src = URL.createObjectURL(input.files[0]);
                        preview.classList.remove('d-none');
                    } else {
                        preview.removeAttribute('src');
                        preview.classList.add('d-none');
                    }
                    refresh();
                });

                item.appendChild(left);
                item.appendChild(removeBtn);
                container.appendChild(item);
                input.dataset.enhanced = '1';
            }

            function createInput() {
                var input = document.createElement('input');
                input.type = 'file';
                input.name = 'media_images[]';
                input.accept = 'image/*,video/*,audio/*';
                return input;
            }

            function refresh() {
                var items = Array.from(container.querySelectorAll('.js-media-item'));
                var inputs = items
                    .map(function (item) { return item.querySelector('input[type="file"]'); })
                    .filter(Boolean);
                var hasEmpty = inputs.some(function (input) { return !(input.files && input.files.length); });

                if (!hasEmpty && inputs.length < maxFiles) {
                    buildPreviewItem(createInput());
                    items = Array.from(container.querySelectorAll('.js-media-item'));
                    inputs = items
                        .map(function (item) { return item.querySelector('input[type="file"]'); })
                        .filter(Boolean);
                }

                items.forEach(function (item) {
                    var btn = item.querySelector('button');
                    var input = item.querySelector('input[type="file"]');
                    if (!btn) return;
                    var hasValue = !!(input && input.files && input.files.length);
                    btn.disabled = !hasValue;
                    btn.classList.toggle('invisible', !hasValue);
                });
            }

            var existingInputs = Array.from(container.querySelectorAll('input[type="file"]'));
            container.innerHTML = '';
            if (existingInputs.length === 0) {
                existingInputs = [createInput()];
            }
            existingInputs.forEach(buildPreviewItem);
            refresh();
        });
    }

    initDynamicMediaInputs(document);

    function initCommentEditors(root) {
        root.querySelectorAll('.js-comment-editor').forEach(function (element) {
            if (element.dataset.editorReady === '1') {
                return;
            }
            element.dataset.editorReady = '1';
            ClassicEditor.create(element)
                .then(function (editor) {
                    var form = element.closest('form');
                    if (form && !form.dataset.editorSubmitBound) {
                        form.addEventListener('submit', function () {
                            editor.updateSourceElement();
                        });
                        form.dataset.editorSubmitBound = '1';
                    }
                })
                .catch(function (error) {
                    console.error(error);
                    element.dataset.editorReady = '0';
                });
        });
    }

    initCommentEditors(document);

    var commentImageFallback = {!! json_encode(asset('images/image-fallback.png')) !!};
    var rtAuth = {
        userId:       {!! auth('web')->id() ?? 'null' !!},
        isAdmin:      {!! auth('admin')->check() ? 'true' : 'false' !!},
        canReply:     {!! (auth('admin')->check() || (auth('web')->check() && auth('web')->user()?->can('create', \App\Models\Comment::class))) ? 'true' : 'false' !!},
        csrfToken:    {!! json_encode(csrf_token()) !!},
    };

    /* ── polling state ─────────────────────────────────────── */
    var commentsPolling = { enabled: false, consecutiveErrors: 0, timerId: null };

    function startCommentsPolling() {
        var root = document.getElementById('js-comments-root');
        if (!root || commentsPolling.enabled) return;
        commentsPolling.enabled = true;
        console.info('[RT] Polling fallback activated.');

        var BASE = 15000;

        function scheduleNext() {
            if (!commentsPolling.enabled) return;
            var factor = commentsPolling.consecutiveErrors >= 2 ? 4
                       : commentsPolling.consecutiveErrors === 1 ? 2 : 1;
            commentsPolling.timerId = window.setTimeout(runOnce, BASE * factor);
        }

        function runOnce() {
            if (!commentsPolling.enabled) return;
            var url = window.location.pathname + '?comments_only=1';
            var ctrl = new AbortController();
            var tid  = window.setTimeout(function () { ctrl.abort(); }, 5000);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal })
                .then(function (r) {
                    window.clearTimeout(tid);
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text();
                })
                .then(function (html) {
                    commentsPolling.consecutiveErrors = 0;
                    var tmp = document.createElement('div');
                    tmp.innerHTML = html;
                    var fresh = tmp.querySelector('#js-comments-root');
                    if (fresh && root.innerHTML !== fresh.innerHTML) {
                        root.innerHTML = fresh.innerHTML;
                        initDynamicMediaInputs(root);
                        initCommentEditors(root);
                    }
                    scheduleNext();
                })
                .catch(function (err) {
                    window.clearTimeout(tid);
                    console.warn('[RT] Polling error:', err);
                    commentsPolling.consecutiveErrors++;
                    scheduleNext();
                });
        }

        scheduleNext();
    }

    function stopCommentsPolling() {
        if (!commentsPolling.enabled) return;
        commentsPolling.enabled = false;
        commentsPolling.consecutiveErrors = 0;
        if (commentsPolling.timerId) {
            window.clearTimeout(commentsPolling.timerId);
            commentsPolling.timerId = null;
        }
        console.info('[RT] Polling stopped (WebSocket connected).');
    }

    /* ── realtime via WebSocket ────────────────────────────── */
    function initRealtimeComments() {
        var root = document.getElementById('js-comments-root');
        if (!root) return;

        var postId = parseInt(root.dataset.postId || '0', 10);
        if (!postId) return;

        if (!window.Pusher || !window.Echo) {
            console.warn('[RT] pusher-js / laravel-echo CDN not loaded → polling.');
            startCommentsPolling();
            return;
        }

        try {
            var wsHost   = {!! json_encode(env('REVERB_HOST', '127.0.0.1')) !!} || window.location.hostname;
            var wsPort   = parseInt({!! json_encode((int) env('REVERB_PORT', 8080)) !!}, 10) || 8080;
            var wsScheme = {!! json_encode(env('REVERB_SCHEME', 'http')) !!};
            var wsKey    = {!! json_encode(env('REVERB_APP_KEY', 'local')) !!};

            console.info('[RT] Connecting WebSocket → ws' + (wsScheme === 'https' ? 's' : '') + '://' + wsHost + ':' + wsPort + ' key=' + wsKey);

            var EchoCtor = window.Echo;
            window.echo = new EchoCtor({
                broadcaster: 'pusher',
                key: wsKey,
                cluster: '',
                wsHost: wsHost,
                wsPort: wsPort,
                wssPort: wsPort,
                forceTLS: wsScheme === 'https',
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
            });

            window.echo.channel('post.' + postId + '.comments')
                .listen('.CommentChanged', function (e) {
                    try {
                        console.info('[RT] CommentChanged received:', e);
                        handleCommentChanged(root, e && e.payload ? e.payload : e);
                    } catch (err) {
                        console.error('[RT] Handler error:', err);
                    }
                });

            /* Sau 5 giây: nếu WS vẫn chưa connected → bật polling.
               Khi WS connected (bất cứ lúc nào) → tắt polling.
               Cách này tránh retry-cycle trigger liên tục. */
            var pusher = window.echo.connector && window.echo.connector.pusher;
            if (pusher && pusher.connection) {
                pusher.connection.bind('connected', function () {
                    console.info('[RT] WebSocket connected ✓');
                    stopCommentsPolling();
                });
                pusher.connection.bind('error', function (err) {
                    console.warn('[RT] WebSocket error:', err);
                });
                pusher.connection.bind('failed', function () {
                    console.warn('[RT] WebSocket failed → polling.');
                    startCommentsPolling();
                });
            }

            window.setTimeout(function () {
                var state = pusher && pusher.connection ? pusher.connection.state : 'unknown';
                console.info('[RT] WS state after 5s:', state);
                if (state !== 'connected') {
                    console.warn('[RT] WS not connected after 5s → polling.');
                    startCommentsPolling();
                }
            }, 5000);

        } catch (err) {
            console.warn('[RT] Echo init failed → polling.', err);
            startCommentsPolling();
        }
    }

    function findCommentNode(root, commentId) {
        return root.querySelector('#comment-' + commentId) || root.querySelector('[data-comment-id="' + commentId + '"]');
    }

    function findInsertAfterNodeForReply(root, parentCommentId, depth) {
        var parent = findCommentNode(root, parentCommentId);
        if (!parent) return null;

        var current = parent;
        var next = current.nextElementSibling;
        while (next) {
            var nextDepth = parseInt(next.dataset.depth || '-1', 10);
            if (!Number.isFinite(nextDepth) || nextDepth <= depth - 1) {
                break;
            }
            current = next;
            next = current.nextElementSibling;
        }

        return current;
    }

    function renderCommentHtml(payload) {
        if (!payload || !payload.comment) return '';
        var c = payload.comment;
        var left = Math.min((parseInt(payload.depth || '0', 10) || 0) * 24, 120);
        var createdAt = c.created_at_iso ? new Date(c.created_at_iso).toLocaleString() : '';

        var mediaHtml = '';
        if (Array.isArray(c.media) && c.media.length) {
            mediaHtml = '<div class="row mt-2">' + c.media.map(function (m) {
                var kind = m.kind || '';
                if (kind === 'image') {
                    return '' +
                        '<div class="col-md-3 mb-2">' +
                        '<img src="' + m.path + '" alt="comment media" class="img-fluid rounded border" style="max-width: 280px;" ' +
                        'onerror="this.onerror=null;this.src=\'' + commentImageFallback + '\';">' +
                        '</div>';
                }
                if (kind === 'video') {
                    return '' +
                        '<div class="col-md-3 mb-2">' +
                        '<video src="' + m.path + '" class="img-fluid rounded border" style="max-width: 280px;" controls></video>' +
                        '</div>';
                }
                if (kind === 'audio') {
                    return '' +
                        '<div class="col-md-3 mb-2">' +
                        '<audio src="' + m.path + '" class="w-100" controls></audio>' +
                        '</div>';
                }

                return '' +
                    '<div class="col-md-3 mb-2">' +
                    '<a href="' + m.path + '" target="_blank" rel="noopener" class="small">file</a>' +
                    '</div>';
            }).join('') + '</div>';
        }

        /* ── Reply form ── */
        var actionsHtml = '';
        if (rtAuth.canReply) {
            actionsHtml +=
                '<details>' +
                '<summary class="small text-primary">Trả lời bình luận</summary>' +
                '<form action="/comment/reply/' + c.id + '" method="post" class="mt-2" enctype="multipart/form-data">' +
                '<input type="hidden" name="_token" value="' + rtAuth.csrfToken + '">' +
                '<textarea name="content" rows="3" class="form-control mb-2 js-comment-editor"></textarea>' +
                '<div class="form-group mb-2"><div class="js-media-inputs" data-max-files="5">' +
                '<input type="file" name="media_images[]" class="form-control-file mb-2" accept="image/*,video/*,audio/*">' +
                '</div><small class="text-muted">Tối đa 5 file media.</small></div>' +
                '<button type="submit" class="btn btn-sm btn-outline-primary">Gửi trả lời</button>' +
                '</form></details>';
        }

        /* ── Edit / Delete buttons ── */
        var canEdit   = rtAuth.isAdmin || (rtAuth.userId && rtAuth.userId === c.author_id);
        var canDelete = rtAuth.isAdmin || (rtAuth.userId && rtAuth.userId === c.author_id);
        if (canEdit || canDelete) {
            var btns = '';
            if (canEdit) {
                btns += '<a href="/comment/' + c.id + '/edit" class="btn btn-sm btn-outline-secondary">Sửa</a> ';
            }
            if (canDelete) {
                btns +=
                    '<form action="/comment/' + c.id + '" method="post" class="d-inline"' +
                    ' onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa bình luận này?\');">' +
                    '<input type="hidden" name="_token" value="' + rtAuth.csrfToken + '">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>' +
                    '</form>';
            }
            actionsHtml += '<div class="mt-2">' + btns + '</div>';
        }

        var actionsBlock = actionsHtml ? '<div class="mt-3">' + actionsHtml + '</div>' : '';

        return (
            '<div id="comment-' + c.id + '" class="border rounded p-3 mb-3" style="margin-left: ' + left + 'px;" ' +
                'data-comment-id="' + c.id + '" data-depth="' + (payload.depth || 0) + '"' +
                (payload.parent_comment_id ? ' data-parent-comment-id="' + payload.parent_comment_id + '"' : '') +
            '>' +
                '<div class="d-flex justify-content-between"><div>' +
                    '<strong>' + escapeHtml(c.author_name || 'N/A') + '</strong>' +
                    '<span class="text-muted small ml-2">' + escapeHtml(createdAt) + '</span>' +
                '</div></div>' +
                '<div class="mt-2">' + (c.content_html || '') + '</div>' +
                mediaHtml +
                actionsBlock +
            '</div>'
        );
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function handleCommentChanged(root, payload) {
        if (!payload || !payload.action) return;

        var noComments = root.querySelector('.js-no-comments');
        if (noComments) noComments.remove();

        var action = payload.action;
        var commentId = parseInt(payload.comment_id || '0', 10);
        var parentId = payload.parent_comment_id ? parseInt(payload.parent_comment_id, 10) : null;

        if (action === 'deleted') {
            var node = findCommentNode(root, commentId);
            if (node) node.remove();
            return;
        }

        if (action !== 'created' && action !== 'updated') return;

        // Best-effort depth inference from DOM.
        var depth = 0;
        if (parentId) {
            var parent = findCommentNode(root, parentId);
            var parentDepth = parent ? parseInt(parent.dataset.depth || '0', 10) : 0;
            depth = parentDepth + 1;
        }
        payload.depth = depth;

        var html = renderCommentHtml(payload);
        if (!html) return;

        var existing = findCommentNode(root, commentId);
        if (existing) {
            existing.outerHTML = html;
        } else if (parentId) {
            var afterNode = findInsertAfterNodeForReply(root, parentId, depth);
            if (afterNode && afterNode.parentNode) {
                afterNode.insertAdjacentHTML('afterend', html);
            } else {
                root.insertAdjacentHTML('beforeend', html);
            }
        } else {
            root.insertAdjacentHTML('beforeend', html);
        }

        // Re-init CKEditor và media inputs trên node vừa được render.
        var newNode = findCommentNode(root, commentId);
        if (newNode) {
            initDynamicMediaInputs(newNode);
            initCommentEditors(newNode);
        }
    }

    initRealtimeComments();
</script>
</body>
</html>
