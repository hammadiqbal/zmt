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
                <li class="breadcrumb-item active">Division</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Divisions</h4>
                </div>
                @php
                $divisionSetup = explode(',', $rights->divisions);
                $add = $divisionSetup[0];
                $view = $divisionSetup[1];
                $edit = $divisionSetup[2];
                $updateStatus = $divisionSetup[3];
                @endphp

            @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-division">
                        <i class="fa fa-map-marker"></i> Add Division
                    </button>
                </div>
            </div>
            @endif

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-division" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Division</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>

                        <form id="add_division" method="post">
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
                                                                    <label class="control-label">Enter Division Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Enter Division Name.." id="input01" name="division" ><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="division_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Province</label>
                                                                    <select class="form-control selecter p-0" required name="province" id="p_name" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Province</option>
                                                                        @foreach ($ProvinceData as $Province)
                                                                        <option value="{{ $Province['id'] }}"> {{ $Province['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="province_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="dedt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                            <span class="text-danger" id="dedt_error"></span>

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
                                                <label class="filterlabel">Provinces</label>
                                                <select class="form-control selecter p-0" id="fb_province" style="color:#222d32">
                                                    <option selected disabled >Select Province</option>
                                                    @foreach ($ProvinceData as $Province)
                                                        <option value="{{ $Province['id'] }}"> {{ $Province['name'] }}</option>
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
                <table id="view-division" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Division</th>
                            <th>Province</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-division" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_division" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Division Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <form action="#" class="floating-labels form-horizontal ">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Division Name</label>
                                                        <input type="hidden" class="form-control division-id" name="division-id">
                                                        <input type="text" class="form-control division-name" required name="u_division">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Province</label>
                                                        <select class="form-control selecter p-0 province_name"  required name="ud_province" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="u_edt" required class="form-control input06 dt edt" >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });

    </script>
    <script src="{{ asset('assets/custom/division.js') }}"></script>