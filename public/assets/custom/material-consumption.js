$(document).ready(function() {
    //Open Add Requisition For Material Consumption Setup
    $(document).on('click', '.add-materialconsumption', function() {
        $('.duplicate:not(:first)').remove();
        $('#mc_patient,#mc_service').prop('required', false);
        $('.mr_optional,.text-danger').show();
        $('.mc_source_location, .mc_destination_location').hide(); // Hide source/destination sections initially
        var orgId = $('#mc_org').val();
        $('#serviceSelect').hide();
        if(!orgId)
        {
            $('#mc_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#mc_org', function(data) {
                $('#mc_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#mc_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#mc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#mc_org', '#mc_site', '#add_materialconsumption');
            // $('#mc_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
            // OrgChangeTransactionTypes('#mc_org', '#mc_transactiontype', '#add_materialconsumption',true);
            $('.mc_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#mc_org', '.mc_itemgeneric', '#add_materialconsumption' ,'material');

        }
        else{
            fetchOrganizationSites(orgId, '#mc_site', function(data) {
                $('#mc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#mc_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
            // fetchTransactionTypes(orgId, '#mc_transactiontype', true, function(data) {
            //     $('#mc_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',false);
            //     if (data && data.length > 0) {
            //         $.each(data, function(key, value) {
            //             $('#mc_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
            //         });
            //     }
            // });
             $('.mc_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>");
            //  fetchInventoryGenerics('.mc_itemgeneric', 'material', function (data) {
            //     if (data.length > 0) {
            //         $.each(data, function (key, value) {
            //             $('.mc_itemgeneric' ).append('<option value="' + value.id + '">' + value.name + '</option>');
            //         });
            //         $('.mc_itemgeneric').select2();
            //     }
            // });
            fetchOrganizationItemGeneric(orgId, '.mc_itemgeneric', 'material', function(data) {
                $.each(data, function(key, value) {
                    $('.mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }

        $('#mc_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
        SiteChangeMaterialManagementTransactionTypes('#mc_site','#mc_org', '#mc_transactiontype', '#add_materialconsumption','issue_dispense','y');

        // Initialize source and destination location dropdowns
        $('#mc_source_location').html("<option selected disabled value=''>Select Source Location</option>").prop('disabled', true);
        $('#mc_destination_location').html("<option selected disabled value=''>Select Destination Location</option>").prop('disabled', true);

        $('#mc_patient').html("<option selected disabled value=''>Select Patient MR#</option>").prop('disabled',true);
        SiteChangeMRCode('#mc_site', '#mc_patient', '#add_materialconsumption', 'materialConsumption');
        MRChangeService('#mc_patient', '#mc_service');
        
        // // Add change event listener for site field to refresh generics
        // $(document).off('change', '#mc_site').on('change', '#mc_site', function() {
        //     var orgId = $('#mc_org').val();
        //     var selectedMR = $('#mc_patient').val();
            
        //     if (orgId) {
        //         // Clear existing generics
        //         $('.mc_itemgeneric').empty().append('<option selected disabled value="">Select Item Generic</option>');
                
        //         // Determine condition based on MR selection
        //         var condition = selectedMR && selectedMR !== '' ? 'medicine' : 'material';
                
        //         // Fetch generics based on condition
        //         fetchOrganizationItemGeneric(orgId, '.mc_itemgeneric', condition, function(data) {
        //             if (data && data.length > 0) {
        //                 $.each(data, function(key, value) {
        //                     $('.mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
        //                 });
        //             } else {
        //                 $('.mc_itemgeneric').append('<option disabled value="">No generics available</option>');
        //             }
        //         });
        //     }
        // });

        // Add change event listener for organization field to update generics
        // $(document).off('change', '#mc_org').on('change', '#mc_org', function() {
        //     var orgId = $(this).val();
        //     var selectedMR = $('#mc_patient').val();
            
        //     if (orgId) {
        //         // Clear existing generics
        //         $('.mc_itemgeneric').empty().append('<option selected disabled value="">Select Item Generic</option>');
                
        //         // Determine condition based on MR selection
        //         var condition = selectedMR && selectedMR !== '' ? 'medicine' : 'material';
                
        //         // Fetch generics based on condition
        //         fetchOrganizationItemGeneric(orgId, '.mc_itemgeneric', condition, function(data) {
        //             if (data && data.length > 0) {
        //                 $.each(data, function(key, value) {
        //                     $('.mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
        //                 });
        //             } else {
        //                 $('.mc_itemgeneric').append('<option disabled value="">No generics available</option>');
        //             }
        //         });
        //     }
        // });

        // Add change event listener for MR patient field to update generics
        $(document).off('change', '#mc_patient').on('change', '#mc_patient', function() {
            var selectedMR = $(this).val();
            var orgId = $('#mc_org').val();
            
            if (orgId) {
                // Clear existing generics in all generic fields
                $('.mc_itemgeneric').empty().append('<option selected disabled value="">Select Item Generic</option>');
                
                // Determine condition based on MR selection
                var condition = 'material_medicine';
                
                // Fetch generics based on condition and populate all generic fields
                fetchOrganizationItemGeneric(orgId, '.mc_itemgeneric', condition, function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(key, value) {
                            $('.mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('.mc_itemgeneric').append('<option disabled value="">No generics available</option>');
                    }
                });
            }
        });

        $(document).off('change', '#mc_transactiontype').on('change', '#mc_transactiontype', function() {
            let transactionTypeID = $(this).val();
            let siteId = $('#mc_site').val();
            if (!siteId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Site Required',
                    text: 'Please select a site before choosing the transaction type.'
                });
                $('#mc_transactiontype')
                    .prop('disabled', false)
                    .children('option[value=""]').remove().end()
                    .prepend('<option value="" disabled>Select Transaction Type</option>')
                    .val('');
                return;
            }

            Swal.fire({
                title: "Processing",
                allowOutsideClick: false,
                willOpen: () => {
                    Swal.showLoading();
                },
                showConfirmButton: false
            });

            $.ajax({
                url: 'inventory/gettransactiontypeim',
                type: 'GET',
                data: {
                    transactionTypeId: transactionTypeID,
                    siteId: siteId,
                    transactionType: 'requisition'
                },
                success: function(resp) {
                    Swal.close();
                    // Show transaction info
                    let infoHtml = `
                        <div class="col-12 mt-1 mb-1 transaction-block">
                            <div class="card shadow-sm border mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="row align-items-center text-center">
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Source:</small><br>
                                            <strong class="text-primary source">${resp.Source || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Destination:</small><br>
                                            <strong class="text-primary destination">${resp.Destination || '-'}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Remove existing transaction info and add new one
                     $('#transaction-info-row').find('.transaction-block').remove();

                       $('#transaction-info-row')
                        .append(infoHtml)
                        .show();

                    let sourceType = (resp.Source || '').toLowerCase();
                    let destType = (resp.Destination || '').toLowerCase();
                    
                    // Handle source location
                    if (sourceType.includes('location')) {
                        $('.mc_source_location').show();
                        $('#mc_source_location').prop('required', true);
                        $('#mc_source_location').prop('disabled', false);
                        
                        // Populate source locations
                        $('#mc_source_location').empty().append('<option selected disabled value="">Select Source Location</option>');
                        if (resp.sourceData && resp.sourceData.length > 0) {
                            resp.sourceData.forEach(function(item) {
                                $('#mc_source_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        }
                        else{
                            $('#mc_source_location')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>')
                            .prop('disabled', true)
                            .prop('required', false);
                        }
                            
                    } else {
                        // console.log('else');
                        $('.mc_source_location').hide();
                        $('#mc_source_location').prop('required', false);
                        $('#mc_source_location').val('').prop('disabled', true);
                    }
                    
                    // Handle destination location
                    if (destType.includes('location')) {
                        $('.mc_destination_location').show();
                        $('#mc_destination_location').prop('required', true);
                        $('#mc_destination_location').prop('disabled', false);
                        
                        // Populate destination locations
                        $('#mc_destination_location').empty().append('<option selected disabled value="">Select Destination Location</option>');
                        if (resp.destinationData && resp.destinationData.length > 0) {
                            resp.destinationData.forEach(function(item) {
                                $('#mc_destination_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        }
                        else{
                            $('#mc_destination_location')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>')
                            .prop('disabled', true)
                            .prop('required', false);
                        }
                    } else {
                        $('.mc_destination_location').hide();
                        $('#mc_destination_location').prop('required', false);
                        $('#mc_destination_location').val('').prop('disabled', true);
                    }
                    
                    // Handle patient requirement
                    if (sourceType.includes('patient') || destType.includes('patient')) {
                        $('.mr_optional').hide();
                        $('#mc_patient')
                            .prop('required', true)
                            .attr('data-validation-required', 'true');
                    }
                    else {
                        $('.mr_optional').show();
                        $('#mc_patient')
                            .prop('required', false)
                            .removeAttr('data-validation-required');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });
        $('#add-materialconsumption').modal('show');
    });
    //Open Add Requisition For Material Consumption Setup

    //Add Requisition For Material Consumption
    $('#add_materialconsumption').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');
                if (!value || value === "" || (elem.is('select') && value === null)) {
                    errorField.text("This field is required");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').addClass('requirefield');
                        elem.on('select2:open', function() {
                            errorField.text("");
                            elem.next('.select2-container').find('.select2-selection').removeClass("requirefield");
                        });
                    }
                    else {
                        elem.addClass('requirefield');
                        elem.focus(function() {
                            errorField.text("");
                            elem.removeClass("requirefield");
                        });
                    }
                    resp = false;
                } else {
                    errorField.text("");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').removeClass('requirefield');
                    } else {
                        elem.removeClass('requirefield');
                    }
                }
            });
        });

        var excludedFields = ['mc_patient', 'mc_service', 'mc_remarks'];
        
        // Exclude source/destination location fields if they are hidden
        if ($('.mc_source_location').is(':hidden')) {
            excludedFields.push('mc_source_location');
        }
        if ($('.mc_destination_location').is(':hidden')) {
            excludedFields.push('mc_destination_location');
        }
        $(data).each(function(i, field){
            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true;
            }
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                var FieldName = FieldName.replace('[]', '');
                    console.log(FieldName);

                var FieldID = '#'+FieldName + "_error";

                $(FieldID).text("This field is required");
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

        const $mrPatient = $('#mc_patient');
        if ($mrPatient.attr('data-validation-required') === 'true') {
            if (!$mrPatient.val() || $mrPatient.val() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Patient MR# is required. Please select a different site with available MR numbers.'
                });
                return false;
            }
        }

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addmaterialconsumption",
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
                                $('#add-materialconsumption').modal('hide');
                                $('#view-materialconsumption').DataTable().ajax.reload();
                                $('#add_materialconsumption')[0].reset();
                                // $('#add_materialconsumption').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
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
                                $('#add-materialconsumption').modal('hide');
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
    //Add Requisition For Material Consumption

    // View Requisition For Material Consumption Data
    var viewRequisitionMaterialConsumption =  $('#view-materialconsumption').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/materialconsumption',
        order: [[1, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'requisition_detail', name: 'requisition_detail' },
            { data: 'patientDetails', name: 'patientDetails' },
            { data: 'InventoryDetails', name: 'InventoryDetails' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
              {
                targets: 2,
                width: "250px"
            },
              {
                targets: 3,
                width: "300px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });

    viewRequisitionMaterialConsumption.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewRequisitionMaterialConsumption.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewRequisitionMaterialConsumption.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Requisition For Material Consumption Data

    // Update Requisition For Material Consumption Status
    $(document).on('click', '.mc_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/materialconsumption-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-materialconsumption').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Requisition For Material Consumption Status

    //Update Requisition For Material Consumption Modal
    $(document).on('click', '.edit-materialconsumption', function() {
        var RequisitionId = $(this).data('mc-id');
        var url = '/inventory/updatematerialconsumption/' + RequisitionId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#u_serviceSelect').hide();
                
                // Set effective date/time
                if (response.effective_timestamp) {
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('.uedt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
                }
                
                $('.u_mc-id').val(response.id);
                $('#u_mc_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_mc_org', function(data) {
                    $('#u_mc_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_mc_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                $('#u_mc_site').html("<option selected value='"+response.siteId+"'>" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_mc_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_mc_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);
                OrgChangeSites('#u_mc_org', '#u_mc_site', '#update_materialconsumption');
               
                $('#u_mc_transactionType').html("<option selected value="+ response.transactionTypeId +">" + response.transactionType + "</option>");
                fetchMaterialManagementTransactionTypes(response.orgId, '#u_mc_transactionType','issue_dispense','y', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.transactionTypeId)
                        {
                            $('#u_mc_transactionType').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                OrgChangeTransactionTypes('#u_mc_org', '#u_mc_transactionType', '#update_materialconsumption',true);

                // Initialize source and destination location dropdowns for update modal
                // $('#u_mc_source_location').html("<option selected disabled value=''>Select Source Location</option>").prop('disabled', true);
                // $('#u_mc_destination_location').html("<option selected disabled value=''>Select Destination Location</option>").prop('disabled', true);
                
                if (response.sourceLocationId && response.sourceLocationName) {
                    $('.u_mc_source_location').show();
                    $('#u_mc_source_location').html("<option selected value='" + response.sourceLocationId + "'>" + response.sourceLocationName + "</option>");
                } else {
                    $('.u_mc_source_location').hide();
                    $('#u_mc_source_location').html("<option selected disabled value=''>Select Source Location</option>");
                }

                // Set current value and populate options
                if (response.destinationLocationId && response.destinationLocationName) {
                    $('.u_mc_destination_location').show();
                    $('#u_mc_destination_location').html("<option selected value='" + response.destinationLocationId + "'>" + response.destinationLocationName + "</option>");
                } else {
                    $('.u_mc_destination_location').hide();
                    $('#u_mc_destination_location').html("<option selected disabled value=''>Select Destination Location</option>");
                }

                // Use the same route as add modal to respect employee location filtering
                $.ajax({
                    url: 'inventory/gettransactiontypeim',
                    type: 'GET',
                    data: { 
                        transactionTypeId: response.transactionTypeId, 
                        siteId: response.siteId,
                        transactionType: 'requisition'
                    },
                    success: function(resp) {
                        // Handle source location
                        if (resp.Source && resp.Source.toLowerCase().includes('location')) {
                            // $('.u_mc_source_location').show();
                            $('#u_mc_source_location').prop('required', true);
                            $('#u_mc_source_location').prop('disabled', false);
                            
                            // Set current value and populate options
                            if (resp.sourceData && resp.sourceData.length > 0) {
                                resp.sourceData.forEach(function(item) {
                                    if (item.id != response.sourceLocationId) {
                                        $('#u_mc_source_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                    }
                                });
                            }
                        } else {
                            $('.u_mc_source_location').hide();
                            $('#u_mc_source_location').prop('required', false);
                            $('#u_mc_source_location').val('').prop('disabled', true);
                        }
                        
                        // Handle destination location
                        if (resp.Destination && resp.Destination.toLowerCase().includes('location')) {
                            // $('.u_mc_destination_location').show();
                            $('#u_mc_destination_location').prop('required', true);
                            $('#u_mc_destination_location').prop('disabled', false);
                            
                            if (resp.destinationData && resp.destinationData.length > 0) {
                                resp.destinationData.forEach(function(item) {
                                    if (item.id != response.destinationLocationId) {
                                        $('#u_mc_destination_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                    }
                                });
                            }
                        } else {
                            // $('.u_mc_destination_location').hide();
                            $('#u_mc_destination_location').prop('required', false);
                            $('#u_mc_destination_location').val('').prop('disabled', true);
                        }
                    }
                });

                fetchPatientMR(response.siteId, '#u_mc_patient', 'materialConsumption', function(data) {
                    $('#u_mc_patient').empty();
                    $('#u_mc_patient').find('option:contains("Loading...")').remove();

                    if (!data || data.length === 0) {
                        $('#u_mc_patient')
                            .html('<option selected disabled value="">Not Available</option>')
                            .prop('disabled', true);
                        $('#u_serviceSelect').hide();
                        $('#u_mc_service').empty();
                        return;
                    } else {
                        $('#u_mc_patient').prop('disabled', false);
                    }

                    if(response.mrCode && response.mrCode.trim() !== '')
                    {
                        $('#u_mc_patient').html("<option selected value='"+response.mrCode+"'>" + response.mrCode + "</option>");
                        $('#u_serviceSelect').show();
                        $('#u_mc_service').html("<option selected value='"+response.serviceId+"'>" + response.serviceName + "</option>");
                        fetchMRServices(response.mrCode, '#u_mc_service', function(data) {
                            if (data && data.length > 0) {
                                $.each(data, function(key, values) {
                                    if (response.serviceId != values.id) {
                                        $('#u_mc_service').append('<option value="' + values.id + '">' + values.name + '</option>');
                                    }
                                });
                            }
                        });
                    }
                    else{
                        $('#u_mc_patient').append('<option selected disabled value="">Select Patient MR#</option>');
                        fetchMRServices(response.mrCode, '#u_mc_service', function(data) {
                            if (data && data.length > 0) {
                                $.each(data, function(key, values) {
                                    if (response.serviceId != values.id) {
                                        $('#u_mc_service').append('<option value="' + values.id + '">' + values.name + '</option>');
                                    }
                                });
                            }
                        });
                        $('#u_serviceSelect').hide();
                    }

                    $.each(data, function(key, value) {
                        if (response.mrCode != value.mr_code) {
                            $('#u_mc_patient').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
                        }
                    });
                });
                MRChangeService('#u_mc_patient', '#u_mc_service');

                $(document).off('change', '#u_mc_transactionType').on('change', '#u_mc_transactionType', function() {
                    let transactionTypeID = $(this).val();
                    let siteId = $('#u_mc_site').val();  // you must already have a selected site
                    if (!siteId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Site Required',
                            text: 'Please select a site before choosing the transaction type.'
                        });
                        $('#u_mc_transactionType')
                            .prop('disabled', false)
                            .children('option[value=""]').remove().end()
                            .prepend('<option value="" disabled>Select Transaction Type</option>')
                            .val('');
                        return;
                    }

                    Swal.fire({
                        title: "Processing",
                        allowOutsideClick: false,
                        willOpen: () => {
                            Swal.showLoading();
                        },
                        showConfirmButton: false
                    });

                    $.ajax({
                        url: 'inventory/gettransactiontypeim',
                        type: 'GET',
                        data: {
                            transactionTypeId: transactionTypeID,
                            siteId: siteId,
                            transactionType: 'requisition'
                        },
                        success: function(resp) {
                            let sourceType = (resp.Source || '').toLowerCase();
                            let destType = (resp.Destination || '').toLowerCase();
                            // Handle source location
                            if (sourceType.includes('location')) {
                                $('.u_mc_source_location').show();
                                $('#u_mc_source_location').prop('required', true);
                                $('#u_mc_source_location').prop('disabled', false);
                                
                                // Populate source locations
                                $('#u_mc_source_location').empty().append('<option selected disabled value="">Select Source Location</option>');
                                if (resp.sourceData && resp.sourceData.length > 0) {
                                    resp.sourceData.forEach(function(item) {
                                        $('#u_mc_source_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                    });
                                }
                            } else {
                                $('.u_mc_source_location').hide();
                                $('#u_mc_source_location').prop('required', false);
                                $('#u_mc_source_location').val('').prop('disabled', true);
                            }
                            // Handle destination location
                            if (destType.includes('location')) {
                                $('.u_mc_destination_location').show();
                                $('#u_mc_destination_location').prop('required', true);
                                $('#u_mc_destination_location').prop('disabled', false);
                                
                                // Populate destination locations
                                $('#u_mc_destination_location').empty().append('<option selected disabled value="">Select Destination Location</option>');
                                if (resp.destinationData && resp.destinationData.length > 0) {
                                    resp.destinationData.forEach(function(item) {
                                        $('#u_mc_destination_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                    });
                                }
                            } else {
                                $('.u_mc_destination_location').hide();
                                $('#u_mc_destination_location').prop('required', false);
                                $('#u_mc_destination_location').val('').prop('disabled', true);
                            }
                            
                            if (sourceType.includes('patient') || destType.includes('patient')) {
                                $('.umr_optional').hide();
                                $('#u_mc_patient')
                                    .prop('required', true)
                                    .attr('data-validation-required', 'true');
                            }
                            else {
                                $('.umr_optional').show();
                                $('#u_mc_patient')
                                    .prop('required', false)
                                    .removeAttr('data-validation-required');
                            }

                            Swal.close();
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            console.log(error);
                        }
                    });
                });

                // $(document).off('change', '#u_mc_patient').on('change', '#u_mc_patient', function() {
                //     var selectedMR = $(this).val();
                //     var orgId = $('#mc_org').val();
                    
                //     if (orgId) {
                //         // Clear existing generics in all generic fields
                //         $('.mc_itemgeneric').empty().append('<option selected disabled value="">Select Item Generic</option>');
                        
                //         // Determine condition based on MR selection
                //         var condition = 'material_medicine';
                        
                //         // Fetch generics based on condition and populate all generic fields
                //         fetchOrganizationItemGeneric(orgId, '.u_mc_itemgeneric', condition, function(data) {
                //             if (data && data.length > 0) {
                //                 $.each(data, function(key, value) {
                //                     $('.u_mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                //                 });
                //             } else {
                //                 $('.u_mc_itemgeneric').append('<option disabled value="">No generics available</option>');
                //             }
                //         });
                //     }
                // });

                // Set remarks
                if (response.remarks) {
                    $('#u_mc_remarks').val(response.remarks);
                } else {
                    $('#u_mc_remarks').val('');
                }

                // Handle generic IDs and names
                var genericIds = [];
                var genericNames = [];
                var Qty = [];
                
                if (response.genericIds && response.genericIds.trim() !== '') {
                    genericIds = response.genericIds.split(',');
                }
                if (response.genericNames && response.genericNames.trim() !== '') {
                    genericNames = response.genericNames.split(',');
                }
                if (response.Qty && response.Qty.trim() !== '') {
                    Qty = response.Qty.split(',');
                }

                $('.uduplicate').empty();
                
                // Only process if we have generic data
                if (genericIds.length > 0 && genericNames.length > 0 && Qty.length > 0) {
                    var selectedMR = $('#u_mc_patient').val();
                    var condition = (selectedMR && selectedMR !== '') ? 'material' : 'material_medicine';
                    for (var i = 0; i < genericIds.length; i++) {
                    var GenericField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Inventory Generic</label>' +
                        '<select class="form-control selecter p-0 u_mc_itemgeneric" name="u_mc_itemgeneric[]" id="u_mc_itemgeneric' + i + '" required style="color:#222d32">' +
                        '<option value="' + genericIds[i] + '"> ' + genericNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchInventoryGenerics('#u_mc_itemgeneric' + index, condition, function (data) {
                            if (data.length > 0) {
                                $('#u_mc_itemgeneric' + index).select2();
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), genericIds) === -1)
                                    {
                                        $('#u_mc_itemgeneric' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                            }
                        });
                    })(i);

                    var qtyField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Demand Qty</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_mc_qty[]" value="' + Qty[i] + '">' +
                        '</div>' +
                        '<span class="text-danger u_mc_qty_error"></span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                        var row =+ '</div>';
                        $('.uduplicate').append('<div class="row pt-3 pb-1 mc_details" style="border: 1px solid #939393;">' + GenericField + qtyField +'</div>');
                    }
                } else {
                    $('.uduplicate').html('<div class="col-12"><p class="text-muted">No generic items found for this requisition.</p></div>');
                }

                $(document).off('change', '#u_mc_patient').on('change', '#u_mc_patient', function() {
                    var selectedMR = $(this).val();
                    var orgId = $('#u_mc_org').val() || response.orgId;
                    
                    if (orgId) {
                        // Clear existing generic fields and keep only the first one
                        $('.uduplicate').empty();
                        
                        // Create a single clean generic field
                        var cleanGenericField = '<div class="row pt-3 pb-1 mc_details" style="border: 1px solid #939393;">' +
                            '<div class="col-md-6">' +
                            '<div class="form-group">' +
                            '<label class="control-label">Update Inventory Generic</label>' +
                            '<select class="form-control selecter p-0 u_mc_itemgeneric" name="u_mc_itemgeneric[]" id="u_mc_itemgeneric0" required style="color:#222d32">' +
                            '<option selected disabled value="">Select Item Generic</option>' +
                            '</select>' +
                            '</div>' +
                            '</div>' +
                            '<div class="col-md-6">' +
                            '<div class="form-group row">' +
                            '<div class="col-md-12">' +
                            '<div class="form-group has-custom m-b-5">' +
                            '<label class="control-label">Update Demand Qty</label>' +
                            '<input type="number" class="form-control input-sm" required name="u_mc_qty[]" min="1" value="1">' +
                            '</div>' +
                            '<span class="text-danger u_mc_qty_error"></span>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        
                        $('.uduplicate').append(cleanGenericField);
                        
                        var condition = 'material_medicine';
                        
                        fetchInventoryGenerics('.uduplicate .u_mc_itemgeneric', condition, function(data) {
                            if (data && data.length > 0) {
                                $('.uduplicate .u_mc_itemgeneric').select2();
                                $.each(data, function(key, value) {
                                    $('.uduplicate .u_mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });

                            } else {
                                $('.uduplicate .u_mc_itemgeneric').append('<option disabled value="">No generics available</option>');
                            }
                        });
                    }
                });


                $('#edit-materialconsumption').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Requisition For Material Consumption Modal

    //Update Requisition For Material Consumption
    $('#update_materialconsumption').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var materialconsumptionId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_mc-id') {
                materialconsumptionId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-materialconsumption/' + materialconsumptionId;
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
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
                            $('#edit-materialconsumption').modal('hide');
                            $('#view-materialconsumption').DataTable().ajax.reload(); // Refresh DataTable
                            $('.text-danger').hide();
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Requisition For Material Consumption

    $(document).on('change', '#mc_transactiontype', function() {
        let transactionTypeId = $(this).val();

        // Reset the patient field state first
        $('.mr_optional').text('(Optional)');
        $('#mc_patient').prop('required', false);

        if (transactionTypeId) {
            $.ajax({
                url: 'inventory/gettransactiontypes',
                type: 'GET',
                data: { id: transactionTypeId },
                success: function(resp) {
                    let sourceType = (resp.Source || '').toLowerCase();
                    let destType = (resp.Destination || '').toLowerCase();

                    // If either source or destination includes 'patient')
                    if (sourceType.includes('patient') || destType.includes('patient')) {
                        // Make the patient field required
                        $('.mr_optional').text('(Required)');
                        $('#mc_patient').prop('required', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching transaction types:', error);
                }
            });
        }
    });
});
