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

Route::get('/', 'InputForms@setForms')->name('index');

#Route::post('/','curl@getContet')->name('curler');

Route::post('/fetch-form', 'FormInput\FormFetch@getInput')->name('formFetcher');
Route::post('/pagination','Pagination\Pagination@startGrabbingPagination')->name('paginationFetch');

# Ajax #
Route::get('inputForm-singlePages',function () {
    return view('input-forms/singlePages');
});

Route::get('inputForm-pagination',function () {
    return view('input-forms/pagination');
});