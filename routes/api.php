<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SerieController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Middleware\Auth;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//series
Route::get('series',[SerieController::class,'seriesGet']);
Route::get('series/{id}',[SerieController::class,'serieDetails']);


//category


//user
Route::post('register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);

Route::middleware([Auth::class])->group(function(){

    //series

    Route::post('series',[SerieController::class,'seriesPost']);
    Route::put('series/{id}',[SerieController::class,'seriesPut']);
    Route::delete('series/{id}',[SerieController::class,'seriesDelete']);

    //category
    Route::post('category',[CategoryController::class,'categoryPost']);
    Route::put('category/{id}',[CategoryController::class,'categoryPut']);
    Route::delete('category/{id}',[CategoryController::class,'categoryDelete']);


});
