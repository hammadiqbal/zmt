
//Item Rates
$(document).ready(function() {
    //Open Item Rates
    $(document).on('click', '.add-itemrate', function() {
        var orgId = $('#ir_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#ir_site', function(data) {
                $('#ir_site').html("<option selected disabled value=''>Select Site</option>");
                $.each(data, function(key, value) {
                    $('#ir_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchOrganizationItemGeneric(orgId,'#ir_generic', function(data) {
                $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>");
                $.each(data, function(key, value) {
                    $('#ir_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#ir_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#ir_org', function(data) {
                $('#ir_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ir_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#ir_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#ir_org', '#ir_site', '#add_financepayment');
    
            $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled',true);
            OrgChangeInventoryGeneric('#ir_org', '#ir_generic', '#add_itemrate');
        }
        $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled',true);
        GenericChangeBrand('#ir_generic', '#ir_brand', '#add_itemrate');
        
        $('#ir_batch').html("<option selected disabled value=''>Select Batch Number</option>").prop('disabled',true);
        BrandChangeBatchForItemRates('#ir_brand', '#ir_batch');
        
        // Add site change handler to reset dependent dropdowns
        $('#ir_site').off('change.siteChange').on('change.siteChange', function() {
            // Reset generic dropdown to first option
            $('#ir_generic').val($('#ir_generic option:first').val()).prop('disabled', false);
            
            // Reset brand dropdown to first option
            $('#ir_brand').val($('#ir_brand option:first').val()).prop('disabled', true);
            
            // Reset batch dropdown to first option
            $('#ir_batch').val($('#ir_batch option:first').val()).prop('disabled', true);
            
            // Trigger generic change to populate brands
            $('#ir_generic,#ir_brand,#ir_batch').trigger('change');
        });
        
        // $('#ft_discount').hide();
        // $('#fp_discount').attr('required', false);
        $('#add-itemrate').modal('show');
    });
    //Open Item Rates

    //Add Item Rates
    $('#add_itemrate').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && ((field.name != 'fp_paymentoptiondetails') && (field.name != 'fp_discount')))
            {
                var FieldName = field.name;
                var FieldName = field.name.replace('[]', '');
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

        if(resp != false)
        {
            $.ajax({
                url: "/finance/additemrates",
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
                    else if (fieldName == 'info')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add_itemrate')[0].reset();
                                $('#add_itemrate').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('.text-danger').hide();
                            }
                        });
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
                                $('#add-itemrate').modal('hide');
                                $('#view-itemrate').DataTable().ajax.reload();
                                $('#add_itemrate')[0].reset();
                                $('#add_itemrate').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('.text-danger').hide();
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
    //Add Item Rates

    // View Item Rates
    var viewItemRates =  $('#view-itemrate').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/itemrate',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'details', name: 'details' },
            { data: 'unit_cost', name: 'unit_cost' },
            { data: 'billed_amount', name: 'billed_amount' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });
    viewItemRates.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewItemRates.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewItemRates.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Item Rates

    // Update Item Rates Status
    $(document).on('click', '.ir_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/itemrate-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-itemrate').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Item Rates Status

    //Update Item Rates Modal
    $(document).on('click', '.edit-ir', function() {
        var irId = $(this).data('ir-id');
        var url = '/finance/updateitemrate/' + irId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_ir_edt').val(formattedDateTime);
                $('#ir-id').val(response.id);
                $('#u_ir_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_ir_org', function(data) {
                    $('#u_ir_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_ir_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#u_ir_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_ir_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_ir_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }   
                }, function(error) {
                    console.log(error);
                },response.siteId);

                OrgChangeSites('#u_ir_org', '#u_ir_site', '#update_itemrate');

                $('#u_ir_generic').html("<option selected value="+ response.GenericId +">" + response.GenericName + "</option>");
                fetchOrganizationItemGeneric(response.orgId,'#u_ir_generic', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.GenericId)
                        {
                            $('#u_ir_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeInventoryGeneric('#u_ir_org', '#u_ir_generic', '#update_itemrate');

                $('#u_ir_brand').html("<option selected value="+ response.BrandId +">" + response.BrandName + "</option>");
                fetchGenericItemBrand(response.GenericId,'#u_ir_brand', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.BrandId)
                        {
                            $('#u_ir_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                GenericChangeBrand('#u_ir_generic', '#u_ir_brand', '#update_itemrate');
                
                $('#u_ir_batch').html("<option selected value="+ response.batch +">" + response.batch + "</option>");
                BrandChangeBatchForItemRates('#u_ir_brand', '#u_ir_batch');

                $('#u_ir_unitcost').val(response.UnitCost);
                $('#u_ir_packsize').val(response.packSize);
                $('#u_ir_billedamount').val(response.BilledAmount);

                $('#edit-itemrate').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Item Rates Modal

    //Update Item Rates
    $('#update_itemrate').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var irId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ir-id') {
                irId = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-itemrate/' + irId;
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
                            $('#edit-itemrate').modal('hide');
                            $('#view-itemrate').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_itemrate')[0].reset();
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
    //Update Item Rates
});

// Brand Change Batch Function specifically for Item Rates
function BrandChangeBatchForItemRates(brandSelector, batchSelector) {
    $(brandSelector).off('change.BrandChangeBatch').on('change.BrandChangeBatch', function(){
        var brandId = $(this).val();
        
        // Determine which modal we're in (add or edit)
        var isEditModal = $(this).closest('#edit-itemrate').length > 0;
        
        var orgId, siteId, genericId;
        
        if (isEditModal) {
            // Edit modal selectors
            orgId = $('#u_ir_org').val();
            siteId = $('#u_ir_site').val();
            genericId = $('#u_ir_generic').val();
        } else {
            // Add modal selectors
            orgId = $('#ir_org').val();
            siteId = $('#ir_site').val();
            genericId = $('#ir_generic').val();
        }
        
        console.log('BrandChangeBatchForItemRates called with:', {brandId, orgId, siteId, genericId, isEditModal});
        
        if (brandId && orgId && siteId && genericId) {
            const $batch = $(batchSelector);
            
            // Show loading
            $batch.empty()
                .append('<option selected disabled value="">Loading...</option>')
                .prop('disabled', true);

            $.ajax({
                url: 'inventory/getbatchno',
                type: 'GET',
                data: { orgId, siteId, genericId, brandId },
            })
            .done(function(resp) {
                if (resp && Array.isArray(resp) && resp.length > 0) {
                    $batch.empty()
                        .append(resp.map(({batch_no}) => `<option value="${batch_no}">${batch_no}</option>`).join(''))
                        .prop('disabled', false)
                        .find('option:contains("Loading...")').remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No batch# found',
                        text: 'Batch no is not available for selected combination.',
                    });
                    
                    $batch.empty()
                        .append('<option selected disabled value="">Select Batch Number</option>')
                        .prop('disabled', true);
                }
            })
            .fail(function() {
                Swal.fire('Error','Could not fetch batch info','error');
                $batch.empty()
                    .append('<option selected disabled value="">Select Batch Number</option>')
                    .prop('disabled', true);
            });
        } else {
            $(batchSelector).empty()
                .append('<option selected disabled value="">Select Batch Number</option>')
                .prop('disabled', true);
        }
    });
}

//Item Rates
