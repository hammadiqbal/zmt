<?php

use App\Models\Users;

if (!function_exists('getUserNameById')) {
    /**
     * Get the name of a user by their ID.
     *
     * @param int $userId
     * @return string
     */
    function getUserNameById($userId)
    {
        $name = Users::where('id', $userId)
            ->pluck('name')
            ->first();

        return $name ? $name : 'Unknown User';
    }
}
