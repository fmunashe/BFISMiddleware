<?php

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
    return view('auth.login');
});

Auth::routes();

Route::middleware(['auth'])->group( function() {
    Route::get('/changeProfile','HomeController@changeProfile')->name('changeProfile');
    Route::put('/uploadProfile/{user}','HomeController@uploadProfile')->name('uploadProfile');
    Route::get('/home', 'DataController@index')->name('home');
    Route::get('/home/{id}', 'DataController@show')->name('viewRecords');
    Route::get('/excelExport/{id}', 'HomeController@export')->name('excelExport');
    Route::get('/debitAccounts', 'DebitAccountController@index')->name('debitAccounts');
    Route::get('/createDebitAccount', 'DebitAccountController@create')->name('createDebitAccount');
    Route::post('/createDebitAccount', 'DebitAccountController@store')->name('createDebitAccount');
    Route::get('/removeDebitAccount/{debitAccount}', 'DebitAccountController@destroy')->name('removeDebitAccount');
    Route::get('/editDebitAccount/{debitAccount}', 'DebitAccountController@edit')->name('editDebitAccount');
    Route::put('/updateDebitAccount/{debitAccount}', 'DebitAccountController@update')->name('updateDebitAccount');
    Route::get('/showDebitAccount/{debitAccount}', 'DebitAccountController@show')->name('showDebitAccount');
    Route::get('/localBatches','HomeController@index')->name('localBatches');
    Route::get('/viewLocalRecords/{batch}','HomeController@show')->name('viewLocalRecords');
    Route::get('/processedBatches','HomeController@processed')->name('processedBatches');
    Route::get('/pendingBatches','HomeController@pending')->name('pendingBatches');
    Route::get('/corporateBatches','HomeController@corporateBatches')->name('corporateBatches');
    Route::get('/viewCorporateBatches/{batch}','HomeController@individualCorporateBatches')->name('viewCorporateBatches');
});
