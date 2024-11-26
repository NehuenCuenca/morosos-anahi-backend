<?php
namespace App\Exports;

use App\Exports\Sheets\BalancesByMonthYear;
use App\Exports\Sheets\DebtsByMonthYear;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DefaulterDebtsOfMonthExport implements WithMultipleSheets {
    use Exportable;

    private $defaulter;
    private $month;
    private $year;

    public function __construct($defaulter, int $month, int $year) 
    {
        $this->defaulter = $defaulter;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[0] = new DebtsByMonthYear($this->defaulter, $this->month, $this->year);
        $sheets[1] = new BalancesByMonthYear($this->defaulter, $this->month, $this->year);

        return $sheets;
    }
}
