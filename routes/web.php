<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'app')->name('app');

/*
 * The whole CRM speaks through two endpoints: one to sign in, one dispatcher
 * for everything else. Both are stateless — the client holds a bearer token —
 * so they sit outside the session-cookie CSRF flow.
 */
Route::post('/api/login', [ApiController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

Route::post('/api', [ApiController::class, 'dispatchAction'])
    ->middleware('throttle:120,1')
    ->name('api.dispatch');
