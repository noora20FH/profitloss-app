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


