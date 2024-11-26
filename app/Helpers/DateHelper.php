<?php

namespace App\Helpers;

use Carbon\Carbon;

if(!function_exists('GetDateTimeFormated')) {
    function GetDateTimeFormated($inputDate){ 
        return (isset($inputDate))   
                ? Carbon::parse($inputDate)->toDateTimeString('second') //returned date with format 'Y-m-d H:m:s'
                : null;
    }
};

if(!function_exists('CustomFormatByYear_Month_MonthName')) {
    function CustomFormatByYear_Month_MonthName($debt){
        $retiredAt = Carbon::parse($debt->pivot->retired_at)->locale('es');
        return "{$retiredAt->year}_{$retiredAt->month}_{$retiredAt->monthName}";
    }
};
