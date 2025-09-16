$(document).ready(function() {
    
    //Open Activate Services Setup
    $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#act_s_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    OrgChangeSites('#act_s_org', '#act_s_site', '#activate_service');
    SiteChangeServiceActivation('#act_s_site', '#service_name', '#activate_service');
    // SiteChangeServiceCostCenter('#act_s_site', '#service_name', '#act_s_cc', '#activate_service');

    $('#allServices').on('change', 'input[name="activationServices[]"]', function() {
        updateHiddenServicesForActivation();
    });
      $('#selectAll').on('change', function() {
        $('input[name="activationServices[]"]').prop('checked', $(this).prop('checked')); // Check or uncheck all checkboxes
        updateHiddenServicesForActivation(); // Update hidden inputs with selected values
    });
    
    $('#allCostCenters').on('change', 'input[name="service_billingCC[]"], input[name="service_performingCC[]"],input[name="service_modes[]"]', function() {
        updateHiddenCCForServiceActivation();
    });
    // $(document).on('change', '.select-all-billing, .select-all-performing, .select-all-modes', function() {
    //     updateHiddenCCForServiceActivation();
    // });
    let checkboxStates = {}; 
    $(document).on('click', '.costcenterModal', function() {
        var serviceVal =$('#service_name').val();
        if (serviceVal !== '') {
            $('#act_s_cc').prop('disabled', false);
            $('.activationMsg,.s_modeMsg').hide();
            saveCheckboxStates();
            ActivateServiceCostCenter('#act_s_site', '#act_s_service', '#act_s_billingcc', '#act_s_performingcc');
            $('#costcenterModal').modal('show');
        }
        else{
            $('#act_s_cc').prop('disabled', true);
            $('.activationMsg').text('(Select a service to choose Cost Centers)').show();
        $   ('.s_modeMsg').text('(Select a service to choose Service Modes)').show();
        }
    });

    $(document).on('click', '.service_activation', function() {
        $('input[name="selectedSM[]"]').prop('checked', false);
        $('#siteservice').text('(Select a site to choose Services)').show();
        $('.s_modeMsg').text('(Select a service to choose Service Modes)').show();
        $('.activationMsg').text('(Select a service to choose Cost Centers)').show();
        $('#act_s_cc,#service_name,#servicemode_value').prop('disabled', true);
        $('#service_name,#act_s_service,#act_s_cc_name,#act_s_cc').val('');
        $('input[name="activationServices[]"]:checked, input[name="service_billingCC[]"]:checked, input[name="service_performingCC[]"]:checked,input[name="service_modes[]"]:checked').prop('checked', false);
        $('#allServices,#allCostCenters').empty();
        // $('input[name="activationServices[]"]:checked').prop('checked', false);

        var orgId = $('#act_s_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#act_s_site', function(data) {
                $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#act_s_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        $('#service_activation').modal('show');
    });
    //Open Activate Services Setup

    //Activate Services
    $('input[name="selectedSM[]"]').change(function() {
        updateHiddenServiceModes();
    });

    $('#activate_service').submit(function(e) {
        e.preventDefault();
        updateHiddenServicesForActivation();
        updateHiddenCCForServiceActivation();
        // updateHiddenServiceModes();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            // if ((field.value == '') || (field.value == null)  && field.name != 'act_s_mode[]')
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                var FieldName = field.name.replace('[]', '');
                var FieldID = '#'+FieldName + "_error";
                $(FieldID).text("This fielad is required");
                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                $( 'input[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                })

                $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
                    $(FieldID).text("");
                    $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                });
                resp = false;
            }
        });
        if(resp != false)
        {
            $.ajax({
                url: "/services/activateservice",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: data,
                beforeSend: function() {
                    Swal.fire({
                        title: "Processing",
                        allowOutsideClick: false,
                        willOpen: () => {
                            Swal.showLoading();
                        },
                        showConfirmButton: false
                    });
                },
                success: function(response) {
                    for (var fieldName in response) {
                        var fieldErrors = response[fieldName];
                        var fieldName = fieldName;
                    }
                    if (fieldName == 'error')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        })
                    }
                    else if (fieldName == 'success')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#service_activation').modal('hide');
                                $('#view-serviceactivation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#activate_service').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#act_s_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);

                                $('#activate_service')[0].reset();
                                $('.text-danger').hide();
                            }
                        });
                    }
                    else if (fieldName == 'info')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#activate_service').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_service')[0].reset();
                            }
                        });
                    }

                },
                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        $('.text-danger').show();
                        var errors = error.responseJSON.errors;
                        for (var fieldName in errors) {
                            var fieldErrors = errors[fieldName];
                            for (var i = 0; i < fieldErrors.length; i++) {
                                fieldName = '#'+fieldName + '_error';
                                $(fieldName).text(fieldErrors[i]);
                            }
                        }
                        Swal.close();
                    }
                }
            });
        }
    });
    //Activate Services

    // View Activated ServicesData
    var ActivatedServiceData =  $('#view-serviceactivation').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        // ajax: '/services/getactivateservicedata',
        ajax: {
            url: '/services/getactivateservicedata',
            data: function (d) {
                d.site = $('#fb_site').val();  
                d.costcenter = $('#fb_cc').val();  
                d.service_type = $('#fb_st').val();  
                d.service_group = $('#fb_sg').val();  
                d.service_mode = $('#fb_sm').val();  
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'billingCC', name: 'billingCC' },
            { data: 'performingCC', name: 'performingCC' },
            { data: 'ServiceModes', name: 'ServiceModes' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 6,
                width: "250px"
            }
        ]
    });

    $('#fb_site,#fb_cc,#fb_st,#fb_sg,#fb_sm').on('change', function () {
        ActivatedServiceData.ajax.reload();  
    });

    $('.clearFilter').on('click', function () {
        $('#fb_site, #fb_cc, #fb_st, #fb_sg, #fb_sm').each(function() {
            $(this).val($(this).find('option:first').val()).change();
        });
        ActivatedServiceData.ajax.reload();   
    });

    ActivatedServiceData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedServiceData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedServiceData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Activated ServicesData

    // Update Activated Services Status
    $(document).on('click', '.activateservice', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/update-activateservice',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-serviceactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Activated Services Status

    // Update Activated Services Modal
    $(document).on('click', '.edit-activateservice', function() {
        var activateserviceId = $(this).data('activateservice-id');
        $('input[name="uselectedSM[]"], input[name="uselectedbillingCC[]"], input[name="uselectedperformingCC[]"]').prop('checked', false);
        $('#u_sorg').empty();
        $('#u_ssite').empty();
        $('#u_service').empty();
        $('#u_scc').empty();
        $('#update_serviceactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/updateactivateservice/' + activateserviceId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var siteId = response.siteId;
                var ccName = response.ccName;
                var ccID = response.ccID;
                var serviceName = response.serviceName;
                var serviceID = response.serviceID;
                var serviceModeNames = response.serviceModeNames;
                $('input[name="uservicemode_value"]').val(serviceModeNames);
                var servicemodeIDs = response.servicemodeIDs;
                var servicemodeIDs = servicemodeIDs.split(',');
                for(var i = 0; i < servicemodeIDs.length; i++) {
                    $('#usm_' + servicemodeIDs[i]).prop('checked', true);
                }
                var orderingCCNames = response.orderingCCNames;
                $('input[name="ubillingcc_value"]').val(orderingCCNames);
                
                var performingCCNames = response.performingCCNames;
                $('input[name="uperformingcc_value"]').val(performingCCNames);

                $.ajax({
                    url: 'costcenter/getorderingcc',
                    type: 'GET',
                    data: { 
                        siteId: siteId      
                    },
                    success: function(resp) {
                        $('#userviceBillingccModal .modal-body .row').empty();
                        resp.forEach(function(item) {
                            let checkboxHtml = `
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" name="uselectedbillingCC[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="ubcc_${item.id}">
                                        <label class="custom-control-label" for="ubcc_${item.id}">${item.name}</label>
                                    </div>
                                </div>
                            `;
                            $('#userviceBillingccModal .modal-body .row').append(checkboxHtml);
                        });
                        var orderingCCIDs = response.orderingCCIDs;
                        if (orderingCCIDs) {
                            var orderingCCIDsArray = orderingCCIDs.split(',');
                            for(var i = 0; i < orderingCCIDsArray.length; i++) {
                                $('#ubcc_' + orderingCCIDsArray[i]).prop('checked', true);
                            }
                        }
                        
                    },
                    error: function(xhr, status, error) {
                        if (typeof errorCallback === "function") {
                            errorCallback(error);
                        }
                    }
                });

                $.ajax({
                    url: 'costcenter/getperformingcc',
                    type: 'GET',
                    data: { 
                        siteId: siteId      
                    },
                    success: function(resp) {
                        $('#uperformingPerformingccModal .modal-body .row').empty();
                        resp.forEach(function(item) {
                            let checkboxHtml = `
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" name="uselectedperformingCC[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="upcc_${item.id}">
                                        <label class="custom-control-label" for="upcc_${item.id}">${item.name}</label>
                                    </div>
                                </div>
                            `;
                            $('#uperformingPerformingccModal .modal-body .row').append(checkboxHtml);
                        });

                        var performingCCIDs = response.performingCCIDs;
                        if (performingCCIDs) {
                            var performingCCIDsArray = performingCCIDs.split(',');
                            for(var i = 0; i < performingCCIDsArray.length; i++) {
                                $('#upcc_' + performingCCIDsArray[i]).prop('checked', true);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        if (typeof errorCallback === "function") {
                            errorCallback(error);
                        }
                    }
                });
                
                $('#u_scc').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                $('#u_service').html("<option selected value='"+serviceID+"'>" + serviceName + "</option>");
                $('.u_service_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                $.ajax({
                    url: 'services/getselectedservices',
                    type: 'GET',
                    data: {
                        serviceID: serviceID,
                        site_id: siteId      
                    },
                    beforeSend: function() {
                        $('#u_service').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_service').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('#u_service').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#edit-serviceactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('input[name="uselectedSM[]"]').change(function() {
        updateHiddenUpdatedServiceModes();
    });
    $(document).on('change', 'input[name="uselectedbillingCC[]"]', function() {
        updateHiddenUpdatedBillingCC();
    });
    $(document).on('change', 'input[name="uselectedperformingCC[]"]', function() {
        updateHiddenUpdatedPerformingCC();
    });
    // Update Activated Services Modal
   

    //Update Activated Service
    $('#update_serviceactivation').on('submit', function (event) {
        event.preventDefault();
        updateHiddenUpdatedServiceModes();
        updateHiddenUpdatedBillingCC();
        updateHiddenUpdatedPerformingCC();
        var data = SerializeForm(this);
        var Id = $('.u_service_id').val();
        var url = '/update-activateservice/' + Id;
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: data,
            beforeSend: function() {
                Swal.fire({
                    title: "Processing",
                    allowOutsideClick: false,
                    willOpen: () => {
                        Swal.showLoading();
                    },
                    showConfirmButton: false
                });
            },
            success: function (response) {
                for (var fieldName in response) {
                    var fieldErrors = response[fieldName];
                    var fieldName = fieldName;
                }
                if (fieldName == 'error')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    })
                }
                else if (fieldName == 'success')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-serviceactivation').modal('hide');
                            $('#view-serviceactivation').DataTable().ajax.reload(); 
                            $('#update_serviceactivation')[0].reset();
                            $('.text-danger').hide();
                        }
                        $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Activated Services
});