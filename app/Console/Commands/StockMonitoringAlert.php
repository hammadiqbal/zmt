<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMonitoring;
use App\Models\InventoryBalance;
use App\Mail\StockAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockMonitoringAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send stock monitoring alerts based on inventory levels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting stock monitoring alert process...');
        // Get all active stock monitoring records
        $stockMonitoringRecords = StockMonitoring::where('status', 1)->get();
        $this->info("Found {$stockMonitoringRecords->count()} active stock monitoring records");
        
        $alertCount = 0;
        
        foreach ($stockMonitoringRecords as $monitoring) {
            try {
                $this->info("Processing monitoring ID: {$monitoring->id}");
                // Get current inventory balance for this monitoring record
                $currentBalance = $this->getCurrentInventoryBalance($monitoring);
                
                if ($currentBalance === null) {
                    $this->warn("No inventory balance found for monitoring ID: {$monitoring->id}");
                    continue;
                }
                
                $this->info("Current balance for monitoring ID {$monitoring->id}: {$currentBalance}");
                
                // Check for alerts
                $alerts = $this->checkStockAlerts($monitoring, $currentBalance);
                
                $this->info("Found " . count($alerts) . " alerts for monitoring ID: {$monitoring->id}");
                
                foreach ($alerts as $alert) {
                    $this->sendAlertEmail($monitoring, $alert);
                    $alertCount++;
                }
                
            } catch (\Exception $e) {
                $this->error("Error processing monitoring ID {$monitoring->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Stock monitoring alert process completed. {$alertCount} alerts sent.");
    }
    
    /**
     * Get current inventory balance for a stock monitoring record
     */
    private function getCurrentInventoryBalance($monitoring)
    {
        $this->info("Searching for inventory balance with criteria:");
        $this->info("- org_id: {$monitoring->org_id}");
        $this->info("- site_id: {$monitoring->site_id}");
        $this->info("- generic_id: {$monitoring->item_generic_id}");
        $this->info("- brand_id: {$monitoring->item_brand_id}");
        $this->info("- location_id: {$monitoring->service_location_id}");
        
        $balance = InventoryBalance::where('org_id', $monitoring->org_id)
            ->where('site_id', $monitoring->site_id)
            ->where('generic_id', $monitoring->item_generic_id)
            ->where('brand_id', $monitoring->item_brand_id)
            ->where('location_id', $monitoring->service_location_id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($balance) {
            $this->info("Found inventory balance: {$balance->location_balance} (ID: {$balance->id})");
         
        } else {
            $this->warn("No inventory balance record found");
        }
            
        return $balance ? $balance->location_balance : null;
    }
    
    /**
     * Check for various stock alerts
     */
    private function checkStockAlerts($monitoring, $currentBalance)
    {
        $alerts = [];
        
        $this->info("Checking alert conditions:");
        $this->info("- Current Balance: {$currentBalance}");
        $this->info("- Min Stock: {$monitoring->min_stock}");
        $this->info("- Max Stock: {$monitoring->max_stock}");
        $this->info("- Min Reorder Qty: {$monitoring->min_reorder_qty}");
        $this->info("- Monthly Consumption Ceiling: {$monitoring->monthly_consumption_ceiling}");
   
        
        // 1. Check minimum stock alert
        if ($monitoring->min_stock > $currentBalance) {
            $this->info("ALERT: Min stock condition met ({$monitoring->min_stock} > {$currentBalance})");

            $alerts[] = [
                'type' => 'min_stock',
                'current_balance' => $currentBalance,
                'threshold_value' => $monitoring->min_stock
            ];
        } else {
            $this->info("No min stock alert ({$monitoring->min_stock} <= {$currentBalance})");
        }
        
        // 2. Check maximum stock alert
        if ($monitoring->max_stock < $currentBalance) {
            $this->info("ALERT: Max stock condition met ({$monitoring->max_stock} < {$currentBalance})");

            $alerts[] = [
                'type' => 'max_stock',
                'current_balance' => $currentBalance,
                'threshold_value' => $monitoring->max_stock
            ];
        } else {
            $this->info("No max stock alert ({$monitoring->max_stock} >= {$currentBalance})");
        }
        
        // 3. Check monthly consumption ceiling alert
        $consumptionAmount = $this->calculateMonthlyConsumption($monitoring);
        $this->info("Monthly consumption calculated: {$consumptionAmount}");
        if ($consumptionAmount > $monitoring->monthly_consumption_ceiling) {
            $this->info("ALERT: Consumption ceiling condition met ({$consumptionAmount} > {$monitoring->monthly_consumption_ceiling})");

            $alerts[] = [
                'type' => 'consumption_ceiling',
                'current_balance' => $currentBalance,
                'threshold_value' => $monitoring->monthly_consumption_ceiling
            ];
        } else {
            $this->info("No consumption ceiling alert ({$consumptionAmount} <= {$monitoring->monthly_consumption_ceiling})");
        }
        
        // 4. Check minimum reorder quantity alert
        if ($monitoring->min_reorder_qty > $currentBalance) {
            $this->info("ALERT: Min reorder qty condition met ({$monitoring->min_reorder_qty} > {$currentBalance})");

            $alerts[] = [
                'type' => 'reorder_qty',
                'current_balance' => $currentBalance,
                'threshold_value' => $monitoring->min_reorder_qty
            ];
        } else {
            $this->info("No min reorder qty alert ({$monitoring->min_reorder_qty} <= {$currentBalance})");
        }
        
        return $alerts;
    }
    
    /**
     * Calculate monthly consumption by comparing first and last entries
     */
    private function calculateMonthlyConsumption($monitoring)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $this->info("Calculating monthly consumption for current month ({$currentMonth}/{$currentYear})...");
        
        // Get first entry of current month (earliest timestamp)
        $firstEntry = InventoryBalance::where('org_id', $monitoring->org_id)
            ->where('site_id', $monitoring->site_id)
            ->where('generic_id', $monitoring->item_generic_id)
            ->where('brand_id', $monitoring->item_brand_id)
            ->where('location_id', $monitoring->service_location_id)
            ->whereRaw('MONTH(FROM_UNIXTIME(timestamp)) = ?', [$currentMonth])
            ->whereRaw('YEAR(FROM_UNIXTIME(timestamp)) = ?', [$currentYear])
            ->orderBy('timestamp', 'asc')
            ->first();
            
        // Get last entry of current month (latest timestamp)
        $lastEntry = InventoryBalance::where('org_id', $monitoring->org_id)
            ->where('site_id', $monitoring->site_id)
            ->where('generic_id', $monitoring->item_generic_id)
            ->where('brand_id', $monitoring->item_brand_id)
            ->where('location_id', $monitoring->service_location_id)
            ->whereRaw('MONTH(FROM_UNIXTIME(timestamp)) = ?', [$currentMonth])
            ->whereRaw('YEAR(FROM_UNIXTIME(timestamp)) = ?', [$currentYear])
            ->orderBy('timestamp', 'desc')
            ->first();
            
        if (!$firstEntry || !$lastEntry) {
            $this->warn("No first or last entry found for current month ({$currentMonth}/{$currentYear})");
   
            return 0;
        }
        
        $firstTimestamp = Carbon::createFromTimestamp($firstEntry->timestamp)->format('Y-m-d H:i:s');
        $lastTimestamp = Carbon::createFromTimestamp($lastEntry->timestamp)->format('Y-m-d H:i:s');
        
        $this->info("First entry balance: {$firstEntry->location_balance} (ID: {$firstEntry->id}, Time: {$firstTimestamp})");
        $this->info("Last entry balance: {$lastEntry->location_balance} (ID: {$lastEntry->id}, Time: {$lastTimestamp})");

        
        // Calculate consumption (first balance - last balance)
        $consumption = $firstEntry->location_balance - $lastEntry->location_balance;
        
        $this->info("Calculated consumption: {$consumption} (First: {$firstEntry->location_balance} - Last: {$lastEntry->location_balance})");
        
        // Return positive consumption amount
        $positiveConsumption = max(0, $consumption);
        $this->info("Positive consumption (final): {$positiveConsumption}");

        
        return $positiveConsumption;
    }
    
    /**
     * Send alert email
     */
    private function sendAlertEmail($monitoring, $alert)
    {
        try {
            $this->info("Preparing to send alert email for type: {$alert['type']}");
            
            // Get related data for email
            $itemGeneric = DB::table('inventory_generic')->where('id', $monitoring->item_generic_id)->value('name') ?? 'Unknown';
            $itemBrand = DB::table('inventory_brand')->where('id', $monitoring->item_brand_id)->value('name') ?? 'Unknown';
            $siteName = DB::table('org_site')->where('id', $monitoring->site_id)->value('name') ?? 'Unknown';
            $orgName = DB::table('organization')->where('id', $monitoring->org_id)->value('organization') ?? 'Unknown';
            $locationName = DB::table('service_location')->where('id', $monitoring->service_location_id)->value('name') ?? 'Unknown';
            
            $this->info("Email data retrieved:");
            $this->info("- Item Generic: {$itemGeneric}");
            $this->info("- Item Brand: {$itemBrand}");
            $this->info("- Site: {$siteName}");
            $this->info("- Organization: {$orgName}");
            $this->info("- Location: {$locationName}");

            
            // Send email to primary email if available
            if (!empty($monitoring->primary_email)) {
                $this->info("Sending email to primary: {$monitoring->primary_email}");
                
                Mail::to($monitoring->primary_email)->send(new StockAlertMail(
                    $alert['type'],
                    $itemGeneric,
                    $itemBrand,
                    $siteName,
                    $orgName,
                    $locationName,
                    $alert['current_balance'],
                    $alert['threshold_value']
                ));
                
                $this->info("SUCCESS: Alert sent to primary email: {$monitoring->primary_email}");
            } else {
                $this->warn("Primary email is empty, skipping primary email");
            }
            
            // Send email to secondary email if available
            if (!empty($monitoring->secondary_email)) {
                $this->info("Sending email to secondary: {$monitoring->secondary_email}");
                Mail::to($monitoring->secondary_email)->send(new StockAlertMail(
                    $alert['type'],
                    $itemGeneric,
                    $itemBrand,
                    $siteName,
                    $orgName,
                    $locationName,
                    $alert['current_balance'],
                    $alert['threshold_value']
                ));
                
                $this->info("SUCCESS: Alert sent to secondary email: {$monitoring->secondary_email}");
               
            } else {
                $this->warn("Secondary email is empty, skipping secondary email");
            }
            
        } catch (\Exception $e) {
            $this->error("FAILED to send email for monitoring ID {$monitoring->id}: " . $e->getMessage());
        }
    }
}
