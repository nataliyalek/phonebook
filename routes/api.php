<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PhonebookController;
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

Route::group(['middleware' => 'auth:sanctum'], function() {
    // список всех контактов
    Route::get('contacts', [PhonebookController::class, 'contacts']);
    // получить информацию о контакте
    Route::get('contacts/{id}', [PhonebookController::class, 'singleContact']);
    // добавить новый контакт
    Route::post('contacts', [PhonebookController::class, 'createContact']);
    // изменить контакт
    Route::put('contacts/{id}', [PhonebookController::class, 'updateContact']);
    // удалить контакт
    Route::delete('contacts/{id}', [PhonebookController::class, 'deleteContact']);
});
