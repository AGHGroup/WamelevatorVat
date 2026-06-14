<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected array $supported = ['ar', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'ar'));

        if (!in_array($locale, $this->supported)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
