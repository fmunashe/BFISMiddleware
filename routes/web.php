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
    Route::get('/home', 'DataController@index')->name('home');
    Route::get('/home/{id}', 'DataController@show')->name('viewRecords');
    Route::get('/getResponse/{id}', 'DataController@getResponse')->name('getResponse');
    Route::get('/debitAccounts', 'DebitAccountController@index')->name('debitAccounts');
    Route::get('/createDebitAccount', 'DebitAccountController@create')->name('createDebitAccount');
    Route::post('/createDebitAccount', 'DebitAccountController@store')->name('createDebitAccount');
    Route::get('/removeDebitAccount/{debitAccount}', 'DebitAccountController@destroy')->name('removeDebitAccount');
    Route::get('/editDebitAccount/{debitAccount}', 'DebitAccountController@edit')->name('editDebitAccount');
    Route::put('/updateDebitAccount/{debitAccount}', 'DebitAccountController@update')->name('updateDebitAccount');
    Route::get('/showDebitAccount/{debitAccount}', 'DebitAccountController@show')->name('showDebitAccount');
});
