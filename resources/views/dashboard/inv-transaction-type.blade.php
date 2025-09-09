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
                <li class="breadcrumb-item active">Inventory Transaction Type</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Inventory Transaction Types</h4>
                </div>
                @php
                $ItemTransactionType = explode(',', $rights->transaction_types);
                $add = $ItemTransactionType[0];
                $view = $ItemTransactionType[1];
                $edit = $ItemTransactionType[2];
                $updateStatus = $ItemTransactionType[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-invtransactiontype">
                        <i class="mdi mdi-database"></i> Add Inventory Transaction Type
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-invtransactiontype" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Inventory Transaction Type</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_invtransactiontype" method="post">
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
                                                        <select class="form-contro selecter p-0" id="itt_org" name="itt_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="itt_org" id="itt_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="itt_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Inventory Transaction Type Description</label>
                                                                    <input type="text" placeholder="Inventory Transaction Type Description" class="form-control input-sm" name="description" id="input01">
                                                                </div>
                                                                <span class="text-danger" id="description_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Activity Type</label>
                                                                    <select class="form-control selecter p-0" name="activity_type" id="activity_type" style="color:#222d32">
                                                                        <option selected disabled >Select Activity Type</option>
                                                                        @foreach ($TransactionActivities as $TransactionActivity)
                                                                            <option value="{{ $TransactionActivity['id'] }}">{{ $TransactionActivity['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="activity_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Requesting Mandatory Status</label>
                                                                    <select class="form-control selecter p-0" name="request_mandatory" id="request_mandatory" style="color:#222d32">
                                                                        <option selected disabled >Select Request Mandatory Status</option>
                                                                        <option value="y">Yes</option>
                                                                        <option value="n">No</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="request_mandatory_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                      <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee Location Check (Requesting)</label>
                                                                    <select class="form-control selecter p-0" name="request_emp_location" id="request_emp_location" style="color:#222d32">
                                                                        <option selected disabled >Select Employee Location Status</option>
                                                                        <option value="s">Source</option>
                                                                        <option value="d">Destination</option>
                                                                        <option value="n">Not Applicable</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="request_emp_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Source Location Type</label>
                                                                    <select class="form-control selecter p-0" name="source_location_type" id="source_location_type" style="color:#222d32">
                                                                        <option selected disabled >Select Source Location Type</option>
                                                                        @foreach ($InventorySourceDestinationTypes as $InventorySourceDestinationType)
                                                                            <option value="{{ $InventorySourceDestinationType['id'] }}">{{ $InventorySourceDestinationType['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="source_location_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Action At Source Location</label>
                                                                    <select class="form-control selecter p-0" name="source_action" id="source_action" style="color:#222d32">
                                                                        <option selected disabled >Select Source Transaction Action</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="source_action_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Destination Location Type</label>
                                                                    <select class="form-control selecter p-0" name="destination_location_type" id="destination_location_type" style="color:#222d32">
                                                                        <option selected disabled >Select Destination Location Type</option>
                                                                        @foreach ($InventorySourceDestinationTypes as $InventorySourceDestinationType)
                                                                            <option value="{{ $InventorySourceDestinationType['id'] }}">{{ $InventorySourceDestinationType['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="destination_location_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Action At Destination Location</label>
                                                                    <select class="form-control selecter p-0" name="destination_action" id="destination_action" style="color:#222d32">
                                                                        <option selected disabled >Select Destination Transaction Action</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="destination_action_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Controlled / Alloted Inventory Locations</label>
                                                                    <select class="form-control p-0 selecter" multiple name="inventory_location" id="inventory_location" style="color:#222d32">
                                                                        <option selected disabled >Select Alloted Inventory Locations</option>
                                                                        @foreach ($ServiceLocations as $ServiceLocation)
                                                                            <option value="{{ $ServiceLocation['id'] }}">{{ $ServiceLocation['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="inventory_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Controlled / Alloted Source Locations</label>
                                                                    <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                    data-target="#invLocationModal" readonly placeholder="Select Source Locations" id="source_locations_value" name="source_locations_value">
                                                                    <span class="text-danger" id="source_locations_value_error"></span>
                                                                    <input type="hidden" name="source_locations[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Controlled / Alloted Destination Locations</label>
                                                                    <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                    data-target="#invLocationModal" readonly placeholder="Select Destination Locations" id="destination_locations_value" name="destination_locations_value">
                                                                    <span class="text-danger" id="destination_locations_value_error"></span>
                                                                    <input type="hidden" name="destination_locations[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee Location Check (Source/Destination)</label>
                                                                    <select class="form-control selecter p-0" name="emp_location_check" id="emp_location_check" style="color:#222d32">
                                                                        <option selected disabled >Select Applicable Location</option>
                                                                        <option value="s">Source</option>
                                                                        <option value="d">Destination</option>
                                                                        <option value="n">Not Applicable</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="emp_location_check_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Transaction Expired Status</label>
                                                                    <select class="form-control selecter p-0" name="transaction_expired_status" id="transaction_expired_status" style="color:#222d32">
                                                                        <option selected disabled >Select Transaction Expired Status</option>
                                                                        <option value="y">Yes</option>
                                                                        <option value="n">No</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="transaction_expired_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="itt_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="itt_edt_error"></span>
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

            <!-- Inventory Location Modal -->
            <div class="modal fade" id="invLocationModal" tabindex="-1" role="dialog" aria-labelledby="invLocationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="invLocationModalLabel">Select Controlled / Alloted Inventory Locations</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Inventory Location Modal -->
            @endif

            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-invtransactiontype" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Type Details</th>
                            <th>Source & Destination Details</th>
                            <th>Location Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-invtransactiontype" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Transaction Type Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_invtransactiontype">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="u_itt-id" id="u_itt-id">
                                            
                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" name="u_itt_org" required id="u_itt_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Inventory Transaction Type Desription</label>
                                                    <input type="text" name="u_description" id="u_description" required class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Transaction Activity</label>
                                                    <select class="form-control selecter p-0" name="u_activity_type" required id="u_activity_type" style="color:#222d32">
                                                        @foreach ($TransactionActivities as $TransactionActivity)
                                                            <option value="{{ $TransactionActivity['id'] }}">{{ $TransactionActivity['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Request Mandatory Status</label>
                                                    <select class="form-control selecter p-0" name="u_request_mandatory" required id="u_request_mandatory" style="color:#222d32">
                                                        <option value="y">Yes</option>
                                                        <option value="n">No</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Employee Location Check (Requesting)</label>
                                                    <select class="form-control selecter p-0" name="u_request_emp_location" required id="u_request_emp_location" style="color:#222d32">
                                                        <option value="s">Source</option>
                                                        <option value="d">Destination</option>
                                                        <option value="n">Not Applicable</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Source Location Type</label>
                                                    <select class="form-control selecter p-0" name="u_source_location_type" required id="u_source_location_type" style="color:#222d32">
                                                        @foreach ($InventorySourceDestinationTypes as $InventorySourceDestinationType)
                                                            <option value="{{ $InventorySourceDestinationType['id'] }}">{{ $InventorySourceDestinationType['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Action At Source Location</label>
                                                    <select class="form-control selecter p-0" name="u_source_action" required id="u_source_action" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Destination Location Type</label>
                                                    <select class="form-control selecter p-0" name="u_destination_location_type" required id="u_destination_location_type" style="color:#222d32">
                                                        @foreach ($InventorySourceDestinationTypes as $InventorySourceDestinationType)
                                                            <option value="{{ $InventorySourceDestinationType['id'] }}">{{ $InventorySourceDestinationType['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Action At Destination Location</label>
                                                    <select class="form-control selecter p-0" name="u_destination_action" required id="u_destination_action" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Controlled / Alloted Source Locations</label>
                                                    <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                    data-target="#uinvsourceLocationModal" required name="u_source_locations_value">
                                                    <input type="hidden" name="u_source_locations[]">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Controlled / Alloted Destination Locations</label>
                                                    <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                    data-target="#uinvdestinationLocationModal" required name="u_destination_locations_value">
                                                    <input type="hidden" name="u_destination_locations[]">
                                                </div>
                                            </div>

                                            

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Employee Location Check</label>
                                                    <select class="form-control selecter p-0" name="u_emp_location_check" required id="u_emp_location_check" style="color:#222d32">
                                                        <option selected disabled >Select Applicable Location</option>
                                                        <option value="s">Source</option>
                                                        <option value="d">Destination</option>
                                                        <option value="n">Not Applicable</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Transaction Expired Status</label>
                                                    <select class="form-control selecter p-0" name="u_transaction_expired_status" required id="u_transaction_expired_status" style="color:#222d32">
                                                        <option value="y">Yes</option>
                                                        <option value="n">No</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_itt_edt" required class="form-control input06 uedt">
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
    <!-- Update Source Location Modal -->
    <div class="modal fade" id="uinvsourceLocationModal" tabindex="-1" role="dialog" aria-labelledby="uinvsourceLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uinvsourceLocationModalLabel">Update Controlled / Alloted Source Locations</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Source Location Modal -->

    <!-- Update Destination Location Modal -->
    <div class="modal fade" id="uinvdestinationLocationModal" tabindex="-1" role="dialog" aria-labelledby="uinvdestinationLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uinvdestinationLocationModalLabel">Update Controlled / Alloted Destination Locations</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Destination Location Modal -->

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
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
        
    </script>
    <script src="{{ asset('assets/custom/inv_transaction_type.js') }}"></script>