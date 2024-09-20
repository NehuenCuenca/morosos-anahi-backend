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
        'debt_balance',
        'discount_balance',
        'total_balance',
        'created_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',    
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
