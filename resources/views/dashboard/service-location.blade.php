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
                <li class="breadcrumb-item">Service Location</li>
                <li class="breadcrumb-item active">Service Location Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Service Locations</h4>
                </div>
                @php
                $serviceLocation = explode(',', $rights->service_location_setup);
                $add = $serviceLocation[0];
                $view = $serviceLocation[1];
                $edit = $serviceLocation[2];
                $updateStatus = $serviceLocation[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-servicelocation">
                        <i class="mdi mdi-crosshairs-gps"></i> Add Service Location
                    </button>
                </div>
                @endif
            </div>


            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-servicelocation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Service Location</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_servicelocation" method="post">
                            @csrf
                        <div class="modal-body">
                            <!-- Row -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input04">Enter Service Location Description</label>
                                                                <textarea class="form-control" rows="1" id="input04" placeholder="Service Location Description" name="service_location" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="service_location_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="sl_org" name="sl_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                <select class="form-control selecter p-0" name="sl_org" id="sl_org" style="color:#222d32">
                                                                    <option selected disabled >Select Organization</option>
                                                                    @foreach ($Organizations as $Organization)
                                                                        <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="sl_org_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
{{-- 
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Site</label>
                                                                <select class="form-control selecter p-0" id="sl_site" name="sl_site" style="color:#222d32">
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="sl_site_error"></span>
                                                        </div>
                                                    </div>
                                                </div> --}}


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Inventory Location Status</label>
                                                                <select class="form-control selecter p-0" id="inv_status" name="inv_status" style="color:#222d32">
                                                                    <option selected disabled>Select Inventory Location Status</option>
                                                                        <option value="1">Yes</option>
                                                                        <option value="0">No</option>
                                                                    </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="inv_status_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective DateTime</label>
                                                                <input type="text" id="date-format" class="form-control input06 dt edt" name="sl_edt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="sl_edt_error"></span>
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
            <div class="table-responsive m-t-40">
                <table id="view-servicelocation" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-servicelocation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_servicelocation" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Cost Center Type Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" name="servicelocation_id" class="servicelocation_id">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Service Location Description</label>
                                                        <textarea class="form-control" rows="1" id="u_sl" name="u_sl" spellcheck="false"></textarea>
                                                    </div>
                                                </div>

                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Organization</label>
                                                        <select class="form-control selecter p-0" id="u_slorg" required name="u_slorg" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                {{-- <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Site</label>
                                                        <select class="form-control selecter p-0" id="u_slsite" required name="u_slsite" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div> --}}

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Inventory Location Status</label>
                                                        <select class="form-control selecter p-0" id="u_invstatus" required name="u_invstatus" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="usl_edt" required class="form-control input06 dt edt">
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
    <script src="{{ asset('assets/custom/service_location.js') }}"></script>
