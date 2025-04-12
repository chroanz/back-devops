<?php

use App\Http\Controllers\CursosController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

Route::get('/user/me',[UserController::class, 'me'])->middleware('auth:sanctum');
Route::apiResource("user", UserController::class);

Route::prefix('cursos')->group(function () {
    Route::get('/', [CursosController::class, 'index']);
    Route::post('/create', [CursosController::class, 'store']);
    Route::get('/show/{curso}', [CursosController::class, 'show']);
    Route::put('/update/{curso}', [CursosController::class, 'update']);
    Route::delete('/delete/{curso}', [CursosController::class, 'destroy']);
    Route::get('/search/{search}', [CursosController::class, 'search']);
    Route::post('/subscribe/{cursos}', [CursosController::class, 'subscribe'])->middleware(['auth:sanctum']);
});

Route::prefix('admin')->group(function () {
    Route::post('/store', [UserController::class, 'storeAdmin']);
    Route::put('/update', [UserController::class, 'updateAdmin']);
    Route::get('/list', [UserController::class, 'getAdmins']);
});

// Route::prefix('aulas')->group(function () {
//     Route::get('/', [CursosController::class, 'index']);
//     Route::post('/create', [CursosController::class, 'store']);
//     Route::get('/show/{aulas}', [CursosController::class, 'show']);
//     Route::put('/update/{aulas}', [CursosController::class, 'update']);
//     Route::delete('/delete/{aulas}', [CursosController::class, 'destroy']);
//     Route::get('/search/{search}', [CursosController::class, 'search']);
// });

Route::post('login', [UserController::class, 'login'])->name('login');


Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('forgot.password');
Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.reset');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/logout', [UserController::class, 'logout']);
    // outras rotas protegidas
});
