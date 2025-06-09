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
                <li class="breadcrumb-item">Human Resource</li>
                <li class="breadcrumb-item active">Cost Center Activation</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Allocated Employee Cost Center</h4>
                </div>
                @php
                $empCCAllocation = explode(',', $rights->employee_cost_center_allocation);
                $add = $empCCAllocation[0];
                $view = $empCCAllocation[1];
                $edit = $empCCAllocation[2];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 addEmpCC">
                        <i class="mdi mdi-human"></i> Allocate New Employee Cost Center
                    </button>
                </div>
                @endif
            </div>


            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-empcc" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Allocate Employee Cost Center</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_empCC" method="post">
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
                                                        <select class="form-contro selecter p-0" id="empcc_org" name="empcc_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="empcc_org" id="empcc_org" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="empcc_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Head Count Site</label>
                                                                    <select class="form-control selecter p-0" name="emp_headcountsite" id="emp_headcountsite" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="emp_headcountsite_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee</label>
                                                                    <select class="form-control selecter p-0 emp-id" name="emp-id" id="show_emp" style="color:#222d32">
                                                                        <option selected disabled >Select Employee</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="emp-id_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt edt" name="empCC-ed" placeholder="Select Effective Date & Time">
                                                                    {{-- <input type="text" id="date-format" class="form-control input06 dt edt" name="empCC-ed[]" placeholder="Select Effective Date & Time"> --}}
                                                                </div>
                                                                <span class="text-danger" id="empCC-ed_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row duplicate" style="border: 1px solid grey;padding: 30px 20px 0 20px;margin-bottom:10px">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">

                                                            <div class="col-md-4">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Site</label>
                                                                            <select class="form-control selecter p-0 empcc_site" name="empcc_site[]" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger empcc_site_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-5">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Cost Center</label>
                                                                            <select class="form-control selecter p-0 emp_costcenter" name="emp_costcenter[]" style="color:#222d32">
                                                                            </select>
                                                                            
                                                                        </div>
                                                                        <span class="text-danger emp_costcenter_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="col-md-3">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input0112">CC Percentage</label>
                                                                            <input type="number" class="form-control input-sm" placeholder="50" name="cc_percent[]" max="100" min="0" id="input0112">
                                                                        </div>
                                                                        <span class="text-danger cc_percent_error"></span>
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
            @endif

            @if ($view == 1)
                @if($EmployeeCCCount > 0)
                <div class="card-body">
                    <hr>
                    <div class="col-md-12 justify-content-center align-items-center">
                        <div class="form-group has-success">
                            <label class="control-label">Select Employee</label>
                            <select class="form-control selecter p-0" id="viewempCC">
                                <option selected disabled>Select Employee</option>
                                @foreach ($Employees as $Employee)
                                    <option value="{{ $Employee['id'] }}"> {{ $Employee['name'] }}</option>
                                @endforeach
                            </select>

                            <small class="form-control-feedback text-danger"> Select an employee to view assigned Cost Centers.</small>
                            <br>
                            <br>
                        </div>
                    </div>
                    <form id="updateEmpCC">@csrf<div class="profiletimeline"></div></form>
                </div>
                @endif
            @endif

        </div>
    </div>




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
    </script>
    <script src="{{ asset('assets/custom/emp_cc_allocation.js') }}"></script>