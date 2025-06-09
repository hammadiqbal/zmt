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
                <li class="breadcrumb-item">Human Resource</li>
                <li class="breadcrumb-item active">Employee Documents</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Employee Documentss</h4>
                </div>
                @php
                $cadreSetup = explode(',', $rights->cadre_setup);
                $add = $cadreSetup[0];
                $view = $cadreSetup[1];
                $edit = $cadreSetup[2];
                $updateStatus = $cadreSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-empDocuments">
                        <i class="mdi mdi-human"></i> Add Employee Documents
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-empDocuments" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Employee Documents</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_empDocuments" method="post" enctype="multipart/form-data">
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
                                                                    <label for="input01">Enter Documents Description</label>
                                                                    <input type="text" class="form-control input-sm" required placeholder="Enter Employee Documents" name="document_desc">
                                                                </div>
                                                                <span class="text-danger" id="document_desc_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($user->org_id != 0)
                                                    <div class="userOrganization">
                                                        {{-- <input type="hidden"  id="ed_org" name="ed_org" value='{{ $user->org_id }}'> --}}
                                                        <select class="form-control selecter p-0" id="ed_org" name="ed_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ed_org" required id="ed_org" style="color:#222d32">
                                                                        <option selected disabled >Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ed_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" required name="ed-site" id="ed-site" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="ed-site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Employee</label>
                                                                    <select class="form-control selecter p-0" required name="empid-document" id="empid-document" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="empid-document_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label">Attachments</label>
                                                            <input type="file" name="emp_documents[]" required class="form-control dropify attachments" 
                                                                data-height="100" 
                                                                multiple 
                                                                style="height:80px" />
                                                        </div>
                                                        <div class="file-names" style="margin-top: 10px; font-size: 14px; color: #555;"></div>

                                                        <span class="text-danger" id="emp_documents_error"></span>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="ed_edt" required class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="ed_edt_error"></span>
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
                <table id="view-empDocuments" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee Details</th>
                            <th>Description</th>
                            <th>Employee Documents</th>
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
    {{-- <div class="modal fade bs-example-modal-lg" id="edit-empDocuments" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Employee Documents Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_empDocuments">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                          

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Attachments</label>
                                                    <input type="file" name="u_emp_documents[]" required class="form-control dropify attachments" 
                                                        data-height="100" 
                                                        multiple 
                                                        style="height:80px" />
                                                </div>
                                                <div id="file-names" style="margin-top: 10px; font-size: 14px; color: #555;"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" id="date-format1" name="u_ec_edt" required class="form-control input06 dt edt">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="modal fade bs-example-modal-lg" id="edit-empDocuments" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Employee Documents Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="u_empDocuments" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="ued-id" name="ued-id">

                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Existing Documents</h5>
                                <ul id="existing-documents" style="list-style-type: none; padding: 0;">
                                </ul>
                            </div>
                        </div>
    
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Add New Documents</h5>
                                <input type="file" name="u_emp_documents[]" class="form-control dropify attachments" data-height="100" multiple />
                                <div class="file-names" style="margin-top: 10px; font-size: 14px; color: #555;"></div>
                                <br>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Update Documents Description</label>
                                    <textarea class="form-control" rows="4" name="u_document_desc" id="u_document_desc" ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Update Effective Date & Time</label>
                                    <input type="text" id="date-format1" name="u_ed_edt" class="form-control input06 dt edt" required />
                                </div>
                            </div>
                        </div>
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
        $(document).ready(function () {
            const fileNamesContainer = $('.file-names');
            const dropifyInstance = $('.dropify').dropify();
            $('.attachments').on('change', function () {
                fileNamesContainer.empty(); 
                const files = this.files;
                if (files.length > 0) {
                    Array.from(files).forEach(file => {
                        fileNamesContainer.append(`<p>${file.name}</p>`); 
                    });
                }
            });
            dropifyInstance.on('dropify.afterClear', function (event, element) {
                fileNamesContainer.empty();
            });
        });
        
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });

    </script>
    <script src="{{ asset('assets/custom/emp_documents.js') }}"></script>

