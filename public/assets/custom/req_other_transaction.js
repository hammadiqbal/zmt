$(document).ready(function() {
    //Open Add Requisition For Other Transactions
    $(document).on('click', '.add-reqothertransaction', function() {
        $('.duplicate:not(:first)').remove();
        $('.text-danger').show();
        var orgId = $('#rot_org').val();
        if(!orgId)
        {
            $('#rot_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#rot_org', function(data) {
                $('#rot_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#rot_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#rot_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#rot_org', '#rot_site', '#add_reqothertransaction');
            $('.rot_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#rot_org', '.rot_itemgeneric', '#add_reqothertransaction');

        }
        else{
            fetchOrganizationSites(orgId, '#rot_site', function(data) {
                $('#rot_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#rot_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
             $('.rot_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>");
            fetchOrganizationItemGeneric(orgId, '.rot_itemgeneric', function(data) {
                $.each(data, function(key, value) {
                    $('.rot_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        $('#rot_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
        SiteChangeMaterialManagementTransactionTypes('#rot_site','#rot_org', '#rot_transactiontype', '#add_reqothertransaction','other_transaction','y');

        $('#rot_inv_location').html("<option selected disabled value=''>Select Inventory Location</option>").prop('disabled', true);
        SiteChangeActivatedServiceLocation('#rot_site','#rot_inv_location', '#add_reqothertransaction',true );

        $('#add-reqothertransaction').modal('show');
    });
    //Open Add Requisition For Other Transactions

    // Add Requisition For Other Transactions
    $('#add_reqothertransaction').submit(function(e) {
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

        var excludedFields = ['rot_remarks'];
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
        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addreqothertransaction",
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
                                $('#add-reqothertransaction').modal('hide');
                                $('#view-reqothertransaction').DataTable().ajax.reload();
                                $('#add_reqothertransaction')[0].reset();
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
                                $('#add-reqothertransaction').modal('hide');
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
    //Add Requisition For Other Transactions

    // View Requisition For Other Transactions Data
    var viewRequisitionOtherTransaction =  $('#view-reqothertransaction').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/reqothertransaction',
        order: [[1, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'requisition_detail', name: 'requisition_detail' },
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
                width: "300px"
            },
              {
                targets: 3,
                width: "300px"
            },
            {
                targets: 4,
                width: "300px"
            }
        ]
    });

    viewRequisitionOtherTransaction.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewRequisitionOtherTransaction.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewRequisitionOtherTransaction.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Requisition For Other Transactions Data

    // Update Requisition For Other Transactions Status
    $(document).on('click', '.rot_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/reqothertransaction-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-reqothertransaction').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Requisition For Other Transactions Status

    //Update Requisition For Other Transactions Modal
    $(document).on('click', '.edit-reqothertransaction', function() {
        var RequisitionId = $(this).data('rot-id');
        $('.text-danger').show();
        var url = '/inventory/updatereqothertransaction/' + RequisitionId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('.u_rot-id').val(response.id);
                $('#u_rot_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_rot_org', function(data) {
                    $('#u_rot_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_rot_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                $('#u_rot_site').html("<option selected value='"+response.siteId+"'>" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_rot_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_rot_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);
                OrgChangeSites('#u_rot_org', '#u_rot_site', '#update_reqothertransaction');

                $('#u_rot_transactiontype').html("<option selected value="+ response.transactionTypeId +">" + response.transactionType + "</option>");
                fetchMaterialManagementTransactionTypes(response.orgId, '#u_rot_transactiontype','other_transaction','y', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.transactionTypeId)
                        {
                            $('#u_rot_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                $('#u_rot_inv_location').html("<option selected value="+ response.ServiceLocationId +">" + response.ServicelocationName + "</option>");
                fetchActiveSL(response.siteId, '#u_rot_inv_location', function(data) {
                    $.each(data, function(key, value) {
                        if(value.location_id != response.ServiceLocationId)
                        {
                            $('#u_rot_inv_location').append('<option value="' + value.location_id + '">' + value.name + '</option>');
                        }
                    });
                });
                

                $('#u_rot_remarks').val(response.remarks);

                var genericIds = response.genericIds.split(',');
                var genericNames = response.genericNames.split(',');
                var Qty = response.Qty.split(',');

                $('.uduplicate').empty();
                for (var i = 0; i < genericIds.length; i++) {
                    var GenericField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Inventory Generic</label>' +
                        '<select class="form-control selecter p-0 u_rot_itemgeneric" name="u_rot_itemgeneric[]" id="u_rot_itemgeneric' + i + '" required style="color:#222d32">' +
                        '<option value="' + genericIds[i] + '"> ' + genericNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchInventoryGenerics('#u_rot_itemgeneric' + index, 'material', function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), genericIds[index]) === -1) 
                                    {
                                        $('#u_rot_itemgeneric' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_rot_itemgeneric' + index).select2();
                            }
                        });
                    })(i); 

                    var qtyField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Demand Qty</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_rot_qty[]" value="' + Qty[i] + '">' +
                        '</div>' +
                        '<span class="text-danger u_rot_qty_error"></span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                
                    var row =+ '</div>';
                    $('.uduplicate').append('<div class="row pt-3 pb-1 rot_details" style="border: 1px solid #939393;">' + GenericField + qtyField +'</div>');
                }


                $('#edit-reqothertransaction').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Requisition For Other Transactions Modal

    //Update Requisition For Other Transactions
    $('#update_reqothertransaction').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var reqOtherTransactionId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_rot-id') {
                reqOtherTransactionId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-reqothertransaction/' + reqOtherTransactionId;
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
                            $('#edit-reqothertransaction').modal('hide');
                            $('#view-reqothertransaction').DataTable().ajax.reload(); // Refresh DataTable
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
    //Update Requisition For Other Transactions


});