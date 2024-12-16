<?php

namespace App\Exports\Sheets;

use App\Models\Thing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalancesByMonthYear implements FromCollection, WithStrictNullComparison, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    private $defaulter;
    private $month;
    private $year;

    public function __construct($defaulter, int $month, int $year) 
    {
        $this->defaulter = $defaulter;
        $this->month = $month;
        $this->year = $year;
    }

    public function headings(): array
    {
        return [
            ['DEUDA', 'A FAVOR', 'SALDO'],
        ];
    }
    
    public function collection()
    {
        $debtsByMonthYearQuery = $this->defaulter->debts()
                            ->whereMonth('defaulter_thing.retired_at', $this->month)
                            ->whereYear('defaulter_thing.retired_at', $this->year)
                            ->get();

        $initialBalancesAcumulator = [
            'againstOfDefaulter' => 0,
            'inFavorOfDefaulter' => 0,
            'total' => 0
        ];

        $balances = $debtsByMonthYearQuery
                        ->reverse()
                        ->reduce(function (array $acum, Thing $thing) use ($initialBalancesAcumulator, $debtsByMonthYearQuery) {
                            if($thing->pivot->was_paid) return $acum;
                            
                            $totalOfDebt = $thing->pivot->unit_price * $thing->pivot->quantity;
                            $againstBalances = ($totalOfDebt > 0) 
                                                    ? $totalOfDebt+$acum['againstOfDefaulter']
                                                    : $acum['againstOfDefaulter'];
                            $inFavorBalances = ($totalOfDebt < 0) 
                                                    ? $totalOfDebt+$acum['inFavorOfDefaulter']
                                                    : $acum['inFavorOfDefaulter'];
                            
                            if( $thing->name === 'PASADA EN LIMPIO' ){
                                if( $totalOfDebt != 0 ){
                                    return [
                                        'againstOfDefaulter' => ($totalOfDebt > 0) ? $totalOfDebt : 0,
                                        'inFavorOfDefaulter' => ($totalOfDebt < 0) ? $totalOfDebt : 0,
                                        'total' => $totalOfDebt
                                    ];
                                } else {
                                    return $initialBalancesAcumulator;
                                }
                                
                            } else {
                                return [
                                    'againstOfDefaulter' => $againstBalances, 
                                    'inFavorOfDefaulter' => $inFavorBalances,
                                    'total' => ($againstBalances + $inFavorBalances)
                                ];
                            }
                        }, $initialBalancesAcumulator);                           
                            
        return new Collection([
            [ $balances['againstOfDefaulter'], $balances['inFavorOfDefaulter'], $balances['total'] ],
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Balances';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            "A" => ['font' => ['size' => 13],],
            "B" => ['font' => ['size' => 13],],
            "C" => ['font' => ['size' => 13],],
            
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ],
                ]
            ],

            'A1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'ff9191'],
                    'endColor' => ['argb' => 'ff9191'],
                ]
            ],

            'B1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'adff91'],
                    'endColor' => ['argb' => 'adff91'],
                ]
            ],

            'C1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'fcc467'],
                    'endColor' => ['argb' => 'fcc467'],
                ]
            ],
        ];
    }
}