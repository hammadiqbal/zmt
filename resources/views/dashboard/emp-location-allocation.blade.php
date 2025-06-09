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
<style>
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after {
    border: 1px solid black;
}
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after{
    border: 1px solid black;
}
</style>


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Human Resource</li>
                <li class="breadcrumb-item active">Employee Inventory Location Allocation</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Employee Inventory Locations</h4>
                </div>
                @php
                $empLocationAllocation = explode(',', $rights->employee_inventory_location_allocation);
                $add = $empLocationAllocation[0];
                $view = $empLocationAllocation[1];
                $edit = $empLocationAllocation[2];
                $updateStatus = $empLocationAllocation[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 emp-locationAllocation">
                        <i class="mdi mdi-radioactive"></i> Employee Inventory Location Allocation
                    </button>
                </div>
                @endif

            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" style="overflow:auto" id="empLocationAllocation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Employee Inventory Location Allocation</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="emp_locationallocation" method="post">
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
                                                        <select class="form-contro selecter p-0 org_ela" name="org_ela">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0 org_ela" name="org_ela" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger org_ela_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0 site_ela" name="site_ela" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger site_ela_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employees</label>
                                                                    <select class="form-control selecter p-0 emp_ela" name="emp_ela" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger emp_ela_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt edt" name="ela_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger ela_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row duplicate" style="border: 1px solid grey;padding: 30px 20px 0 20px;margin-bottom:10px">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Site</label>
                                                                            <select class="form-control selecter p-0 invSite" name="invSite[]" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger invSite_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Inventory Locations <code class="emp_location">(Select an organization)</code></label>
                                                                            <input type="text" class="form-control cursor-pointer location_ela_value"
                                                                            readonly placeholder="Select Inventory Location" name="location_ela_value">
                                                                            <span class="text-danger location_ela_value_error" ></span>
                                                                            <input type="hidden" name="location_ela[]">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div> 
                                                            
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-center p-1">
                                                    <button type="button" id="addMoreBtn" class="btn btn-success mr-2"> <i class="mdi mdi-plus"></i>  Add More</button>
                                                    <button type="button" id="removeBtn" class="btn btn-danger"> <i class="mdi mdi-minus"></i>  Remove</button>
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

            <!-- Services Modal -->
            <div class="modal fade" id="empLocationAllocationModal" tabindex="-1" role="dialog" aria-labelledby="empLocationAllocationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Inventory Locations</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12">
                                        <table id="emplocationallocationtable" class="table table-striped table-dark" style="color:black;">
                                            <thead class="thead-dark">
                                                <tr style="font-size:14px;">
                                                    <th>Select</th>
                                                    <th>Inventory Location</th>
                                            </thead>
                                            <tbody id="multiServicelocation">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Services Modal -->
            @endif


            @if ($view == 1)
            {{-- <div class="table-responsive m-t-40">
                <table id="view-locationallocation" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee</th>
                            <th>Assigned Locations</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div> --}}
            @if($EmployeeLocationCount > 0)
                <div class="card-body">
                    <hr>
                    <div class="col-md-12 justify-content-center align-items-center">
                        <div class="form-group has-success">
                            <label class="control-label">Select Employee</label>
                            <select class="form-control selecter p-0" id="viewempLocation">
                                <option selected disabled>Select Employee</option>
                                @foreach ($Employees as $Employee)
                                    <option value="{{ $Employee['id'] }}"> {{ $Employee['name'] }}</option>
                                @endforeach
                            </select>

                            <small class="form-control-feedback text-danger"> Select an employee to view assigned Locations.</small>
                            <br>
                            <br>
                        </div>
                    </div>
                    <form id="updateEmpLocation">@csrf<div class="profiletimeline"></div></form>
                </div>
                @endif
            @endif
        </div>
    </div>


    @if ($edit == 1)
    <!-- Update Services Modal -->
    <div class="modal fade" id="uempLocationAllocationModal" tabindex="-1" role="dialog" aria-labelledby="uempLocationAllocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Inventory Locations</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <table id="uemplocationallocationtable" class="table table-striped table-dark" style="color:black;">
                                    <thead class="thead-dark">
                                        <tr style="font-size:14px;">
                                            <th>Select</th>
                                            <th>Inventory Location</th>
                                    </thead>
                                    <tbody id="umultiServicelocation">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Services Modal -->
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
    <script src="{{ asset('assets/custom/emp_inventory_location.js') }}"></script>
