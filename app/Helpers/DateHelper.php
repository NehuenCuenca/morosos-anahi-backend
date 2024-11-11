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
