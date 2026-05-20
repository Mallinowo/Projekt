<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\SwipeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/lang/{locale}', [AuthController::class, 'setLocale'])->name('locale');

Route::middleware(['auth', 'locale'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/spotify/connect', [SpotifyController::class, 'connect'])->name('spotify.connect');
    Route::get('/spotify/callback', [SpotifyController::class, 'callback'])->name('spotify.callback');
    Route::get('/spotify/search-artists', [SpotifyController::class, 'searchArtists'])->name('spotify.search');
    Route::get('/spotify/top-artists', [SpotifyController::class, 'topArtists'])->name('spotify.top');
    Route::post('/spotify/disconnect', [SpotifyController::class, 'disconnect'])->name('spotify.disconnect');

    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo');
    Route::delete('/profile/photo/{photo}', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    Route::middleware('onboarding')->group(function () {
        Route::get('/', [DiscoverController::class, 'index'])->name('home');
        Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');
        Route::get('/discover/matches/updates', [DiscoverController::class, 'matchUpdates'])->name('discover.matches.updates');
        Route::post('/swipe', [SwipeController::class, 'swipe'])->name('swipe');

        Route::get('/chat', [ChatController::class, 'index'])->name('chat');
        Route::get('/chat/gifs/search', [ChatController::class, 'gifSearch'])->name('chat.gifs.search');
        Route::get('/chat/{match}/messages', [ChatController::class, 'messages'])->name('chat.messages');
        Route::post('/chat/{match}/messages', [ChatController::class, 'send'])->name('chat.send');
        Route::post('/chat/{match}/messages/{message}/react', [ChatController::class, 'react'])->name('chat.react');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
