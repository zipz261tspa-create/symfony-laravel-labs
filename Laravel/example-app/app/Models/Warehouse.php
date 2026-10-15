<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name', 'location', 'description'];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}