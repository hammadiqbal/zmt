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

<style>.icheckbox_line-blue, .iradio_line-blue{width:80%;margin: 0 auto;}</style>
<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Rights Setup</li>
            </ol>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h2 class="card-title">{{ ucwords($role->role) }}</h2>
                    <label>{{ ucwords($role->remarks) }}</label>
                    <hr>
                </div>
            </div>
            <form id="rights_setup" method="post">
                @csrf
                <input type="hidden" id="role_id" name="role_id" value="{{ $role->id }}">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-10 p-0">
                            <div class="vtabs">
                                <ul class="nav nav-tabs tabs-vertical" role="tablist">
                                    @php
                                    $modulesWithParent = $modules->filter(fn($m) => $m->parent != null)->unique('parent');
                                    $modulesWithoutParent = $modules->filter(fn($m) => $m->parent == null);
                                    @endphp
                                    {{-- @foreach ($modules->unique('parent') as $parentModule) --}}
                                    @foreach ($modulesWithParent as $parentModule)
                                        <li class="nav-item mt-3">
                                            <span class="hidden-xs-down font-weight-bold">{{ $parentModule->parent }}</span>
                                            <ul class="submenu">
                                                @foreach ($modules->where('parent', $parentModule->parent) as $key => $childModule)
                                                    <li>
                                                        <a class="nav-link {{ $key == 0 ? 'active' : '' }}" data-toggle="tab" href="#{{ $childModule->name }}" role="tab" aria-expanded="{{ $key == 0 ? 'true' : 'false' }}" style="font-size:12px;">
                                                            {{ ucwords(str_replace('-', ' ', $childModule->name)) }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach

                                    <li class="nav-item mt-3">
                                        <span class="hidden-xs-down font-weight-bold">No Parent</span>
                                        <ul class="submenu">
                                            @foreach ($modulesWithoutParent as $key => $childModule)
                                                <li>
                                                    <a class="nav-link" data-toggle="tab" href="#{{ $childModule->name }}" role="tab" aria-expanded="false" style="font-size:12px;">
                                                        {{ ucwords(str_replace('-', ' ', $childModule->name)) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
                                
                                <div class="tab-content">
                                    <h4 class="selected-module-name" style="text-align: center;text-transform: capitalize;">
                                     </h4>
                                    @foreach ($modules->unique('parent') as $parentModule)
                                        @foreach ($modules->where('parent', $parentModule->parent) as $key => $childModule)
                                            @php
                                                $moduleName = str_replace('-', '_', $childModule->name);
                                            @endphp
                                            
                                            <div class="tab-pane {{ $key == 0 ? 'active' : '' }}" id="{{ $childModule->name }}" role="tabpanel" aria-expanded="{{ $key == 0 ? 'true' : 'false' }}">
                                                <div class="input-group" style="justify-content:center;">
                                                    <ul class="icheck-list" style="display: inline-flex; gap: 20px;">
                                                        @if($moduleName == 'investigation_tracking')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check view_it" id="minimal-checkbox-{{ $moduleName }}-view_it-{{ $key}}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-view_it-{{ $key }}">View</label>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check confirm_sample" id="minimal-checkbox-{{ $moduleName }}-confirm_sample-{{ $key}}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-confirm_sample-{{ $key }}">Confirm Sample</label>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check upload_report" id="minimal-checkbox-{{ $moduleName }}-upload_report-{{ $key}}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-upload_report-{{ $key }}">Upload Report</label>
                                                        </li>
                                                    @else
                                                        @if($moduleName != 'service_requisition_setup' && $moduleName != 'procedure_coding')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check add" id="minimal-checkbox-{{ $moduleName }}-add-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-add-{{ $key }}">Add</label>
                                                        </li>
                                                        @endif
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check view" id="minimal-checkbox-{{ $moduleName }}-view-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-view-{{ $key }}">View</label>
                                                        </li>

                                                        @if($moduleName != 'modules' && $moduleName != 'external_transaction' && $moduleName != 'issue_and_dispense' && $moduleName != 'consumption' && $moduleName != 'inventory_return' && $moduleName != 'other_transactions' && $moduleName != 'reversal_of_transactions')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check edit" id="minimal-checkbox-{{ $moduleName }}-edit-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-edit-{{ $key }}">Edit</label>
                                                        </li>
                                                        @endif
                                                        @if($moduleName != 'modules' && $moduleName != 'employee_qualification_setup' && $moduleName != 'employee_medical_license_setup' && $moduleName != 'employee_cost_center_allocation' && $moduleName != 'external_transaction' && $moduleName != 'issue_and_dispense' && $moduleName != 'consumption' && $moduleName != 'inventory_return' && $moduleName != 'other_transactions' && $moduleName != 'reversal_of_transactions')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-update-status-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-update-status-{{ $key }}">Update Status</label>
                                                        </li>
                                                        @endif
                                
                                                        @if($moduleName == 'user_roles')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-assign-rights-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-assign-rights-{{ $key }}">Assign Rights</label>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-update-rights-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-update-rights-{{ $key }}">Update Rights</label>
                                                        </li>
                                                        @endif
                                
                                                        @if($moduleName == 'patient_arrival_and_departure')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-end-service-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-end-service-{{ $key }}">End Service</label>
                                                        </li>
                                                        @endif

                                                        @if($moduleName == 'purchase_order')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-approve-po-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-approve-po-{{ $key }}">Approve PO</label>
                                                        </li>
                                                        @endif

                                                        @if($moduleName == 'work_order')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-work_order-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-work_order-{{ $key }}">Approve WO</label>
                                                        </li>
                                                        @endif

                                                        @if($moduleName == 'issue_and_dispense')
                                                        <li>
                                                            <input type="checkbox" name="{{ $moduleName }}[]" class="check update_status" id="minimal-checkbox-{{ $moduleName }}-issue_and_dispense-{{ $key }}">
                                                            <label for="minimal-checkbox-{{ $moduleName }}-issue_and_dispense-{{ $key }}">Respond</label>
                                                        </li>
                                                        @endif
                                                    @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
            
                        <div class="col-2" style="border-left:1px solid #b7b4b4;">
                            <div class="row justify-content-center align-items-center" style="height: 100%;">
                                <div class="col">
                                    <div class="form-group text-center">
                                        <input type="checkbox"style="widh:50%" class="check" id="checkAll" data-checkbox="icheckbox_line-blue" data-label="Check All"> </li>
                                        <br>
                                        <button type="submit" class="btn waves-effect waves-light btn-primary" style="width:100%;">Assign Roles</button>                                    
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            

        </div>
    </div>


    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->
    <script>
        $(document).ready(function() {
            $('#checkAll').on('ifChanged', function(event) {
                if ($(this).prop('checked')) {
                    $('.check').iCheck('check');
                } else {
                    $('.check').iCheck('uncheck');
                }
            });

            var initialModuleName = $(".tab-pane.active").attr('id').replace(/-/g, ' ');
            $('.selected-module-name').text(initialModuleName);

            $('.nav-link').on('click', function() {
                var moduleName = $(this).attr('href').substring(1).replace(/-/g, ' ');
                $('.selected-module-name').text(moduleName);
            });

        });
        
        document.querySelectorAll('.check').forEach(function(checkbox) {
            $(checkbox).on('ifChanged', function(event) {
                var $relatedCheckboxes = $(this).closest('.icheck-list').find('.check');

                if ($(this).hasClass('edit')) {
                    $(this).prop('checked') && $relatedCheckboxes.filter('.view').iCheck('check');
                } 
                else if ($(this).hasClass('update_status')) {
                    $(this).prop('checked') && $relatedCheckboxes.filter('.view').iCheck('check');
                } 
                else if ($(this).hasClass('view')) {
                    if (!$(this).prop('checked')) {
                        $relatedCheckboxes.filter('.edit,.update_status,.add').iCheck('uncheck');
                    }
                } 
                else if ($(this).hasClass('add')) {
                    $(this).prop('checked') && $relatedCheckboxes.filter('.view').iCheck('check');
                } 
                else if ($(this).hasClass('confirm_sample') || $(this).hasClass('upload_report')) {
                    if ($(this).prop('checked')) {
                        $relatedCheckboxes.filter('.view_it').iCheck('check');
                    }
                } 
                else if ($(this).hasClass('view_it')) {
                    if (!$(this).prop('checked')) {
                        $relatedCheckboxes.filter('.confirm_sample, .upload_report').iCheck('uncheck');
                    }
                }
            });
        });
    </script>