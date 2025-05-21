<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceLocationScheduling;
use Carbon\Carbon;

class ServiceScheduling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:scheduling';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Service Status according to DateTime and Schedule pattern';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTimestamp = Carbon::now();
        $noneLocations = ServiceLocationScheduling::where('schedule_pattern', 'none')
            ->where('end_timestamp', '>', $currentTimestamp)
            ->get();

        foreach ($noneLocations as $location) {
            $locationEndTimestamp = Carbon::createFromTimestamp($location->end_timestamp);
            if ($locationEndTimestamp < $currentTimestamp) {
                $location->update(['status' => 0, 'effective_timestamp' => 0]);
            }
        }

        $weeklyLocations = ServiceLocationScheduling::where('schedule_pattern', 'weekly')
            ->where('end_timestamp', '>', $currentTimestamp)
            ->get();

       foreach ($weeklyLocations as $weeklocation) {
            $WeeklylocationStartTimestamp = Carbon::createFromTimestamp($weeklocation->start_timestamp);
            $WeeklylocationEndTimestamp = Carbon::createFromTimestamp($weeklocation->end_timestamp);
            if ($WeeklylocationEndTimestamp < $currentTimestamp) {
                $nextWeekStartTimestamp = $WeeklylocationStartTimestamp->copy()->addWeek();
                $nextWeekEndTimestamp = $WeeklylocationEndTimestamp->copy()->addWeek();
                $nextWeekStartTimestamp->setTime($WeeklylocationStartTimestamp->hour, $WeeklylocationStartTimestamp->minute);
                $nextWeekEndTimestamp->setTime($WeeklylocationEndTimestamp->hour, $WeeklylocationEndTimestamp->minute);
                $weeklocation->update(['start_timestamp' => $nextWeekStartTimestamp->timestamp,'end_timestamp' => $nextWeekEndTimestamp->timestamp]);
            }
        }

        $dailyLocations = ServiceLocationScheduling::where('schedule_pattern', 'daily')
            ->where('end_timestamp', '>', $currentTimestamp)
            ->get();

        foreach ($dailyLocations as $dailyLocation) {
            $dailylocationStartTimestamp = Carbon::createFromTimestamp($dailyLocation->start_timestamp);
            $dailylocationEndTimestamp = Carbon::createFromTimestamp($dailyLocation->end_timestamp);
            if ($dailylocationEndTimestamp < $currentTimestamp) {
                $nextDayStartTimestamp = $dailylocationStartTimestamp->copy()->addDay();
                $nextDayEndTimestamp = $dailylocationEndTimestamp->copy()->addDay();
                $nextDayStartTimestamp->setTime($nextDayStartTimestamp->hour, $nextDayStartTimestamp->minute);
                $nextDayEndTimestamp->setTime($nextDayEndTimestamp->hour, $nextDayEndTimestamp->minute);
                $dailyLocation->update(['start_timestamp' => $nextDayStartTimestamp->timestamp,'end_timestamp' => $nextDayEndTimestamp->timestamp]);
            }
        }

        $currentTimestamp = Carbon::now();
        $monSatLocations = ServiceLocationScheduling::where('schedule_pattern', 'monday to saturday')
        ->where('end_timestamp', '>', $currentTimestamp)
        ->get();

        foreach ($monSatLocations as $monSatLocation) {
            $montosatlocationStartTimestamp = Carbon::createFromTimestamp($monSatLocation->start_timestamp);
            $montosatlocationEndTimestamp = Carbon::createFromTimestamp($monSatLocation->end_timestamp);

            if ($currentTimestamp->isSaturday()) {

                $monSatLocation->update(['status' => 0, 'effective_timestamp' => 0]);
            } else {
                if ($montosatlocationEndTimestamp < $currentTimestamp) {
                    $montosatStartTimestamp = $montosatlocationStartTimestamp->copy()->addDay();
                    $montosatEndTimestamp = $montosatlocationEndTimestamp->copy()->addDay();
                    $montosatStartTimestamp->setTime($montosatStartTimestamp->hour, $montosatStartTimestamp->minute);
                    $montosatEndTimestamp->setTime($montosatEndTimestamp->hour, $montosatEndTimestamp->minute);
                    $monSatLocation->update(['start_timestamp' => $montosatStartTimestamp->timestamp,'end_timestamp' => $montosatEndTimestamp->timestamp]);
                    if ($currentTimestamp->isMonday()) {
                        $monSatLocation->update(['effective_timestamp' => $currentTimestamp]);
                    }
                }
            }
        }

    }
}
