<?php

namespace App\Helpers;

class FormatHelper
{
    public static function formatMoney($amount)
    {
        return number_format($amount, 2);
    }
}
