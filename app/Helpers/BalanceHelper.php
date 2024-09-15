<?php

namespace App\Helpers;

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