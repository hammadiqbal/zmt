$(document).ready(function() {
    //Open Add Requisition For Material Consumption Setup
    $(document).on('click', '.add-materialconsumption', function() {
        $('.duplicate:not(:first)').remove();
        $('#mc_patient,#mc_service').prop('required', false);
        $('.mr_optional,.text-danger').show();
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
            OrgChangeInventoryGeneric('#mc_org', '.mc_itemgeneric', '#add_materialconsumption');

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
            fetchOrganizationItemGeneric(orgId, '.mc_itemgeneric', function(data) {
                $.each(data, function(key, value) {
                    $('.mc_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
            
        }

        $('#mc_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
        SiteChangeMaterialManagementTransactionTypes('#mc_site','#mc_org', '#mc_transactiontype', '#add_materialconsumption','issue_dispense','y');

        $('#mc_inv_location').html("<option selected disabled value=''>Select Inventory Location</option>").prop('disabled', true);
        SiteChangeActivatedServiceLocation('#mc_site','#mc_inv_location', '#add_materialconsumption',true );

        $('#mc_patient').html("<option selected disabled value=''>Select Patient MR#</option>").prop('disabled',true);
        SiteChangeMRCode('#mc_site', '#mc_patient', '#add_materialconsumption', 'materialConsumption');
        MRChangeService('#mc_patient', '#mc_service');

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
                    siteId: siteId
                },
                success: function(resp) {
                    console.log(resp);
                    Swal.close();
                    let sourceType = (resp.Source || '').toLowerCase();
                    let destType = (resp.Destination || '').toLowerCase();
                    if (sourceType.includes('patient') || destType.includes('patient')) {
                        $('.mr_optional').hide();
                        $('#mc_patient')
                            .prop('required', true)
                            .attr('data-validation-required', 'true'); // Add custom attribute
                    }
                    else {
                        $('.mr_optional').show();
                        $('#mc_patient')
                            .prop('required', false)
                            .removeAttr('data-validation-required');
                    }
                    // if (resp.LocationMandatory && resp.LocationMandatory.toLowerCase() === 'y') {
                    //     // Show the inventory location dropdown
                    //     $('.mc_inv_location').show(); // Add a class to the container div for easy selection
                    //     $('#mc_inv_location').prop('required', true);
                    // } else {
                    //     // Hide the inventory location dropdown and set value to 0
                    //     $('.mc_inv_location').hide();
                    //     // $('#mc_inv_location').val(0).prop('required', false);
                    //     $('#mc_inv_location').html("<option selected value='0'>0</option>").prop('required',true);

                    // }
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
                    console.log(errorField);
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
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
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

                // $('#u_mc_transactionType').html("<option selected value='"+response.transactionTypeId+"'>" + response.transactionType + "</option>").trigger('change');
                // fetchTransactionTypes(response.orgId, '#u_mc_transactionType', true, function(data) {
                //     if (data && data.length > 0) {
                //         $.each(data, function(key, value) {
                //             if(value.id != response.transactionTypeId){
                //                 $('#u_mc_transactionType').append('<option value="' + value.id + '">' + value.name + '</option>');
                //             }
                //         });
                //     } 
                // });

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

                // $('#u_mc_inv_location').val(response.ServiceLocationId).change();
                $('#u_mc_inv_location').html("<option selected value="+ response.ServiceLocationId +">" + response.ServiceLocationName + "</option>");
                fetchActiveSL(response.siteId, '#u_mc_inv_location', function(data) {
                    $.each(data, function(key, value) {
                        if(value.location_id != response.ServiceLocationId)
                        {
                            $('#u_mc_inv_location').append('<option value="' + value.location_id + '">' + value.name + '</option>');
                        }
                    });
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
                            siteId: siteId
                        },
                        success: function(resp) {
                            Swal.close();
                            let sourceType = (resp.Source || '').toLowerCase();
                            let destType = (resp.Destination || '').toLowerCase();
                            if (sourceType.includes('patient') || destType.includes('patient')) {
                                $('.umr_optional').hide();
                                $('#u_mc_patient')
                                    .prop('required', true)
                                    .attr('data-validation-required', 'true'); // Add custom attribute
                            }
                            else {
                                $('.umr_optional').show();
                                $('#u_mc_patient')
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

                $('#u_mc_remarks').val(response.remarks);

                var genericIds = response.genericIds.split(',');
                var genericNames = response.genericNames.split(',');
                var Qty = response.Qty.split(',');

                $('.uduplicate').empty();
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
                        fetchInventoryGenerics('#u_mc_itemgeneric' + index, 'material', function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), genericIds[index]) === -1) 
                                    {
                                        $('#u_mc_itemgeneric' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_mc_itemgeneric' + index).select2();
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
                    
                    // If either source or destination includes 'patient'
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