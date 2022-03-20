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
});

Route::group(['prefix' => 'user'], function () {
    Route::get('folders/{user_uid}', 'UserController@getUserFolders');
});



Route::post('addproject/{ownerable}/{id}', 'ProjectController@addProject');

Route::post('addproduct/{store_id}', 'ProductController@AddProduct');
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

// upload / update user profile pic
Route::post('useravatar/{user_id}', 'UserController@uploadAvatar');

Route::post('option-covers/{id}', 'ProductController@attachProductOptionPictures');

Route::post('user/register', 'ManagementController@registerUser');
Route::post('user/login', 'ManagementController@loginUser');
Route::post('user/update-name', 'ManagementController@updateDisplayName');
Route::post('user/update-photo', 'ManagementController@updateProfilePic');
Route::post('user', 'ManagementController@verifyEmailCode');
Route::post('validate-code', 'ManagementController@validatingCode');
Route::post("update-phone", "ManagementController@updatePhoneNumber");
Route::post("subscribe", "ManagementController@subscribe");

// brand endpoints..
Route::post("brand", "StoreController@createBrand");
Route::post("brand/update/{id}", "StoreController@updateBrand");
Route::get("brand/{id}", "StoreController@getBrandById");
Route::post("brandcover", "StoreController@brandCover");
Route::post("brandlogo/{id}", "StoreController@brandLogo");
Route::post("brand/follow/{id}", "StoreController@followStore");
Route::post("brand/unfollow/{id}", "StoreController@unFollowStore");
Route::get("brandproductsfilter/{store_id}", "StoreController@storeProductFilter");
Route::get("brandtypefilter/{store_id}", "StoreController@storeProductByTypeFilter");

Route::post("brand/edit", "ManagementController@editBrandById");

// Route::post("collect", "ManagementController@addProductToNewColelction");
Route::post("add-to-collection", "ManagementController@addProductToExistingCollection");
Route::get("collections/{store_id}", "ManagementController@getAllCollectionsbyStoreId");
Route::get("store-id/{product_id}", "ManagementController@getStoreIdByProductId");
Route::post("publish-name/{identity_id}", "ManagementController@editNameForProductPublishing");
Route::post("preview", "ManagementController@previewProduct");
Route::get("search", "ProductController@filterProductSearchPage");
Route::get("products", "ProductController@getAllProducts");
Route::get("home/products", "ProductController@getHomeProducts");
// getHomeProducts
Route::post('upcrop/{product_id}', "ProductController@UpdateOrCreateOption");
Route::post('addfile/{product_id}', "ProductController@UpdateOrCteateFile");
//uploadCover
Route::post('cover-upload', "CoverController@uploadCover");

//attach covers to new option
Route::post("attchcovernewop", "CoverController@attachCoversToNewOption");

//test routes
Route::get("get-options", "ProductController@fakeOptionsData");

Route::get("test-pdf/{id}", "SubscriptionController@testPDF");
Route::get("powerpoint", "CoverController@powerPoint");


Route::post("request/{id}", "ProductController@requestProduct");



// create user collection api (folder)..
Route::post("collection", "ProductController@makeNewCollection");
Route::post("save", "ProductController@saveToFolder");
Route::post("unsave", "ProductController@removerFromFolder");
Route::get("allcollections/{id}", "ProductController@listAllFolders");
Route::get("collections", "ProductController@allFolders");
Route::get("folders/{user_id}/{product_id}", "ProductController@listAllFoldersByProduct");
Route::get("folder/{id}", "UserController@getUserCollectionById");
Route::post("update-collection/{id}", "UserController@editCollection");
Route::post("delete-collection/{id}", "UserController@deleteCollection");


// listAllFoldersByProduct


//create store collection (collection) Api
Route::post("brandcollection", "ProductController@newBrandColelction");
Route::post("brandcollect", "ProductController@attachProductToBrandCollection");
Route::post("branduncollect", "ProductController@deAttachProductToBrandCollection");
Route::get("collection/{id}", "ProductController@getCollectionById");
Route::get("col/{id}", "StoreController@getBrandCollectionbyId");
Route::get("collections/{id}", "ProductController@getCollectionById");

Route::get("searchbar", "ProductController@searchBar");
Route::post("testdelete", "ProductController@testDeleteRelated");


Route::group(['prefix' => 'product/delete'], function () {
    Route::post("/{id}", "ProductController@deleteProduct");
    Route::post("option/{id}", "ProductController@deleteOption");
    Route::post("file/{id}", "ProductController@deleteFile");
    Route::post("gallery/{id}", "ProductController@deleteGallery");
});


// passport firebase apis test routes

Route::post("registeruser", "UserController@registerUser");
Route::post("updateuser/{user_id}", "UserController@updateUser");
Route::post("deleteuser/{uid}", "UserController@deleteUserr");

Route::post("user-designer/{user_uid}", "UserController@upgradeUserToDesigner");
