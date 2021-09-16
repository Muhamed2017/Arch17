<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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
Route::post('create_designer_page', 'CompanyController@save_company');
Route::post('update_designer_page', 'CompanyController@update_company_info');
Route::post('update_designer_info', 'CompanyController@update_company_profile');
Route::post('upload_designer_avatar', 'CompanyController@upload_designer_avatar');
Route::post('follow_company', 'CompanyController@follow');
Route::post('follow_store', 'StoreController@follow');
Route::get('designer/{slug}', 'CompanyController@get_company');


Route::group(['prefix' => 'user/registration'], function () {
    Route::post('signin', 'LoginController@login')->name('user');
    Route::post('signup', 'RegisterController@register')->name('user');
    // Route::post('resetpassword', 'Api\Site\RegisterController@resetPassword')->name('user');
    // Route::post('verify', 'Api\Site\RegisterController@verify')->name('user');

});

Route::group(['middleware' => 'auth_user', 'prefix' => 'account'], function () {
    Route::post('create-business-account', 'UserController@CreateBusinessAccount');
    Route::post('create-store', 'UserController@CreateStore');
    Route::post('add-product', 'ProductController@AddProducuct');
    Route::post('create-product-collection', 'UserController@create_product_collection');
    Route::post('add-product-to-collection', 'UserController@add_product_to_collection');
    Route::post('remove-product-from-collection', 'UserController@remove_product_from_collection');
    Route::post('get-user-product-collection', 'UserController@geUserProductCollections');
    Route::post('add-product', 'ProductController@AddProduct');
    // Route::post('add-Project', 'ProjectController@AddProject');
});
// Route::post('add-project', 'ProjectController@AddProject');

Route::group(['middleware' => 'auth_user', 'prefix' => 'account/addproject'], function () {
    Route::post('info', 'ProjectController@addProjectInfo');
    Route::post('description', 'ProjectController@addProjectDescription');
    Route::post('supplier', 'ProjectController@addProjectSupplier');
    Route::post('addContentImage', 'ProjectController@addProjectContentImage');
    Route::post('designer', 'ProjectController@addProjectDesigner');
    Route::post('role', 'ProjectController@addProjectRole');
    Route::post('cover', 'ProjectController@addProjectCover');
});


//add product process
// Route::group(['middleware' => 'auth_user', 'prefix' => 'addproduct'], function () {

Route::post('addproduct', 'ProductController@AddProduct');
Route::post('identity/{id}', 'ProductController@AddProductIdentity');
Route::post('option-price/{id}/{option_id}', 'ProductController@addOptionToProduct');
Route::post('description/{id}', 'ProductController@addDescriptionToProduct');
Route::post('desc/{id}', 'ProductController@ProductDescription');
Route::post('files/{id}', 'ProductController@ProductFiles');
Route::get('product/{id}', 'ProductController@getProductById');
Route::post('overviewContnet/{id}', 'ProductController@ProductDescriptionCotent');

Route::post('descContent/{id}', 'ProductController@ProductDescriptionContent');
// test image upload ...
Route::post('upload/{id}', 'ProductController@testImageUpload');
Route::post('option-covers/{id}', 'ProductController@attachProductOptionPictures');


Route::post('user', 'ManagementController@verifyEmailCode');
Route::post('validate-code', 'ManagementController@validatingCode');
