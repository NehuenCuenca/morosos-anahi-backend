<?php

namespace App\Http\Controllers;

use App\Models\Thing;
use App\Http\Requests\StoreThingRequest;
use App\Http\Requests\UpdateThingRequest;

class ThingController extends Controller
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
    public function store(StoreThingRequest $request) 
    {
        $trimmedName = $request->string('name')->trim();
        $firstThingFound = Thing::where('name', '=', $trimmedName)->first();
        $thingAlreadyExist = $firstThingFound !== null;

        if($thingAlreadyExist){
            return response()->json([
                'message' => "El articulo $firstThingFound->name ($firstThingFound->id) NO FUE REGISTRADO, debido a que ya existe en la base de datos.",
                'alreadyExist' => $firstThingFound
            ], 400);
        }
        
        $newThing = Thing::create([
            'name' => $trimmedName,
            'suggested_unit_price' => $request->integer('suggested_unit_price'),
        ]);

        return response()->json([
            'message' => "Se registró un nuevo articulo $newThing->name ($newThing->id)",
            'newThing' => $newThing,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Thing $thing)
    {
        return response()->json([
            'message' => "Informacion de el articulo $thing->name ($thing->id).",
            'thing' => $thing
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThingRequest $request, Thing $thing)
    {
        $thing->update($request->all());
        
        if( !$thing->wasChanged() ){
            return response()->json([
                'message' => "No se pudo actualizar el articulo, no se justifican los cambios a realizar.",
            ], 400);
        }

        return response()->json([
            'message' => "Se actualizo el articulo $thing->name ($thing->id).",
            'thing' => $thing
        ]);
    }
 
    /**
     * Remove the specified resource from storage.
     */
    /* public function destroy(Thing $thing)
    {
        $thingWasDeleted = $thing->delete();
        
        if($thingWasDeleted) {
            return response()->json([
                'message' => "El articulo '$thing->name' fue borrado exitosamente"
            ]);
        } else {
            return response()->json([
                'message' => "No pudo ser borrado el articulo $thing->name"
            ]);
        }
    } */
}
