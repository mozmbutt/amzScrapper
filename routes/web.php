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
/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', 'MyController@index')->name('/');
Route::get('importExportView', 'MyController@importExportView');
Route::post('import', 'MyController@import')->name('import');
Route::get('/products', 'MyController@products')->name('products');
Route::get('/updateDB', 'MyController@updateDB')->name('updateDB');

Route::get('/getProductURL', 'MyController@getProductURL')->name('getProductURL');
Route::get('/amazon', 'MyController@amazon')->name('amazon');
Route::get('/singleURL', 'MyController@singleURL')->name('singleURL');
Route::get('/export-amazon-products', 'ProductDescriptionController@export')->name('export');

Route::get('/hafeez-center-scrap', 'HafeezCenterController@scrap')->name('hafeez-center-scrap');
Route::get('/export-shops', 'HafeezCenterController@export')->name('export-shops');