<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Models\Thing;
use App\Models\Debt;
use App\Models\Defaulter;
use function App\Helpers\UpdateBalancesOfDefaulter;

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDebtRequest $request) 
    {
        $trimmedDefaulterName = $request->string('defaulter_name')->trim();
        $firstDefaulterFound = Defaulter::find($request->integer('defaulter_id'));
        
        if(!isset($firstDefaulterFound)){ 
            $firstDefaulterFound = Defaulter::where('name', '=', $trimmedDefaulterName)->first();
        }
                                        
        $defaulterAlreadyExist = $firstDefaulterFound !== null;
        $newDefaulter = null;

        if( !$defaulterAlreadyExist ){
            $newDefaulter = Defaulter::create([
                'name' => $trimmedDefaulterName,
                'debt_balance' => 0,
                'discount_balance' => 0,
                'total_balance' => 0
            ]);
        }

        $finalDefaulter = ($defaulterAlreadyExist) ? $firstDefaulterFound : $newDefaulter;
        
        $incomingThings = $request->input('things');
        for ($i=0; $i < sizeof($incomingThings); $i++) { 
            $firstThingFound = Thing::where('name', '=', $incomingThings[$i]['thing_name'])->first();
            $thingAlreadyExist = $firstThingFound !== null;
            $newThing = null;

            if( !$thingAlreadyExist ){
                $newThing = Thing::create([
                    "name" => $incomingThings[$i]['thing_name'],
                    "suggested_unit_price" => $incomingThings[$i]['unit_price'],
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
                "retired_at" => $incomingThings[$i]['retired_at'],
                "filed_at" => $incomingThings[$i]['filed_at'],
                "was_paid" => $incomingThings[$i]['was_paid'],
            ]);
        }

        $finalDefaulter = UpdateBalancesOfDefaulter($finalDefaulter->id);
        $finalDefaulter->debts->sortByDesc('pivot.retired_at');

        return response()->json([
            'message' => "Se registró la deuda exitosamente a nombre de: $finalDefaulter->name",
            'defaulter' => $finalDefaulter
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Debt $debt)
    {
        return response()->json([
            'message' => "Informacion de la deuda nro. $debt->id",
            'debt' => $debt
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDebtRequest $request, Debt $debt)
    {
        $beforeUpdateDefaulterId = null;

        // if he wants to change the FK for a new or existing one...
        if( $request->hasAny(['new_defaulter_name', 'new_thing_name']) ){ 
            $trimmedDefaulterName = $request->string('new_defaulter_name')->trim();
            $trimmedThingName = $request->string('new_thing_name')->trim();

            $beforeUpdateDefaulterId = $debt->defaulter_id;
            $defaulterAlreadyExist = Defaulter::where('name', '=', $trimmedDefaulterName)->first();
            $thingAlreadyExist = Thing::where('name', '=', $trimmedThingName)->first();
            
            $debt->defaulter_id = ($defaulterAlreadyExist) ? $defaulterAlreadyExist->id : $debt->defaulter_id;
            $debt->thing_id = ($thingAlreadyExist) ? $thingAlreadyExist->id : $debt->thing_id;

            if( !$defaulterAlreadyExist ){
                $newDefaulter = Defaulter::create([
                    'name' => $trimmedDefaulterName,
                    'debt_balance' => 0,
                    'discount_balance' => 0,
                    'total_balance' => 0
                ]);

                $debt->defaulter_id = $newDefaulter->id;
            }

            if( !$thingAlreadyExist ){
                $newThing = Thing::create([
                    'name' => $trimmedThingName,
                    'suggested_unit_price' => $request->integer('unit_price'),
                ]);

                $debt->thing_id = $newThing->id;
            }

            $debt->save();
        }

        $debt->refresh();
        $debt->update($request->except(['defaulter_id','thing_id']));
        if( isset($beforeUpdateDefaulterId) ) { UpdateBalancesOfDefaulter($beforeUpdateDefaulterId); }
        
        if( !$debt->wasChanged() ){
            return response()->json([
                'message' => "No se pudo actualizar la deuda, no se justifican los cambios a realizar",
            ], 400);
        }

        if ($request->hasAny(['unit_price', 'quantity', 'was_paid'])) {
            $updatedDefaulter = UpdateBalancesOfDefaulter($debt->defaulter_id);
        }

        return response()->json([
            'message' => "La deuda $debt->id fue actualizada con exito.",
            'updatedDebt' => $debt,
            'defaulter' => $updatedDefaulter,
        ]);
    }
 
    /**
     * hard delete
     */
    /* public function destroy(Debt $debt)
    {
        $defaulter = Defaulter::find($debt->defaulter_id);
        $thingName = Thing::find($debt->thing_id)->name;
        $debtTotalPrice = ($debt->unit_price * $debt->quantity);

        $debtWasDeleted = $debt->delete();

        $updatedDefaulter = UpdateBalancesOfDefaulter($defaulter->id);
 
        if($debtWasDeleted) {
            return response()->json([
                'message' => "La deuda de '$defaulter->name' ($thingName $$debtTotalPrice) fue borrado exitosamente.",
                'oldDefaulter' => $defaulter,
                'newDefaulter' => $updatedDefaulter
            ]);
        } else {
            return response()->json([
                'message' => "No pudo ser borrada la deuda $debt->name"
            ]);
        }
    } */
}
