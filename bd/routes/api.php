<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'v1'
], function ($router) {
    Route::post('/login', [AuthController::class, 'index'])->name('login');
    Route::get('/memo', [AuthController::class, 'memo'])->name('logmemo');

});
// Route::get('/login', function (Request $request) {
//     return $request->user();
// })->middleware('jwt.verify');
Route::post('me', [AuthController::class, 'me'])->name('log');
