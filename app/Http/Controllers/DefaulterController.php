<?php

namespace App\Http\Controllers;

use App\Models\Defaulter;
use App\Http\Requests\StoreDefaulterRequest;
use App\Http\Requests\UpdateDefaulterRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\DefaulterDebtsOfMonthExport;
use App\Http\Requests\GetExcelDebtsByMonthYearRequest;
use Maatwebsite\Excel\Facades\Excel;

class DefaulterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $defaultersLength = sizeof( Defaulter::all() );
        $paginateBy = $request->integer('paginatedBy', $defaultersLength) ?? $defaultersLength;
        $orderByAlphabet = $request->boolean('orderByAlphabet', 0);
        $orderByLargestDebtor = $request->boolean('orderByLargestDebtor', 0);
        $orderByOldestCreated = $request->boolean('orderByOldestCreated', 0);
        // $eliminated = $request->boolean('eliminated', 0);

        $defaulters = Defaulter::orderBy('created_at', 'DESC')->paginate($paginateBy);

        if( $orderByAlphabet ) {
            $defaulters = Defaulter::orderBy('name', 'ASC')->paginate($paginateBy);
        }

        if( $orderByLargestDebtor ) {
            $defaulters = Defaulter::orderBy('total_balance', 'DESC')->paginate($paginateBy);
        }
        
        if( $orderByOldestCreated ) {
            $defaulters = Defaulter::orderBy('created_at', 'ASC')->paginate($paginateBy);
        }

        $msgInResponse = ($request->has('paginatedBy')) ? "Lista de morosos paginada de a $paginateBy" : "Lista de todos los morosos";

        return response()->json([
            'message' => $msgInResponse,
            "defaulters" => $defaulters
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDefaulterRequest $request)
    {
        $newDefaulter = Defaulter::create([
            'name' => $request->string('name')->trim(),
            'debt_balance' => 0,
            'discount_balance' => 0,
            'total_balance' => 0
        ]);

        $newDefaulter->debts;

        return response()->json([
            'message' => "Se registro un nuevo moroso $newDefaulter->name",
            'defaulter' => $newDefaulter,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Defaulter $defaulter)
    {
        return response()->json([
            'message' => "Informacion de moroso $defaulter->name ($defaulter->id)",
            'defaulter' => $defaulter
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDefaulterRequest $request, Defaulter $defaulter)
    {
        $defaulter->update([
            'name' => $request->string('name')->trim(),
            'is_deleted' => $request->boolean('is_deleted'),
        ]);
        
        if( !$defaulter->wasChanged() ){
            return response()->json([
                'message' => "No se pudo actualizar el moroso, no se justifican los cambios a realizar",
            ], 400);
        }

        $defaulter->debts->sortByDesc('pivot.retired_at');

        return response()->json([
            'message' => "Se actualizo el moroso $defaulter->name ($defaulter->id)",
            'defaulter' => $defaulter
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    /* public function destroy(Defaulter $defaulter)
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
    } */
    
    public function get_debts(Defaulter $defaulter)
    {            
        return response()->json([
            'message' => "Lista de deudas del moroso $defaulter->name ($defaulter->id).",
            'debts' => $defaulter->debts_by_month_year
        ]);
    }

    public function get_excel_debts_by_month_year(GetExcelDebtsByMonthYearRequest $request, Defaulter $defaulter)
    {
        $currentDatetime = Carbon::now();
        $month = $request->integer('month', $currentDatetime->month);
        $year = $request->integer('year', $currentDatetime->year); 

        $monthYearFormated = Carbon::create($year, $month, null, null)->format('m-Y');

        $formatedDefaulterName = str_replace(' ', '-', $defaulter->name);
        $typeFile = ".xlsx";
        $customFileName = "{$formatedDefaulterName}_{$monthYearFormated}_debts{$typeFile}";

        return Excel::download(new DefaulterDebtsOfMonthExport($defaulter, $month, $year), $customFileName);
    }
}
