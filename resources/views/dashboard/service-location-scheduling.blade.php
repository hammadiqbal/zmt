<!-- ============================================================== -->
<!-- Start Header  -->
<!-- ============================================================== -->
@include('partials/header')
<!-- ============================================================== -->
<!-- End Header  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Top Bar  -->
<!-- ============================================================== -->
@include('partials/topbar')
<!-- ============================================================== -->
<!-- End Top Bar  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Side Bar  -->
<!-- ============================================================== -->
@include('partials/sidebar')
<!-- ============================================================== -->
<!-- End Side Bar  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Service Location Scheduling</li>
                <li class="breadcrumb-item active">Service Location Scheduling Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Service Location Schedules</h4>
                </div>
                @php
                $serviceLocationScheduling = explode(',', $rights->service_location_scheduling);
                $add = $serviceLocationScheduling[0];
                $view = $serviceLocationScheduling[1];
                $edit = $serviceLocationScheduling[2];
                $updateStatus = $serviceLocationScheduling[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-locationscheduling">
                        <i class="mdi mdi-crosshairs-gps"></i> Add Service Location Scheduling
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-locationscheduling" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Service Location Scheduling</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_locationscheduling" method="post">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    @if($user->org_id != 0)
                                                    <div class="userOrganization">
                                                        <select class="form-contro selecter p-0" id="ss_org" name="ss_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ss_org" id="ss_org" style="color:#222d32">
                                                                        <option selected disabled >Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="ss_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" id="ss_site" name="ss_site" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="ss_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input04">Enter Service Location Schedule Description</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Service Location Schedule Description" name="service_schedule" id="input04">
                                                                </div>
                                                                <span class="text-danger" id="service_schedule_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Location</label>
                                                                    <select class="form-control selecter p-0" id="ss_location" name="ss_location" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ss_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Select Calendar Day & Time (Start - End)</h6>
                                                                    <input type="text" class="form-control input-daterange-timepicker"  name="schedule_datetime"  placeholder="Select Date & Time Range"/>
                                                                </div>
                                                                <span class="text-danger" id="schedule_datetime_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Calendar Time (Start - End)</label>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <input type="text" id="start_time" class="form-control" name="start_time" placeholder="Start Time" />
                                                                            <span class="text-danger" id="start_time_error"></span>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input type="text" id="end_time" class="form-control" name="end_time" placeholder="End Time" />
                                                                            <span class="text-danger" id="end_time_error"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                {{-- <span class="text-danger" id="ss_location_error"></span> --}}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="row">
                                                        <div class="col-md-6">
                                                            <input type="text" id="start_time" class="form-control" name="start_time" placeholder="Start Time" />
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="text" id="end_time" class="form-control" name="end_time" placeholder="End Time" />
                                                        </div>
                                                    </div> --}}

                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Select Schedule Pattern</h6>
                                                                    <select class="form-control p-0 cursor-pointer selecter" id="ss_pattern" name="ss_pattern" style="color:#222d32">
                                                                        <option value="none">None</option>
                                                                        <option value="daily">Daily</option>
                                                                        <option value="weekly">Weekly</option>
                                                                        <option value="monday to saturday">Monday To Saturday</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ss_pattern_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Select Schedule Pattern</label>
                                                                    <input type="text" id="ss_pattern" name="ss_pattern" class="form-control cursor-pointer open_schedule" placeholder="Select Days" readonly >
                                                                </div>
                                                                <span class="text-danger" id="ss_pattern_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Total Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="total_patient" max="100" min="0">
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Total Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="total_patient" max="100" min="0">
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter New Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="new_patient" max="100" min="0" >
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Follow Up Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="followup_patient" max="100" min="0">
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Routine Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="routine_patient" max="100" min="0" >
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Urgent Patient Limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="number" class="form-control input-sm" placeholder="100" name="urgent_patient" max="100" min="0" >
                                                                </div>
                                                                <span class="text-danger cc_percent_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Designated Physician <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <select class="form-control p-0 cursor-pointer selecter" name="ss_emp" id="ss_emp">
                                                                        {{-- <option selected disabled >Select Designated Physician</option>
                                                                        @foreach ($Employees as $Employee)
                                                                            <option value="{{ $Employee['id'] }}">{{ $Employee['prefix'] }} {{ $Employee['name'] }}</option>
                                                                        @endforeach --}}
                                                                    </select>
                                                                    <span class="bar"></span>

                                                                </div>
                                                                <span class="text-danger" id="ss_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt edt" name="ss_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="ss_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Row -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="daySelectorModal" tabindex="-1" role="dialog" aria-labelledby="daySelectorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Days</h5>
                        
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Monday" id="monday"><label class="form-check-label" for="monday">Monday</label></div>
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Tuesday" id="tuesday"><label class="form-check-label" for="tuesday">Tuesday</label></div>
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Wednesday" id="wednesday"><label class="form-check-label" for="wednesday">Wednesday</label></div>
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Thursday" id="thursday"><label class="form-check-label" for="thursday">Thursday</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Friday" id="friday"><label class="form-check-label" for="friday">Friday</label></div>
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Saturday" id="saturday"><label class="form-check-label" for="saturday">Saturday</label></div>
                                    <div class="form-check"><input type="checkbox" class="form-check-input day-check" value="Sunday" id="sunday"><label class="form-check-label" for="sunday">Sunday</label></div>
                                </div>
                            </div>
                            <span class="text-danger d-none" id="dayError">Please select at least one day.</span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="saveDays">Done</button>
                        </div>
                    </div>
                </div>
            </div>


            @endif


            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-locationscheduling" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Service Description</th>
                            <th>Timing & Venue</th>
                            <th>Patient Limits</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-locationscheduling" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_locationscheduling" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Service Location Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" name="u_slocation_id" class="u_slocation_id">

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" id="u_ssorg" required name="u_ssorg" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Site</label>
                                                    <select class="form-control selecter p-0" id="u_sssite" required name="u_sssite" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Service Location Schedule Description</label>
                                                    <input type="text" class="form-control input-sm u_service_schedule" name="u_service_schedule" id="input04">
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Service Location</label>
                                                    <select class="form-control selecter p-0" id="u_sslocation" required name="u_sslocation" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Day & Time</label>
                                                    <input type="text" class="form-control input-daterange-timepicker" required id="day_time" name="u_schedule_datetime"/>
                                                </div>
                                            </div> --}}

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Start Time</label>
                                                    <input type="text" class="form-control input-daterange-timepicker" required id="u_start_time" name="u_start_time"  placeholder="Start Time">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">End Time</label>
                                                    <input type="text" class="form-control input-daterange-timepicker" required id="u_end_time" name="u_end_time"  placeholder="End Time">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Schedule Pattern</label>
                                                    {{-- <input type="text" id="u_sspattern" 
                                                        name="u_sspattern" required
                                                        class="form-control cursor-pointer open_schedule" 
                                                        placeholder="Select Days" readonly> --}}
                                                    <input type="text" id="u_sspattern" class="form-control open_schedule cursor-pointer" required name="u_sspattern" placeholder="Select Days" readonly>
                                                    {{-- <select class="form-control selecter p-0" id="u_sspattern" required name="u_sspattern" style="color:#222d32">
                                                    </select> --}}
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Total Patient limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="number" class="form-control input-sm u_total_patient" name="u_total_patient">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update New Patient limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="number" class="form-control input-sm u_new_patient" name="u_new_patient">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Follow Up Patient limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="number" class="form-control input-sm u_followup_patient" name="u_followup_patient">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Routine Patient limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="number" class="form-control input-sm u_routine_patient" name="u_routine_patient">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Urgent Patient limit <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="number" class="form-control input-sm u_urgent_patient" name="u_urgent_patient">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Designated Physician <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <select class="form-control selecter p-0" id="u_ssemp"  name="u_ssemp" style="color:#222d32">
                                                        @foreach ($Employees as $Employee)
                                                            <option value="{{ $Employee['id'] }}">{{ $Employee['prefix'] }} {{ $Employee['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="uss_edt" required class="form-control input06 dt uedt">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif


    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->

    <script>
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date(),
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });

        $('#start_time,#u_start_time').bootstrapMaterialDatePicker({
            date: false,
            format: 'hh:mm A',
            shortTime: false,   // 12-hour AM/PM
            clearButton: true
        });

        $('#end_time,#u_end_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'hh:mm A',
            clearButton: true
        });

        // $('.input-daterange-timepicker').daterangepicker({
        //     timePicker: true,
        //     locale: {
        //         format: 'MM/DD/YYYY h:mm A'
        //     },
        //     timePickerIncrement: 1,
        //     timePicker12Hour: true,
        //     timePickerSeconds: false,
        //     startDate: moment().startOf('hour').add(1, 'hour'),
        //     endDate: moment().startOf('hour').add(2, 'hour'),
        //     buttonClasses: ['btn', 'btn-sm'],
        //     applyClass: 'btn-danger',
        //     cancelClass: 'btn-inverse'
        // });

        // $('.input-daterange-timepicker').daterangepicker({
        //     timePicker: true,
        //     timePicker24Hour: false,       // 12-hour format with AM/PM
        //     timePickerIncrement: 1,
        //     timePickerSeconds: false,
        //     locale: {
        //         format: 'h:mm A'          // Only time format
        //     },
        //     startDate: moment().startOf('hour').add(1, 'hour'),
        //     endDate: moment().startOf('hour').add(2, 'hour'),
        //     buttonClasses: ['btn', 'btn-sm'],
        //     applyClass: 'btn-danger',
        //     cancelClass: 'btn-inverse'
        // }, function(start, end) {
        //     $('.input-daterange-timepicker').val(start.format('h:mm A') + ' - ' + end.format('h:mm A'));
        // });
    </script>
    <script src="{{ asset('assets/custom/service_location_scheduling.js') }}"></script>
