<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostSlugPreviewController extends Controller
{
    /**
     * Trả về slug xem trước (không kiểm tra trùng DB) — trùng logic Str::slug khi lưu bài.
     */
    public function __invoke(Request $request): JsonResponse
    {
        if (! Auth::guard('web')->check() && ! Auth::guard('admin')->check()) {
            abort(403);
        }

        $title = (string) $request->query('title', '');
        if ($title === '') {
            return response()->json(['slug' => '']);
        }

        $slug = Str::slug($title, '-', 'vi');

        return response()->json([
            'slug' => $slug !== '' ? $slug : 'bai-viet',
        ]);
    }
}
