<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSelectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $systems = [];

        if ($user->hasZatcaAccess()) {
            $systems[] = 'zatca';
        }

        if ($user->hasWamelevatorAccess()) {
            $systems[] = 'wamelevator';
        }

        // If only one system, skip this page
        if (count($systems) === 1) {
            session(['active_system' => $systems[0]]);
            return redirect()->route('dashboard');
        }

        return view('auth.system-select', compact('systems'));
    }

    public function choose(Request $request)
    {
        $user = Auth::user();

        $request->validate(['system' => 'required|in:zatca,wamelevator']);

        $system = $request->input('system');

        // Verify user actually has access to the chosen system
        if ($system === 'zatca' && ! $user->hasZatcaAccess()) {
            abort(403);
        }

        if ($system === 'wamelevator' && ! $user->hasWamelevatorAccess()) {
            abort(403);
        }

        session(['active_system' => $system]);

        return redirect()->route('dashboard');
    }
}
