<?php

namespace App\Http\Controllers;

use App\Models\Defaulter;
use App\Http\Requests\StoreDefaulterRequest;
use App\Http\Requests\UpdateDefaulterRequest;

use function App\Helpers\PricesAcumuluted;

class DefaulterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $defaulters = Defaulter::all();

        return response()->json([
            'message' => "Lista de todos los morosos",
            'defaulters' => $defaulters
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDefaulterRequest $request)
    {
        // dd($request);
        
        $debtPrices = PricesAcumuluted($request->input('items'), true);
        $discountPrices = PricesAcumuluted($request->input('items'), false);

        $newDefaulter = Defaulter::create([
            'name' => $request->input('name'),
            'debt_balance' => $debtPrices ?? 0,
            'discount_balance' => $discountPrices ?? 0,
            'total_balance' => ($debtPrices + $discountPrices) ?? 0
        ]);

        return response()->json([
            'message' => "Se registro un nuevo moroso $newDefaulter->name",
            'newDefaulter' => $newDefaulter
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Defaulter $defaulter)
    {
        return response()->json([
            'message' => "Informacion de moroso nro. $defaulter->id",
            'defaulter' => $defaulter
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDefaulterRequest $request, Defaulter $defaulter)
    {
        if( empty($request->all()) ){
            return response()->json([
                "message" => "Debes enviar al menos un campo para actualizar el moroso"
            ], 400);
        }

        $defaulter->update($request->all());
        
        if( !$defaulter->wasChanged() ){
            return response()->json([
                'message' => "No se pudo actualizar el moroso, no se justifican los cambios a realizar",
            ], 400);
        }

        return response()->json([
            'message' => "Se actualizo el moroso $defaulter->id",
            'updatedDefaulter' => $defaulter
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Defaulter $defaulter)
    {
        $defaulterWasDeleted = $defaulter->delete();
        
        if($defaulterWasDeleted) {
            return response()->json([
                'message' => "El moroso '$defaulter->name' fue borrado exitosamente"
            ]);
        } else {
            return response()->json([
                'message' => "No pudo ser borrado el moroso $defaulter->name"
            ]);
        }
    }

    public function get_items(int $id)
    {
        $defaulter = Defaulter::where('id', $id)->first();

        if( !isset($defaulter) ) {
            return response()->json([
                'message' => "El moroso nro $id no fue encontrado."
            ], 400);
        } else {
            return response()->json([
                'message' => "Lista de items adeudados por El moroso nro $id.",
                'items' => $defaulter->items->makeHidden('defaulter_id')
            ]);
        }
    }
}
