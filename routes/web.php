<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

// 🔐 Protected Routes (Login required)
Route::middleware(['auth'])->group(function () {

    Route::get('/', [ImportController::class, 'index']);
    Route::post('/import', [ImportController::class, 'import']);

    Route::get('/contacts-data', [ImportController::class, 'contactsData']);

    Route::post('/contacts/store', [ImportController::class, 'store']);
    Route::get('/contacts/edit/{id}', [ImportController::class, 'edit']);
    Route::post('/contacts/update/{id}', [ImportController::class, 'update']);
    Route::delete('/contacts/delete/{id}', [ImportController::class, 'destroy']);

});

// ✅ Breeze auth routes (login, register, logout)
require __DIR__.'/auth.php';
