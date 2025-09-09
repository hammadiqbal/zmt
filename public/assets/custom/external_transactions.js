$(document).ready(function() {
    //Open External Transaction Modal
    $(document).on('click', '.add-externaltransactions', function() {
        // $('.text-danger').show();
        $('.duplicate:not(:first)').remove();
        $('.duplicate').each(function(){
            $(this).find('input[type="text"], input[type="number"], textarea').val('');
            $(this).find('input[type="checkbox"], input[type="radio"]')
                   .prop('checked', false);
            $(this).find('select').prop('selectedIndex', 0);
        });
        $('#et_dl,#et_sl').hide();
        $('.hide_init_row').hide();
        $('.hide_init_btn')
        .removeClass('d-flex justify-content-center') // remove any !important display rules
        .hide(); 
        var orgId = $('#et_org').val();
        $('#et_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
        if(orgId)
        {
            $('#et_site').html("<option selected disabled value=''>Select Site</option>");
            fetchOrganizationSites(orgId, '#et_site', function(data) {
                $.each(data, function(key, value) {
                    $('#et_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
            $('.et_generic').html("<option selected disabled value=''>Select Item Generic</option>");
            fetchOrganizationItemGeneric(orgId, '.et_generic', function(data) {
                    console.log(data);
                $.each(data, function(key, value) {
                    $('.et_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

        }
        else{
            $('#et_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#et_org', function(data) {
                $('#et_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#et_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            
            $('#et_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#et_org', '#et_site', '#add_externaltransactions');
            
            $('.et_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#et_org', '.et_generic', '#add_externaltransactions');
        }
        SiteChangeMaterialManagementTransactionTypes('#et_site','#et_org', '#et_transactiontype', '#add_externaltransactions','external_transaction');

        $(document).off('change', '#et_transactiontype').on('change', '#et_transactiontype', function() {
            let transactionTypeID = $(this).val();
            let siteId = $('#et_site').val();  // you must already have a selected site
        
            if (!siteId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Site Required',
                    text: 'Please select a site before choosing the transaction type.'
                });
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
                    if (!resp.Source && !resp.Destination) {
                        Swal.fire({
                            icon: 'error',
                            text: 'No transaction type data found.'
                        });
                        return;
                    }

                    $('.hide_init_row').show();
                    $('.hide_init_btn')
                    .addClass('d-flex justify-content-center') 
                    .show();  

                    const oldStatus  = previousExpiryStatus;
                    const newStatus  = resp.transaction_expired_status;
                    const statusChanged = oldStatus === null || newStatus !== oldStatus;

                    previousExpiryStatus   = newStatus;
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    initialExpiryMinDate   = (newStatus == 'n') ? tomorrow : null;

                    $('.et_expiry').each(function() {
                        if (statusChanged) {
                            if ($(this).data('bootstrap-material-datetimepicker')) {
                                $(this).bootstrapMaterialDatePicker('destroy');
                            }
                            $(this).val(''); 
                            $(this).off();
                            $(this).removeData(); 
                            $(this).removeAttr('data-value'); 
                        }
                        initExpiryPicker($(this));
                        // let datePickerOptions = {
                        //     format: 'YYYY-MM-DD',
                        //     time: false,
                        //     minDate: null 
                        // };

                        // if (resp.transaction_expired_status === 'n') {
                        //     datePickerOptions.minDate = tomorrow;
                        // } else if (resp.transaction_expired_status === 'y') {
                        //     datePickerOptions.minDate = null; 
                        // }
                        // $(this).bootstrapMaterialDatePicker(datePickerOptions);
                    }); 


                    // 1) Show/hide #et_sl, set label
                    let sourceType = (resp.Source || '').toLowerCase();
                    if (sourceType.includes('location')) {
                        $('#et_sl').show();
                        $('#et_sl label').text('Inventory Source Location');
                    }
                    else if (sourceType.includes('vendor')) {
                        $('#et_sl').show();
                        $('#et_sl label').text('Select Source Vendor');
                    }
                    else if (sourceType.includes('donor')) {
                        $('#et_sl').show();
                        $('#et_sl label').text('Select Source Donor');
                    }
                    else {
                        $('#et_sl').hide();
                    }
        
                    // 2) Show/hide #et_dl, set label
                    let destType = (resp.Destination || '').toLowerCase();
                    if (destType.includes('location')) {
                        $('#et_dl').show();
                        $('#et_dl label').text('Inventory Destination Location');
                    }
                    else if (destType.includes('vendor')) {
                        $('#et_dl').show();
                        $('#et_dl label').text('Select Destination Vendor');
                    }
                    else if (destType.includes('donor')) {
                        $('#et_dl').show();
                        $('#et_dl label').text('Select Destination Donor');
                    }
                    else {
                        $('#et_dl').hide();
                    }

                    if (resp.sourceData && resp.sourceData.length > 0) {
                        $('#et_source')
                            .empty()
                            .append('<option selected disabled value="">Select Source</option>');
                        resp.sourceData.forEach(function(item) {
                            let displayText = '';

                            if (item.person_name) {
                                displayText = (item.prefix ? item.prefix + ' ' : '') + item.person_name;
                            } else {
                                displayText = item.name || 'Unnamed';
                            }
                            if (item.corporate_name) {
                                displayText += ' - ' + item.corporate_name;
                            }
                            $('#et_source').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });

                        $('#et_source').prop('disabled', false);
                    } else {
                        $('#et_source')
                            .empty()
                            .append('<option selected disabled value="">No Data Available</option>')
                            .prop('disabled', true);
                    }

                    if (resp.destinationData && resp.destinationData.length > 0) {
                        $('#et_destination')
                            .empty()
                            .append('<option selected disabled value="">Select Destination</option>');

                        resp.destinationData.forEach(function(item) {
                            let displayText = '';
                            if (item.person_name) {
                                displayText = (item.prefix ? item.prefix + ' ' : '') + item.person_name;
                            } else {
                                displayText = item.name || 'Unnamed';
                            }
                            if (item.corporate_name) {
                                displayText += ' - ' + item.corporate_name;
                            }
                            $('#et_destination').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });

                        $('#et_destination').prop('disabled', false);
                    } else {
                        $('#et_destination')
                            .empty()
                            .append('<option selected disabled value="">No Data Available</option>')
                            .prop('disabled', true);
                    }

                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });
       
        $('.et_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
        // GenericChangeBrand('.et_generic', '.et_brand', '#add_externaltransactions');
       $('#add-externaltransactions').modal({
            backdrop: 'static',
            keyboard: false 
        }); 
    });

    // $(document).on('change', '.et_generic', function() {
    $(document).off('change', '.et_generic').on('change', '.et_generic', function() {
        var genericId = $(this).val();
        var currentRow = $(this).closest('.duplicate'); // Find the current row
        var currentRowCCSelect = currentRow.find('.et_brand'); // Find the cost center dropdown in the current row
    
        if (genericId) {
            fetchGenericItemBrand(genericId, currentRowCCSelect, function(data) {
                if (data.length > 0) {
                    currentRowCCSelect.empty();
                    currentRowCCSelect.append('<option selected disabled value="">Select Brand</option>');
                    $.each(data, function(key, value) {
                        currentRowCCSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowCCSelect.find('option:contains("Loading...")').remove();
                    currentRowCCSelect.prop('disabled', false);
                } else {
                    Swal.fire({
                        text: 'Brands are not available for the selected Item Generic',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentRowCCSelect.empty();
                            currentRowCCSelect.html("<option selected disabled value=''>Select Brand</option>").prop('disabled', true);
                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });
        } else {
            currentRowCCSelect.empty();
            currentRowCCSelect.html("<option selected disabled value=''>Select Brand</option>").prop('disabled', true);
        }
    });

    // BrandChangeBatch('.et_brand', '#u_im_batch_no', '#add_externaltransactions');

    // $('.et_brand').off('change.etBrand').on ('change.etBrand', function(){
    //     var brandId = $(this).val();
    //     var currentRow = $(this).closest('.duplicate'); 
    //     var currentRowBrandSelect = currentRow.find('.et_brand'); 
    
    //     if (brandId) {
    //          fetchBrandBatch(brandId,currentRowBrandSelect, function(data) {
    //                 currentRowBrandSelect.find('option:contains("Loading...")').remove();
    //                 $.each(data, function(key, value) {
    //                     currentRowBrandSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
    //                 });
    //             });
    //     } 
    //     else {
    //         currentRowCCSelect.empty();
    //         currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    //     }
    // });

    $('#add_externaltransactions').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        var requireSource = $('#et_sl').is(':visible');
        var requireDestination = $('#et_dl').is(':visible');

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

        var excludedFields = ['et_reference_document', 'et_remarks'];
        $(data).each(function(i, field){
            var FieldName = field.name;

            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true; 
            }
            if ((FieldName === 'et_source' && !requireSource) || (FieldName === 'et_destination' && !requireDestination)) {
                return true;
            }
            if ((field.value == '') || (field.value == null))
            {
                var FieldID = '#'+sanitizedFieldName + "_error";
              
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
        if (resp) {
            $.ajax({
                url: "/inventory/addexternaltransaction",
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
                    if (response.error) {
                        Swal.fire({
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (response.success) {
                        Swal.fire({
                            text: response.success,
                            icon: 'success',
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-externaltransactions').modal('hide');
                                $('#view-externaltransactions').DataTable().ajax.reload();
                                $('#add_externaltransactions')[0].reset();
                                // $('#add_externaltransactions').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
                                $('.text-danger').hide();
                            }
                        });
                    } else if (response.info) {
                        Swal.fire({
                            text: response.info,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-externaltransactions').modal('hide');
                                $('#view-externaltransactions').DataTable().ajax.reload();
                                $('#add_externaltransactions')[0].reset();
                            }
                        });
                    }
                },
                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        $('.text-danger').show();
                        var errors = error.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            // 'field' might still have [] or not,
                            // so if your error IDs are sanitized, remove [] before targeting
                            var sanitizedField = field.replace(/\[\]/g, '');
                            var errorSelector = '#' + sanitizedField + '_error';
                            $(errorSelector).text(messages.join(' '));
                        });
                        Swal.close();
                    }
                }
            });
        }
    });
    //Add External Transaction

    // View External Transaction
    var viewExternalTransaction =  $('#view-externaltransactions').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/externaltransaction',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'transaction_details', name: 'transaction_details' },
            { data: 'item_details', name: 'item_details' },
            // { data: 'transaction_details', name: 'transaction_details' },
            // { data: 'status', name: 'status' },
            // { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            }
            // ,
            //   {
            //     targets: 3,
            //     width: "350px"
            // }
            // ,
            // {
            //     targets: 5,
            //     width: "350px"
            // }
        ]
    });

    viewExternalTransaction.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewExternalTransaction.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewExternalTransaction.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View External Transaction

});