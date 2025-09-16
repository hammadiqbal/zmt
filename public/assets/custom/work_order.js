//Work Order
$(document).ready(function() {
    //Open Work Order Setup
    // $(document).on('click', '.add-workorder', function() {
    $(document).off('click.addWorkorder').on('click.addWorkorder', '.add-workorder', function() {
        $('.duplicate:not(:first)').remove();
        var orgId = $('#wo_org').val();
        if(orgId)
        {
            $('#wo_site').html("<option selected disabled value=''>Select Site</option>");
            fetchOrganizationSites(orgId, '#wo_site', function(data) {
                $.each(data, function(key, value) {
                    $('#wo_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('#wo_vendor').html("<option selected disabled value=''>Select Vendor</option>");
            fetchOrganizationVendor(orgId, '#wo_vendor', function(data) {
                $.each(data, function(key, value) {
                    $('#wo_vendor').append('<option value="' + value.id + '">' + value.prefixName +" "+value.person_name +' - '+value.corporateName + '</option>');
                });
            });
        }
        else{
            $('#wo_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#wo_org', function(data) {
                $('#wo_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#wo_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#wo_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#wo_org', '#wo_site', '#add_workorder');
            
            $('#wo_vendor').html("<option selected disabled value=''>Select Vendor</option>").prop('disabled', true);
            OrgChangeVendor('#wo_org', '#wo_vendor', '#add_workorder');
        }

        $('#add-workorder').modal('show');
    });
    //Open Work Order Setup

    //Approve Work Order 
    $(document).on('click', '#wo_approve', function(e) {
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
                    url: "/inventory/approve-wo",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', 'Work Order Approved Successfully.', 'success');
                            $('#view-workorder').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                            $('#view-workorder').DataTable().ajax.reload();
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } 
        });
    });
    //Approve Work Order 

    // Add Work Order
    $('#add_workorder').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'wo_remarks[]') 
            {
                var FieldName = field.name;
                console.log(FieldName);

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
        console.log(resp);

        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                if (elem.attr('name') === 'wo_remarks[]') {
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
                url: "/inventory/addworkorder",
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
                                $('#add-workorder').modal('hide');
                                $('#view-workorder').DataTable().ajax.reload();
                                $('#add_workorder')[0].reset();
                                $('#add_workorder').find('select').each(function(){
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
                                $('#add_workorder').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_workorder')[0].reset();
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
    //Add Work Order

    // Save PDF
    $(document).on('click', '.save-pdf-wo', function() {
        var workOrderId = $(this).data('workorder-id');
        window.location.href = '/work-order/' + workOrderId + '/pdf';
    });
    // Save PDF

    // View Work Order
    var viewWorkOrder =  $('#view-workorder').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/workorder',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'item_details', name: 'item_details' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "450px"
            },
            {
                targets: 4,
                width: "350px"
            }
        ]
    });
    viewWorkOrder.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewWorkOrder.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewWorkOrder.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Work Order

    // Update Work Order Status
    $(document).on('click', '.wo_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/workorder-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-workorder').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Work Order Status

    //Update Work Order Modal
    $(document).on('click', '.edit-workorder', function() {
        var woId = $(this).data('workorder-id');
        var url = '/inventory/updateworkorder/' + woId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_wo_edt').val(formattedDateTime);
                $('#wo-id').val(response.id);
                $('#u_wo_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_wo_org', function(data) {
                    $('#u_wo_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_wo_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#u_wo_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_wo_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_wo_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);
                OrgChangeSites('#u_wo_org', '#u_wo_site', '#update_workorder');

                $('#u_wo_vendor').html("<option selected value="+ response.vendorId +">" + response.prefixName +" "+response.vendorName +' - '+response.corporateName + "</option>");
                fetchOrganizationVendor(response.orgId, '#u_wo_vendor', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if (value.id !== response.vendorId) {
                                $('#u_wo_vendor').append('<option value="' + value.id + '">' + value.prefixName +" "+value.person_name +' - '+value.corporateName + '</option>');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.vendorId);
                OrgChangeVendor('#u_wo_org', '#u_wo_vendor', '#update_workorder');


                var particulars = response.Particulars.split(',');
                var amounts = response.Amounts.split(',');
                var discounts = response.Discounts.split(',');
                var remarks = response.remarks.split(',');
                
                $('.uwoduplicate').empty();
                for (var i = 0; i < particulars.length; i++) {
                    var particularsField = '<div class="col-md-6">' +
                    '<div class="form-group row">' +
                    '<div class="col-md-12">' +
                    '<div class="form-group has-custom m-b-10">' +
                    '<label class="control-label">Update Particulars</label>' +
                    '<textarea class="form-control" rows="1" name="u_wo_particulars[]" required  spellcheck="false">' + particulars[i] + '</textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
        
                    var amountField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Amount</label>' +
                        '<input type="number" class="form-control input-sm amount" required name="u_wo_amount[]" value="' + amounts[i] + '">' +
                        '<small class="amount_conversion"></small>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
        
                    var discountField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Discount</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_wo_discount[]" id="input0575" value="' + discounts[i] + '">' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    var remarksField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-10">' +
                        '<label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>' +
                        '<textarea class="form-control" rows="1" name="u_wo_remarks[]" id="input07" spellcheck="false">' + remarks[i] + '</textarea>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                        var row =+ '</div>';
                    $('.uwoduplicate').append('<div class="row pt-3 pb-1 wo_details" style="border: 1px solid #939393;">' + particularsField + amountField + discountField + remarksField + '</div>');
                }
                $('#edit-workorder').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Work Order Modal

    //Update Work Order
    $('#update_workorder').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var woId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'wo-id') {
                woId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-workorder/' + woId;
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
                            $('#edit-workorder').modal('hide');
                            $('#view-workorder').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_workorder')[0].reset();
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
    //Update Work Order
});
//Work Order