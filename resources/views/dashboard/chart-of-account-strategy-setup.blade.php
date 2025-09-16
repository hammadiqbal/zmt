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
<style>
@keyframes blink {0%{ opacity: 1;}50% {opacity: 0.5;}100% {opacity: 1;}}.blink {animation: blink 8s linear infinite;}
#basic-form{width: 97%;margin: 0 auto 15px;}
.cloned-form{padding: 10px;border: 1px solid grey;}
</style>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Chart Of Account Strategy</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Chart Of Account Strategies Setup</h4>
                </div>
                @php
                $ChartOfAccountStrategySetup = explode(',', $rights->chart_of_accounts_strategy_setup);
                $add = $ChartOfAccountStrategySetup[0];
                $view = $ChartOfAccountStrategySetup[1];
                $edit = $ChartOfAccountStrategySetup[2];
                $updateStatus = $ChartOfAccountStrategySetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto" id="strategy_btn">
                    @if(!$Organizations->isEmpty())
                    <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-accountStrategySetup">
                        <i class="mdi mdi-bank"></i> Setup Chart Of Account Strategy
                    </button>
                    @else
                    <div class="alert alert-info" role="alert">
                        <b>Account strategy setup completed for all Organizations.</b>
                    </div>
                    @endif
                </div>
                @endif

            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-accountStrategySetup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Setup Chart Of Account Strategy</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_accountStrategySetup" method="post">
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
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ass_org" id="ass_org" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="ass_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Account Level</label>
                                                                    <select class="form-control selecter p-0" name="ass_level" id="ass_level" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Account Level</option>
                                                                        @foreach ($AccountStrategyLevels as $AccountStrategyLevel)
                                                                            <option value="{{ $AccountStrategyLevel['id'] }}"> {{ $AccountStrategyLevel['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="ass_level_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="ass_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="ass_edt_error"></span>
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
                <table id="view-accountStrategySetup" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Account Level</th>
                            <th>Account Levels</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-accountStrategySetup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Chart Of Account Strategy Setup Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_accountStrategySetup" method="post">
                @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="hidden" class="form-control ass-id" name="ass-id">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0 u_ass_org" required name="u_ass_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Account Level</label>
                                                    <select class="form-control selecter p-0 u_ass_level" required name="u_ass_level" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_ass_edt" required class="form-control input06 dt edt">
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


    @if ($add == 1)
    <div class="modal fade bs-example-modal-lg" id="setup-accountLevel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Setup Account Levels</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="setup_accountLevel" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row" id="compulsory-form">

                                    </div>
                                    <div class="row" id="basic-form">
                                    </div>
                                    <div class="row" id="compulsory-form-one">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif


    @if ($view == 1)
    <div class="modal fade bs-example-modal-lg" id="view-accountLevel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="height:95vh;overflow-y: auto;overflow-x: hidden;">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">All Account Levels</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="nestable" class="dd" style="margin: 0 auto;">
                                    <ol class="dd-list"></ol>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="update-accountLevel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Account Levels</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_accountLevel" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row" id="u-compulsory-form">

                                    </div>
                                    <div class="row " id="u-basic-form">

                                    </div>
                                    <div class="row" id="u-compulsory-form-one">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
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
    <style>.bootstrap-tagsinput{width: 100%;padding: 8px;}.bootstrap-tagsinput input{width:100% !important;}.bootstrap-tagsinput .tag{padding:4px;margin: 1px 2px 1px 0px;}</style>
    <script>
        $('#date-format').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
    </script>
    <script src="{{ asset('assets/custom/chart_of_account_strategy_setup.js') }}"></script>

