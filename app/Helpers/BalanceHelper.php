<?php

namespace App\Helpers;

use App\Models\Defaulter;

if(!function_exists('PricesAcumuluted')) {
    function PricesAcumuluted($items, $acumPositives){ 
        return array_reduce($items, function($acum, $item) use ($acumPositives){
            // dd($item);
            if( 
                ($item['unit_price'] > 0 && $acumPositives) || 
                ($item['unit_price'] < 0 && !$acumPositives)
            ){
                $acum += $item['unit_price'] * $item['quantity'];
            }

            return $acum;
        }, 0);
    }
};

if(!function_exists('UpdateBalancesOfDefaulter')) {
    function UpdateBalancesOfDefaulter($defaulter_id){ 
        $defaulter = Defaulter::find($defaulter_id);
        $itemsOfDefaulter = $defaulter->items->all();
        $debtPrices = PricesAcumuluted($itemsOfDefaulter, true);
        $discountPrices = PricesAcumuluted($itemsOfDefaulter, false);

        $defaulter->update([
            'debt_balance' => $debtPrices ?? 0,
            'discount_balance' => $discountPrices ?? 0,
            'total_balance' => ($debtPrices + $discountPrices) ?? 0
        ]);

        return $defaulter;
    }
};