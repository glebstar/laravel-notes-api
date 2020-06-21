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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'Auth\AuthController@apiregister');
Route::post('login', 'Auth\AuthController@apiauth');

Route::middleware(['api'])->group(function () {
    Route::resource('note', 'NoteController');
    Route::post('note/addfile/{id}', 'NoteController@addfile');
    Route::get('note/restore/{id}', 'NoteController@restore')->name('note.restore');
});

Route::group([
    'middleware' => 'api',
], function ($router) {

});
