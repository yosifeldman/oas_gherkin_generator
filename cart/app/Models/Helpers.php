<?php


namespace App\Models;


class Helpers
{
    /**
     * @param     $a
     * @param     $b
     * @param int $precision
     *
     * @return int
     */
    public static function float_cmp($a, $b, $precision = 5): int
    {
        $a = round($a, $precision);
        $b = round($b, $precision);
        $epsilon = 1 / (10 * $precision);
        $abs = abs($a-$b);
        if($abs < $epsilon) {
            return 0;
        }
        if ($abs >= $epsilon) {
            return 1;
        }
        return -1;
    }
}