<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Support\ActorUserResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $notifications = $actor->notifications()->latest()->paginate(15);

        return view('mypage.notifications.index', compact('notifications'));
    }

    public function read(string $id): RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $notification = $actor->notifications()->findOrFail($id);
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('home');

        return redirect($link);
    }

    public function readAll(): RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $actor->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
