<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'Ecommerce\FrontController@index')->name('front.index');
Route::get('/product', 'Ecommerce\FrontController@product')->name('front.product');
Route::get('/product/{slug}', 'Ecommerce\FrontController@show')->name('front.show');
Route::get('/category/{slug}', 'Ecommerce\FrontController@categoryProduct')->name('front.category');
Route::post('/cart', 'Ecommerce\CartController@addToCart')->name('front.cart');
Route::get('/carts', 'Ecommerce\CartController@listCart')->name('front.list_cart');
Route::put('/cart/update', 'Ecommerce\CartController@updateCart')->name('front.update_cart');
Route::get('/checkout', 'Ecommerce\CartController@checkout')->name('front.checkout');

Route::post('/checkout', 'Ecommerce\CartController@processCheckout')->name('front.store_checkout');
Route::get('/checkout/{invoice}', 'Ecommerce\CartController@checkoutFinish')->name('front.finish_checkout');

Route::group(['prefix' => 'member', 'namespace' => 'Ecommerce'], function () {
    Route::get('dashboard', 'LoginController@dashboard')->name('customer.dashboard');
    Route::get('login', 'LoginController@loginForm')->name('customer.login');
    Route::get('logout', 'LoginController@logout')->name('customer.logout');
    Route::post('login', 'LoginController@login')->name('customer.post_login');
    Route::get('verify/{token}', 'FrontController@verifyCustomerRegistratition')->name('customer.verify');
});

Auth::routes();

Route::group(['prefix' => 'administrator', 'middleware' => 'auth'], function () {
    Route::get('/product/bulk', 'ProductController@massUploadForm')->name('product.bulk');
    Route::resource('category', 'CategoryController')->except(['create', 'show']);

    Route::get('/home', 'HomeController@index')->name('home');
    Route::post('/product/bulk', 'ProductController@massUpload')->name('product.saveBulk');
    Route::resource('product', 'ProductController');
});
