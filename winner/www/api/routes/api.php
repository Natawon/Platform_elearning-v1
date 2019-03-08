<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

// For Test
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return AUTH::user();
});

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::post('logout', 'UserController@logout');

Route::group(['middleware' => 'auth:api'], function(){
	Route::get('details', 'UserController@details');
	Route::post('details', 'UserController@details');
});

// End For Test

// Set OpenUrl
Route::group(['prefix' => 'set'], function () {
    // E1
    Route::post('{group_key}/login', 'SetAppController@singleSignOnTest');

    // E2
    Route::get('{group_key}/courses', 'SetAppController@groupsCoursesLists');
    Route::post('{group_key}/courses', 'SetAppController@groupsCoursesLists');

    // E3
    Route::post('{group_key}/courses/{course_id}', 'SetAppController@groupsCoursesInfo');

    // E4
    Route::post('{group_key}/courses/{course_id}/download/certificate', 'SetAppController@downloadCertificate');

    // S2
    Route::post('logout', 'SetAppController@logout');
});




