<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');

        if (!$locale && Auth::check()) {
            $locale = Auth::user()->locale;
        }

        if ($locale && in_array($locale, ['pl', 'en'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
