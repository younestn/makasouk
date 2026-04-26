<?php

use App\Http\Controllers\ShopController;
use App\Http\Controllers\ContentPageController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public-site');
Route::view('/how-it-works', 'public-site');
Route::view('/for-customers', 'public-site');
Route::view('/for-tailors', 'public-site');
Route::view('/faq', 'public-site');
Route::view('/contact', 'public-site');

Route::get('/locale/{locale}', function (Request $request, string $locale): RedirectResponse {
    $supported = config('localization.supported_locales', ['ar', 'en']);
    $cookieName = (string) config('localization.cookie_name', 'makasouk_locale');
    $cookieLifetime = (int) config('localization.cookie_lifetime_minutes', 525600);
    $locale = strtolower(trim($locale));
    $normalized = null;

    if (str_starts_with($locale, 'ar')) {
        $normalized = 'ar';
    } elseif (str_starts_with($locale, 'en')) {
        $normalized = 'en';
    }

    if ($normalized === null || ! in_array($normalized, $supported, true)) {
        $normalized = (string) config('localization.default_locale', 'ar');
    }

    app()->setLocale($normalized);
    $request->session()->put('locale', $normalized);

    $redirectUrl = url()->previous() ?: url('/');
    $safeRedirect = str_starts_with($redirectUrl, url('/')) ? $redirectUrl : url('/');

    return redirect()->to($safeRedirect)->withCookie(cookie(
        name: $cookieName,
        value: $normalized,
        minutes: $cookieLifetime,
        path: '/',
        domain: null,
        secure: $request->isSecure(),
        httpOnly: false,
        raw: false,
        sameSite: 'lax',
    ));
})->name('locale.switch');

Route::get('/email/verify/{id}/{hash}', function (Request $request, int $id, string $hash): RedirectResponse {
    abort_unless($request->hasValidSignature(), 403);

    /** @var User $user */
    $user = User::query()->findOrFail($id);

    abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect('/app/login?email_verified=1');
})->middleware('throttle:6,1')->name('verification.verify');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/shop/products/{product:slug}', [ShopController::class, 'showProduct'])->name('shop.product.show');
Route::get('/pages/{contentPage:slug}', [ContentPageController::class, 'show'])->name('content-pages.show');

Route::view('/app/{any?}', 'spa')->where('any', '.*');
