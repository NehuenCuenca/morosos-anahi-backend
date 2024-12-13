<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMultipleDebtsRequest;
use App\Http\Requests\UpdateMultipleDebtsRequest;
use App\Models\Debt;
use App\Models\Defaulter;
use App\Models\Thing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function App\Helpers\GetDateTimeFormated;
use function App\Helpers\UpdateBalancesOfDefaulter;

class MultipleDebtController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    /* public function store_multiple_debts(Request $request)
    {
        $trimmedDefaulterName = $request->string('defaulter_name')->trim();
        $firstDefaulterFound = Defaulter::find($request->integer('defaulter_id'));

        if (!isset($firstDefaulterFound)) {
            $firstDefaulterFound = Defaulter::where('name', '=', $trimmedDefaulterName)->first();
        }

        $defaulterAlreadyExist = $firstDefaulterFound !== null;
        $newDefaulter = null;

        $defaulterHasDeliverPayment = false;

        try {
            DB::beginTransaction();
            if (!$defaulterAlreadyExist) {
                $newDefaulter = Defaulter::create([
                    'name' => $trimmedDefaulterName,
                    'debt_balance' => 0,
                    'discount_balance' => 0,
                    'total_balance' => 0,
                    'is_deleted' => false
                ]);
            }
            
            $finalDefaulter = ($defaulterAlreadyExist) ? $firstDefaulterFound : $newDefaulter;
    
            $incomingThings = $request->input('things');
            for ($i = 0; $i < sizeof($incomingThings); $i++) {
                $firstThingFound = Thing::where('name', '=', $incomingThings[$i]['thing_name'])->first();
                $thingAlreadyExist = $firstThingFound !== null;
                $newThing = null;
                $defaulterHasDeliverPayment = ($incomingThings[$i]['unit_price'] < 0) && ($incomingThings[$i]['thing_name'] === 'entrego');

    
                if (!$thingAlreadyExist) {
                    $newThing = Thing::create([
                        "name" => $incomingThings[$i]['thing_name'],
                        "suggested_unit_price" => $incomingThings[$i]['unit_price'],
                        'is_deleted' => false
                    ]);
                } else {
                    $firstThingFound->suggested_unit_price = $incomingThings[$i]['unit_price'];
                    $firstThingFound->save();
                }
    
                $thingIdToRecord = ($thingAlreadyExist) ? $firstThingFound['id'] : $newThing['id'];
                $unitPriceToRecord = ($thingAlreadyExist) ? $firstThingFound['suggested_unit_price'] : $newThing['suggested_unit_price'];
    
                Debt::create([
                    "defaulter_id" => $finalDefaulter->id,
                    "thing_id" => $thingIdToRecord,
                    "unit_price" => $unitPriceToRecord,
                    "quantity" => $incomingThings[$i]['quantity'],
                    "retired_at" => GetDateTimeFormated($incomingThings[$i]['retired_at']),
                    "filed_at" => GetDateTimeFormated($incomingThings[$i]['filed_at']),
                    "was_paid" => $incomingThings[$i]['was_paid'],
                ]);
            }
    
            $finalDefaulter = UpdateBalancesOfDefaulter($finalDefaulter->id);
            $finalDefaulter->debts->sortByDesc('pivot.retired_at');

            DB::commit();
    
            return response()->json([
                'message' => "Se registró la deuda exitosamente a nombre de: $finalDefaulter->name",
                'defaulter' => $finalDefaulter,
                'defaulterHasDeliverPayment' => $defaulterHasDeliverPayment
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                "message" => "La deuda no pudo ser creada, ocurrio un error inesperado. Ejecutando rollback de la transaccion.",
                "throwable" => $th
            ], 400);
        } 
    } */

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
