<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'code', 'name'];

    public function category()
    {
        // Satu COA dimiliki oleh satu Kategori COA
        return $this->belongsTo(CoaCategory::class, 'category_id');
    }

    public function transactions()
    {
        // Satu COA bisa digunakan dalam banyak Transaksi
        return $this->hasMany(Transaction::class, 'coa_id');
    }
}