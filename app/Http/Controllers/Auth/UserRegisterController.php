<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserRegisterController extends Controller
{
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('user.index');
        }

        return view('auth.user.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|max:255|confirmed|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $user->rule()->create([
            'can_post' => true,
            'can_comment' => true,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        event(new Registered($user));

        return redirect()->route('user.index');
    }
}
