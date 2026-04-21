<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('rule')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.rule.index', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'can_post' => ['required', 'in:0,1'],
            'can_comment' => ['required', 'in:0,1'],
        ]);

        $user->rule()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'can_post' => $request->boolean('can_post'),
                'can_comment' => $request->boolean('can_comment'),
            ]
        );

        return redirect()
            ->route('admin.rule.index')
            ->with('status', 'Đã cập nhật quyền cho '.$user->name.'.');
    }
}
