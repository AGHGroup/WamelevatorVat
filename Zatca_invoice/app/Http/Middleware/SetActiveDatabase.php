<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetActiveDatabase
{
    public function handle(Request $request, Closure $next)
    {
        $system = session('active_system');

        if ($system === 'wamelevator') {
            // Point the default oracle connection to the wamelevator DB
            Config::set('database.connections.oracle.host',     config('database.connections.wamelevator.host'));
            Config::set('database.connections.oracle.port',     config('database.connections.wamelevator.port'));
            Config::set('database.connections.oracle.database', config('database.connections.wamelevator.database'));
            Config::set('database.connections.oracle.username', config('database.connections.wamelevator.username'));
            Config::set('database.connections.oracle.password', config('database.connections.wamelevator.password'));

            DB::purge('oracle');
        }

        return $next($request);
    }
}
