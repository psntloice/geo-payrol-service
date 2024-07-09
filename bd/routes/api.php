<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayPeriodController;
use App\Http\Controllers\EarningController;
use App\Http\Controllers\DeductionController;

Log::info('routes recahed');

// })->middleware('auth:sanctum');
Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'v1'
], function ($router) {
    Log::info('JWT routes');

    Route::post('/login', [AuthController::class, 'index'])->name('login');
    Route::get('/memo', [AuthController::class, 'memo'])->name('logmemo');

    Route::resource('payPeriods', PayPeriodController::class);
    Route::resource('earnings', EarningController::class);
    Route::resource('deductions', DeductionController::class);
    Route::resource('payrolls', PayrollController::class); 
    // Route::get('/employees', [EmployeeController::class, 'show']);
    Route::resource('employees', EmployeeController::class);

});

Route::post('me', [AuthController::class, 'me'])->name('log');

// Route::resource('payrolls', PayrollController::class);



// Route::get('/payPeriods', [PayPeriodController::class, 'index']);

// Route::group(['prefix' => 'v1'], function(){

//     // Public Routes/End Points
//     Route::post('/register', [AuthController::class, 'register']);
//     Route::post('/login', [AuthController::class, 'login']);

    

//     // Private/Protected Routes
//     Route::group(['middleware' => ['auth:sanctum']], function(){
//         Route::post('/logout', [AuthController::class, 'logout']);

//         Route::get('/roles', [RoleController::class, 'index']);
//         Route::post('/roles', [RoleController::class, 'store']);
//         Route::post('/update_role', [RoleController::class, 'update']);
//         Route::post('/delete_role', [RoleController::class, 'destroy']);

//         Route::get('/users', [UserController::class, 'index']);
//         Route::post('/users', [UserController::class, 'store']);
//         Route::post('/update_user', [UserController::class, 'update']);
//         Route::post('/delete_user', [UserController::class, 'destroy']);

//         Route::post('/stkpush', [PaymentsController::class, 'STKPush']);
//         Route::post('/confirm', [PaymentsController::class, 'STKConfirm']);

//         Route::get('/payments', [RecordController::class, 'index']);
//     });
// });