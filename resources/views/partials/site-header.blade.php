<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container flex-wrap">
        <a class="navbar-brand font-weight-bold" href="{{ route('home') }}">{{ config('app.name', 'PostaHub') }}</a>


        <div class="d-flex align-items-center flex-grow-1 justify-content-center flex-wrap">
            <a class="btn btn-outline-light btn-sm mr-2" href="{{ route('home') }}">Trang chủ</a>
            <a class="btn btn-outline-light btn-sm mr-2" href="{{ route('user.index') }}">Trang cá nhân</a>
            @if (auth('admin')->check())
                <a class="btn btn-outline-light btn-sm" href="{{ route('admin.dashboard') }}">Dashboard</a>
            @endif
        </div>

        <div class="d-flex align-items-center">
            @if (auth('web')->check() || auth('admin')->check())
                {{-- Bell dropdown --}}
                <div id="js-notif-wrapper" class="mr-2" style="position:relative; display:inline-block;">
                    <button id="js-notif-btn"
                            type="button"
                            class="btn btn-outline-light btn-sm position-relative"
                            title="Thông báo">
                        🔔
                        <span id="js-notif-badge"
                              data-count="{{ $unreadNotificationCount ?? 0 }}"
                              class="badge badge-danger position-absolute"
                              style="top:-6px; right:-6px; font-size:0.65rem; min-width:18px;
                                     {{ ($unreadNotificationCount ?? 0) > 0 ? '' : 'display:none;' }}">
                            {{ ($unreadNotificationCount ?? 0) > 99 ? '99+' : ($unreadNotificationCount ?? 0) }}
                        </span>
                    </button>

                    {{-- Dropdown panel --}}
                    <div id="js-notif-dropdown"
                         style="display:none; position:absolute; right:0; top:calc(100% + 6px);
                                width:320px; max-height:440px; overflow-y:auto;
                                background:#fff; border:1px solid rgba(0,0,0,.15); border-radius:6px;
                                box-shadow:0 6px 20px rgba(0,0,0,.18); z-index:9999;">

                        {{-- Header row --}}
                        <div style="padding:8px 12px; border-bottom:1px solid #e9ecef;
                                    display:flex; justify-content:space-between; align-items:center;
                                    position:sticky; top:0; background:#fff;">
                            <strong style="font-size:.85rem; color:#333;">Thông báo</strong>
                            @if (($unreadNotificationCount ?? 0) > 0)
                                <form action="{{ route('mypage.notifications.read-all') }}" method="post" style="margin:0;">
                                    @csrf
                                    <button type="submit"
                                            style="border:none; background:none; color:#007bff;
                                                   font-size:.75rem; padding:0; cursor:pointer;">
                                        ✓ Đọc tất cả
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Notification items --}}
                        @forelse ($recentNotifications ?? [] as $notif)
                            @php
                                $nd    = $notif->data ?? [];
                                $ntype = $nd['type'] ?? '';
                                $nIcon = match($ntype) {
                                    'new_comment_on_post'  => '💬',
                                    'new_reply_to_comment' => '↩️',
                                    'post_liked'           => '👍',
                                    'comment_liked'        => '👍',
                                    default                => '🔔',
                                };
                                $nText = match($ntype) {
                                    'new_comment_on_post'  => '<strong>' . e($nd['actor_name'] ?? '') . '</strong> đã bình luận bài của bạn',
                                    'new_reply_to_comment' => '<strong>' . e($nd['actor_name'] ?? '') . '</strong> đã trả lời bình luận của bạn',
                                    'post_liked'           => '<strong>' . e($nd['actor_name'] ?? '') . '</strong> đã thích bài của bạn',
                                    'comment_liked'        => '<strong>' . e($nd['actor_name'] ?? '') . '</strong> đã thích bình luận của bạn',
                                    default                => 'Bạn có thông báo mới',
                                };
                                $nBg  = $notif->read_at ? '#fff' : '#eef4ff';
                                $nUrl = route('mypage.notifications.read', $notif->id);
                            @endphp
                            <a href="{{ $nUrl }}"
                               style="display:block; padding:9px 12px; text-decoration:none;
                                      border-bottom:1px solid #f0f0f0; background:{{ $nBg }}; color:#333;"
                               onmouseover="this.style.background='#f1f5fb'"
                               onmouseout="this.style.background='{{ $nBg }}'">
                                <div style="display:flex; align-items:flex-start; gap:8px;">
                                    <span style="font-size:1.05rem; flex-shrink:0; line-height:1.5;">{{ $nIcon }}</span>
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:.78rem; line-height:1.4;">{!! $nText !!}</div>
                                        @if (!empty($nd['post_title']))
                                            <div style="font-size:.73rem; color:#555; white-space:nowrap;
                                                        overflow:hidden; text-overflow:ellipsis; max-width:230px;">
                                                {{ $nd['post_title'] }}
                                            </div>
                                        @endif
                                        <div style="font-size:.7rem; color:#aaa; margin-top:2px;">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if (!$notif->read_at)
                                        <span style="width:7px; height:7px; border-radius:50%;
                                                     background:#007bff; flex-shrink:0; margin-top:4px;"></span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div style="padding:24px; text-align:center; color:#aaa; font-size:.85rem;">
                                🔔 Chưa có thông báo nào
                            </div>
                        @endforelse

                        {{-- Footer --}}
                        <div style="padding:8px; border-top:1px solid #e9ecef; text-align:center; background:#fafafa;">
                            <a href="{{ route('mypage.notifications.index') }}"
                               style="color:#007bff; font-size:.8rem; text-decoration:none;">
                                Xem tất cả thông báo →
                            </a>
                        </div>
                    </div>
                </div>
                <script>(function () {
                    var btn  = document.getElementById('js-notif-btn');
                    var drop = document.getElementById('js-notif-dropdown');
                    var wrap = document.getElementById('js-notif-wrapper');
                    if (!btn || !drop) return;
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        drop.style.display = drop.style.display === 'none' ? 'block' : 'none';
                    });
                    document.addEventListener('click', function (e) {
                        if (wrap && !wrap.contains(e.target)) drop.style.display = 'none';
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') drop.style.display = 'none';
                    });
                })();</script>
            @endif

            @if (auth('web')->check())
                <span class="navbar-text text-white mr-2 small">Xin chào {{ auth('web')->user()->name }}</span>
                <form action="{{ route('user.logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Đăng xuất</button>
                </form>
            @elseif (auth('admin')->check())
                <span class="navbar-text text-white mr-2 small">Xin chào Admin {{ auth('admin')->user()->name }}</span>
                <form action="{{ route('admin.logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Đăng xuất</button>
                </form>
            @else
                <a class="btn btn-light btn-sm mr-2" href="{{ route('user.register.form') }}">Đăng ký</a>
                <a class="btn btn-outline-light btn-sm" href="{{ route('user.login.form') }}">Đăng nhập</a>
            @endif
        </div>
    </div>
