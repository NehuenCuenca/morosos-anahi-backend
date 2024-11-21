<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalancesByMonthYear implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
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
        $debtBalance = intval( $this->defaulter->debts()
                            ->whereMonth('defaulter_thing.retired_at', $this->month)
                            ->whereYear('defaulter_thing.retired_at', $this->year)
                            ->where('defaulter_thing.unit_price', '>', 0)
                            ->sum('defaulter_thing.unit_price') );
                            
        $discountBalance = intval( $this->defaulter->debts()
                            ->whereMonth('defaulter_thing.retired_at', $this->month)
                            ->whereYear('defaulter_thing.retired_at', $this->year)
                            ->where('defaulter_thing.unit_price', '<', 0)
                            ->sum('defaulter_thing.unit_price') );

        return new Collection([
            [$debtBalance, $discountBalance, ($debtBalance + $discountBalance)],
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