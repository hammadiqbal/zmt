
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

            fetchOrganizationBrand(orgId,'#ir_brand', function(data) {
                $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>");
                $.each(data, function(key, value) {
                    $('#ir_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
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
    
            $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled',true);
            OrgChangeBrand('#ir_org', '#ir_brand', '#add_itemrate');
    
        }
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

                $('#u_ir_brand').html("<option selected value="+ response.BrandId +">" + response.BrandName + "</option>");
                fetchOrganizationBrand(response.orgId,'#u_ir_brand', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.BrandId)
                        {
                            $('#u_ir_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeBrand('#u_ir_org', '#u_ir_brand', '#update_itemrate');

                $('#u_ir_batch').val(response.batch);
                $('#u_ir_unitcost').val(response.UnitCost);
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
//Item Rates