</nav>
@if (!empty($notificationUserId))
@php
    $hdrWsHost   = env('REVERB_HOST', '127.0.0.1');
    $hdrWsPort   = (int) env('REVERB_PORT', 8080);
    $hdrWsScheme = env('REVERB_SCHEME', 'http');
    $hdrWsKey    = env('REVERB_APP_KEY', 'local');
@endphp
{{-- Pusher/Echo CDN — chỉ load cho web user đã đăng nhập --}}
<script src="https://unpkg.com/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://unpkg.com/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>(function () {
    if (!window.Pusher || !window.Echo) return;
    try {
        var EchoCtor = window.Echo;
        /* Tạo instance dùng chung toàn trang (reuse trong post/detail.blade.php) */
        window.echo = new EchoCtor({
            broadcaster      : 'pusher',
            key              : {!! json_encode($hdrWsKey) !!},
            cluster          : '',
            wsHost           : {!! json_encode($hdrWsHost) !!},
            wsPort           : {{ $hdrWsPort }},
            wssPort          : {{ $hdrWsPort }},
            forceTLS         : {{ $hdrWsScheme === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
            disableStats     : true,
            authEndpoint     : '/broadcasting/auth',
            auth             : {
                headers: {
                    'X-CSRF-TOKEN'     : {!! json_encode(csrf_token()) !!},
                    'X-Requested-With' : 'XMLHttpRequest',
                }
            }
        });

        /* Lắng nghe thông báo trên kênh private của user hiện tại */
        window.echo.private('App.Models.User.{{ $notificationUserId }}')
            .notification(function (notification) {
                var badge = document.getElementById('js-notif-badge');
                if (!badge) return;
                var count = parseInt(badge.dataset.count || '0', 10) || 0;
                count += 1;
                badge.dataset.count = String(count);
                badge.textContent   = count > 99 ? '99+' : String(count);
                badge.style.display = '';
                console.info('[Notify] Realtime notification received:', notification.type || notification);
            });

        console.info('[Notify] Echo ready, listening notifications for userId={{ $notificationUserId }}');
    } catch (e) {
        console.warn('[Notify] Echo init failed:', e);
    }
})();
</script>
@endif
