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
        
                    // 3) Populate #et_source select
                    $('#et_source')
                        .empty()
                        .append('<option selected disabled value="">Select Source</option>');
        
                    if (resp.sourceData && resp.sourceData.length > 0) {
                        resp.sourceData.forEach(function(item) {
                            // "Inventory Location" => item.name
                            // "Vendor/Donor" => item.person_name
                            // We'll display whichever is present:
                            let displayText = item.name || item.person_name || 'Unnamed';
                            $('#et_source').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });
                        $('#et_source').prop('disabled', false);
                    } else {
                        $('#et_source').prop('disabled', true);
                    }
        
                    // 4) Populate #et_destination select
                    $('#et_destination')
                        .empty()
                        .append('<option selected disabled value="">Select Destination</option>');
        
                    if (resp.destinationData && resp.destinationData.length > 0) {
                        resp.destinationData.forEach(function(item) {
                            let displayText = item.name || item.person_name || 'Unnamed';
                            $('#et_destination').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });
                        $('#et_destination').prop('disabled', false);
                    } else {
                        $('#et_destination').prop('disabled', true);
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
        $('#add-externaltransactions').modal('show');
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
            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true; 
            }
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
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

    

    // //Update Inventory Management Modal
    // $(document).on('click', '.edit-externaltransactions', function() {
    //     var inventoryId = $(this).data('externaltransactions-id');
    //     var url = '/inventory/updateinvmanagement/' + inventoryId;
    //     $('#ajax-loader').show();
    //     $.ajax({
    //         url: url,
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             $('#u_im_brand').empty();
    //             $('#u_et_site').empty();
    //             $('#ajax-loader').hide();
    //             var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
    //             $('.uedt').each(function() {
    //                 var edtElement = $(this);
    //                 edtElement.val(formattedDateTime);
    //             });
    //             $('.u_im-id').val(response.id);
    //             var transactionType =  response.TransactionType;

    //             $('#u_im_qty').prop('disabled',false);

    //             var orgId = $('#u_et_org').val();
    //             if(!orgId)
    //             {
    //                 $('#u_et_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
    //                 fetchOrganizations('null', '','#u_et_org', function(data) {
    //                     $('#u_et_org').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.orgId)
    //                         {
    //                             $('#u_et_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
    //                         }
    //                     });
    //                 });
    //                 OrgChangeSites('#u_et_org', '#u_et_site', '#add_externaltransactions');
    //                 OrgChangeBrand('#u_et_org', '#u_im_brand', '#add_externaltransactions');

    //             }
    //             $('#u_et_site').html("<option selected value='"+response.siteId+"'>" + response.siteName + "</option>");
    //             fetchSites(response.orgId, '#u_et_site', function(data) {
    //                 if (data.length > 0) {
    //                     $.each(data, function(key, value) {
    //                         $('#u_et_site').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                     });
    //                 }
    //                 else {
    //                     Swal.fire({
    //                         text: 'Sites are not available for selected Organization',
    //                         icon: 'error',
    //                         confirmButtonText: 'OK'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             $('#edit-materialconsumption').modal('hide');
    //                         }
    //                     });
    //                 }
    //             }, function(error) {
    //                 console.log(error);
    //             },response.siteId);

    //             $('#u_im_brand').html("<option selected value='"+response.brandId+"'>" + response.brandName + "</option>");
    //             fetchOrganizationBrand(response.orgId,'#u_im_brand', function(data) {
    //                 $('#u_im_brand').find('option:contains("Loading...")').remove();
    //                 $.each(data, function(key, value) {
    //                     if(value.id != response.brandId)
    //                     {
    //                         $('#u_im_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                     }
    //                 });
    //             });
    //             $('#u_et_transactiontype').html("<option selected  data-type="+ transactionType +" value="+response.transactionTypeId+">"+response.TransactionTypeName+"</option>").prop('disabled',true);
                
    //             if(transactionType == 'opening balance')
    //             {
    //                 $('#u_reference_document_section, #u_from_section, #u_to_section').hide();
    //                 var referenceDocument = 'null';
    //                 var From = 'null';
    //                 var To = 'null';
    //                 $('#u_selectoption').hide();
    //                 $('#u_selectoption select').removeAttr('name id');
    //                 $('#u_opentext').show();
    //                 $('#u_opentext input').attr({
    //                     'name': 'u_im_reference_document',
    //                     'id': 'u_im_reference_document'
    //                 });
    //                 $('#u_im_reference_document').val(response.document_no);

    //                 $('#u_select_batch select').select2('destroy');
    //                 $('#u_select_batch').find('label, select').remove();
    //                 $('#u_enter_batch').show();
    //                 $('#u_enter_batch').html('<label for="im_reference_document">Update Batch #</label><input type="text" class="form-control input-sm">');
    //                 $('#u_enter_batch input').attr({
    //                     'name': 'u_im_batch_no',
    //                     'id': 'u_im_batch_no'
    //                 });
    //                 $('#u_im_batch_no').val(response.batchNo);

    //             }
    //             else if(transactionType == 'addition')
    //             {
    //                 $('#u_reference_document_section, #u_from_section, #u_to_section').show();
    //                 var referenceDocument = 'Open Text';
    //                 if(!orgId)
    //                 {
    //                     OrgChangeVendor('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //                     OrgChangeSites('#u_et_org', '#u_im_destination', '#update_externaltransactions');
    //                 }
    //                 $('#u_im_origin').attr({'required': 'required'});
    //                 $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
    //                 fetchOrganizationVendor(response.orgId, '#u_im_origin', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.OriginId)
    //                         {
    //                             $('#u_im_origin').append('<option value="' + value.id + '">' + value.person_name + '</option>');
    //                         }
    //                     });
    //                 });

    //                 $('#u_im_destination').attr({'required': 'required'});
    //                 $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
    //                 fetchOrganizationSites(response.orgId, '#u_im_destination', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.DestinationId)
    //                         {
    //                             $('#u_im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                         }
    //                     });
    //                 });
                    
    //                 $('#u_selectoption').hide();
    //                 $('#u_selectoption select').removeAttr('name id');
    //                 $('#u_opentext').show();
    //                 $('#u_opentext input').attr({
    //                     'name': 'u_im_reference_document',
    //                     'id': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });
    //                 $('#u_im_reference_document').val('');
    //                 $('#u_im_reference_document').val(response.document);

    //                 $('#u_select_batch select').select2('destroy');
    //                 $('#u_select_batch').find('label, select').remove();
    //                 $('#u_enter_batch').show();
    //                 $('#u_enter_batch').html('<label for="im_reference_document">Update Batch #</label><input type="text" class="form-control input-sm">');
    //                 $('#u_enter_batch input').attr({
    //                     'name': 'u_im_batch_no',
    //                     'id': 'u_im_batch_no'
    //                 });
    //                 $('#u_im_batch_no').val(response.batchNo);

    //             }
    //             else if(transactionType == 'reduction')
    //             {
    //                 $('#u_opentext').hide();
    //                 $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
    //                 $('#u_opentext input').removeAttr('name id');
    //                 $('#u_selectoption').show();
    //                 $('#u_selectoption select').attr({
    //                     'id': 'u_im_reference_document',
    //                     'name': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });
    //                 $('#u_im_reference_document').empty();
    //                 $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
    //                 if(!orgId)
    //                 {
    //                     OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //                     OrgChangeVendor('#u_et_org', '#u_im_destination', '#update_externaltransactions');

    //                 }
                    
    //                 fetchBrandInventory(response.brandId, '#u_im_reference_document', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.documentId)
    //                         {
    //                             $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.code+'-00000'+value.id + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_externaltransactions');

    //                 $('#u_im_origin').attr({'required': 'required'});
    //                 $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
    //                 fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.DestinationId)
    //                         {
    //                             $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                         }
    //                     });
    //                 });


    //                 $('#u_im_destination').attr({'required': 'required'});
    //                 $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
    //                 fetchOrganizationVendor(response.orgId, '#u_im_destination', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.DestinationId)
    //                         {
    //                             $('#u_im_destination').append('<option value="' + value.id + '">' + value.person_name + '</option>');
    //                         }
    //                     });
    //                 });
    //                 $('#u_reference_document_section, #u_from_section, #u_to_section').show();

    //                 $('#u_select_batch').show();
    //                 $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
    //                 $('#u_enter_batch').hide();
    //                 $('#u_enter_batch').find('label, input').remove();
    //                 $('#u_select_batch select').attr({
    //                     'id': 'u_im_batch_no',
    //                     'name': 'u_im_batch_no'
    //                 }).select2();
    //                 $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
    //                 fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
    //                     $('#u_im_batch_no').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.id)
    //                         {
    //                             $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_externaltransactions');
    //                 BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'reduction');
    //             }
    //             else if(transactionType == 'transfer')
    //             {
    //                 $('#u_reference_document_section').hide();
    //                 $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
    //                 $('#u_from_section, #u_to_section').show();
    //                 $('#u_selectoption').hide();
    //                 $('#u_selectoption select').removeAttr('name id');
    //                 $('#u_opentext').show();
    //                 $('#u_opentext input').attr({
    //                     'name': 'u_im_reference_document',
    //                     'id': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });

    //                 if(!orgId)
    //                 {
    //                     OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //                     OrgChangeSites('#u_et_org', '#u_im_destination', '#update_externaltransactions');
    //                 }

    //                 $('#u_im_origin').attr({'required': 'required'});
    //                 $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
    //                 fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.OriginId)
    //                         {
    //                             $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                         }
    //                     });
    //                 });

    //                 $('#u_im_destination').attr({'required': 'required'});
    //                 $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
    //                 fetchOrganizationSites(response.orgId, '#u_im_destination', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.DestinationId)
    //                         {
    //                             $('#u_im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                         }
    //                     });
    //                 });

    //                 $('#u_select_batch').show();
    //                 $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
    //                 $('#u_enter_batch').hide();
    //                 $('#u_enter_batch').find('label, input').remove();
    //                 $('#u_select_batch select').attr({
    //                     'id': 'u_im_batch_no',
    //                     'name': 'u_im_batch_no'
    //                 }).select2();
    //                 $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
    //                 fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
    //                     $('#u_im_batch_no').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.id)
    //                         {
    //                             $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_externaltransactions');
    //                 BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'transfer');
    //             }
    //             else if(transactionType == 'general consumption')
    //             {
    //                 $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
    //                 $('#u_to_section').hide();
    //                 $('#u_reference_document_section, #u_from_section').show();

    //                 $('#u_im_origin').attr({'required': 'required'});
    //                 $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
    //                 fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.DestinationId)
    //                         {
    //                             $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                         }
    //                     });
    //                 });
    //                 if(!orgId)
    //                 {
    //                     OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //                 }

    //                 $('#u_opentext').hide();
    //                 $('#u_opentext input').removeAttr('name id');
    //                 $('#u_selectoption').show();
    //                 $('#u_selectoption select').attr({
    //                     'id': 'u_im_reference_document',
    //                     'name': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });
    //                 $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
    //                 fetchSiteRequisition(response.siteId, response.transactionTypeId, '#u_im_reference_document', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.documentId)
    //                         {
    //                             $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.remarks + '</option>');
    //                         }
    //                     });
    //                 });
    //                 SiteChangeRequisition('#u_et_site', '#u_et_transactiontype', '#u_im_reference_document', '#update_externaltransactions');
                    
    //                 $('#u_select_batch').show();
    //                 $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
    //                 $('#u_enter_batch').hide();
    //                 $('#u_enter_batch').find('label, input').remove();
    //                 $('#u_select_batch select').attr({
    //                     'id': 'u_im_batch_no',
    //                     'name': 'u_im_batch_no'
    //                 }).select2();
    //                 $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
    //                 fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
    //                     $('#u_im_batch_no').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.id)
    //                         {
    //                             $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_externaltransactions');
    //                 BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'general_consumption');
    //             }
    //             else if(transactionType == 'patient consumption')
    //             {
    //                 $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
    //                 $('#u_reference_document_section, #u_from_section, #u_to_section').show();

    //                 $('#u_im_origin').attr({'required': 'required'});
    //                 $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
    //                 fetchOrganizationVendor(response.orgId, '#u_im_origin', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.OriginId)
    //                         {
    //                             $('#u_im_origin').append('<option value="' + value.id + '">' + value.person_name + '</option>');
    //                         }
    //                     });
    //                 });
    //                 if(!orgId)
    //                 {
    //                     OrgChangeVendor('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //                 }
    //                 $('#u_im_destination').attr({'required': 'required'});
    //                 $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
    //                 fetchPatientMR(response.siteId, '#u_im_destination', null, function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.mr_code != response.DestinationId)
    //                         {
    //                             $('#u_im_destination').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
    //                         }
    //                     });
    //                 });
    //                 SiteChangeMRCode('#u_et_site', '#u_im_destination', '#update_externaltransactions', null);
    //                 $('#u_opentext').hide();
    //                 $('#u_opentext input').removeAttr('name id');
    //                 $('#u_selectoption').show();
    //                 $('#u_selectoption select').attr({
    //                     'id': 'u_im_reference_document',
    //                     'name': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });
    //                 $('#u_im_reference_document').html("<option selected disabled value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
    //                 fetchSiteRequisition(response.siteId, response.transactionTypeId, '#u_im_reference_document', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.documentId)
    //                         {
    //                             $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.remarks + '</option>');
    //                         }
    //                     });
    //                 });
    //                 SiteChangeRequisition('#u_et_site', '#u_et_transactiontype', '#u_im_reference_document', '#update_externaltransactions');
                    
    //                 $('#u_select_batch').show();
    //                 $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
    //                 $('#u_enter_batch').hide();
    //                 $('#u_enter_batch').find('label, input').remove();
    //                 $('#u_select_batch select').attr({
    //                     'id': 'u_im_batch_no',
    //                     'name': 'u_im_batch_no'
    //                 }).select2();
    //                 $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
    //                 fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
    //                     $('#u_im_batch_no').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.id)
    //                         {
    //                             $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_externaltransactions');
    //                 BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'patient_consumption');
    //             }
    //             else if(transactionType == 'reversal')
    //             {
    //                 $('#u_im_expirydate,#u_im_rate,#u_im_qty').prop('disabled',true);
    //                 $('#u_opentext').hide();
    //                 $('#u_opentext input').removeAttr('name id');
    //                 $('#u_selectoption').show();
    //                 $('#u_selectoption select').attr({
    //                     'id': 'u_im_reference_document',
    //                     'name': 'u_im_reference_document',
    //                     'required': 'required'
    //                 });
    //                 $('#u_im_reference_document').empty();
    //                 $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
    //                 fetchBrandInventory(response.brandId, '#u_im_reference_document', function(data) {
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.documentId)
    //                         {
    //                             $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.code+'-00000'+value.id + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_externaltransactions');
    //                 $('#u_reference_document_section').show();
    //                 $('#u_from_section, #u_to_section').hide();

    //                 $('#u_select_batch').show();
    //                 $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
    //                 $('#u_enter_batch').hide();
    //                 $('#u_enter_batch').find('label, input').remove();
    //                 $('#u_select_batch select').attr({
    //                     'id': 'u_im_batch_no',
    //                     'name': 'u_im_batch_no'
    //                 }).select2();
    //                 $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
    //                 fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
    //                     $('#u_im_batch_no').find('option:contains("Loading...")').remove();
    //                     $.each(data, function(key, value) {
    //                         if(value.id != response.id)
    //                         {
    //                             $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
    //                         }
    //                     });
    //                 });
    //                 BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_externaltransactions');
    //                 BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'reversal');
    //             }

               
    //             var formattedExpiryDate = moment(response.expiryDate).format('YYYY-MM-DD');
    //             $('#u_im_expirydate').each(function() {
    //                 var expiryDateElement = $(this);
    //                 expiryDateElement.val(formattedExpiryDate);
    //             });
    //             $('#u_im_rate').val(response.rate);
    //             $('#u_im_qty').val(response.qty);
    //             var documentType = response.document_type;
                
    //             // fetchTransactionTypeOrganizations(TransactionTypeID,'#u_et_org', function(data) {
    //             //     $('#u_et_org').empty();
    //             //     $('#u_et_org').html("<option selected disabled value=''>Select Organization</option>");
    //             //     $('#u_et_org').find('option:contains("Loading...")').remove();
    //             //     $.each(data, function(key, value) {
    //             //         $('#u_et_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
    //             //     });
    //             // });
    //             // fetchTransactionTypes('null', '#u_et_transactiontype', false, function(data) {
    //             //     if (data && data.length > 0) {
    //             //         $.each(data, function(key, value) {
    //             //             if(value.id != response.transactionTypeId)
    //             //             {
    //             //                 $('#u_et_transactiontype').append('<option data-type="' + value.transaction_type + '" value="' + value.id + '">' + value.name + '</option>');
    //             //             }
    //             //         });
    //             //     } else {
    //             //         Swal.fire({
    //             //             text: 'Transaction Types are not currently available.',
    //             //             icon: 'error',
    //             //             confirmButtonText: 'OK'
    //             //         }).then((result) => {
    //             //             if (result.isConfirmed) {
    //             //                 $('#edit-externaltransactions').modal('hide');
    //             //             }
    //             //         });

    //             //     }
    //             // });

                
    //             // $(document).off('change', '#u_et_transactiontype').on('change', '#u_et_transactiontype', function() {
    //             // // $(document).on('change', '#u_et_transactiontype', function() {
    //             //     $('#u_et_org').val($(this).find('option:first').val()).trigger('change');
    //             //     $('#u_et_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
    //             //     $('#u_im_brand').html("<option selected disabled value=''>Select Brand</option>").prop('disabled',true);
    //             //     OrgChangeBrand('#u_et_org', '#u_im_brand', '#add_externaltransactions');

    //             //     var TransactionTypeID = $(this).val();
    //             //     var dataType = $(this).find('option:selected').data('type'); 
    //             //     $('#u_inv-management-section').show();
    //             //     var referenceDocument = '';
    //             //     var From = '';
    //             //     var To = '';

    //             //     if(dataType == 'opening balance')
    //             //     {
    //             //         $('#u_reference_document_section, #u_from_section, #u_to_section').hide();
    //             //         var referenceDocument = 'null';
    //             //         var From = 'null';
    //             //         var To = 'null';
    //             //         $('#u_selectoption').hide();
    //             //         $('#u_selectoption select').removeAttr('name id');
    //             //         $('#u_opentext').show();
    //             //         $('#u_opentext input').attr({
    //             //             'name': 'u_im_reference_document',
    //             //             'id': 'u_im_reference_document'
    //             //         });
    //             //     }
    //             //     else if(dataType == 'addition')
    //             //     {
    //             //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
    //             //         var referenceDocument = 'Open Text';
    //             //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
    //             //         OrgChangeVendor('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //             //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
    //             //         OrgChangeSites('#u_et_org', '#u_im_destination', '#update_externaltransactions');
    //             //         $('#u_selectoption').hide();
    //             //         $('#u_selectoption select').removeAttr('name id');
    //             //         $('#u_opentext').show();
    //             //         $('#u_opentext input').attr({
    //             //             'name': 'u_im_reference_document',
    //             //             'id': 'u_im_reference_document'
    //             //         });
    //             //     }
    //             //     else if(dataType == 'reduction')
    //             //     {
    //             //         $('#u_opentext').hide();
    //             //         $('#u_opentext input').removeAttr('name id');
    //             //         $('#u_selectoption').show();
    //             //         $('#u_selectoption select').attr({
    //             //             'id': 'u_im_reference_document',
    //             //             'name': 'u_im_reference_document'
    //             //         });
    //             //         $('#u_im_reference_document').empty();
    //             //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>");
    //             //         BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_externaltransactions');
    //             //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
    //             //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
    //             //         OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //             //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
    //             //         OrgChangeVendor('#u_et_org', '#u_im_destination', '#update_externaltransactions');
                        
    //             //     }
    //             //     else if(dataType == 'transfer')
    //             //     {
    //             //         $('#u_reference_document_section').hide();
    //             //         $('#u_from_section, #u_to_section').show();
    //             //         $('#u_selectoption').hide();
    //             //         $('#u_selectoption select').removeAttr('name id');
    //             //         $('#u_opentext').show();
    //             //         $('#u_opentext input').attr({
    //             //             'name': 'u_im_reference_document',
    //             //             'id': 'u_im_reference_document'
    //             //         });
    //             //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
    //             //         OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //             //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
    //             //         OrgChangeSites('#u_et_org', '#u_im_destination', '#update_externaltransactions');
    //             //     }
    //             //     else if(dataType == 'general consumption')
    //             //     {
    //             //         $('#u_to_section').hide();
    //             //         $('#u_reference_document_section, #u_from_section').show();
    //             //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
    //             //         OrgChangeSites('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //             //         $('#u_opentext').hide();
    //             //         $('#u_opentext input').removeAttr('name id');
    //             //         $('#u_selectoption').show();
    //             //         $('#u_selectoption select').attr({
    //             //             'id': 'u_im_reference_document',
    //             //             'name': 'u_im_reference_document'
    //             //         });
    //             //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
    //             //         SiteChangeRequisition('#u_et_site', '#u_et_transactiontype', '#u_im_reference_document', '#update_externaltransactions');
    //             //     }
    //             //     else if(dataType == 'patient consumption')
    //             //     {
    //             //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
    //             //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
    //             //         OrgChangeVendor('#u_et_org', '#u_im_origin', '#update_externaltransactions');
    //             //         $('#u_im_destination').html("<option selected disabled value=''>Select MR#</option>").prop('disabled',true);
    //             //         SiteChangeMRCode('#u_et_site', '#u_im_destination', '#update_externaltransactions', null);
    //             //         $('#u_opentext').hide();
    //             //         $('#u_opentext input').removeAttr('name id');
    //             //         $('#u_selectoption').show();
    //             //         $('#u_selectoption select').attr({
    //             //             'id': 'u_im_reference_document',
    //             //             'name': 'u_im_reference_document'
    //             //         });
    //             //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
    //             //         SiteChangeRequisition('#u_et_site', '#u_et_transactiontype', '#u_im_reference_document', '#update_externaltransactions');
        
    //             //     }
    //             //     else if(dataType == 'reversal')
    //             //     {
    //             //         $('#u_opentext').hide();
    //             //         $('#u_opentext input').removeAttr('name id');
    //             //         $('#u_selectoption').show();
    //             //         $('#u_selectoption select').attr({
    //             //             'id': 'u_im_reference_document',
    //             //             'name': 'u_im_reference_document'
    //             //         });
    //             //         $('#u_im_reference_document').empty();
    //             //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>");
                       
    //             //         BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_externaltransactions');
    //             //         $('#u_reference_document_section').show();
    //             //         $('#u_from_section, #u_to_section').hide();
    //             //     }
    //             // });
    //             $('#edit-externaltransactions').modal('show');
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             $('#ajax-loader').hide();
    //             console.log(textStatus, errorThrown);
    //         }
    //     });
    // });
    // //Update Inventory Management Modal

    // //Update Inventory Management
    // $('#update_externaltransactions').on('submit', function (event) {
    //     event.preventDefault();
    //     var formData = SerializeForm(this);
    //     var inventoryManagementId;
    //     for (var i = 0; i < formData.length; i++) {
    //         if (formData[i].name === 'u_im-id') {
    //             inventoryManagementId = formData[i].value;
    //             break;
    //         }
    //     }
    //     var url = 'inventory/update-invmanagement/' + inventoryManagementId;
    //     $.ajax({
    //         url: url,
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: formData,
    //         beforeSend: function() {
    //             Swal.fire({
    //                 title: "Processing",
    //                 allowOutsideClick: false,
    //                 willOpen: () => {
    //                     Swal.showLoading();
    //                 },
    //                 showConfirmButton: false
    //             });
    //         },
    //         success: function (response) {
    //             for (var fieldName in response) {
    //                 var fieldErrors = response[fieldName];
    //                 var fieldName = fieldName;
    //             }
    //             if (fieldName == 'error')
    //             {
    //                 Swal.fire({
    //                     text: fieldErrors,
    //                     icon: fieldName,
    //                     confirmButtonText: 'OK'
    //                 })
    //             }
    //             else if (fieldName == 'success')
    //             {
    //                 Swal.fire({
    //                     text: fieldErrors,
    //                     icon: fieldName,
    //                     allowOutsideClick: false,
    //                     confirmButtonText: 'OK'
    //                 }).then((result) => {
    //                     if (result.isConfirmed) {
    //                         $('#edit-externaltransactions').modal('hide');
    //                         $('#view-externaltransactions').DataTable().ajax.reload(); // Refresh DataTable
    //                         $('.text-danger').hide();
    //                     }
    //                 });
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             console.log(xhr.responseText);
    //         }
    //     });
    // });
    // //Update Inventory Management

});