<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Defaulter;
use Carbon\Carbon;

class ItemController extends Controller
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
    public function store(StoreItemRequest $request)
    {
        $defaulterExist = null !== Defaulter::find($request->input('defaulter_id'));

        if ($request->missing('defaulter_id') || !$defaulterExist) {
            return response()->json([
                'message' => "No se pudo registrar el articulo ya que el ID del moroso no se encontro en la solicitud o no está registrado en la base de datos.",
            ], 400);
        }

        $newItem = Item::create([
            'defaulter_id' => $request->input('defaulter_id'),
            'name' => $request->input('name'),
            'unit_price' => $request->input('unit_price'),
            'quantity' => $request->input('quantity', 1),
            'retirement_date' => $request->input('retirement_date', Carbon::now()->format('Y-m-d') ),
            'was_paid' => $request->input('was_paid', 0),
        ]);

        return response()->json([
            'message' => "Se registró un nuevo articulo $newItem->name",
            'newItem' => $newItem
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return response()->json([
            'message' => "Informacion de el articulo nro. $item->id",
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        if( empty($request->all()) ){
            return response()->json([
                "message" => "Debes enviar al menos un campo para actualizar el articulo"
            ], 400);
        }

        $defaulterExist = null !== Defaulter::find($request->input('defaulter_id'));
        if ($request->has('defaulter_id') && !$defaulterExist) {
            return response()->json([
                'message' => "No se pudo registrar el articulo ya que el ID del moroso no se encontro en la solicitud o no está registrado en la base de datos.",
            ], 400);
        }

        $item->update($request->all());
        
        if( !$item->wasChanged() ){
            return response()->json([
                'message' => "No se pudo actualizar el articulo, no se justifican los cambios a realizar",
            ], 400);
        }

        return response()->json([
            'message' => "Se actualizo el articulo $item->id",
            'updatedItem' => $item
        ]);
    }

    /**
     * Tacha la deuda o se adeuda devuelta.
     */
    public function destroy(Item $item)
    {
        $itemGotPaidOrNot = ($item->was_paid === 1) ? 0 : 1; // 0 === adeudado | 1 === tachado
        $response = ($itemGotPaidOrNot === 1) ? 'tachado' : 'adeudado';

        $item->was_paid = $itemGotPaidOrNot;
        $item->save();

        return response()->json([
            'message' => "El item $item->name fue $response satisfactoriamente."
        ]);
    }
}
