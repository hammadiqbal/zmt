<?php

use App\Models\Logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('createLog')) {
    /**
     * Create a log entry
     *
     * @param string $module Module name (e.g., 'user_setup', 'role', 'inventory')
     * @param string $event Event type (e.g., 'insert', 'update', 'delete', 'status_change')
     * @param mixed $summary Summary data (will be JSON encoded)
     * @param int|null $recordId ID of the record being modified
     * @param mixed|null $previousData Old data (for updates/deletes)
     * @param mixed|null $newData New data (for inserts/updates)
     * @param mixed|null $changes Description of what changed (will be JSON encoded)
     * @param int|null $userId User ID (defaults to authenticated user)
     * @return int Log ID
     */
    function createLog(
        string $module,
        string $event,
        $summary,
        ?int $recordId = null,
        $previousData = null,
        $newData = null,
        ?int $userId = null
    ) {
        try {
            $userId = $userId ?? (Auth::check() ? Auth::id() : 0);
            
            $log = Logs::create([
                'module' => $module,
                'event' => $event,
                'user_id' => $userId,
                'record_id' => $recordId,
                'summary' => json_encode($summary),
                'previous_data' => $previousData ? json_encode($previousData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'timestamp' => Carbon::now('Asia/Karachi')->timestamp,
            ]);

            return $log->id;
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Failed to create log entry: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getLog')) {
    /**
     * Get a log entry by ID
     *
     * @param int $logId
     * @return object|null
     */
    function getLog(int $logId)
    {
        return Logs::find($logId);
    }
}

if (!function_exists('getRecordLogs')) {
    /**
     * Get all logs for a specific record
     *
     * @param int $recordId
     * @param string $module Optional module filter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getRecordLogs(int $recordId, ?string $module = null)
    {
        $query = Logs::where('record_id', $recordId);
        
        if ($module) {
            $query->where('module', $module);
        }
        
        return $query->orderBy('timestamp', 'desc')->get();
    }
}

