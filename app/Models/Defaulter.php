<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Defaulter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'debt_balance',
        'discount_balance',
        'total_balance',
        'created_at',
        'is_deleted'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',    
    ];

    protected $casts = [
        'is_deleted' => 'boolean'
    ];

    // Many DEFAULTER's can debt many THING's
    public function debts(): BelongsToMany
    {
        return $this->belongsToMany(Thing::class, 'defaulter_thing', 'defaulter_id', 'thing_id')
                    ->withPivot('unit_price', 'quantity', 'retired_at', 'filed_at', 'was_paid');
    }
}
