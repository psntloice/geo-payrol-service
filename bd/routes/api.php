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
  
});