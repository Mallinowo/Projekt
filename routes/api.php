<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/profiles',          [ApiController::class, 'profiles']);
    Route::get('/profiles/{id}',     [ApiController::class, 'profile']);
    Route::get('/subcultures',       [ApiController::class, 'subcultures']);
});
