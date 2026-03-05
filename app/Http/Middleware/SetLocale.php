<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var array<string> */
    private const SUPPORTED_LOCALES = ['id', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Resolve locale from user profile, cookie, or default.
     */
    private function resolveLocale(Request $request): string
    {
        // 1. Authenticated user's saved preference
        if ($request->user() && in_array($request->user()->locale, self::SUPPORTED_LOCALES, true)) {
            return $request->user()->locale;
        }

        // 2. Cookie
        $cookieLocale = $request->cookie('locale');
        if ($cookieLocale && in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
            return $cookieLocale;
        }

        // 3. Default
        return config('app.locale', 'id');
    }
}
