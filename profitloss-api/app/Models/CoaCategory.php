<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function accounts()
    {
        // Satu kategori memiliki banyak Chart of Accounts
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}