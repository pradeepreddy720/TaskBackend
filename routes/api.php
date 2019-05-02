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

Route::post('login', 'UserController@login');
Route::post('register', 'UserController@register');
Route::post('confirmEmail', 'UserController@verifyEmail');
Route::post('resetPassword', 'UserController@resetPassword');
Route::post('completeReset', 'UserController@completeReset');

Route::get('getCompany', 'UserController@getCompanys');
Route::post('searchAndSort', 'UserController@sortandsearchCompany');

Route::post('addFavourite','UserController@addFav')->middleware('auth:api');
Route::get('getFavourite','UserController@getFav')->middleware('auth:api');
Route::post('removeFavourite','UserController@removeFav')->middleware('auth:api');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
