<?php

namespace App\Helpers;

use App\Models\Defaulter;

if(!function_exists('PricesAcumuluted')) {
    function PricesAcumuluted($things, $acumPositives){ 
        return array_reduce($things, function($acum, $thing) use ($acumPositives){
            // dd($thing->pivot);
            $unitPrice = $thing->pivot->unit_price;
            $quantity = $thing->pivot->quantity;
            $was_paid = $thing->pivot->was_paid;
            if( 
                (!boolval($was_paid)) && 
                (($unitPrice > 0 && $acumPositives) || 
                ($unitPrice < 0 && !$acumPositives))
            ){
                $acum += $unitPrice * $quantity;
            }

            return $acum;
        }, 0);
    }
};

if(!function_exists('UpdateBalancesOfDefaulter')) {
    function UpdateBalancesOfDefaulter($defaulter_id){
        if($defaulter_id <= 0) return;
        $defaulter = Defaulter::find($defaulter_id);
        
        $debts = $defaulter->debts->sortByDesc('pivot.retired_at')->all();
        $debtPrices = PricesAcumuluted($debts, true);
        $discountPrices = PricesAcumuluted($debts, false);

        $defaulter->update([
            'debt_balance' => $debtPrices ?? 0,
            'discount_balance' => $discountPrices ?? 0,
            'total_balance' => ($debtPrices + $discountPrices) ?? 0
        ]);

        return $defaulter;
    }
};