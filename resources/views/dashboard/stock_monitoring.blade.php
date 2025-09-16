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
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Stock Monitoring</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Stock Monitoring</h4>
                </div>
                @php
                $StockMonitoring = explode(',', $rights->stock_monitoring);
                $add = $StockMonitoring[0];
                $view = $StockMonitoring[1];
                $edit = $StockMonitoring[2];
                $updateStatus = $StockMonitoring[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-stockmonitoring">
                        <i class="mdi mdi-bank"></i> Add Stock Monitoring Details
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-stockmonitoring" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Stock Monitoring Details</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_stockmonitoring" method="post">
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
                                                        <input type="hidden" id="sm_org" name="sm_org" value="{{ $user->org_id }}"/>
                                                        {{-- <select class="form-control selecter p-0" id="sm_org" name="sm_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select> --}}
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="sm_org" id="sm_org" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sm_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="sm_site" id="sm_site" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sm_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Item Generic</label>
                                                                    <select class="form-control selecter p-0" name="sm_generic" id="sm_generic" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sm_generic_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Item Brand</label>
                                                                    <select class="form-control selecter p-0" name="sm_brand" id="sm_brand" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sm_brand_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Inventory Location</label>
                                                                    <select class="form-control selecter p-0" name="sm_location" id="sm_location" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sm_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Minimum Stock Level</label>
                                                                    <input type="number" class="form-control input-sm" min="1" placeholder="Minimum Stock Level..." name="sm_min_stock" >
                                                                </div>
                                                                <span class="text-danger" id="sm_min_stock_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Maximum Stock Level</label>
                                                                    <input type="number" class="form-control input-sm" min="1" placeholder="Maximum Stock Level..." name="sm_max_stock" >
                                                                </div>
                                                                <span class="text-danger" id="sm_max_stock_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Monthly Consumption Budget Ceiling</label>
                                                                    <input type="number" class="form-control input-sm" min="1" placeholder="Monthly Consumption Budget Ceiling..." name="sm_monthly_consumption" >
                                                                </div>
                                                                <span class="text-danger" id="sm_monthly_consumption_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Minimum Level Reording Qty</label>
                                                                    <input type="number" class="form-control input-sm" min="1" placeholder="Minimum Level Reording Qty..." name="sm_min_reorder" >
                                                                </div>
                                                                <span class="text-danger" id="sm_min_reorder_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Primary Email</label>
                                                                    <input type="email" class="form-control input-sm" min="1" placeholder="Primary Email..." name="sm_primary_email" >
                                                                </div>
                                                                <span class="text-danger" id="sm_primary_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Secondary Email</label>
                                                                    <input type="email" class="form-control input-sm" min="1" placeholder="Secondary Email..." name="sm_secondary_email" >
                                                                </div>
                                                                <span class="text-danger" id="sm_secondary_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective Date&Time</label>
                                                                    <input type="text" id="date-format" name="sm_edt" class="form-control input06" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="sm_edt_error"></span>
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
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Sites</label>
                                                <select class="form-control selecter p-0" id="fb_site" style="color:#222d32">
                                                    <option selected disabled >Select Site</option>
                                                    @foreach ($Sites as $Site)
                                                        <option value="{{ $Site['id'] }}"> {{ $Site['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Generics</label>
                                                <select class="form-control selecter p-0" id="fb_generic" style="color:#222d32">
                                                    <option selected disabled >Select Generic</option>
                                                    @foreach ($Generics as $Generic)
                                                        <option value="{{ $Generic['id'] }}"> {{ $Generic['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Brands</label>
                                                <select class="form-control selecter p-0" id="fb_brand" style="color:#222d32">
                                                    <option selected disabled >Select Brand</option>
                                                    @foreach ($Brands as $Brand)
                                                        <option value="{{ $Brand['id'] }}"> {{ $Brand['name'] }}</option>
                                                    @endforeach
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
                <table id="view-stockmonitoring" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Item Details</th>
                            <th>Stock Details</th>
                            <th>Contact Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Stock Monitoring Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_stockmonitoring" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control u_sm-id" name="u_sm-id">
                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" id="u_sm_org" required name="u_sm_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Site</label>
                                                    <select class="form-control selecter p-0" id="u_sm_site" required name="u_sm_site" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Item Generic</label>
                                                    <select class="form-control selecter p-0" id="u_sm_generic" required name="u_sm_generic" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{-- <small class="text-danger" style="font-size:11px;">(Optional)</small> --}}
                                                    <label class="control-label">Update Item Brand </label>
                                                    <select class="form-control selecter p-0" id="u_sm_brand" required name="u_sm_brand" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Inventory Location</label>
                                                    <select class="form-control selecter p-0" id="u_sm_servicelocation" required name="u_sm_servicelocation" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Minimum Stock Level</label>
                                                    <input type="number" class="form-control input-sm" id="u_sm_min_stock" required  name="u_sm_min_stock">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Maximum Stock Level</label>
                                                    <input type="number" class="form-control input-sm" id="u_sm_max_stock" required  name="u_sm_max_stock">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Monthly Consumption Budget Ceiling</label>
                                                    <input type="number" class="form-control input-sm" required id="u_sm_monthly_consumption"  name="u_sm_monthly_consumption">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Minimum Level Reording Qty</label>
                                                    <input type="number" class="form-control input-sm" required id="u_sm_min_reorder" name="u_sm_min_reorder">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Primary Email</label>
                                                    <input type="email" class="form-control input-sm" required id="u_sm_primary_email" name="u_sm_primary_email">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Secondary Email</label>
                                                    <input type="email" class="form-control input-sm" required id="u_sm_secondary_email" name="u_sm_secondary_email">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" name="u_sm_edt" id="date-format1" required class="form-control input06 dt uedt">
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
                </form>
            </div>
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
            currentDate: new Date()
        });        
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
    </script>
    <script src="{{ asset('assets/custom/stock_monitoring.js') }}"></script>