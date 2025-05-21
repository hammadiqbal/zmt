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
                <li class="breadcrumb-item active">Employee Qualification Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">Employee Qualification Setup</h4>
                </div>
                @php
                $empQualificationSetup = explode(',', $rights->employee_qualification_setup);
                $add = $empQualificationSetup[0];
                $view = $empQualificationSetup[1];
                $edit = $empQualificationSetup[2];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 addqualificationSetup">
                        <i class="mdi mdi-human"></i> Add New Employee Qualifications
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-qualificationSetup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Employee Qualification</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_qualificationSetup" method="post">
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
                                                        <select class="form-contro selecter p-0" id="eq-org" name="eq-org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="eq-org" id="eq-org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="eq-org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0 eq-site" name="eq-site" id="eq-site" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="eq-site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee</label>
                                                                    <select class="form-control selecter p-0 emp-id" name="emp-id" id="show_emp" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="emp-id_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row duplicate" style="border: 1px solid grey;padding: 10px;margin-bottom:10px">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Qualification Level</label>
                                                                    <select class="form-control selecter p-0 emp-ql" name="emp-ql[]" style="color:#222d32">
                                                                        <option selected disabled >Select Qualification Level</option>
                                                                        @foreach ($QualificationLevels as $QualificationLevel)
                                                                            <option value="{{ $QualificationLevel['id'] }}"> {{ $QualificationLevel['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger emp-ql_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Qualification Date</label>
                                                                    <input type="text" name="q_date[]" class="form-control input06 dt qd" placeholder="Select Date Of Qualification">
                                                                </div>
                                                                <span class="text-danger q_date_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Qualification Description</label>
                                                                    <textarea class="form-control" rows="2" name="qualification[]" id="input03" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger qualification_error"></span>
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
                @if($EmployeeQualificationCount > 0)
                <div class="card-body">
                    <hr>
                    <div class="col-md-12 justify-content-center align-items-center">
                        <div class="form-group has-success">
                            <label class="control-label">Select Employee</label>
                            {{-- <select class="form-control custom-select selecter select-width-50 mx-auto" id="viewempQualification"> --}}
                            <select class="form-control selecter p-0" id="viewempQualification" style="color:#222d32">
                                <option selected disabled>Select Employee</option>
                                @foreach ($Employees as $Employee)
                                    <option value="{{ $Employee['id'] }}"> {{ $Employee['name'] }}</option>
                                @endforeach
                            </select>
                            <small class="form-control-feedback text-danger"> Select an employee to view qualifications.</small>
                        </div>
                    </div>
                    <form id="updateQualification">@csrf<div class="profiletimeline"></div></form>

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
        $('.qd').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
    </script>
    <script src="{{ asset('assets/custom/emp_qualification.js') }}"></script>

