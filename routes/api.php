<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Auth Routes
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');


//Expert Routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/expert/get_appointments',[ExpertController::class,'get_appointments']);
    Route::post('/available_time/edit',[ExpertController::class,'edit_available_time']);
    Route::get('expert/rates',[ExpertController::class, 'show_rates']);
    Route::post('/add_favorite',[UserController::class,'add_favorite']);
    Route::get('/show_favorites',[UserController::class,'show_favorites']);
    Route::delete('/unfavorite',[UserController::class,'unfavorite']);
    Route::post('/rate',[UserController::class,'rate']);
    Route::post('/appointments/book',[UserController::class,'book_appointment']);
    Route::get('/appointments/booked',[ExpertController::class,'booked_appointments']);
    Route::get('/appointments/available',[ExpertController::class,'available_time']);
    Route::get('/my_appointments',[UserController::class,'booked_appointments']);
});
Route::get('/search/{query}',[UserController::class,'search']);
Route::get('/details',[ExpertController::class,'expert_details']);