//Inventory Category
$(document).ready(function() {
    //Open Inventory Category
    $(document).on('click', '.add-inventorycategory', function() {
        var orgId = $('#ic_org').val();
        if(!orgId)
        {
            $('#ic_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#ic_org', function(data) {
                $('#ic_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ic_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('#add-inventorycategory').modal('show');
    });
    //Open Inventory Category

    //Add Inventory Category
    $('#add_inventorycategory').submit(function(e) {
        e.preventDefault(); 
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
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

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addinvcategory",
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
                                $('#add-inventorycategory').modal('hide');
                                $('#view-inventorycategory').DataTable().ajax.reload();
                                $('#add_inventorycategory')[0].reset();
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
                                $('#add_inventorycategory')[0].reset();
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
    //Add Inventory Category

    // View Inventory Category Data
    var viewinventoryCat =  $('#view-inventorycategory').DataTable({
        processing: true,
        serverSide: true,
        // ajax: '/inventory/inventorycategory',
        ajax: {
            url: '/inventory/inventorycategory',
            data: function (d) {
                d.cg = $('#fb_cg').val();  
                d.cm = $('#fb_cm').val();    
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'consumption_group', name: 'consumption_group' },
            { data: 'consumption_method', name: 'consumption_method' },
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
                width: "250px"
            }
        ]
    });

    $('#fb_cg, #fb_cm').on('change', function () {
        viewinventoryCat.ajax.reload();  
    });

    $('.clearFilter').on('click', function () {
        $('#fb_cg').val($('#fb_cg option:first').val()).change();
        $('#fb_cm').val($('#fb_cm option:first').val()).change();
        viewinventoryCat.ajax.reload();   
    });

    viewinventoryCat.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventoryCat.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventoryCat.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Category Data

    // Update Inventory Category Status
    $(document).on('click', '.inventorycategory_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invcat-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-inventorycategory').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Category Status

    //Update Inventory Category Modal
    $(document).on('click', '.edit-inventorycategory', function() {
        var InnventoryCatId = $(this).data('inventorycategory-id');
        var url = '/inventory/updateinventorycategory/' + InnventoryCatId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.ic-id').val(response.id);
                $('.u_invcat').val(response.name);
                $('#u_ic_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_ic_org', function(data) {
                    $('#u_ic_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_ic_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                // var UsageType = response.UsageType;
                // var UsageTypeVal = (UsageType === 'c') ? 'Consumable' : 'Fixed';
                // $('#u_usage_type option[value="' + (UsageType === 'c' ? 'c' : 'f') + '"]').remove();
                // $('#u_usage_type').prepend("<option selected value="+ UsageType +">" + UsageTypeVal + "</option>");
                $('#u_ic_cg').val(response.consumptionGroupId).change();
                $('#u_ic_cm').val(response.consumptionMethodId).change();

                $('#edit-inventorycategory').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Category Modal

    //Update Inventory Category
    $('#u_inventorycategory').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invCatId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ic-id') {
                invCatId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-inventorycategory/' + invCatId;
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
                            $('#edit-inventorycategory').modal('hide');
                            $('#view-inventorycategory').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_inventorycategory')[0].reset();
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
    //Update Inventory Category
});
//Inventory Category