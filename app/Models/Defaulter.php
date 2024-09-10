<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Defaulter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'negative_balance',
        'positive_balance',
        'total_balance'        
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
