<?php

namespace App\Http\Controllers;

use App\Models\Defaulter;
use App\Http\Requests\StoreDefaulterRequest;
use App\Http\Requests\UpdateDefaulterRequest;
use App\Models\Item;
use Illuminate\Http\Request;

use function App\Helpers\PricesAcumuluted;

class DefaulterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $defaultersLength = sizeof( Defaulter::all() );
        $paginateBy = $request->integer('paginatedBy', $defaultersLength) ?? $defaultersLength;
        $orderByLastestRecent = $request->boolean('orderByLastestRecent', 0) ?? 0;
        $orderByAlphabet = $request->boolean('orderByAlphabet', 0) ?? 0;
        $orderByLargestDebtor = $request->boolean('orderByLargestDebtor', 0) ?? 0;

        $defaulters = Defaulter::paginate($paginateBy);
        
        if( $orderByLastestRecent ) {
            $defaulters = Defaulter::orderBy('created_at', 'DESC')->paginate($paginateBy);
        }

        if( $orderByAlphabet ) {
            $defaulters = Defaulter::orderBy('name', 'ASC')->paginate($paginateBy);
        }

        if( $orderByLargestDebtor ) {
            $defaulters = Defaulter::orderBy('total_balance', 'DESC')->paginate($paginateBy);
        }

        return response()->json([
            'message' => "Lista de todos los morosos",
            "defaulters" => $defaulters
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDefaulterRequest $request)
    {
        // dd( $request );
        
        $debtPrices = PricesAcumuluted($request->input('items'), true);
        $discountPrices = PricesAcumuluted($request->input('items'), false);

        $newDefaulter = Defaulter::create([
            'name' => $request->input('name'),
            'debt_balance' => $debtPrices ?? 0,
            'discount_balance' => $discountPrices ?? 0,
            'total_balance' => ($debtPrices + $discountPrices) ?? 0
        ]);

        for ($i=0; $i < sizeof($request->input('items')); $i++) { 
            Item::create([
                "defaulter_id" => $newDefaulter->id,
                "unit_price" => $request->items[$i]['unit_price'],
                "quantity" => $request->items[$i]['quantity'],
                "name" => $request->items[$i]['name'],
                "retirement_date" => $request->items[$i]['retirement_date'],
                "was_paid" => $request->items[$i]['was_paid'],
            ]);
        }

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
        $items = $defaulter->items->sortByDesc('retirement_date')->makeHidden('defaulter_id');

        if( !isset($defaulter) ) {
            return response()->json([
                'message' => "El moroso nro $id no fue encontrado."
            ], 400);
        } else {
            return response()->json([
                'message' => "Lista de items adeudados por el moroso nro $id.",
                'items' => $items->values()->all()
            ]);
        }
    }
    
}
