<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    'namespace' => 'Api'
], function () {
    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post('signup', 'AuthController@signup');
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
    });     
});
Route::post('edit-user-profile', 'Api\AuthController@editProfile');
Route::post('create_post', 'Api\PostController@createPost');
