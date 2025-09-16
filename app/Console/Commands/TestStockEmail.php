<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMonitoring;
use App\Models\InventoryBalance;
use App\Mail\StockAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TestStockEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:test-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test stock monitoring email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Stock Monitoring Email System...');
        
        // Test 1: Check stock monitoring record
        $monitoring = StockMonitoring::first();
        if (!$monitoring) {
            $this->error('No stock monitoring records found!');
            return;
        }
        
        $this->info("Testing with monitoring ID: {$monitoring->id}");
        $this->info("Primary email field: " . ($monitoring->primary_email ?? $monitoring->primary ?? 'not_set'));
        $this->info("Secondary email field: " . ($monitoring->secondary_email ?? $monitoring->secondary ?? 'not_set'));
        
        // Test 2: Check inventory balance
        $balance = InventoryBalance::where('org_id', $monitoring->org_id)
            ->where('site_id', $monitoring->site_id)
            ->where('generic_id', $monitoring->item_generic_id)
            ->where('brand_id', $monitoring->item_brand_id)
            ->where('location_id', $monitoring->service_location_id)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$balance) {
            $this->error('No inventory balance found for this monitoring record!');
            return;
        }
        
        $this->info("Current balance: {$balance->location_balance}");
        $this->info("Min stock threshold: {$monitoring->min_stock}");
        
        // Test 3: Check if alert should trigger
        if ($monitoring->min_stock > $balance->location_balance) {
            $this->info("ALERT CONDITION MET: Min stock ({$monitoring->min_stock}) > Current balance ({$balance->location_balance})");
            
            // Test 4: Get related data
            $itemGeneric = DB::table('item_generic')->where('id', $monitoring->item_generic_id)->value('name') ?? 'Unknown';
            $itemBrand = DB::table('item_brand')->where('id', $monitoring->item_brand_id)->value('name') ?? 'Unknown';
            $siteName = DB::table('sites')->where('id', $monitoring->site_id)->value('name') ?? 'Unknown';
            $orgName = DB::table('organizations')->where('id', $monitoring->org_id)->value('name') ?? 'Unknown';
            $locationName = DB::table('inventory_location')->where('id', $monitoring->service_location_id)->value('name') ?? 'Unknown';
            
            $this->info("Item Generic: {$itemGeneric}");
            $this->info("Item Brand: {$itemBrand}");
            $this->info("Site: {$siteName}");
            $this->info("Organization: {$orgName}");
            $this->info("Location: {$locationName}");
            
            // Test 5: Try to send email
            $primaryEmail = $monitoring->primary_email ?? $monitoring->primary ?? null;
            if ($primaryEmail) {
                $this->info("Attempting to send test email to: {$primaryEmail}");
                
                try {
                    Mail::to($primaryEmail)->send(new StockAlertMail(
                        'min_stock',
                        $itemGeneric,
                        $itemBrand,
                        $siteName,
                        $orgName,
                        $locationName,
                        $balance->location_balance,
                        $monitoring->min_stock,
                        null
                    ));
                    
                    $this->info("SUCCESS: Test email sent to {$primaryEmail}");
                } catch (\Exception $e) {
                    $this->error("FAILED to send email: " . $e->getMessage());
                    $this->error("Error details: " . $e->getTraceAsString());
                }
            } else {
                $this->warn("No primary email found to send test email");
            }
            
        } else {
            $this->info("No alert condition met. Min stock ({$monitoring->min_stock}) <= Current balance ({$balance->location_balance})");
        }
        
        $this->info('Test completed!');
    }
}
