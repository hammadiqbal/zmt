<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceLocationScheduling;
use App\Models\ServiceBooking;
use Carbon\Carbon;

class ServiceBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:bookingstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Status according to the schedule for Service Booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTimestamp = Carbon::now();
        $serviceBookings = ServiceBooking::where('status', 1)->get();

        foreach ($serviceBookings as $serviceBooking) {
            // Get the associated schedule_id from service_booking
            // $scheduleId = $serviceBooking->schedule_id;

            // Find the corresponding schedule in service_location_scheduling
            // $schedule = ServiceLocationScheduling::find($scheduleId);
            $ScheduleEndTimestamp = Carbon::createFromTimestamp($serviceBooking->service_endtime);


            if ($ScheduleEndTimestamp < $currentTimestamp) {
                $serviceBooking->update(['status' => 0]);
            }

        }
    }
}
