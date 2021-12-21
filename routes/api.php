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

Route::group([
    'prefix' => 'auth'

], function () {

    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

});

Route::group(['middleware' => ['jwt.auth']], function() {
    /*AÑADE AQUI LAS RUTAS QUE QUIERAS PROTEGER CON JWT*/

    Route::get('groups', 'GroupController@index');
    Route::post('groups', 'GroupController@store');
    Route::get('groups/data/joinTo/{idGroup}', 'GroupController@joinTo');
    Route::get('groups/{idGroup}', 'GroupController@show')->middleware('check.group');
    Route::post('groups/data/createNote/{idGroup}', 'GroupController@createNote')->middleware('check.group');

});
