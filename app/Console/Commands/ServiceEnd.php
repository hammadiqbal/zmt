<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PatientArrivalDeparture;
use Carbon\Carbon;


class ServiceEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Service End Date&Time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTimestamp = Carbon::now();
        $PatientArrivalDepartures = PatientArrivalDeparture::where('status', 1)
        ->whereNotNull('service_end_time')->get();

        foreach ($PatientArrivalDepartures as $PatientArrivalDeparture) {
            // Get the associated schedule_id from service_booking
            $ID = $PatientArrivalDeparture->id;

            // Find the corresponding schedule in service_location_scheduling
            $PatientInOut = PatientArrivalDeparture::find($ID);
            $ServiceEndTimestamp = Carbon::createFromTimestamp($PatientInOut->service_end_time);


            if ($PatientInOut && $ServiceEndTimestamp < $currentTimestamp) {
                $PatientInOut->update(['status' => 0]);
            }

        }
    }
}
