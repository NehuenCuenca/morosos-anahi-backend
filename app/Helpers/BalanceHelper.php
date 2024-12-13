<?php

namespace App\Helpers;

use App\Models\Debt;
use App\Models\Defaulter;
use App\Models\Thing;
use Carbon\Carbon;

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

if(!function_exists('FixBalancesByCrossAndJoin')) {
    function FixBalancesByCrossAndJoin($paymentDebt){
        $beforeClarificationDebtQuery = Debt::where([
            ['defaulter_id', '=', $paymentDebt->defaulter_id],
            ['retired_at', '<=', $paymentDebt->retired_at],
        ]);

        if( $beforeClarificationDebtQuery->count() <= 1 ){ return; }

        $defaulter = UpdateBalancesOfDefaulter($paymentDebt->defaulter_id);

        $clarificationThing = Thing::firstWhere('name', '=', 'PASADA EN LIMPIO');
        
        $clarificationDebt = Debt::create([
            "defaulter_id" => $paymentDebt->defaulter_id,
            "thing_id" => $clarificationThing->id,
            "unit_price" => $defaulter->total_balance,
            "quantity" => 1,
            "retired_at" => Carbon::parse($paymentDebt->retired_at)->addSecond(),
            "filed_at" => null,
            "was_paid" => false,
        ]);

        $beforeClarificationDebtQuery->update(['was_paid' => true]);
    }
};