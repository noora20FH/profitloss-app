<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CoaCategoryController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\TransactionController;


Route::get('/api/connection-test', function () {
    return response()->json([
        'message' => 'API connection successful!',
        'framework' => 'Laravel',
        'status' => 200,
    ]);
});

// Anda bisa menambahkan route accounts di sini juga:
Route::get('/accounts', function () {
    return response()->json([
        'data' => [
            ['id' => 1, 'name' => 'Cash'],
            ['id' => 2, 'name' => 'Bank'],
        ],
        'message' => 'Accounts data retrieved.',
    ]);
});



// Grouping routes di bawah /api
Route::prefix('api')->group(function () {
    // CRUD untuk Master Kategori COA
    Route::resource('categories', CoaCategoryController::class)->only(['index', 'store', 'update']);

    // CRUD untuk Master Chart of Accounts (COA)
    Route::resource('coa', ChartOfAccountController::class)->only(['index', 'store', 'update']);

    // CRUD untuk Transaksi
    Route::resource('transactions', TransactionController::class)->only(['index', 'store', 'update']);
});