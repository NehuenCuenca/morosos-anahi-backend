<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Models\Thing;
use App\Models\Debt;
use App\Models\Defaulter;
use Exception;
use Illuminate\Support\Facades\DB;

use function App\Helpers\UpdateBalancesOfDefaulter;
use function App\Helpers\GetDateTimeFormated;

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

        if (!isset($firstDefaulterFound)) {
            $firstDefaulterFound = Defaulter::where('name', '=', $trimmedDefaulterName)->first();
        }

        $defaulterAlreadyExist = $firstDefaulterFound !== null;
        $newDefaulter = null;

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
                'defaulter' => $finalDefaulter
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                "message" => "La deuda no pudo ser creada, ocurrio un error inesperado. Ejecutando rollback de la transaccion.",
                "throwable" => $th
            ], 400);
        } 
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
        $beforeUpdateDefaulter = Defaulter::find($debt->defaulter_id);
        $beforeUpdateThing = Thing::find($debt->thing_id);

        try {
            DB::beginTransaction();

            // if he wants to change the FK for a existing one or just edit the name of the same defaulter...
            if ($request->hasAny(['new_defaulter_name', 'new_thing_name'])) {
                $trimmedDefaulterName = $request->string('new_defaulter_name')->trim();
                $trimmedThingName = $request->string('new_thing_name')->trim();
                
                $defaulterAlreadyExist = Defaulter::where('name', '=', $trimmedDefaulterName)->first();
                $thingAlreadyExist = Thing::where('name', '=', $trimmedThingName)->first();

                if( isset($defaulterAlreadyExist) ){ $debt->defaulter_id = $defaulterAlreadyExist->id; }
                if( isset($thingAlreadyExist) ){ $debt->thing_id = $thingAlreadyExist->id; }
                
                if( !isset($defaulterAlreadyExist) && $request->filled('new_defaulter_name') ) { 
                    $beforeUpdateDefaulter->update(['name' => $trimmedDefaulterName]);
                }

                if( !isset($thingAlreadyExist) && $request->filled('new_thing_name') ){
                    $newThing = Thing::create([
                        'name' => $trimmedThingName,
                        'suggested_unit_price' => $request->integer('unit_price'),
                        'is_deleted' => false
                    ]);

                    $debt->thing_id = $newThing->id;
                }

                $debt->save();
            }

            // $getDateFormated = fn($inputDate) => Carbon::parse($inputDate)->toDateTimeLocalString('second');
            $debt->update([
                ...$request->only(['unit_price', 'quantity', 'was_paid']), 
                'retired_at' => ($request->has('retired_at')) ? GetDateTimeFormated($request->retired_at) : $debt->retired_at,
                'filed_at' => GetDateTimeFormated($request->filed_at)
            ]);
            Thing::find($debt->thing_id)->update(['suggested_unit_price' => $request->integer('unit_price')]);

            if( !$debt->wasChanged() && 
                (!$beforeUpdateDefaulter->wasChanged() && !$beforeUpdateThing->wasChanged())
            ) {
                return response()->json([
                    'message' => "NO se actualizó la deuda, debido a que no se justifican los cambios a realizar.",
                    'debt' => $debt->wasChanged(),
                    'beforeUpdateDefaulter' => $beforeUpdateDefaulter->wasChanged(),
                    'beforeUpdateThing' => $beforeUpdateThing->wasChanged()
                ], 400);
            }

            if( isset($defaulterAlreadyExist) ){
                UpdateBalancesOfDefaulter($beforeUpdateDefaulter->id);
            }

            if( $request->hasAny(['unit_price', 'quantity', 'was_paid']) ){
                UpdateBalancesOfDefaulter($debt->defaulter_id);
            }

            $updatedDefaulter = Defaulter::find($debt->defaulter_id);
            $updatedDefaulter->debts;

            DB::commit();

            return response()->json([
                'message' => "La deuda $debt->id fue actualizada con exito.",
                'updatedDebt' => $debt,
                'defaulter' => $updatedDefaulter,
            ]);

        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                "message" => "La deuda no pudo ser editada, ocurrio un error inesperado. Ejecutando rollback de la transaccion.",
                "throwable" => $th
            ], 400);
        }

        
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
