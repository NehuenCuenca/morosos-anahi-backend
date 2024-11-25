<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'debts', // OR use makeHidden (https://laravel.com/docs/11.x/eloquent-serialization#temporarily-modifying-attribute-visibility)
    ];

    protected $casts = [
        'is_deleted' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['debts_by_month_year'];

    // Many DEFAULTER's can debt many THING's
    public function debts(): BelongsToMany
    {
        return $this->belongsToMany(Thing::class, 'defaulter_thing', 'defaulter_id', 'thing_id')
                    ->withPivot('id', 'unit_price', 'quantity', 'retired_at', 'filed_at', 'was_paid')
                    ->orderByPivot('filed_at', 'ASC')
                    ->orderByPivot('retired_at', 'DESC')
                    ->using(Debt::class);
    }

    /**
     * Get the debts by month and year
     */
    protected function debtsByMonthYear(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->debts
                                ->groupBy( fn($debt) => Carbon::parse($debt->pivot->retired_at)->format('Y_m_F'))
                                ->sortByDesc( fn($debtsByMonthYear) => Carbon::parse(collect($debtsByMonthYear)->first()->pivot->retired_at)->format('Y_m_F'))
        );
    }
}
