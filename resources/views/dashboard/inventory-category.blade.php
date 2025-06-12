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
                <li class="breadcrumb-item active">Item Category</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Item Categories</h4>
                </div>
                @php
                $ItemCatSetup = explode(',', $rights->item_category);
                $add = $ItemCatSetup[0];
                $view = $ItemCatSetup[1];
                $edit = $ItemCatSetup[2];
                $updateStatus = $ItemCatSetup[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-inventorycategory" data-toggle="modal" data-target="#add-inventorycategory">
                        <i class="mdi mdi-database"></i> Add Item Category
                    </button>
                </div>
                @endif
            </div>
            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-inventorycategory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Item Category</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_inventorycategory" method="post">
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
                                                        <select class="form-contro selecter p-0" id="ic_org" name="ic_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ic_org" id="ic_org" style="color:#222d32">
                                                                        {{-- <option selected disabled >Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach --}}
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ic_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Item Category</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Item Category..." name="inv_cat" id="input01">
                                                                </div>
                                                                <span class="text-danger" id="inv_cat_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Consumption Group</label>
                                                                    <select class="form-control selecter p-0" name="ic_cg" id="ic_cg" style="color:#222d32">
                                                                        <option selected disabled >Select Consumption Group</option>
                                                                        @foreach ($ConsumptionGroups as $ConsumptionGroup)
                                                                            <option value="{{ $ConsumptionGroup['id'] }}"> {{ $ConsumptionGroup['description'] }}</option>
                                                                         @endforeach     
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ic_cg_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Consumption Method</label>
                                                                    <select class="form-control selecter p-0" name="ic_cm" id="ic_cm" style="color:#222d32">
                                                                         <option selected disabled >Select Consumption Method</option>
                                                                         @foreach ($ConsumptionMethods as $ConsumptionMethod)
                                                                            <option value="{{ $ConsumptionMethod['id'] }}"> {{ $ConsumptionMethod['description'] }}</option>
                                                                         @endforeach 
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ic_cm_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="invcat_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="invcat_edt_error"></span>
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
                                                <label class="filterlabel">Consumption Group</label>
                                                <select class="form-control selecter p-0" id="fb_cg"  name="fb_cg" style="color:#222d32">
                                                    <option selected disabled >Select Consumption Group</option>
                                                    @foreach ($ConsumptionGroups as $ConsumptionGroup)
                                                    <option value="{{ $ConsumptionGroup['id'] }}"> {{ $ConsumptionGroup['description'] }}</option>
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
                                                <label class="filterlabel">Consumption Method</label>
                                                <select class="form-control selecter p-0" id="fb_cm" name="fb_cm" style="color:#222d32">
                                                    <option selected disabled >Select Consumption Method</option>
                                                    @foreach ($ConsumptionMethods as $ConsumptionMethod)
                                                        <option value="{{ $ConsumptionMethod['id'] }}"> {{ $ConsumptionMethod['description'] }}</option>
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
                <table id="view-inventorycategory" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Category Details</th>
                            <th>Consumption Group</th>
                            <th>Consumption Method</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-inventorycategory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Item Category Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_inventorycategory">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" required name="u_ic_org" id="u_ic_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="hidden" class="form-control ic-id" name="ic-id">
                                                    <label class="control-label">Update Item Category</label>
                                                    <input type="text" name="u_invcat" required class="form-control u_invcat">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Consumption Group</label>
                                                    <select class="form-control selecter p-0" required name="u_ic_cg" id="u_ic_cg" style="color:#222d32">
                                                        @foreach ($ConsumptionGroups as $ConsumptionGroup)
                                                            <option value="{{ $ConsumptionGroup['id'] }}"> {{ $ConsumptionGroup['description'] }}</option>
                                                        @endforeach     
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Consumption Method</label>
                                                    <select class="form-control selecter p-0" required name="u_ic_cm" id="u_ic_cm" style="color:#222d32">
                                                        @foreach ($ConsumptionMethods as $ConsumptionMethod)
                                                            <option value="{{ $ConsumptionMethod['id'] }}"> {{ $ConsumptionMethod['description'] }}</option>
                                                        @endforeach  
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_ic_edt" required class="form-control input06 uedt">
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
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/inv_cat.js') }}"></script>
