<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id'  => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'user_id'  => strtoupper(trim($request->user_id)),
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('user_id'))
                ->withErrors(['user_id' => __('auth.failed')]);
        }

        $user = Auth::user();

        // Block locked / deleted / inactive accounts
        if (! $user->isActive()) {
            Auth::logout();
            return back()
                ->withInput($request->only('user_id'))
                ->withErrors(['user_id' => __('auth.account_inactive')]);
        }

        $request->session()->regenerate();
        $request->session()->put('is_admin', $user->isAdmin());

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
