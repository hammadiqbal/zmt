
//Purchase Order
$(document).ready(function() {
    //Open Purchase Order Setup
    $(document).on('click', '.add-purchaseorder', function() {
        $('.duplicate:not(:first)').remove();
        $('.duplicate').each(function(){
            $(this).find('input[type="text"], input[type="number"], textarea').val('');
            $(this).find('input[type="checkbox"], input[type="radio"]')
                   .prop('checked', false);
            $(this).find('select').prop('selectedIndex', 0);
        });
        $('.payable_amount').hide();
        var orgId = $('#po_org').val();
        if(orgId)
        {
            $('#po_site').html("<option selected disabled value=''>Select Site</option>");
            fetchOrganizationSites(orgId, '#po_site', function(data) {
                $.each(data, function(key, value) {
                    $('#po_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('.po_brand').html("<option selected disabled value=''>Select Item Brand</option>");
            fetchOrganizationBrand(orgId,'.po_brand', function(data) {
                $('.po_brand').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('.po_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('#po_vendor').html("<option selected disabled value=''>Select Vendor</option>");
            fetchOrganizationVendor(orgId, '#po_vendor', function(data) {
                $.each(data, function(key, value) {
                    $('#po_vendor').append('<option value="' + value.id + '">' + value.person_name + '</option>');
                });
            });
        }
        else{
            $('#po_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#po_org', function(data) {
                $('#po_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#po_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            
            $('#po_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#po_org', '#po_site', '#add_purchaseorder');

            $('.po_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
            OrgChangeBrand('#po_org', '.po_brand', '#add_purchaseorder');

            $('#po_vendor').html("<option selected disabled value=''>Select Vendor</option>").prop('disabled', true);
            OrgChangeVendor('#po_org', '#po_vendor', '#add_purchaseorder');
        }
        $('#add-purchaseorder').modal('show');
    });
    //Open Purchase Order Setup

    //Approve Purchase Order 
    $(document).on('click', '#po_approve', function(e) {
        e.preventDefault();
        var Id = $(this).data('id');
        var userId = $(this).data('userid');
        var data = {
            id: Id,
            userId: userId,
        };
        Swal.fire({
            text: 'Are You Sure?',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/inventory/approve-po",
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
                        if (response.success) {
                            Swal.fire('Success', 'Your action has been executed successfully.', 'success');
                            $('#view-purchaseorder').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                            $('#view-purchaseorder').DataTable().ajax.reload();
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } 
        });
    });
    //Approve Purchase Order 

    //Add Purchase Order
    $('#add_purchaseorder').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'po_remarks[]') 
            {


                var FieldName = field.name;
                var FieldName = field.name.replace('[]', '');

                var FieldID = '.'+FieldName + "_error";
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });

        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                
                var fieldName = elem.attr('name').replace('[]', '');
                if (elem.attr('name') === 'po_remarks[]') {
                    return true; 
                }
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

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addpurchaseorder",
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
                                $('#add-purchaseorder').modal('hide');
                                $('#view-purchaseorder').DataTable().ajax.reload();
                                $('#add_purchaseorder')[0].reset();
                                $('#add_purchaseorder').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
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
                                // $('#add_purchaseorder').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
                                // $('#add_purchaseorder')[0].reset();
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
    //Add Purchase Order

    // View Purchase Order
    var viewPurchaseOrder =  $('#view-purchaseorder').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/purchaseorder',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'item_details', name: 'item_details' },
            // { data: 'other_details', name: 'other_details' },
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
                width: "600px"
            },
            {
                targets: 4,
                width: "300px"
            }
        ]
    });
    viewPurchaseOrder.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewPurchaseOrder.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewPurchaseOrder.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Purchase Order

    // Save PDF
    $(document).on('click', '.save-pdf-po', function() {
        var purchaseOrderId = $(this).data('purchaseorder-id');
        window.location.href = '/purchase-order/' + purchaseOrderId + '/pdf';
    });
    // Save PDF

    // Update Purchase Order Status
    $(document).on('click', '.po_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/purchaseorder-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-purchaseorder').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Purchase Order Status

    //Update Purchase Order Modal
    $(document).on('click', '.edit-purchaseorder', function() {
        var poId = $(this).data('purchaseorder-id');
        var url = '/inventory/updatepurchaseorder/' + poId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_po_edt').val(formattedDateTime);
                $('#po-id').val(response.id);
                $('#u_po_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_po_org', function(data) {
                    $('#u_po_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_po_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#u_po_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_po_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_po_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);
                OrgChangeSites('#u_po_org', '#u_po_site', '#update_purchaseorder');

                $('#u_po_vendor').html("<option selected value="+ response.vendorId +">" + response.vendorName + "</option>");
                fetchOrganizationVendor(response.orgId, '#u_po_vendor', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.vendorId) {
                            $('#u_po_vendor').append('<option value="' + value.id + '">' + value.person_name + '</option>');
                        }
                    });
                });
                OrgChangeVendor('#u_po_org', '#u_po_vendor', '#update_purchaseorder');

                var brandIds = response.brandId.split(',');
                var brandNames = response.brandNames.split(',');
                var quantities = response.Quantities.split(',');
                var amounts = response.Amounts.split(',');
                var discounts = response.Discounts.split(',');
                var remarks = response.remarks.split(',');

                $('.uduplicate').empty();
                for (var i = 0; i < brandIds.length; i++) {
                    var brandField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Brand</label>' +
                        '<select class="form-control selecter p-0 u_po_brand" name="u_po_brand[]" id="u_po_brand' + i + '" required style="color:#222d32">' +
                        '<option value="' + brandIds[i] + '"> ' + brandNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchOrganizationBrand(response.orgId, '#u_po_brand' + index, function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    var a ='#u_po_brand'+ index;
                                    $('#u_po_brand' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                $('#u_po_brand' + index).select2();
                            }
                        },
                        function (error) {
                            console.log(error);
                        }, brandIds[index]);
                    })(i); 

                    var qtyField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Demand Qty</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_po_qty[]" id="input05" value="' + quantities[i] + '">' +
                        '</div>' +
                        '<span class="text-danger po_qty_error"></span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
        
                    var amountField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Amount</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_po_amount[]" id="input0565" value="' + amounts[i] + '">' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
        
                    var discountField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Discount</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_po_discount[]" id="input0575" value="' + discounts[i] + '">' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
        
                    var remarksField = '<div class="col-md-12">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-10">' +
                        '<label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>' +
                        '<textarea class="form-control" rows="3" name="u_po_remarks[]" id="input07" spellcheck="false">' + remarks[i] + '</textarea>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                        var row =+ '</div>';
                    $('.uduplicate').append('<div class="row pt-3 pb-1 po_details" style="border: 1px solid #939393;">' + brandField + qtyField + amountField + discountField + remarksField + '</div>');
                }

                OrgChangeBrand('#u_po_org', '.u_po_brand', '#add_purchaseorder');

                $('#edit-purchaseorder').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Purchase Order Modal

    //Update Purchase Order
    $('#update_purchaseorder').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var poId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'po-id') {
                poId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-purchaseorder/' + poId;
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
                            $('#edit-purchaseorder').modal('hide');
                            $('#view-purchaseorder').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_purchaseorder')[0].reset();
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
    //Update Purchase Order
});
//Purchase Order