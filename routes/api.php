<?php

use App\Http\Controllers\CursosController;
use App\Http\Controllers\AulasController;
use App\Http\Controllers\LeituraController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/user/me',[UserController::class, 'me'])->middleware('auth:api');
Route::apiResource("user", UserController::class);

Route::prefix('cursos')->group(function () {
    Route::get('/', [CursosController::class, 'index']);
    Route::post('/create', [CursosController::class, 'store'])->middleware(['auth:api', 'isAdmin']);
    Route::get('/show/{curso}', [CursosController::class, 'show']);
    Route::put('/update/{curso}', [CursosController::class, 'update'])->middleware(['auth:api', 'isAdmin']);
    Route::delete('/delete/{curso}', [CursosController::class, 'destroy'])->middleware(['auth:api', 'isAdmin']);
    Route::get('/search/{search}', [CursosController::class, 'search']);
    Route::post('/subscribe/{cursos}', [CursosController::class, 'subscribe'])->middleware(['auth:api']);
    // Route::get('/meus_cursos',[CursosController::class, 'meusCursos'])->middleware('auth:sanctum');
    Route::get('/meus_cursos', [CursosController::class, 'meusCursos'])->middleware('auth:api');

});

Route::prefix('admin')->group(function () {
    Route::post('/store', [UserController::class, 'storeAdmin']);
    Route::put('/update', [UserController::class, 'updateAdmin']);
    Route::get('/list', [UserController::class, 'getAdmins']);
})->middleware(['auth:api','isAdmin']);

Route::prefix('aulas')->group(function () {
    Route::get('/', [AulasController::class, 'index']);
    Route::post('/create', [AulasController::class, 'store'])->middleware(['auth:api','isAdmin']);
    Route::get('/show/{aulas}', [AulasController::class, 'show'])->middleware('auth:api');
    Route::put('/update/{aulas}', [AulasController::class, 'update'])->middleware(['auth:api','isAdmin']);
    Route::delete('/delete/{aula}', [AulasController::class, 'destroy'])->middleware(['auth:api', 'isAdmin']);
    Route::get('/search/{search}', [AulasController::class, 'search']);
    Route::patch('/{aulas}/visto', [AulasController::class, 'marcarVisto'])->middleware(['auth:api']);
});

Route::prefix('leituras')->group(function () {
    Route::get('/', [LeituraController::class, 'index']);
    Route::post('/', [LeituraController::class, 'store'])->middleware(['auth:api','isAdmin']);
    Route::get('/{leitura}', [LeituraController::class, 'show'])->middleware('auth:api');
    Route::put('/{leitura}', [LeituraController::class, 'update'])->middleware(['auth:api','isAdmin']);
    Route::delete('/{leitura}', [LeituraController::class, 'destroy'])->middleware(['auth:api', 'isAdmin']);
    Route::get('/search/{search}', [LeituraController::class, 'search']);
    Route::patch('/{leitura}/visto', [LeituraController::class, 'marcarVisto'])->middleware(['auth:api']);

});


Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('logout', [UserController::class, 'logout'])->name('logout');

Route::get('/test-env', function () {
    return env('DB_DATABASE');
});



Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('forgot.password');
Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.reset');
Route::get('/test', [TestController::class, 'index']);
Route::post('/api/cursos', [CursosController::class, 'store']);




// Route::middleware(['auth:api'])->group(function () {
//     Route::get('/user/me', [UserController::class, 'me']);
//     // Route::get('/user/me', [UserController::class, 'me'])->middleware('auth:api');

//     Route::post('/logout', [UserController::class, 'logout']);
//     // outras rotas protegidas
// });
