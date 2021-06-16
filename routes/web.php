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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['prefix' => 'administrator', 'middleware' => 'auth'], function () {
    Route::get('/product/bulk', 'ProductController@massUploadForm')->name('product.bulk');
    Route::resource('category', 'CategoryController')->except(['create', 'show']);

    Route::get('/home', 'HomeController@index')->name('home');
    Route::post('/product/bulk', 'ProductController@massUpload')->name('product.saveBulk');
    Route::resource('product', 'ProductController');
});
