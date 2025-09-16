$(document).ready(function() {
    //Open Vendor Registration Setup
    $(document).on('click', '.add-vendorregistration', function() {
        var orgId = $('#vendor_org').val();
        if(!orgId)
        {
            $('#vendor_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#vendor_org', function(data) {
                $('#vendor_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#vendor_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('#add-vendorregistration').modal('show');
    });
    //Open Vendor Registration Setup

    // //Add Vendor Registration
    $('#add_vendorregistration').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'vendor_landline'))
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addvendorregistration",
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
                                $('#add-vendorregistration').modal('hide');
                                $('#view-vendorregistration').DataTable().ajax.reload();
                                $('#add_vendorregistration')[0].reset();
                                $('#add_vendorregistration').find('select').each(function(){
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
                                $('#add_vendorregistration').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_vendorregistration')[0].reset();
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
    // //Add Vendor Registration

    // View Vendor Data
    var viewVendor =  $('#view-vendorregistration').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/vendorregistration',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'address',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'contactDetails', name: 'contactDetails' },
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
                width: "250px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });

    viewVendor.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewVendor.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewVendor.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Vendor Data

    // Update Vendor Status
    $(document).on('click', '.vendor_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/vendor-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-vendorregistration').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Vendor Status

    //Update Vendor Registration Modal
    $(document).on('click', '.edit-vendor', function() {
        var vendorId = $(this).data('vendor-id');
        var url = '/inventory/updatevendorregistration/' + vendorId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_vendor-id').val(response.id);
                $('.u_vendor_desc').val(response.name);
                $('#u_vendor_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_vendor_org', function(data) {
                    $('#u_vendor_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_vendor_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('.u_vendor_address').val(response.address);
                $('.u_vendor_name').val(response.personName);
                $('.u_vendor_email').val(response.personEmail);
                $('.u_vendor_cell').val(response.cellNo);
                $('.u_vendor_landline').val(response.landlineNo);
                $('.u_vendor_remarks').val(response.remarks);

                $('#edit-vendorregistration').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Vendor Registration Modal

    //Update Vendor Registration
    $('#update_vendorregistration').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var vendorId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_vendor-id') {
                vendorId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-vendorregistration/' + vendorId;
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
                            $('#edit-vendorregistration').modal('hide');
                            $('#view-vendorregistration').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_vendorregistration')[0].reset();
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
    //Update Vendor Registration
});