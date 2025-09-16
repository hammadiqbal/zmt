<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryManagement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class BrandExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brand:brand-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Status according to the Expiry Date of Brand';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = now();

        $expiredItems = InventoryManagement::where('expiry_date', '<', $currentDate->timestamp)
                                           ->get();
    
        foreach ($expiredItems as $item) {
            $item->update(['status' => 0]);
        }
    }
}
