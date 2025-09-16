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
                <li class="breadcrumb-item active">Module</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Modules</h4>
                </div>
                @php
                $ModuleSetup = explode(',', $rights->modules);
                $add = $ModuleSetup[0];
                $view = $ModuleSetup[1];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-module">
                        <i class="mdi mdi-settings"></i> Add Module
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-module" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Module</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_module" method="post">
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
                                                                    <label class="control-label">Parent Module</label>
                                                                    <select class="form-control selecter p-0" name="parent_module"  style="color:#222d32">
                                                                        <option selected disabled value="">Select Parent Module</option>
                                                                        <option>Users</option>
                                                                        <option>Territories</option>
                                                                        <option>Services</option>
                                                                        <option>Cost Centers</option>
                                                                        <option>Key Performance Indicators</option>
                                                                        <option>Organization</option>
                                                                        <option>Activations</option>
                                                                        <option>Human Resource</option>
                                                                        <option>Front Desk Services</option>
                                                                        <option>Patient Medical Record</option>
                                                                        <option>Material Management</option>
                                                                        <option>Finance</option>
                                                                        <option>Reports</option>
                                                                        <option>Settings</option>
                                                                        <option value="__null__">N/A</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="parent_module_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Module</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Module name..." name="module_name">
                                                                </div>
                                                                <span class="text-danger" id="module_name_error"></span>
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
                <table id="view-module" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Code</th>
                            <th>Module</th>
                            <th>Parent</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
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
    <script src="{{ asset('assets/custom/module.js') }}"></script>



