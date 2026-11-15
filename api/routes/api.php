<?php

use App\Http\Controllers\AreaLogada\Carteira;
use App\Http\Controllers\AreaLogada\Principal;
use App\Http\Controllers\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});


Route::post('/autenticar'        , [Login::class, 'autenticar']);
Route::post('/usuarios/registrar', [Login::class, 'registrar']);

Route::middleware(['auth:sanctum', 'throttle:ratelimit', 'throttle:40,1'])
    ->prefix('area-logada')
    ->group(function () {
        Route::get('/principal', [Principal::class, 'index']);
        Route::post('/logout'  , [Login::class, 'logout']);


        Route::post('/carteira/depositar', [Carteira::class, 'depositar']);
        Route::post('/carteira/sacar',     [Carteira::class, 'sacar']);
    });
