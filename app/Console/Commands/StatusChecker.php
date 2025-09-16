<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StatusChecker extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:checker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status based on effective_timestamp';

    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
    }


    // public function handle()
    // {
    //     $tables = DB::select('SHOW TABLES');
    //     $now = Carbon::now('Asia/Karachi')->timestamp; // current timestamp
    //     foreach ($tables as $table) {
    //         $tableName = array_values((array)$table)[0];

    //         if(Schema::hasColumn($tableName, 'effective_timestamp') && Schema::hasColumn($tableName, 'status')){
    //             DB::table($tableName)
    //                 ->where('effective_timestamp', '>', $now)
    //                 ->update(['status' => 0]);

    //             DB::table($tableName)
    //                 ->where('effective_timestamp', '<=', $now)
    //                 ->update(['status' => 1]);
    //         }
    //     }
    //     $this->info('Status updated successfully.');
    // }

    // public function handle()
    // {
    //     try {
    //         $tables = DB::select('SHOW TABLES');
    //         $now = Carbon::now('Asia/Karachi')->timestamp; // current timestamp

    //         foreach ($tables as $table) {
    //             $tableName = array_values((array)$table)[0];

    //             // Skip the emp_cc table
    //             if($tableName === 'emp_cc') {
    //                 continue;
    //             }

    //             if(Schema::hasColumn($tableName, 'effective_timestamp') && Schema::hasColumn($tableName, 'status')){
    //                 DB::table($tableName)
    //                     ->where('effective_timestamp', '>', $now)
    //                     ->update(['status' => 0]);

    //                 DB::table($tableName)
    //                     ->where('effective_timestamp', '<=', $now)
    //                     ->update(['status' => 1]);
    //             }
    //         }
    //         $this->info('Status updated successfully.');
    //     } catch (\Exception $e) {
    //         $this->error('An error occurred: ' . $e->getMessage());
    //         \Log::error('Status Checker Error: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    //     }
    // }

    public function handle()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $now = Carbon::now('Asia/Karachi')->timestamp; // current timestamp

            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];

                // if($tableName === 'emp_cc') {
                //     continue;
                // }

                if(Schema::hasColumn($tableName, 'effective_timestamp') && Schema::hasColumn($tableName, 'status')){

                    if($tableName === 'employee' && Schema::hasColumn($tableName, 'leaving_date')){
                        // Only update entries where effective_timestamp is not 0 (manually updated entries)
                        DB::table($tableName)
                            ->where('leaving_date', 0)
                            ->where('effective_timestamp', '>', $now)
                            ->where('effective_timestamp', '!=', 0) // Skip manually updated entries
                            ->update(['status' => 0]);

                        DB::table($tableName)
                            ->where('leaving_date', 0)
                            ->where('effective_timestamp', '<=', $now)
                            ->where('effective_timestamp', '!=', 0) // Skip manually updated entries
                            ->update(['status' => 1]);

                    } else {
                        // Only update entries where effective_timestamp is not 0 (manually updated entries)
                        DB::table($tableName)
                            ->where('effective_timestamp', '>', $now)
                            ->where('effective_timestamp', '!=', 0) // Skip manually updated entries
                            ->update(['status' => 0]);

                        DB::table($tableName)
                            ->where('effective_timestamp', '<=', $now)
                            ->where('effective_timestamp', '!=', 0) // Skip manually updated entries
                            ->update(['status' => 1]);
                    }
                }
            }
            $this->info('Status updated successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            \Log::error('Status Checker Error: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
