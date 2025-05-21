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
                <li class="breadcrumb-item active">Requisition For {{ $Title }}</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Requisition For {{ $Title }}</h4>
                </div>
                @php
                $ICDCodeSetup = explode(',', $rights->icd_coding);
                $add = $ICDCodeSetup[0];
                $view = $ICDCodeSetup[1];
                $edit = $ICDCodeSetup[2];
                $updateStatus = $ICDCodeSetup[3];
                @endphp

            </div>


            @if ($view == 1)
            <div class="table-responsive">
                <table class="view-reqepi table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>MR #</th>
                            <th>Remarks</th>
                            <th>Service Details</th>
                            <th>Billing CC</th>
                            <th>Status</th>
                            <th>Action</th>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-reqi" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Requisition For EPI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_reqi">
                    @csrf
                    <input type="hidden" name="req_epiID" id="req_epiID">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Remarks</label>
                                                <textarea class="form-control" required placeholder="Remarks..." id="u_repi_remarks"  style="height: 100%;" name="u_repi_remarks" rows="3"></textarea>
                                            </div>

                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Effective DateTime</label>
                                                <input type="text" required name="u_repi_edt" class="form-control input06 dt" id="u_repi_edt"  style="height:40px" placeholder="Select Effective Date & Time">
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
                        <button type="submit" class="btn btn-primary">Save</button>
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

