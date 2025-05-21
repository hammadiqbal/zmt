<?php 
if (!function_exists('numberToWordOrdinal')) {
    function numberToWordOrdinal($number) {
        $words = [
            1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Fifth',
            6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth', 9 => 'Ninth', 10 => 'Tenth'
        ];
    
        if (array_key_exists($number, $words)) {
            return $words[$number];
        }
        // For numbers greater than 20
        $tens = floor($number / 10) * 10;
        $units = $number % 10;
    
        if ($units === 0) {
            return numberToWordOrdinal($tens);
        }
    
        return numberToWordOrdinal($tens) . '-' . numberToWordOrdinal($units);
    }
}

?>