<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DebtsByMonthYear implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
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
            ['NRO DEUDA', 'PRODUCTO','CANTIDAD','PRECIO UNITARIO', 'PRECIO FINAL'],
        ];
    }
    
    public function collection()
    {
        return $this->defaulter->debts()
                        ->whereMonth('defaulter_thing.retired_at', $this->month)
                        ->whereYear('defaulter_thing.retired_at', $this->year)
                        ->select('defaulter_thing.id', 'things.name', 'defaulter_thing.quantity', 'defaulter_thing.unit_price', DB::raw('(defaulter_thing.unit_price * defaulter_thing.quantity) as final_price'))
                        ->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Deudas';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            "A" => ['font' => ['size' => 13],],
            "B" => ['font' => ['size' => 13],],
            "C" => ['font' => ['size' => 13],],
            "D" => ['font' => ['size' => 13],],
            "E" => ['font' => ['size' => 13],],
            "F" => ['font' => ['size' => 13],],
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'fffc91'],
                    'endColor' => ['argb' => 'fffc91'],
                ],
            ],
        ];
    }
}