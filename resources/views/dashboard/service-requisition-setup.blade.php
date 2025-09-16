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
                <li class="breadcrumb-item">Service Requisition</li>
                <li class="breadcrumb-item active">Service Requisition Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Service Requisitions</h4>
                </div>
                @php
                $serviceRequisitionSetup = explode(',', $rights->service_requisition_setup);
                // $add = $serviceRequisitionSetup[0];
                $view = $serviceRequisitionSetup[0];
                $edit = $serviceRequisitionSetup[1];
                $updateStatus = $serviceRequisitionSetup[2];
                @endphp

                {{-- @if ($add == 1) --}}
                <!-- <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-serviceRequisitionSetup">
                        <i class="mdi mdi-crosshairs-gps"></i> Add Service Requisition
                    </button>
                </div> -->
                {{-- @endif --}}
            </div>


            {{-- @if ($add == 1) --}}
            <!-- <div class="modal fade bs-example-modal-lg" id="add-serviceRequisitionSetup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Service Requisition</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_serviceRequisitionSetup" method="post">
                            @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="sr_org" name="sr_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                <select class="form-control selecter p-0" name="sr_org" id="sr_org" style="color:#222d32">
                                                                    <option selected disabled >Select Organization</option>
                                                                    @foreach ($Organizations as $Organization)
                                                                        <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="sr_org_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Service</label>
                                                                <select class="form-control selecter p-0" id="sr_service" name="sr_service" style="color:#222d32">
                                                                <option selected disabled >Select Service</option>
                                                                    @foreach ($Services as $Service)
                                                                        <option value="{{ $Service['id'] }}"> {{ $Service['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="sr_service_error"></span>
                                                        </div>
                                                    </div>
                                                </div> 


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Service Request Status</label>
                                                                <select class="form-control selecter p-0" id="sr_status" name="sr_status" style="color:#222d32">
                                                                    <option selected disabled>Select Service Request Status</option>
                                                                        <option value="1">Yes</option>
                                                                        <option value="0">No</option>
                                                                    </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="sr_status_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input04">Enter Service Requisition Description <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <textarea class="form-control" rows="1" id="input04" placeholder="Service Requisition Description" name="sr_description" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="sr_description_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective DateTime</label>
                                                                <input type="text" id="date-format" class="form-control input06 dt edt" name="sr_edt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="sr_edt_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
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
            </div> -->
            {{-- @endif --}}


            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-serviceRequisitionSetup" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Service</th>
                            <th>Request Mandatory</th>
                            <th>Description</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-serviceRequisitionSetup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_serviceRequisitionSetup" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Service Requisition Setup Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" name="servicerequisition_id" class="servicerequisition_id">

                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Organization</label>
                                                        <select class="form-control selecter p-0" id="u_srorg" required name="u_srorg" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Service</label>
                                                                <select class="form-control selecter p-0" id="usr_service" name="usr_service" style="color:#222d32">
                                                                    @foreach ($Services as $Service)
                                                                        <option value="{{ $Service['id'] }}"> {{ $Service['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Service Request Status</label>
                                                                <select class="form-control selecter p-0" required id="usr_status" name="usr_status" style="color:#222d32">
                                                                    <option selected disabled>Select Service Request Status</option>
                                                                        <option value="1">Yes</option>
                                                                        <option value="0">No</option>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input04">Update Service Requisition Description <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <textarea class="form-control" rows="1" id="usr_description"  placeholder="Service Requisition Description" name="usr_description" spellcheck="false"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Effective DateTime</label>
                                                                <input type="text" id="date-format" required class="form-control input06 dt edt" name="usr_edt" placeholder="Select Effective Date & Time">
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
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/service_requisition.js') }}"></script>
