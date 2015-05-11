<?php

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);


Route::get('home', 'ChatController@index');
Route::get('systemMessage', 'ChatController@systemMessage');