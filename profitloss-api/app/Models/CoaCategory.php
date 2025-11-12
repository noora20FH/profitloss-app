<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoaCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function coas(): HasMany
    {
        // Diasumsikan nama Foreign Key di tabel 'chart_of_accounts' adalah 'category_id'
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}
