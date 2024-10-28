<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'defaulter_thing';

    protected $fillable = [
        'defaulter_id',
        'thing_id',
        'unit_price',
        'quantity',
        'retired_at',
        'filed_at',
        'was_paid'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',    
    ];

    protected $casts = [
        'was_paid' => 'boolean'
    ];
}
