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
                <li class="breadcrumb-item active">Service Rates</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @php
            $serviceRates = explode(',', $rights->service_rates);
            $add = $serviceRates[0];
            $view = $serviceRates[1];
            $edit = $serviceRates[2];
            @endphp

            @if ($add == 1)
            {{-- <div class="col-auto">
                <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-services">
                    <i class="fa fa-map"></i> Add Service Rates
                </button>
            </div> --}}
            @endif
            <form id="fetchservicerates" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row d-flex justify-content-center" >

                            @if($user->org_id != 0)
                            <div class="userOrganization">
                                <select class="form-contro selecter p-0" id="srate_org" name="srate_org">
                                    <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                </select>
                            </div>
                            @else
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="form-group has-custom m-b-5">
                                            <h4 class="control-label card-title">Select Organization</h4>
                                            <select class="form-control selecter l p-0" id="srate_org" name="srate_org" required style="color:#222d32">
                                                <option selected disabled value=''>Select Organization</option>
                                                @foreach ($Organizations as $organization)
                                                <option value="{{ $organization['id'] }}"> {{ $organization['organization'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="form-group has-custom m-b-5">
                                            <h4 class="control-label card-title">Select Site</h4>
                                            <select class="form-control selecter p-0" required id="srate_site" name="srate_site" style="color:#222d32">
                                            </select>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12 d-flex justify-content-center p-2">
                            <button type="submit" class="btn btn-success mr-2"> <i class="mdi mdi-check"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </form>
            

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-servicerate" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Service Rates</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_servicerates" method="post">
                            @csrf
                            <input type="hidden" name="activated_id">
                            <input type="hidden" name="mode_id">
                            <input type="hidden" name="site_id">
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
                                                                    <label class="control-label">Enter Service Unit Cost</label>
                                                                    <input type="number" step="0.01" class="form-control input-sm" placeholder="Unit Cost.." name="rate_unitCost" >
                                                                </div>
                                                                <span class="text-danger" id="rate_unitCost_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Service Billed Amount</label>
                                                                    <input type="number"  step="0.01" class="form-control input-sm" placeholder="Billed Amount.." name="rate_billedAmount">
                                                                </div>
                                                                <span class="text-danger" id="rate_billedAmount_error"></span>
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
                <table id="view-servicerates" class="table table-bordered table-striped" >
                 
                </table>
            </div>
            @endif
        </div>
    </div>


    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-servicerates" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Service Rates Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_servicerates">
                 @csrf
                    <div class="modal-body">
                        <input type="hidden" name="u_servicerate_id" id="u_servicerate_id">
                        <input type="hidden" name="siteId" id="siteId">
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
                                                            <label class="control-label">Enter Service Unit Cost</label>
                                                            <input type="number" required step="0.01" class="form-control input-sm" placeholder="Unit Cost.." name="u_rate_unitCost" id="u_rate_unitCost">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Enter Service Billed Amount</label>
                                                            <input type="number" required step="0.01" class="form-control input-sm" placeholder="Billed Amount.." name="u_rate_billedAmount" id="u_rate_billedAmount">
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
     
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/service_rates.js') }}"></script>


