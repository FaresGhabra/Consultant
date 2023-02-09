<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\ExpertController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});
Route::get('/getexperinces',[ExperienceController::class,'getexperinces']);
Route::get('/getexperts',[ExperienceController::class,'getexperts']);
Route::get('/searchExpert',[ExpertController::class,'searchExpert']);
Route::post('/setAvailableTime',[ExpertController::class,'setAvailableTime']);
Route::post('/setAppointment',[ExpertController::class,'setAppointment']);
Route::get('/getAvailableTime',[ExpertController::class,'getAvailableTime']);
Route::get('/showAppointment',[ExpertController::class,'showAppointment']);
