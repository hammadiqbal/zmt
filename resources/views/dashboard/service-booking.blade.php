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
                <li class="breadcrumb-item active">Service Booking Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Service Bookings</h4>
                </div>
                @php
                $serviceBooking = explode(',', $rights->services_booking_for_patients);
                $add = $serviceBooking[0];
                $view = $serviceBooking[1];
                $edit = $serviceBooking[2];
                $updateStatus = $serviceBooking[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-servicebooking">
                        <i class="mdi mdi-calendar-clock"></i> Add Service Booking
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-servicebooking" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Service Booking</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_servicebooking" method="post">
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
                                                        <select class="form-contro selecter p-0" id="sb_org" name="sb_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="sb_org" id="sb_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" id="sb_site" name="sb_site" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="sb_mr" name="sb_mr" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_mr_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Location</label>
                                                                    <select class="form-control selecter p-0" id="sb_location" name="sb_location" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Location Schedule</label>
                                                                    <select class="form-control selecter p-0" name="sb_schedule" id="sb_schedule" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_schedule_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Responsible Physician</label>
                                                                    <select class="form-control selecter p-0" name="sb_emp" id="sb_emp" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_emp_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Services</label>
                                                                    <select class="form-control selecter p-0" id="sb_service" name="sb_service" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_service_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Service Modes</label>
                                                                    <select class="form-control selecter p-0" id="sb_serviceMode" name="sb_serviceMode" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_serviceMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Billing Cost Center</label>
                                                                    <select class="form-control selecter p-0" id="sb_billingCC" name="sb_billingCC" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_billingCC_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient Status</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="sbp_status" name="sbp_status" style="color:#222d32">
                                                                        <option selected disabled >Select Patient Status</option>
                                                                        <option value="new">New</option>
                                                                        <option value="follow up">Follow Up</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sbp_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient Priority</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="sbp_priority" name="sbp_priority" style="color:#222d32">
                                                                        <option selected disabled>Select Patient Priority</option>
                                                                        <option value="routine">Routine</option>
                                                                        <option value="urgent">Urgent</option>
                                                                        <option value="emergency">Emergency</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sbp_priority_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt edt" name="sb_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="sb_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" rows="1" placeholder="Remarks.." id="input03" name="sb_remarks" spellcheck="false"></textarea>
                                                                </div>
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
            @endif

            @if ($view == 1)
            <div class="row ">
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
                                    <select class="form-contro selecter p-0" id="fb_org"  name="fb_org">
                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                    </select>
                                </div>
                                @else
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                {{-- <label class="control-label">Organization</label> --}}
                                                <select class="form-control selecter p-0" id="fb_org" name="fb_org" style="color:#222d32">
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

                                <div class="col-md-2">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                {{-- <label class="control-label">Select Site</label> --}}
                                                <select class="form-control selecter p-0" id="fb_site"  name="fb_site" style="color:#222d32">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                {{-- <label class="control-label">Select MR#</label> --}}
                                                <select class="form-control selecter p-0" id="fb_mrno" name="fb_mrno" style="color:#222d32">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="col-md-3">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                {{-- <label class="control-label">Date Filter</label> --}}
                                                <select class="form-control selecter p-0" id="fb_date_filter" name="fb_date_filter" style="color:#222d32">
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

            <div class="table-responsive m-t-10">
                <table id="view-servicebooking" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Patient Details</th>
                            <th>Service Booking Details</th>
                            <th>Service Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-servicebooking" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_servicebooking" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Service Booking Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" name="u_sbooking_id" class="u_sbooking_id">

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" id="u_sb_org" required name="u_sb_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Site</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_site" required name="u_sb_site" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update MR #</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_mr" required name="u_sb_mr" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Service Location</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_location" required name="u_sb_location" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Schedule</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_schedule" required name="u_sb_schedule" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Physician</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_emp" required name="u_sb_emp" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Services</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_service" required name="u_sb_service" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Service Modes</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_sm" required name="u_sb_sm" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Billing Cost Center</label>
                                                    <select class="form-control p-0 selecter" id="u_sb_cc" required name="u_sb_cc" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Patient Status</label>
                                                    <select class="form-control p-0 selecter" id="u_sbp_status" required name="u_sbp_status" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Patient Priority</label>
                                                    <select class="form-control p-0 selecter" id="u_sbp_priority" required name="u_sbp_priority" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <textarea class="form-control u_sb_remarks" rows="1" id="input03" name="u_sb_remarks" spellcheck="false"></textarea>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_sb_edt" required class="form-control input06 dt uedt">
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
    </script>
    <script src="{{ asset('assets/custom/service_booking.js') }}"></script>