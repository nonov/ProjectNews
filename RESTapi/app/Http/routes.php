<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



Route::post('/user/login', 'UserController@authenticate');
Route::post('api/user/emailCheck', 'UserController@emailCheck');
Route::post('api/user/activate', 'UserController@activateUser');

Route::group(['middleware' => 'auth'], function()
{
	Route::get('/user/token', function() 
	{
		csrf_token();
		echo true;	
	});
});

Route::resource('api/user','UserController', ['only' => ['store', 'index']]);

Route::group(['middleware' => ['csrf', 'auth']], function()
{
	Route::post('api/user/passwordCheck', 'UserController@passwordCheck');
	Route::post('user/setPicture', 'UserController@setPicture');
	Route::get('user/getPicture', 'UserController@getPicture');
	Route::get('user/logout', 'UserController@logout');
	Route::post('api/article/display', 'ArticleController@index');
	Route::post('api/article/setPicture', 'ArticleController@setPicture');
	Route::resource('api/article','ArticleController');
	Route::resource('api/user','UserController', ['except' => ['store', 'index']]);	

});

Route::get('home', 'HomeController@index');

Route::controllers([
	'password' => 'Auth\PasswordController',
]);

