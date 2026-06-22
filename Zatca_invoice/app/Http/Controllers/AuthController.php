<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
            'system'   => ['required', 'in:zatca,wamelevator'],
            'user_id'  => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $system = $request->input('system');

        // Switch Oracle connection to the selected system's DB before authenticating
        if ($system === 'wamelevator') {
            Config::set('database.connections.oracle.host',     config('database.connections.wamelevator.host'));
            Config::set('database.connections.oracle.port',     config('database.connections.wamelevator.port'));
            Config::set('database.connections.oracle.database', config('database.connections.wamelevator.database'));
            Config::set('database.connections.oracle.username', config('database.connections.wamelevator.username'));
            Config::set('database.connections.oracle.password', config('database.connections.wamelevator.password'));
            DB::purge('oracle');
        }

        $credentials = [
            'user_id'  => strtoupper(trim($request->user_id)),
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('user_id', 'system'))
                ->withErrors(['user_id' => __('auth.failed')]);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            return back()
                ->withInput($request->only('user_id', 'system'))
                ->withErrors(['user_id' => __('auth.account_inactive')]);
        }

        $request->session()->regenerate();
        $request->session()->put('is_admin',       $user->isAdmin());
        $request->session()->put('active_system',  $system);
        $request->session()->put('user_display_name',
            $user->user_aname ?? $user->USER_ANAME ?? $user->user_ename ?? $user->USER_ENAME ?? $user->user_id ?? ''
        );

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
