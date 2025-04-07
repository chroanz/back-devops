<?php

use App\Http\Controllers\CursosController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

Route::apiResource("user", UserController::class);

Route::get('cursos', [CursosController::class, 'index'])->name('cursos');
Route::get('cursos/{id}', [CursosController::class, 'show'])->name('cursos.show');
Route::get('cursos/search/{term}', [CursosController::class, 'search'])->name('cursos.search');


Route::post('login', [UserController::class, 'login'])->name('login');


Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('forgot.password');
Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.reset');
