$(document).ready(function() {
    //Open Requisition Medication Consumption
    $(document).on('click', '.add-reqmc', function() {
        $('.duplicate:not(:first)').remove();
        var orgId = $('#rmc_orgid').val();
        var siteId = $('#rmc_siteid').val();
        $('#transaction-info-row').hide();
        // fetchTransactionTypes(orgId, '#rmc_transaction_type', true, function(data) {
        //     if (data && data.length > 0) {
        //         $.each(data, function(key, value) {
        //             $('#rmc_transaction_type').append('<option data-type="' + value.transaction_type + '" value="' + value.id + '">' + value.name + '</option>');
        //         });
        //     }
        // });
        $('#rmc_transaction_type').html("<option selected disabled value=''>Select Transaction Type</option>");
        // fetchMaterialManagementTransactionTypes(orgId, '#rmc_transaction_type','issue_dispense','y', function(data) {
        //     $.each(data, function(key, value) {
        //         $('#rmc_transaction_type').append('<option value="' + value.id + '">' + value.name + '</option>');
        //     });
        // });
         fetchMaterialManagementTransactionTypes(orgId, '#rmc_transaction_type','issue_dispense','y', function(data) {
                    $.each(data, function(key, value) {
                            $('#rmc_transaction_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });



        // Initialize source and destination location dropdowns
        $('#rmc_source_location').html("<option selected disabled value=''>Select Source Location</option>").prop('disabled', true);
        $('#rmc_destination_location').html("<option selected disabled value=''>Select Destination Location</option>").prop('disabled', true);
        
        // Hide source/destination sections initially
        // $('.rmc_source_location, .rmc_destination_location').hide();


        $(".rmc_inv_generic, .rmc_route, .rmc_frequency").each(function() {
            $(this).val($(this).find("option:first").val()).change();
        });
        
        // Handle transaction type change for source and destination locations
        // $('#rmc_transaction_type').off('change').on('change', function() {
        $(document).off('change', '#rmc_transaction_type').on('change', '#rmc_transaction_type', function() {
            let transactionTypeID = $(this).val();
            let siteId = $('#rmc_siteid').val();
            
            if (!siteId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Site Required',
                    text: 'Please select a site before choosing the transaction type.'
                });
                $('#rmc_transaction_type')
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
                url: '/inventory/gettransactiontypeim',
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
                        $('.rmc_source_location').show();
                        $('#rmc_source_location').prop('required', true);
                        $('#rmc_source_location').prop('disabled', false);
                        
                        // Populate source locations
                        $('#rmc_source_location').empty().append('<option selected disabled value="">Select Source Location</option>');
                        if (resp.sourceData && resp.sourceData.length > 0) {
                            resp.sourceData.forEach(function(item) {
                                $('#rmc_source_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        } else {
                            $('#rmc_source_location')
                                .empty()
                                .append('<option selected disabled value="">No Data Found</option>')
                                .prop('disabled', true)
                                .prop('required', false);
                        }
                    } else {
                        $('.rmc_source_location').hide();
                        $('#rmc_source_location').prop('required', false);
                        $('#rmc_source_location').val('').prop('disabled', true);
                    }
                    
                    // Handle destination location
                    if (destType.includes('location')) {
                        $('.rmc_destination_location').show();
                        $('#rmc_destination_location').prop('required', true);
                        $('#rmc_destination_location').prop('disabled', false);
                        
                        // Populate destination locations
                        $('#rmc_destination_location').empty().append('<option selected disabled value="">Select Destination Location</option>');
                        if (resp.destinationData && resp.destinationData.length > 0) {
                            resp.destinationData.forEach(function(item) {
                                $('#rmc_destination_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        } else {
                            $('#rmc_destination_location')
                                .empty()
                                .append('<option selected disabled value="">No Data Found</option>')
                                .prop('disabled', true)
                                .prop('required', false);
                        }
                    } else {
                        $('.rmc_destination_location').hide();
                        $('#rmc_destination_location').prop('required', false);
                        $('#rmc_destination_location').val('').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });
        
        $('#add-reqmc').modal('show');
    });
    //Open Requisition Medication Consumption

    //Add Requisition Medication Consumption
    $('#add_reqmc').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            // if ((field.value == '') || (field.value == null) && field.name != 'rmc_remarks')
            if ((field.value == '' || field.value == null) && field.name != 'rmc_remarks')
            {
                var FieldName = field.name;

                // Skip validation if the field is hidden or disabled (e.g., source/destination when not inventory)
                var $form = $('#add_reqmc');
                var $fieldEls = $form.find('[name="' + FieldName + '"]');
                if ($fieldEls.length > 0) {
                    var isHidden = $fieldEls.is(':hidden');
                    var isDisabled = $fieldEls.is(':disabled');
                    if (isHidden || isDisabled) {
                        return true; // continue to next field
                    }
                }

                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                $( 'input[name= "' +FieldName +'"' ).focus(function() {
                    $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
                    $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                });
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/medicalrecord/addrmc",
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
                    let successMessageShown = false;
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
                        if (!successMessageShown) {
                            Swal.fire({
                                text: fieldErrors,
                                icon: fieldName,
                                allowOutsideClick: false,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#view-reqmc').DataTable().ajax.reload();
                                    $('#add_reqmc')[0].reset();
                                    $('#add-reqmc').modal('hide');

                                    $('.text-danger').hide();
                                }
                            });

                            successMessageShown = true;
                        }
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
    //Add Requisition Medication Consumption

    // View Requisition Medication Consumption
    var pathArray = window.location.pathname.split('/');
    var mrPath = pathArray.length - 1;
    var mrPath = pathArray[mrPath];
    var viewReqMC =  $('#view-reqmc').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/medicalrecord/viewreqmc/' + mrPath,
        order: [[1, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id'},
            { data: 'patientDetails', name: 'patientDetails' },
            { data: 'InventoryDetails', name: 'InventoryDetails' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "400px"
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
                width: "200px"
            }
        ]
    });
    viewReqMC.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewReqMC.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewReqMC.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Requisition Medication Consumption

    // Update Requisition Medication Consumption Status
    $(document).on('click', '.rmc_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/medicalrecord/reqmc-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-reqmc').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Requisition Medication Consumption Status

    //Update Requisition Medication Consumption Modal
    $(document).on('click', '.edit-reqmc', function() {
        var Id = $(this).data('reqmc-id');
        $('#ajax-loader').show();
        var url = '/medicalrecord/updatereqmc/' + Id;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                $('#u_rmc_remarks').val(response.Remarks);
                $('#reqmc_id').val(response.id);
                $('#u_rmc_transaction_type').html("<option selected value='"+response.TransactionTypeId+"'>" + response.TransactionType + "</option>");
                // fetchTransactionTypes(response.orgId, '#u_mc_transactionType', true, function(data) {
                //     if (data && data.length > 0) {
                //         $.each(data, function(key, value) {
                //             if(value.id != response.TransactionTypeId){
                //                 $('#u_rmc_transaction_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                //             }
                //         });
                //     }
                // });

                fetchMaterialManagementTransactionTypes(response.orgId, '#u_rmc_transaction_type','issue_dispense','y', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.TransactionTypeId){
                            $('#u_rmc_transaction_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                    // Ensure currently selected shows even with select2
                    $('#u_rmc_transaction_type').val(response.TransactionTypeId).trigger('change.select2');
                });

                // Initialize source and destination location dropdowns for update modal
                if (response.SourceLocationId && response.SourceLocationName) {
                    $('.u_rmc_source_location').show();
                    $('#u_rmc_source_location').html("<option selected value='" + response.SourceLocationId + "'>" + response.SourceLocationName + "</option>");
                } else {
                    $('.u_rmc_source_location').hide();
                    $('#u_rmc_source_location').html("<option selected disabled value=''>Select Source Location</option>");
                }

                if (response.DestinationLocationId && response.DestinationLocationName) {
                    $('.u_rmc_destination_location').show();
                    $('#u_rmc_destination_location').html("<option selected value='" + response.DestinationLocationId + "'>" + response.DestinationLocationName + "</option>");
                } else {
                    $('.u_rmc_destination_location').hide();
                    $('#u_rmc_destination_location').html("<option selected disabled value=''>Select Destination Location</option>");
                }
                
                // Hydrate lists based on current transaction type
                $.ajax({
                    url: '/inventory/gettransactiontypeim',
                    type: 'GET',
                    data: { 
                        transactionTypeId: response.TransactionTypeId, 
                        siteId: response.siteId,
                        transactionType: 'requisition'
                    },
                    success: function(resp) {
                        var sourceType = (resp.Source || '').toLowerCase();
                        var destType = (resp.Destination || '').toLowerCase();

                        if (sourceType.includes('location')) {
                            $('.u_rmc_source_location').show();
                            $('#u_rmc_source_location').prop('required', true).prop('disabled', false);
                            if (resp.sourceData && resp.sourceData.length > 0) {
                                resp.sourceData.forEach(function(item){
                                    if (item.id != response.SourceLocationId) {
                                        $('#u_rmc_source_location').append('<option value="' + item.id + '">' + (item.name || '') + '</option>');
                                    }
                                });
                            }
                        } else {
                            $('.u_rmc_source_location').hide();
                            $('#u_rmc_source_location').prop('required', false).val('').prop('disabled', true);
                        }

                        // Only show destination when it is inventory location
                        if (destType.includes('location')) {
                            $('.u_rmc_destination_location').show();
                            $('#u_rmc_destination_location').prop('required', true).prop('disabled', false);
                            if (resp.destinationData && resp.destinationData.length > 0) {
                                resp.destinationData.forEach(function(item){
                                    if (item.id != response.DestinationLocationId) {
                                        $('#u_rmc_destination_location').append('<option value="' + item.id + '">' + (item.name || '') + '</option>');
                                    }
                                });
                            }
                        } else {
                            $('.u_rmc_destination_location').hide();
                            $('#u_rmc_destination_location').prop('required', false).val('').prop('disabled', true);
                        }
                    }
                });
                
                // Handle transaction type change for source and destination locations in update modal
                $(document).off('change', '#u_rmc_transaction_type').on('change', '#u_rmc_transaction_type', function() {
                    var transactionTypeId = $(this).val();
                    if (!transactionTypeId) { return; }
                    $.ajax({
                        url: '/inventory/gettransactiontypeim',
                        type: 'GET',
                        data: { 
                            transactionTypeId: transactionTypeId, 
                            siteId: response.siteId,
                            transactionType: 'requisition'
                        },
                        success: function(resp) {
                            var sourceType = (resp.Source || '').toLowerCase();
                            var destType = (resp.Destination || '').toLowerCase();

                            // Reset
                            $('#u_rmc_source_location').empty().append('<option selected disabled value="">Select Source Location</option>');
                            $('#u_rmc_destination_location').empty().append('<option selected disabled value="">Select Destination Location</option>');

                            // Update source list
                            if (sourceType.includes('location')) {
                                $('.u_rmc_source_location').show();
                                $('#u_rmc_source_location').prop('required', true).prop('disabled', false);
                                if (resp.sourceData && resp.sourceData.length > 0) {
                                    resp.sourceData.forEach(function(item){
                                        $('#u_rmc_source_location').append('<option value="' + item.id + '">' + (item.name || '') + '</option>');
                                    });
                                }
                            } else {
                                $('.u_rmc_source_location').hide();
                                $('#u_rmc_source_location').prop('required', false).val('').prop('disabled', true);
                            }

                            // Update destination list (only inventory location)
                            if (destType.includes('location')) {
                                $('.u_rmc_destination_location').show();
                                $('#u_rmc_destination_location').prop('required', true).prop('disabled', false);
                                if (resp.destinationData && resp.destinationData.length > 0) {
                                    resp.destinationData.forEach(function(item){
                                        $('#u_rmc_destination_location').append('<option value="' + item.id + '">' + (item.name || '') + '</option>');
                                    });
                                }
                            } else {
                                $('.u_rmc_destination_location').hide();
                                $('#u_rmc_destination_location').prop('required', false).val('').prop('disabled', true);
                            }
                        }
                    });
                });


                var genericIds = response.genericIds.split(',');
                var genericNames = response.genericNames.split(',');
                var dose = response.Dose.split(',');
                var days = response.Days.split(',');
                var routeIds = response.routeIds.split(',');
                var routeNames = response.routeNames.split(',');
                var frequencyIds = response.frequencyIds.split(',');
                var frequencyNames = response.frequencyNames.split(',');

                $('.uduplicate').empty();
                for (var i = 0; i < genericIds.length; i++) {
                    var GenericField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Inventory Generic</label>' +
                        '<select class="form-control selecter p-0 u_rmc_inv_generic" name="u_rmc_inv_generic[]" id="u_rmc_inv_generic' + i + '" required style="color:#222d32">' +
                        '<option value="' + genericIds[i] + '"> ' + genericNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchInventoryGenerics('#u_rmc_inv_generic' + index, 'medication', function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), genericIds[index]) === -1)
                                    {
                                        $('#u_rmc_inv_generic' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_rmc_inv_generic' + index).select2();
                            }
                        });
                    })(i);

                    var routeField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Route</label>' +
                        '<select class="form-control selecter p-0 u_rmc_route" name="u_rmc_route[]" id="u_rmc_route' + i + '" required style="color:#222d32">' +
                        '<option value="' + routeIds[i] + '"> ' + routeNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchMedicationRoute('#u_rmc_route' + index, function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), routeIds) === -1) {
                                        $('#u_rmc_route' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_rmc_route' + index).select2();
                            }
                        });
                    })(i);



                    var frequencyField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Frequency</label>' +
                        '<select class="form-control selecter p-0 u_rmc_frequency" name="u_rmc_frequency[]" id="u_rmc_frequency' + i + '" required style="color:#222d32">' +
                        '<option value="' + frequencyIds[i] + '"> ' + frequencyNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchMedicationFrequency('#u_rmc_frequency' + index, function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), frequencyIds[index]) === -1)
                                    {
                                        $('#u_rmc_frequency' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_rmc_frequency' + index).select2();
                            }
                        });
                    })(i);

                    var doseField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Dose</label>' +
                        '<input type="text" class="form-control input-sm" required name="u_rmc_dose[]" value="' + dose[i] + '">' +
                        '</div>' +
                        '<span class="text-danger u_rmc_dose_error"></span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    var daysField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Days</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_rmc_days[]"  value="' + days[i] + '">' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';


                    var row =+ '</div>';
                    $('.uduplicate').append('<div class="row pt-3 pb-1 rmc_details" style="border: 1px solid #939393;">' + GenericField + doseField + routeField + frequencyField + daysField +'</div>');
                }

                $('#edit-reqmc').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Requisition Medication Consumption Modal

    //Update Requisition For Medication Consumption
    $('#update_reqmc').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('#reqmc_id').val();
        var url = '/medicalrecord/update-reqmc/' + Id;
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
                            $('#edit-reqmc').modal('hide');
                            $('#view-reqmc').DataTable().ajax.reload();
                            $('.text-danger').hide();
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update Requisition For Medication Consumption
});
