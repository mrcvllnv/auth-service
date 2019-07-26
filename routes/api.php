<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::get('/profile', 'Api\AuthController@profile');
    Route::post('/register', 'Api\AuthController@register');
    Route::post('/login', 'Api\AuthController@login');
    Route::post('/logout', 'Api\AuthController@logout');
});