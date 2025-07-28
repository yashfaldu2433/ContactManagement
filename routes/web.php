<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('layouts.app');
});


Route::prefix('contacts')->name('contacts.')->controller(ContactController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/list-ajax', 'listAjax')->name('listAjax');
    Route::get('/create', 'create')->name('create');
    Route::get('/{id}/edit', 'edit')->name('edit');
    Route::post('/{id}/update', 'update')->name('update');
    Route::post('/save', 'saveContactData')->name('save');
    Route::delete('/{id}/delete', 'delete')->name('delete');
    Route::delete('/documents/{id}', 'deleteDocument')->name('deleteDocument');
});
