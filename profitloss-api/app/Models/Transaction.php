<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'coa_id',
        'date',
        'description',
        'debit',
        'credit'
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function coa()
    {
        // Satu Transaksi dimiliki oleh satu Chart of Account
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }



}
