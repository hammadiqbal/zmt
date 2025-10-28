<!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            {{-- <footer class="footer">
                © {{ now()->format('Y') }} {{ config('app.name') }}
            </footer> --}}
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->

</div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('assets/lib/plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('assets/lib/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{ asset('assets/js/jquery.slimscroll.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ asset('assets/js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
    <!--stickey kit -->
    <script src="{{ asset('assets/lib/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/sparkline/jquery.sparkline.min.js') }}"></script>

    <!--Custom JavaScript -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/custom.min.js') }}"></script>
    <script src="{{ asset('assets/custom/view_logs.js') }}"></script>

    <!-- datatable -->
    <script src="{{ asset('assets/lib/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <!-- datatable -->

    {{-- <script src="{{ asset('assets/lib/plugins/jquery.steps/js/jquery.steps.min.js') }}"></script> --}}
    <script src="{{ asset('assets/lib/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>

    <!-- start - This is for export functionality only -->
    {{-- <script src="{{ asset('assets/lib/plugins/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/datatables/buttons.print.min.js') }}"></script> --}}

    <!-- end - This is for export functionality only -->

    <script src="{{ asset('assets/lib/plugins/bootstrap-switch/bootstrap-switch.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
    <!-- ============================================================== -->
    <script src="{{ asset('assets/lib/plugins/styleswitcher/jQuery.style.switcher.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    
    <!-- icheck -->
    <script src="{{ asset('assets/lib/plugins/icheck/icheck.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/icheck/icheck.init.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/dropify/dist/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/multiselect/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/nestable/jquery.nestable.js') }}"></script>

    <script src="{{ asset('assets/lib/plugins/tiny-editable/mindmup-editabletable.js') }}"></script>
    <script src="{{ asset('assets/lib/plugins/tiny-editable/numeric-input-example.js') }}"></script>

    {{-- <script src="{{ asset('assets/lib/plugins/cropper/cropper.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/lib/plugins/cropper/cropper-init.js') }}"></script> --}}
   <!-- logs -->
    <div class="modal fade bs-example-modal-xl" id="logs" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">

                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Not Found</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                        <div class="row">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
        </div>
    </div>
   <!-- logs -->
 

</body>

</html>

<script>
    $(document).ready(function() {
        $('select.selecter').select2();
    });

    jQuery('#date-range').datepicker({
        toggleActive: true
    });

</script>
<script type="text/javascript">
    $(document).ready(function() {
        var nestable = $('#nestable').nestable();
        nestable.find('.dd-handle').css('cursor', 'default').off('mousedown');
    });
</script>

