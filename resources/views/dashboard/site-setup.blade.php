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
                <li class="breadcrumb-item active">Site</li>
            </ol>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Sites</h4>
                </div>
                @php
                $siteSetup = explode(',', $rights->site_setup);
                $add = $siteSetup[0];
                $view = $siteSetup[1];
                $edit = $siteSetup[2];
                $updateStatus = $siteSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add_site">
                        <i class="mdi mdi-hospital-building "></i> Add Site
                    </button>
                </div>
                @endif
            </div>


            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-site" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Site</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_site" method="post" enctype="multipart/form-data">
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
                                                                    <label for="input01">Enter Site Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Site Name..." name="site_name" id="input01"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Organization</label>
                                                                    <select class="form-control selecter p-0" name="site_org" id="site_org" style="color:#222d32">
                                                                        @if ($Organizations->count() == 0)
                                                                        <option value="">No organizations available</option>
                                                                        @else
                                                                            @foreach ($Organizations as $Organization)
                                                                                <option value="{{ $Organization['id'] }}">{{ $Organization['organization'] }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input0111">Enter Old Site Code <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Old Site Code..." name="old_siteCode" id="input0111"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="old_siteCode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Remarks..." id="input03" name="site_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="site_remarks_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Address</label>
                                                                    <textarea class="form-control" rows="1" placeholder="Site Address..." id="input04" name="site_address" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="site_address_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Province</label>
                                                                    <select class="form-control selecter p-0" name="site_province" id="province_name" style="color:#222d32">
                                                                    {{-- <option value="" selected disabled> Select Province</option> --}}
                                                                        {{-- @foreach ($ProvinceData as $Province)
                                                                        <option value="{{ $Province['province_id'] }}"> {{ $Province['province_name'] }}</option>
                                                                        @endforeach --}}
                                                                        @foreach ($ProvinceData as $Province)
                                                                            <option option value="{{ $Province['province_id'] }}" {{ $Province['province_name'] == 'Sindh' ? 'selected' : '' }}>
                                                                                {{ $Province['province_name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    


                                                                </div>
                                                                <span class="text-danger" id="site_province_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Division</label>
                                                                    <select class="form-control selecter p-0" name="site_division" id="division_name" style="color:#222d32">
                                                                        {{-- <option selected value=''>Select Division</option> --}}
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="site_division_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">District</label>
                                                                    <select class="form-control selecter p-0" name="site_district" id="district_name" style="color:#222d32">
                                                                        {{-- <option selected  value=''>Select District</option> --}}
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="site_district_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input08">Enter Site Admin Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Admin Name..." name="site_person_name" id="input08"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_person_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input09">Enter Focal Peson Email</label>
                                                                    <input type="email" class="form-control input-sm" placeholder="Admin Email..." name="site_person_email" id="input09"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_person_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input10">Enter Website URL</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Website URL.." name="site_website" id="input10"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_website_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input11">Enter GPS Coordinates <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="GPS Coordinates.." name="site_gps" id="input11"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_gps_error"></span>

                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input12">Enter Cell #</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Cell #.." name="site_cell" id="input12"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input13">Enter Landline #</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Landline #.." name="site_landline" id="input13"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="site_landline_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input13">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt" name="site_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="site_edt_error"></span>
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
                <table id="view-site" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Address</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-site" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Site</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="edit_site" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Site Name</label>
                                                            <input type="hidden" class="form-control u-site-id" name="u-siteid">
                                                            <input type="text" class="form-control input-sm u_site_name" required name="u_site_name" id="input01">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0 u_site_org" name="u_site_org" required id="u_site_org" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Old Site Code <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_oldcode" name="u_oldcode" id="input01">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <textarea class="form-control u_site_remarks"  rows="1" id="input03"  name="u_site_remarks" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Site Address</label>
                                                            <textarea class="form-control u_site_address" rows="1" id="input04" required name="u_site_address" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Province</label>
                                                            <select class="form-control selecter p-0 u_site_province" name="u_site_province" required id="u_site_province" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Division</label>
                                                            <select class="form-control selecter p-0 u_site_division" name="u_site_division" required id="u_site_division" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update District</label>
                                                            <select class="form-control selecter p-0 u_site_district" name="u_site_district" required id="u_site_district" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Site Admin Name</label>
                                                            <input type="text" class="form-control input-sm u_site_person_name" required name="u_site_person_name" id="input08">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Site Admin Email</label>
                                                            <input type="email" class="form-control input-sm u_site_person_email" required name="u_site_person_email" id="input09">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Website URL</label>
                                                            <input type="text" class="form-control input-sm u_site_website" required name="u_site_website" id="input10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update GPS Coordinates <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_site_gps"  name="u_site_gps" id="input11">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Cell #</label>
                                                            <input type="text" class="form-control input-sm u_site_cell" required name="u_site_cell" id="input12">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Landline #</label>
                                                            <input type="text" class="form-control input-sm u_site_landline" required name="u_site_landline" id="input13">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                            <input type="text" id="date-format1" class="form-control input06 dt u_site_edt" required name="u_site_edt" placeholder="Update Effective Date & Time">
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


    @if ($view == 1)
    <div class="modal fade bs-example-modal-lg" id="site-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body pt-0">
                    <div class="row">
                        {{-- <div class="col-lg-12 col-xlg-12 col-md-12">
                            <div class="card">
                                <div class="row">
                                    <img src="" id="orgBanner" alt="Site Banner" class="img-square" style="width: 100%;height: 150px;">
                                </div>
                            </div>
                        </div> --}}
                        <!-- Column -->
                        <div class="col-lg-4 col-xlg-3 col-md-5">
                            <div class="card">
                                <div class="card-body">
                                    <center class="m-t-30">
                                        <img src="" id="sitelogo" alt="Site Name" class="img-square" width="150">
                                        <h5 class="card-title m-t-10" id="sitename"></h5>
                                        <h6 class="card-title m-t-10" id="siteorg"></h6>

                                    </center>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Old Site Code </small>
                                    <h6 id="oldSiteCode"></h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Email address </small>
                                    <h6 id="siteemail"></h6>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                        <!-- Column -->
                        <div class="col-lg-8 col-xlg-9 col-md-8">
                            <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>Province</strong>
                                                <br>
                                                <p class="text-muted" id="siteprovince"></p>
                                            </div>
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>Division</strong>
                                                <br>
                                                <p class="text-muted" id="sitedivision"></p>
                                            </div>
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>District</strong>
                                                <br>
                                                <p class="text-muted" id="sitedistrict"></p>
                                            </div>
                                        </div>

                                        <hr>
                                        <p><b>Site Admin Name:</b> <span id="sitepersonname"></span></p>
                                        <hr>
                                        <p><b>Website:</b> <span id="sitewebsite"><a></a></span></p>
                                        <hr>
                                        <p><b>Site Address:</b> <span id="siteaddress"></span></p>
                                        <hr>
                                        <p><b>Remarks:</b> <span  id="siteremarks"></span></p>
                                        <hr>
                                        <p><b>Contact #:</b> <span id="sitecontact"></span></p>
                                        <hr>
                                    </div>
                            </div>
                        </div>
                        <!-- Column -->


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
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
        $(document).ready(function() {
            $('.dropify').dropify();
        });
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>

<script src="{{ asset('assets/custom/site.js') }}"></script>