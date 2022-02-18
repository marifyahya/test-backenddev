<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return app()->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->post('/forgote-password', 'AuthController@forgotePassword');
    $router->post('/reset-password', 'AuthController@resetPassword');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');
        $router->get('/profile', 'AuthController@getProfile');
        $router->post('/refresh-token', 'AuthController@refreshTokenJWT');

        // Users --> crud data in MongoDB
        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', 'UserController@getUsersList');
            $router->post('/create', 'UserController@create');
            $router->get('/{user}', 'UserController@getUserDetail');
            $router->put('/{user}/update', 'UserController@update');
            $router->delete('/{user}/delete', 'UserController@destroy');
        });

        // Books --> crud data in Firebase: Realtime Database
        $router->group(['prefix' => 'books'], function () use ($router) {
            $router->get('/', 'BookController@getBooksList');
            $router->post('/create', 'BookController@create');
            $router->get('/{book}', 'BookController@getBookDetail');
            $router->put('/{book}/update', 'BookController@update');
            $router->delete('/{book}/delete', 'BookController@destroy');
        });

        // Billings detail
        // Filter data denoms from file json => https://gist.github.com/Loetfi/fe38a350deeebeb6a92526f6762bd719
        $router->get('/billings-detail', 'BilldetailsController@getBillingsDetail');
    });
});
