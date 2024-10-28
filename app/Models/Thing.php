<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Thing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'suggested_unit_price',
        'is_deleted'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',    
    ];

    protected $casts = [
        'is_deleted' => 'boolean'
    ];

    // Many THING's are debt by many DEFAULTER's
    public function defaulters(): BelongsToMany
    {
        return $this->belongsToMany(Defaulter::class, 'defaulter_thing', 'thing_id', 'defaulter_id');
    }
}
