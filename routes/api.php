<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

Route::apiResource("user", UserController::class);




Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['status' => __($status)])
        : response()->json(['email' => __($status)], 422);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, $token) {
    return redirect()->to('http://192.168.1.9:8080/recuperar-senha?token=' . $token);
})->name('password.reset');
