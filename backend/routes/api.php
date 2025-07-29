<?php

use App\Http\Controllers\Api\{
    AuthController,
    UserSalaryController
};
use App\Http\Middleware\JwtAuthenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the Salary API',
        'version' => '1.0.0',
        'documentation' => url('/api/docs')
    ]);
});

Route::prefix('auth')->middleware(['throttle:api_limiter'])->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::middleware([JwtAuthenticate::class])->group(function () {
        Route::get('me', 'me');
    });
});
Route::controller(UserSalaryController::class)->group(function () {
    Route::middleware([JwtAuthenticate::class])->group(function () {
        Route::get('/salaries/{id}', 'show')
            ->name('salaries.show');
        Route::put('/salaries/{id}', 'update')
            ->name('salaries.update');
        Route::delete('/salaries/{id}', 'destroy')
            ->name('salaries.destroy');
        Route::get('/salaries', 'index')
            ->name('salaries.index');
    });
    Route::middleware(['throttle:api_limiter'])->group(function () {
        Route::post('/salaries', 'store')
            ->name('salaries.store');
    });
});