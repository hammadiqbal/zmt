<?php 
if (!function_exists('PermissionDenied')) {

    function PermissionDenied($colName) {
        $rights = session()->get('rights');
        
        if (isset($rights->$colName)) {
            $colValue = explode(',', $rights->$colName);
            $allZero = true;
            foreach ($colValue as $digit) {
                if ($digit !== '0') {
                    $allZero = false;
                    break;
                }
            }
            if ($allZero) {
                return true; 
            }
        } 
        return false;
    }

}

?>