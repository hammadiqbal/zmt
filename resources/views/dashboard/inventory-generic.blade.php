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
                <li class="breadcrumb-item active">Item Generic</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Item Generics</h4>
                </div>
                @php
                $ItemGenericSetup = explode(',', $rights->item_generic_setup);
                $add = $ItemGenericSetup[0];
                $view = $ItemGenericSetup[1];
                $edit = $ItemGenericSetup[2];
                $updateStatus = $ItemGenericSetup[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-invgeneric">
                        <i class="mdi mdi-database"></i> Add Item Generic
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-invgeneric" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Item Generic</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_invgeneric" method="post">
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
                                                        <select class="form-contro selecter p-0" id="ig_org" name="ig_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ig_org" id="ig_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ig_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="fields-container">
                                                        <div class="row m-0">
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Enter Item Generic Description</label>
                                                                            <input type="text" class="form-control input-sm" placeholder="Item Generic..." name="ig_description" id="input01">
                                                                        </div>
                                                                        <span class="text-danger" id="ig_description_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Item Category</label>
                                                                            <select class="form-control selecter p-0" name="ig_cat" id="ig_cat" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger" id="ig_cat_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Item Sub-Category</label>
                                                                            <select class="form-control selecter p-0" name="ig_subcat" id="ig_subcat" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger" id="ig_subcat_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Item Type</label>
                                                                            <select class="form-control selecter p-0" name="ig_type" id="ig_type" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger" id="ig_type_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6" id="ig_pm">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Patient Mandatory Status</label>
                                                                            <select class="form-control selecter p-0" name="ig_patientmandatory" required id="ig_patientmandatory" style="color:#222d32">
                                                                                <option value="y">Yes</option>
                                                                                <option value="n">No</option>
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger" id="ig_patientmandatory_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label for="input01">Effective DateTime</label>
                                                                            <input type="text"  name="ig_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                        </div>
                                                                        <span class="text-danger" id="ig_edt_error"></span>
                                                                    </div>
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
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-invgeneric" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Type</th>
                            <th>Generic</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-invgeneric" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Item Generic Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_invgeneric">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">

                                            @if($user->org_id == 0)
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" name="u_ig_org" required id="u_ig_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="hidden" class="form-control u_ig-id" name="u_ig-id">
                                                    <label class="control-label">Update Item Generic Desription</label>
                                                    <input type="text" name="u_ig_description" required class="form-control u_ig_description">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Item Category</label>
                                                    <select class="form-control selecter p-0" name="u_ig_cat" required id="u_ig_cat" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Item Sub Category</label>
                                                    <select class="form-control selecter p-0" name="u_ig_subcat" required id="u_ig_subcat" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Item Type</label>
                                                    <select class="form-control selecter p-0" name="u_ig_type" required id="u_ig_type" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>



                                            <div class="col-md-6" id="u_ig_pm">
                                                <div class="form-group">
                                                    <label class="control-label">Update Patient Mandatory Status </label>
                                                    {{-- <select class="form-control selecter p-0" name="u_ig_consumptiontype" required id="u_ig_consumptiontype" style="color:#222d32">
                                                    </select> --}}
                                                    <select class="form-control selecter p-0" name="u_ig_patientmandatory" required id="u_ig_patientmandatory" style="color:#222d32">
                                                        <option value="y">Yes</option>
                                                        <option value="n">No</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_ig_edt" required class="form-control input06 uedt">
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
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/inv_generic.js') }}"></script>

