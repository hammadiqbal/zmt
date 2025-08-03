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
                <li class="breadcrumb-item active">Employee Service Allocation</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Employee Service Allocation</h4>
                </div>
                @php
                $empServiceAllocation = explode(',', $rights->employee_services_allocation);
                $add = $empServiceAllocation[0];
                $view = $empServiceAllocation[1];
                $edit = $empServiceAllocation[2];
                $updateStatus = $empServiceAllocation[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 emp-serviceallocation">
                        <i class="mdi mdi-radioactive"></i> Employee Service Allocation
                    </button>
                </div>
                @endif

            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="empserviceallocation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Employee Service Allocation</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="row" id="emp-info-row" style="width:98%;display:none;font-size:13px;border:1px solid black;margin: 0 auto;"></div>
                        <form id="emp_serviceallocation" method="post">
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
                                                    <select class="form-contro selecter p-0" id="org_sa" name="org_sa">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                <select class="form-control selecter p-0" name="org_sa" id="org_sa" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="org_sa_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Site</label>
                                                                <select class="form-control selecter p-0" id="site_sa" name="site_sa" style="color:#222d32">
                                                                </select>
                                                                <span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="site_sa_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Employees</label>
                                                                <select class="form-control selecter p-0" name="emp_sa" id="emp_sa" style="color:#222d32">
                                                                </select>
                                                                <span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="emp_sa_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Services <code id="emp_services">(Select an employee to choose services)</code></label>
                                                                <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                data-target="#serviceAllocationModal" id="service_sa_value" readonly placeholder="Select Services" name="service_sa_value">
                                                                <span class="text-danger" id="service_sa_value_error"></span>
                                                                <input type="hidden" name="service_sa[]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective DateTime</label>
                                                                <input type="text" id="date-format" class="form-control input06 dt edt" name="sa_edt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="sa_edt_error"></span>
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

            <!-- Services Modal -->
            <div class="modal fade" id="serviceAllocationModal" tabindex="-1" role="dialog" aria-labelledby="serviceAllocationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="costCenterModalLabel">Select Services</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12">
                                        <table id="empServicetable" class="table table-striped table-dark" style="color:black;">
                                            <thead class="thead-dark">
                                                <tr style="font-size:14px;">
                                                    <th>
                                                        <div class="custom-control custom-checkbox p-1">
                                                            <input type="checkbox" name="activateallempServiceAllocation" id="selectAllempServiceAllocation" class="custom-control-input">
                                                            <label class="custom-control-label" for="selectAllempServiceAllocation"> </label>
                                                        </div>
                                                    </th>
                                                    <th>Service</th>
                                                    <th>Service Type</th>
                                                    <th>Service Group</th>
                                                    <th>Billing Cost Center</th>
                                                    <th>Performing Cost Center</th>
                                                    <th>Primary Service Modes</th>
                                                    
                                            </thead>
                                            <tbody id="multiService">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                {{-- <div class="row" id="multiService">
                                </div> --}}
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
            <div class="table-responsive m-t-40">
                <table id="view-allocatedservice" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee</th>
                            <th>Service</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-serviceallocation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_serviceallocation" method="post">
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
                                                <input type="hidden" name="u_sa_id" class="u_sa_id">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Employee Name</label>
                                                        <input type="text" id="u_saemp" name="u_saemp" required readonly class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Services</label>
                                                                <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                data-target="#uservicesModal" readonly required name="uservice_value">
                                                                <input type="hidden" name="u_saservice[]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="usa_edt" required class="form-control input06 dt usa_edt">
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

    <!-- Update Services Modal -->
    <div class="modal fade" id="uservicesModal" tabindex="-1" role="dialog" aria-labelledby="uservicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uservicesModalLabel">Update Services</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <table id="umultiserviceTable" class="table table-striped table-dark" style="color:black;">
                                    <thead class="thead-dark">
                                        <tr style="font-size:14px;">
                                            <th>Select</th>
                                            <th>Service</th>
                                            <th>Service Type</th>
                                            <th>Service Group</th>
                                            <th>Billing Cost Center</th>
                                            <th>Performing Cost Center</th>
                                            <th>Primary Service Modes</th>
                                            
                                    </thead>
                                    <tbody id="umultiService">
                                    </tbody>
                                </table>
                            </div>
                        {{-- <div class="row" id="umultiService">
                           
                        </div> --}}
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
    <script src="{{ asset('assets/custom/emp_service_allocation.js') }}"></script>