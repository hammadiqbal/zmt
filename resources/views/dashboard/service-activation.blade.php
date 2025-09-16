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
.show-tick .btn{background: none !important;padding: 4px 20px 2px 5px;border:1px solid #d9d9d9;height: calc(2.45rem + 2px);}
.smode button.btn.dropdown-toggle.btn-default {
    border: 1px solid rgba(0,0,0,.15);
}
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after {
    border: 1px solid black;
}
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after{
    border: 1px solid black;
}
</style>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Service Activation</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Activated Service</h4>
                </div>
                @php
                $serviceActivation = explode(',', $rights->service_activation);
                $add = $serviceActivation[0];
                $view = $serviceActivation[1];
                $edit = $serviceActivation[2];
                $updateStatus = $serviceActivation[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 service_activation">
                        <i class="mdi mdi-radioactive"></i> Activate Service
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="service_activation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Activate Service</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="activate_service" method="post">
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
                                                        <select class="form-contro selecter p-0" id="act_s_org" name="act_s_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="act_s_org" id="act_s_org" style="color:#222d32">
                                                                        <option selected disabled >Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="act_s_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" id="act_s_site" name="act_s_site" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="act_s_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service    
                                                                         <code id="siteservice"></code>
                                                                    </label>
                                                                    <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                    data-target="#servicesModal" readonly disabled placeholder="Select Services" id="service_name" name="service_name">
                                                                    <span class="text-danger" id="service_name_error"></span>
                                                                    <input type="hidden" id="act_s_service" name="act_s_service[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

               

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Billing Cost Center <code class="activationMsg"></code></label>
                                                                    <input type="text" class="form-control cursor-pointer costcenterModal"
                                                                     readonly placeholder="Select Billing Cost Centers" id="s_billing_cc" name="s_billing_cc">
                                                                    <span class="text-danger" id="s_billing_cc_error"></span>
                                                                    <input type="hidden" id="act_s_billingcc" name="act_s_billingcc[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Performing Cost Center <code class="activationMsg"></code></label>
                                                                    <input type="text" class="form-control cursor-pointer costcenterModal"  
                                                                     readonly placeholder="Select Performing Cost Centers" id="s_performing_cc" name="s_performing_cc">
                                                                    <span class="text-danger" id="s_performing_cc_error"></span>
                                                                    <input type="hidden" id="act_s_performingcc" name="act_s_performingcc[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Modes <code class="s_modeMsg"></code></label>

                                                                    <input type="text" class="form-control cursor-pointer costcenterModal"  
                                                                     readonly placeholder="Select Service Modes" id="servicemode_value" name="servicemode_value">
                                                                    <span class="text-danger" id="servicemode_value_error"></span>
                                                                    <input type="hidden" id="act_s_mode" name="act_s_mode[]">

                                                                    {{-- <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                    data-target="#serviceModesModal" readonly placeholder="Select Service Modes" name="servicemode_value">
                                                                    <span class="text-danger" id="servicemode_value_error"></span>

                                                                    <input type="hidden" name="act_s_mode[]"> --}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06" name="a_service_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="a_service_edt_error"></span>
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
             <div class="modal fade" id="servicesModal" tabindex="-1" role="dialog" aria-labelledby="servicesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="servicesModalLabel">Select Services</h5>
                            {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button> --}}
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12">
                                        <table id="serviceTable" class="table table-striped table-dark" style="color:black;">
                                            <thead class="thead-dark">
                                                <tr style="font-size:14px;">
                                                    <th>
                                                        <div class="custom-control custom-checkbox p-1">
                                                            <input type="checkbox" name="activateallServices" id="selectAll" class="custom-control-input">
                                                            <label class="custom-control-label" for="selectAll"> </label>
                                                        </div>
                                                    </th>
                                                    <th>Service</th>
                                                    <th>Service Type</th>
                                                    <th>Service Group</th>
                                            </thead>
                                            <tbody id="allServices">
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

            <!-- Cost Center Modal -->
            <div class="modal fade" id="costcenterModal" tabindex="-1" role="dialog" aria-labelledby="costcenterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="costcenterModalLabel">Select Cost Centers & Service Modes</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            {{-- <div class="container-fluid"> --}}
                                <div class="row">
                                    <div class="col-12">
                                        <table id="costcenterTable" class="table table-striped table-dark" style="color:black;">
                                            <thead class="thead-dark">
                                                <tr style="font-size:14px;">
                                                    <th>Service Type<br>
                                                        Service Group<br>
                                                        Service
                                                    </th>
                                                    <th>Billing Cost Center</th>
                                                    <th>Performing Cost Center</th>
                                                    <th>Primary Service Modes</th>
                                                    
                                            </thead>
                                            <tbody id="allCostCenters">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            {{-- </div> --}}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="serviceCCbtn" data-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Cost Center Modal -->

            <!-- Service Modes Modal -->
            <div class="modal fade" id="serviceModesModal" tabindex="-1" role="dialog" aria-labelledby="serviceModesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="serviceModesModalLabel">Select Service Modes</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    @foreach ($ServiceModes as $ServiceMode)
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox mb-3">
                                            <input type="checkbox" name="selectedSM[]" data-id="{{ $ServiceMode['id'] }}" data-name="{{ $ServiceMode['name'] }}" class="custom-control-input" id="sm_{{ $ServiceMode['id'] }}">
                                            <label class="custom-control-label" for="sm_{{ $ServiceMode['id'] }}">{{ $ServiceMode['name']}}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Service Modes Modal -->
            @endif

            @if ($view == 1)
            <div class="row ">
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
                                                <label class="filterlabel">Sites</label>
                                                <select class="form-control selecter p-0" id="fb_site" style="color:#222d32">
                                                    <option selected disabled >Select Site</option>
                                                    @foreach ($Sites as $Site)
                                                        <option value="{{ $Site['id'] }}"> {{ $Site['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Cost Centers</label>
                                                <select class="form-control selecter p-0" id="fb_cc" style="color:#222d32">
                                                    <option selected disabled >Select Cost Center</option>
                                                    @foreach ($CostCenters as $CostCenter)
                                                        <option value="{{ $CostCenter['id'] }}"> {{ $CostCenter['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Service Types</label>
                                                <select class="form-control selecter p-0" id="fb_st" style="color:#222d32">
                                                    <option selected disabled >Select Service Type</option>
                                                    @foreach ($ServiceTypes as $ServiceType)
                                                        <option value="{{ $ServiceType['id'] }}"> {{ $ServiceType['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Service Groups</label>
                                                <select class="form-control selecter p-0" id="fb_sg" style="color:#222d32">
                                                    <option selected disabled >Select Service Group</option>
                                                    @foreach ($ServiceGroups as $ServiceGroup)
                                                        <option value="{{ $ServiceGroup['id'] }}"> {{ $ServiceGroup['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group has-custom m-b-5">
                                                <label class="filterlabel">Service Modes</label>
                                                <select class="form-control selecter p-0" id="fb_sm" style="color:#222d32">
                                                    <option selected disabled >Select Service Mode</option>
                                                    @foreach ($RawServiceModes as $RawServiceMode)
                                                        <option value="{{ $RawServiceMode['id'] }}"> {{ $RawServiceMode['name'] }}</option>
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
                <table id="view-serviceactivation" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Service Type<br>Sevice Group<br>Service</th>
                            <th>Billing Cost Centers</th>
                            <th>Performing Cost Centers</th>
                            <th>Primary Service Modes</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-serviceactivation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_serviceactivation" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Service Activation Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" name="u_aservice_id" class="u_service_id">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Billing Cost Center</label>
                                                        <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                        data-target="#userviceBillingccModal" required name="ubillingcc_value">
                                                        <input type="hidden" name="ubillingcc_id[]">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Performing Cost Center</label>
                                                        <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                        data-target="#uperformingPerformingccModal" required name="uperformingcc_value">
                                                        <input type="hidden" name="uperformingcc_id[]">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Service</label>
                                                        <select class="form-control selecter p-0" id="u_service" required name="u_service" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Service Modes</label>
                                                                <input type="text" class="form-control cursor-pointer" data-toggle="modal" 
                                                                data-target="#userviceModesModal" required name="uservicemode_value">
                                                                <input type="hidden" name="u_ssm[]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="us_edt" required class="form-control input06 dt edt">
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

    <!-- Update Service Modes Modal -->
    <div class="modal fade" id="userviceModesModal" tabindex="-1" role="dialog" aria-labelledby="userviceModesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userviceModesModalLabel">Update Service Modes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            @foreach ($ServiceModes as $ServiceMode)
                            <div class="col-md-3">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" name="uselectedSM[]" data-id="{{ $ServiceMode['id'] }}" data-name="{{ $ServiceMode['name'] }}" class="custom-control-input" id="usm_{{ $ServiceMode['id'] }}">
                                    <label class="custom-control-label" for="usm_{{ $ServiceMode['id'] }}">{{ $ServiceMode['name']}}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Service Modes Modal -->

    <!-- Update Billing CC Modal -->
    <div class="modal fade" id="userviceBillingccModal" tabindex="-1" role="dialog" aria-labelledby="userviceBillingccModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userviceBillingccModalLabel">Update Billing COst Center</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Billing CC Modal -->

    <!-- Update Performing CC Modal -->
    <div class="modal fade" id="uperformingPerformingccModal" tabindex="-1" role="dialog" aria-labelledby="uperformingPerformingccModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uperformingPerformingccModalLabel">Update Performing Cost Center</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Update Performing CC Modal -->

    @endif


    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->

    <script>
        $('.selectpicker').selectpicker();
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/service_activation.js') }}"></script>

