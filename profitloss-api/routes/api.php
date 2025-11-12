<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CoaCategoryController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\TransactionController;


    // CRUD untuk Master Kategori COA
    Route::resource('categories', CoaCategoryController::class)->only(['index', 'store', 'update','destroy']);

    // CRUD untuk Master Chart of Accounts (COA)
    Route::resource('coa', ChartOfAccountController::class)->only(['index', 'store', 'update','destroy']);

    // CRUD untuk Transaksi
    Route::resource('transactions', TransactionController::class)->only(['index', 'store', 'update']);
