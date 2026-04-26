<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $cookieName = (string) config('localization.cookie_name', 'makasouk_locale');
        $cookieLifetime = (int) config('localization.cookie_lifetime_minutes', 525600);
        $currentCookieLocale = $this->normalizeLocale($request->cookie($cookieName));

        if ($currentCookieLocale !== $locale) {
            Cookie::queue(cookie(
                name: $cookieName,
                value: $locale,
                minutes: $cookieLifetime,
                path: '/',
                domain: null,
                secure: $request->isSecure(),
                httpOnly: false,
                raw: false,
                sameSite: 'lax',
            ));
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = $this->supportedLocales();

        $candidates = [
            $request->query('lang'),
            $request->route('locale'),
            $request->header('X-Locale'),
            $request->cookie((string) config('localization.cookie_name', 'makasouk_locale')),
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->getPreferredLanguage($supported),
            $request->header('Accept-Language'),
            config('app.locale'),
            config('localization.default_locale', 'ar'),
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeLocale($candidate);

            if ($normalized !== null && in_array($normalized, $supported, true)) {
                return $normalized;
            }
        }

        return (string) config('localization.default_locale', 'ar');
    }

    private function normalizeLocale(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = strtolower(trim($value));
        $value = str_replace('_', '-', $value);

        if (str_starts_with($value, 'ar')) {
            return 'ar';
        }

        if (str_starts_with($value, 'en')) {
            return 'en';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $supported = config('localization.supported_locales', ['ar', 'en']);

        if (! is_array($supported) || $supported === []) {
            return ['ar', 'en'];
        }

        return array_values(array_filter(array_map(
            fn ($locale): ?string => $this->normalizeLocale($locale),
            $supported,
        )));
    }
}

