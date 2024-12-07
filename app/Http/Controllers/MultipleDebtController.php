<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMultipleDebtsRequest;
use App\Http\Requests\UpdateMultipleDebtsRequest;
use App\Models\Debt;
use App\Models\Defaulter;
use Illuminate\Http\Request;

use function App\Helpers\GetDateTimeFormated;
use function App\Helpers\UpdateBalancesOfDefaulter;

class MultipleDebtController extends Controller
{
    public function update_multiple_debts(Defaulter $defaulter, UpdateMultipleDebtsRequest $request){
        $debts = Debt::where('defaulter_id', '=', $defaulter->id)
                        ->whereIn('id', $request->input('debts_id'))->get(); 

        foreach ($debts as $debt) {
            $debt->update([
                'was_paid' => $request->boolean('was_paid', $debt->was_paid),
                'filed_at' => GetDateTimeFormated($request->filed_at),
                'unit_price' => $request->integer('unit_price', $debt->unit_price),
            ]);
        }

        if($request->hasAny(['was_paid', 'unit_price'])){
            $defaulter = UpdateBalancesOfDefaulter($defaulter->id);
        }
        
        return response()->json([
            'message' => 'La colleccion de deudas fueron actualizadas',
            'debts' => $debts,
            'defaulter' => $defaulter
        ]);
    }
    
    public function destroy_multiple_debts(Defaulter $defaulter, DeleteMultipleDebtsRequest $request){
        $debts = Debt::where('defaulter_id', '=', $defaulter->id)
                        ->whereIn('id', $request->input('debts_id'))->get();

        foreach ($debts as $debt) {
            $debt->delete();
        }

        $updatedDefaulter = UpdateBalancesOfDefaulter($defaulter->id);

        return response()->json([
            'message' => 'La colleccion de deudas fueron eliminadas',
            'debts' => $debts,
            'defaulter' => $updatedDefaulter
        ]);
    }
}
