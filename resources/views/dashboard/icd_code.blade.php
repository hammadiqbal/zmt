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
        <div class="col-md-8 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Patient Medical Record</li>
                <li class="breadcrumb-item active">Medical Code</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Medical Codes</h4>
                </div>
                @php
                $ICDCodeSetup = explode(',', $rights->medical_coding);
                $add = $ICDCodeSetup[0];
                $view = $ICDCodeSetup[1];
                $edit = $ICDCodeSetup[2];
                $updateStatus = $ICDCodeSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-icdcode">
                        <i class="mdi mdi-medical-bag"></i> Add Medical Code
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-icdcode" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Medical Code</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_icdcode" method="post">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Medical Descripton</label>
                                                                    <textarea class="form-control" placeholder="Medical Description..." rows="3" name="icd_desc" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="icd_desc_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Medical Code</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Medical Code.." name="icd_code" id="input01">
                                                                </div>
                                                                <span class="text-danger" id="icd_code_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Medical Code Type</label>
                                                                    <select class="form-control selecter p-0" name="icd_codetype" id="icd_codetype" style="color:#222d32">
                                                                        <option selected disabled >Select Medical Code Type</option>
                                                                        <option value="d"> Diagnosis </option>
                                                                        <option value="s"> Symptom </option>
                                                                        <option value="p"> Procedure </option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="icd_codetype_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="icd_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="icd_edt_error"></span>
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
                <table id="view-icdcode" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Medical Code</th>
                            <th>Description</th>
                            <th>Code Type</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-icdcode" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Medical Code Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_icdcode">
                @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input type="hidden" class="form-control icd-id" name="icd-id">
                                                    <label class="control-label">Update Medical Description</label>
                                                    <textarea class="form-control" rows="3" name="uicd_desc" required id="uicd_desc" spellcheck="false"></textarea>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Medical Code</label>
                                                            <input type="text" class="form-control input-sm" required id="uicd_code" name="uicd_code">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update  Code Type</label>
                                                            <select class="form-control selecter p-0" name="uicd_codetype" required id="uicd_codetype" style="color:#222d32">
                                                                <option selected disabled >Select Medical Code Type</option>
                                                                <option value="d"> Diagnosis </option>
                                                                <option value="s"> Symptom </option>
                                                                <option value="p"> Procedure </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="uicd_edt" name="uicd_edt" required class="form-control input06 dt edt">
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
        $('#uicd_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/medical-coding.js') }}"></script>
