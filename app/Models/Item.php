<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'defaulter_id',
        'name',
        'unit_price',
        'quantity',
        'retirement_date',
        'was_paid'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',    
    ];

    public function defaulter(): BelongsTo
    {
        return $this->belongsTo(Defaulter::class);
    }
}
