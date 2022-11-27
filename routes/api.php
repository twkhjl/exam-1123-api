<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    // 'middleware' => 'api',
    'prefix' => 'reminder'
], function ($router) {
    Route::get('/all', [ReminderController::class,'index']);
    Route::post('/store', [ReminderController::class,'store']);
    Route::post('/destroy', [ReminderController::class,'destroy']);
    Route::post('/update', [ReminderController::class,'update']);
    Route::post('/send', [ReminderController::class,'send']);
    Route::post('/toggle_is_done', [ReminderController::class,'toggle_is_done']);
    Route::post('/ttt', [ReminderController::class,'ttt']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'user'
], function ($router) {
    Route::post('test', [AuthController::class,'test']);
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    Route::post('register', [UserController::class, 'create']);
    Route::post('change_password', [UserController::class, 'change_password']);


});
