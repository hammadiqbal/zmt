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
                <li class="breadcrumb-item active">Patient Arrival & Departure Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Patient Arrival & Departures</h4>
                </div>
                @php
                $ArrivalDeparture = explode(',', $rights->patient_arrival_and_departure);
                $add = $ArrivalDeparture[0];
                $view = $ArrivalDeparture[1];
                $edit = $ArrivalDeparture[2];
                $updateStatus = $ArrivalDeparture[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-patientinout">
                        <i class="mdi mdi-calendar-clock"></i> Add Patient Arrival
                    </button>
                </div>
                @endif

            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-patientinout" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Patient Arrival</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_patientinout" method="post">
                            @csrf
                            {{-- <input type="hidden" name="booking_id"> --}}
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        {{-- <div class="row mb-1" id="booking-status">
                                            <div class="col-md-6 text-left">
                                                <code></code>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <code></code>
                                            </div>
                                        </div> --}}

                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">MR #</h6>
                                                                    <select class="form-control selecter p-0" name="pio_mr" id="enterMR" style="color:#222d32">
                                                                        {{-- <option selected disabled >Select MR #</option>
                                                                        @foreach ($Patients as $Patient)
                                                                            <option value="{{ $Patient['mr_code'] }}"> {{ $Patient['mr_code'] }} - {{ ucwords($Patient['name']) }} @if (!empty($Patient['cell_no'])) - {{ $Patient['cell_no'] }} @endif</option>
                                                                        @endforeach --}}
                                                                    </select>
                                                                    {{-- <input type="text" placeholder="Patient MR #" class="form-control input-sm" name="pio_mr" id="enterMR"> --}}
                                                                </div>
                                                                <span class="text-danger" id="pio_mr_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                              
                                                <div class="row" id="patientArrivalDetails">
                                                    @if($user->org_id != 0)
                                                    <div class="userOrganization">
                                                        <select class="form-contro selecter p-0" id="pio_org" name="pio_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <!-- <h6 class="box-title font-weight-bold" id="scheduleOrg">Organization:</h6> -->
                                                                    <h6 class="box-title font-weight-bold">Organization</h6>
                                                                    <select class="form-control selecter p-0" name="pio_org" id="pio_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <!-- <h6 class="box-title font-weight-bold" id="scheduleSite">Site:</h6> -->
                                                                    <h6 class="box-title font-weight-bold">Site</h6>
                                                                    <select class="form-control selecter p-0" id="pio_site" name="pio_site" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Patient Status</h6>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="pio_status" name="pio_status" style="color:#222d32">
                                                                        <option selected disabled >Select Patient Status</option>
                                                                        <option value="new">New</option>
                                                                        <option value="follow up">Follow Up</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Patient Priority</h6>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="pio_priority" name="pio_priority" style="color:#222d32">
                                                                        <option selected disabled>Select Patient Priority</option>
                                                                        <option value="routine">Routine</option>
                                                                        <option value="urgent">Urgent</option>
                                                                        <option value="emergency">Emergency</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_priority_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Service Location</h6>
                                                                    <!-- <h6 class="box-title font-weight-bold" id="scheduleServiceLocation">Service Location:</h6> -->
                                                                    <select class="form-control selecter p-0" name="pio_serviceLocation" id="pio_serviceLocation" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceLocation_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Service Schedule</h6>
                                                                    <!-- <h6 class="box-title font-weight-bold" id="scheduleServiceSchedule">Service Schedule:</h6> -->
                                                                    <select class="form-control selecter p-0" name="pio_serviceSchedule" id="pio_serviceSchedule" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceSchedule_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-6" id="DateTime">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold" id="scheduleDatetime"></h6>
                                                                    <input type="text" class="form-control input-daterange-timepicker" name="pio_scheduleDatetime" id="pio_scheduleDatetime"  placeholder="Select Date & Time Range"/>
                                                                </div>
                                                                <span class="text-danger" id="pio_scheduleDatetime_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-0">
                                                                    <h6 class="box-title font-weight-bold">Designated Physician</h6>
                                                                    <!-- <h6 class="box-title font-weight-bold" id="schedulephysician">Designated Physician:</h6> -->
                                                                    <select class="form-control selecter p-0" name="pio_emp" id="pio_emp" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_emp_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Services</h6>
                                                                    <select class="form-control selecter p-0" id="pio_service" name="pio_service" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_service_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Service Modes</h6>
                                                                    <select class="form-control selecter p-0" id="pio_serviceMode" name="pio_serviceMode" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">Billing Speciality<h6>
                                                                    <select class="form-control selecter p-0" id="pio_billingCC" name="pio_billingCC" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_billingCC_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Payment Mode</label>
                                                                    <select class="form-control selecter p-0" id="pio_payMode" name="pio_payMode" style="color:#222d32">
                                                                        <option selected>Cash</option>
                                                                        <option>Card</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_payMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Amount Received</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="1000" name="amount_received" id="amount_received">
                                                                </div>
                                                                <span class="text-danger" id="amount_received_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                 
                                                    </div> --}}

                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Service Start Date & Time</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt edt" name="pio_serviceStart" placeholder="Select Service Start Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceStart_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}


                                                
                                                <div class="col-md-12" id="pio_remarks">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <h6 class="box-title font-weight-bold">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></h6>
                                                                <textarea class="form-control" placeholder="Enter Remarks..." rows="1" id="input03" name="pio_remarks" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="pio_remarks_error"></span>
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
                                <button type="button" class="btn btn-default close_bottom" data-dismiss="modal">Exit</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if ($view == 1)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-body">
                        <div class="row align-items-center mb-1">
                            <div class="col-auto filterToggle" style="cursor: pointer;">
                                <span>Filter</span>
                            </div>
                            <div class="filterToggle" style="margin-bottom:-8px;cursor: pointer;">
                                <span>
                                    <div bis_skin_checked="1">
                                        <span class="b-plp_actions-refinements_toggle_icon">
                                            <svg width="19" height="24" viewBox="0 0 19 24" fill="none" focusable="false">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.0126953 9.3H2.67911C2.98774 10.0064 3.69257 10.5 4.5127 10.5C5.33282 10.5 6.03765 10.0064 6.34628 9.3H18.0127V7.7H6.34628C6.03765 6.99364 5.33282 6.5 4.5127 6.5C3.69257 6.5 2.98774 6.99364 2.67911 7.7H0.0126953V9.3ZM14.3463 16.3H18.0127V14.7H14.3463C14.0377 13.9936 13.3328 13.5 12.5127 13.5C11.6926 13.5 10.9877 13.9936 10.6791 14.7H0.0126953V16.3H10.6791C10.9877 17.0064 11.6926 17.5 12.5127 17.5C13.3328 17.5 14.0377 17.0064 14.3463 16.3Z" fill="currentColor"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </span>
                            </div>
                        
                            <!-- Clear Button -->
                            <div class="col-auto ml-auto">
                                <button class="btn btn-outline-secondary btn-sm clearFilter" type="button">
                                    Clear
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-body filterData">
                            <div class="row justify-content-center align-items-center">
                                @if($user->org_id != 0)
                                <div class="userOrganization">
                                    <select class="form-contro selecter p-0" id="pad_org" >
                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                    </select>
                                </div>
                                @else
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <select class="form-control selecter p-0" id="pad_org" style="color:#222d32">
                                                    <option selected disabled >Select Organization</option>
                                                    @foreach ($Organizations as $Organization)
                                                        <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-3">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <select class="form-control selecter p-0" id="pad_site" style="color:#222d32">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <select class="form-control selecter p-0" id="pad_mrno" style="color:#222d32">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <select class="form-control selecter p-0" id="pad_date_filter" style="color:#222d32">
                                                    <option selected disabled value="">Select Date Filter</option>
                                                    <option value="today" selected>Today</option>
                                                    <option value="yesterday">Yesterday</option>
                                                    <option value="this_week">This Week</option>
                                                    <option value="last_week">Last Week</option>
                                                    <option value="this_month">This Month</option>
                                                    <option value="last_month">Last Month</option>
                                                    <option value="this_year">This Year</option>
                                                    <option value="last_year">Last Year</option>
                                                </select>
                                            </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <div class="table-responsive m-t-40">
                <table id="view-patientinout" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Patient Details</th>
                            <th>Service Booking Details</th>
                            <th>Arrival & Departure Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>



    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-patientinout" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_patientinout" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Patient Arrival & Departure Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" name="patientinout_id" id="patientinout_id">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">MR#:</label>
                                                    <input type="text" class="form-control" id="u_pio_mr">
                                                </div>
                                            </div>

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Organization:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_org" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Site:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_site" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Patient Status:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_status" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Patient Priority:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_priority" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Service Location:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_location" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Service Schedule:</label>
                                                    <select class="form-control selecter p-0" id="u_pio_schedule" required  style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="mb-2 row">
                                                    <div class="col-md-12">
                                                        <div class="mb-2 has-custom m-b-5">
                                                            {{-- <label class="control-label">Calendar Day & Time (Start - End):</label> --}}
                                                            {{-- <input type="text" class="form-control input-daterange-timepicker" id="u_pio_scheduleDatetime"/> --}}
                                                            <label class="control-label">Service Schedule:</label>
                                                            <input type="text" class="form-control" id="u_pio_scheduleDatetime"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Designated Physician</label>
                                                    <select class="form-control selecter p-0" id="u_pio_emp" required style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Update Service</label>
                                                    <select class="form-control selecter p-0" id="u_pio_service" required name="u_pio_service" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Update Service Mode</label>
                                                    <select class="form-control selecter p-0" id="u_pio_serviceMode" required name="u_pio_serviceMode" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Update Billing Speciality</label>
                                                    <select class="form-control selecter p-0" id="u_pio_billingCC" required name="u_pio_billingCC" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Update Service Start Date&Time</label>
                                                    <input type="text" class="form-control input06 dt edt" id="u_pio_serviceStart" name="u_pio_serviceStart" placeholder="Select Service Start Date & Time">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <label class="control-label">Update Service End Date&Time</label>
                                                    <input type="text" class="form-control input06 dt edt" id="u_pio_serviceEnd" name="u_pio_serviceEnd" placeholder="Select Service End Date & Time">
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

    @if ($view == 1)
    <div class="modal fade bs-example-modal-sm" id="endServiceModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-sm" style="max-width:350px !important;" role="document">
            <form id="end_service" method="post" class="floating-labels form-horizontal">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Enter Service End Date & Time</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" id="pio_id" name="pio_id">
                                <input type="hidden" id="pio_servicemode_id" name="pio_servicemode_id">
                                <input type="hidden" id="pio_service_id" name="pio_service_id">
                                <input type="hidden" id="pio_billingcc_id" name="pio_billingcc_id">
                                <input type="hidden" id="pio_emp_id" name="pio_emp_id">
                                <input type="hidden" id="pio_mr_code" name="pio_mr_code">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="form-group has-custom m-b-5">
                                            <input type="text" id="date-format1" class="form-control input06" name="pio_serviceEnd" placeholder="Select Service End Date & Time">
                                        </div>
                                        <span class="text-danger" id="pio_serviceEnd_error"></span>
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
        $('.edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
        
        $('.input-daterange-timepicker').daterangepicker({
            timePicker: true,
            locale: {
                format: 'MM/DD/YYYY h:mm A'
            },
            timePickerIncrement: 1,
            timePicker12Hour: true,
            timePickerSeconds: false,
            startDate: moment().startOf('hour').add(1, 'hour'),
            endDate: moment().startOf('hour').add(2, 'hour'),
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-danger',
            cancelClass: 'btn-inverse'
        });
    </script>
    <script src="{{ asset('assets/custom/patient_inout.js') }}"></script>


