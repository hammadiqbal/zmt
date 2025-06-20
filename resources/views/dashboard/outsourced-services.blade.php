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
                <li class="breadcrumb-item active">Outsourced Services</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Outsourced Services</h4>
                </div>
                @php
                $OutsourcedServices = explode(',', $rights->outsourced_services);
                $add = $OutsourcedServices[0];
                $view = $OutsourcedServices[1];
                $edit = $OutsourcedServices[2];
                $updateStatus = $OutsourcedServices[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-outsourcedservice">
                        <i class="mdi mdi-plus"></i> Add Outsourced Service
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-outsourcedservice" tabindex="-1" role="dialog" aria-labelledby="addOutsourcedServiceLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="addOutsourcedServiceLabel">Add Outsourced Service</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_outsourcedservice" method="post">
                            @csrf
                            <div class="modal-body">
                                <div class="row">
                                    @if($user->org_id != 0)
                                    <div class="userOrganization">
                                        <select class="form-contro selecter p-0" id="os_org" name="os_org">
                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                        </select>
                                    </div>
                                    @else
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Organization</label>
                                            <select class="form-control selecter p-0" name="os_org" id="os_org"  style="color:#222d32">
                                                <option selected disabled >Select Organization</option>
                                                @foreach ($Organizations as $Organization)
                                                    <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Site</label>
                                            <select class="form-control selecter p-0" name="os_site" id="os_site" style="color:#222d32"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select MR Code</label>
                                            <select class="form-control selecter p-0" name="os_mr" id="os_mr" style="color:#222d32">
                                                <option selected disabled >Select MR #</option>
                                                @foreach ($Patients as $Patient)
                                                    <option value="{{ $Patient['mr_code'] }}"> {{ $Patient['mr_code'] }} - {{ ucwords($Patient['name']) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Referral Site</label>
                                            <select class="form-control selecter p-0" name="os_referralsite" id="os_referralsite"  style="color:#222d32">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Billing CC</label>
                                            <select class="form-control" name="billing_cc" id="billing_cc"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Physician</label>
                                            <select class="form-control" name="physician" id="physician"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Service</label>
                                            <select class="form-control" name="service_id" id="service_id"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Enter Service Description</label>
                                            <input type="text" class="form-control" name="service_desc" id="service_desc">
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service Start Time</label>
                                            <input type="text" class="form-control" placeholder="Select Service Start Date & Time" name="os_starttime" id="os_starttime">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service End Time</label>
                                            <input type="text" class="form-control" placeholder="Select Service End Date & Time" name="os_endtime" id="os_endtime">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service Billed Amount</label>
                                            <input type="number" class="form-control" name="os_billedamount" id="os_billedamount">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                            <textarea class="form-control" placeholder="Enter Remarks" name="os_remarks" id="os_remarks"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Effective Date and Time</label>
                                            <input type="text" class="form-control" name="os_edt" id="date-format"  placeholder="Select Effective Date & Time">
                                        </div>
                                    </div>
                                </div>
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
            <div class="table-responsive m-t-40">
                <table id="view-outsourcedservice" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif

            @if ($edit == 1)
            <div class="modal fade bs-example-modal-lg" id="edit-outsourcedservice" tabindex="-1" role="dialog" aria-labelledby="editOutsourcedServiceLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <form id="update_outsourced_service" method="post">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="editOutsourcedServiceLabel">Update Outsourced Service</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <input type="hidden" name="outsourced_service_id" id="outsourced_service_id">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Organization</label>
                                            <select class="form-control" name="u_org_id" id="u_org_id"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Site</label>
                                            <select class="form-control" name="u_site_id" id="u_site_id"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select MR Code</label>
                                            <select class="form-control" name="u_mr_code" id="u_mr_code"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Referral Site</label>
                                            <select class="form-control" name="u_referral_site" id="u_referral_site"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Billing CC</label>
                                            <select class="form-control" name="u_billing_cc" id="u_billing_cc"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Physician</label>
                                            <select class="form-control" name="u_physician" id="u_physician"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Service</label>
                                            <select class="form-control" name="u_service_id" id="u_service_id"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Enter Service Description</label>
                                            <input type="text" class="form-control" name="u_service_desc" id="u_service_desc">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea class="form-control" name="u_remarks" id="u_remarks"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service Start Time</label>
                                            <input type="text" class="form-control" name="u_start_time" id="u_start_time">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service End Time</label>
                                            <input type="text" class="form-control" name="u_end_time" id="u_end_time">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Service Billed Amount</label>
                                            <input type="number" class="form-control" name="u_billed_amount" id="u_billed_amount">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Effective Date and Time</label>
                                            <input type="text" class="form-control" name="u_effective_timestamp" id="u_effective_timestamp">
                                        </div>
                                    </div>
                                </div>
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


        </div>
    </div>
</div>

@include('partials/footer') 
<script>
    $('#date-format').bootstrapMaterialDatePicker({
        format: 'dddd DD MMMM YYYY - hh:mm A',
        currentDate: new Date()
    });
</script>
<script src="{{ asset('assets/custom/outsource_service.js') }}"></script>