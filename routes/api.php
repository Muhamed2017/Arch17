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

Route::post('Subscription', 'SubscriptionController@create');


Route::group(['prefix' => 'user/registration'], function () {

    Route::post('signin', 'LoginController@login')->name('user');
    Route::post('signup', 'RegisterController@register')->name('user');
    // Route::post('resetpassword', 'Api\Site\RegisterController@resetPassword')->name('user');
    // Route::post('verify', 'Api\Site\RegisterController@verify')->name('user');

});

Route::group(['middleware' => 'auth_user', 'prefix'=>'account'], function() {
    Route::post('create-business-account', 'UserController@CreateBusinessAccount');
    Route::post('create-store', 'UserController@CreateStore');
    Route::post('add-product', 'ProductController@AddProducuct');
});

