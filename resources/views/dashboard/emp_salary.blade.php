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
                <li class="breadcrumb-item active">Employee Salary</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Employee Salaries</h4>
                </div>
                @php
                $empSalarySetup = explode(',', $rights->employee_salary_setup);
                $add = $empSalarySetup[0];
                $view = $empSalarySetup[1];
                $edit = $empSalarySetup[2];
                $updateStatus = $empSalarySetup[3];
                @endphp
                
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add_empSalary">
                        <i class="mdi mdi-human"></i> Allocate Salary to New Employee
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-empSalary" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Allocate Salary to New Employee</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                {{-- <span aria-hidden="true">&times;</span> --}}
                            </button>
                        </div>
                        <div class="row" id="emp-info-row" style="width:98%;display:none;font-size:13px;border:1px solid black;margin: 0 auto;"></div>
                        <form id="add_empSalary" method="post">
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
                                                        <select class="form-contro selecter p-0" id="es-org" name="es-org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0 es-org" name="es-org" id="es-org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="es-org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="es-site" id="es-site" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="es-site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee</label>
                                                                    <select class="form-control selecter p-0" name="emp-id" id="show_emp" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="emp-id_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @foreach($payrollAdditions as $addition)
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>{{ $addition->name }}</label>
                                                                    <input type="number" placeholder="20000" min="0" class="form-control input-sm amount" name="{{strtolower(str_replace(' ', '_', $addition->name))}}" id="{{strtolower(str_replace(' ', '_', $addition->name))}}">
                                                                    <small class="amount_conversion"></small>
                                                                </div>
                                                                <span class="text-danger" id="{{strtolower(str_replace(' ', '_', $addition->name))}}_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach

                                                    @foreach($payrollDeductions as $deduction)
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>{{ $deduction->name }}</label>
                                                                    <input type="number" min="0" placeholder="10000" class="form-control input-sm amount" name="{{strtolower(str_replace(' ', '_', $deduction->name))}}" id="{{strtolower(str_replace(' ', '_', $deduction->name))}}">
                                                                    <small class="amount_conversion"></small>
                                                                </div>
                                                                <span class="text-danger" id="{{strtolower(str_replace(' ', '_', $deduction->name))}}_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks" rows="1" name="salary_remarks"  id="input03" spellcheck="false"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="salary_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="salary_edt_error"></span>
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
                <table id="view-empSalary" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee Details</th>
                            <th>Additions</th>
                            <th>Deductions</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-empSalary" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Employee Salary Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="u_empSalary">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <input type="hidden" id="usalary-id" name="usalary-id">
                                        <div class="row">
                                            
                                            @foreach($payrollAdditions as $addition)
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label>{{ $addition->name }}</label>
                                                            <input type="number" required class="form-control input-sm amount" name="u_{{ strtolower(str_replace(' ', '_', $addition->name)) }}" id="u_{{ strtolower(str_replace(' ', '_', $addition->name)) }}">
                                                            <small class="amount_conversion"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach

                                            @foreach($payrollDeductions as $deduction)
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label>{{ $deduction->name }}</label>
                                                            <input type="number" required class="form-control input-sm amount" name="u_{{ strtolower(str_replace(' ', '_', $deduction->name)) }}" id="u_{{ strtolower(str_replace(' ', '_', $deduction->name)) }}">
                                                            <small class="amount_conversion"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="usalary_edt" required class="form-control input06 dt edt">
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
    <script src="{{ asset('assets/custom/emp_salary.js') }}"></script>
