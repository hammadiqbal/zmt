
$(document).ready(function() {
    //Add CCType
    $('#add_ccType').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var orderingCC = $('#orderingCC').val();
        var performingCC = $('#performingCC').val();
        data.push({ name: 'ordering_cc', value: orderingCC });
        data.push({ name: 'performing_cc', value: performingCC });
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
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
                url: "/costcenter/addCCType",
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
                        $('.text-danger').hide();
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
                                $('#add-ccType').modal('hide');
                                $('#view-ccType').DataTable().ajax.reload();
                                $('#add_ccType').find('select').val($('#add_ccType').find('select option:first').val()).trigger('change');

                                $('#add_ccType')[0].reset();
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
                                $('#add_ccType').find('select').val($('#add_ccType').find('select option:first').val()).trigger('change');
                                $('#add_ccType')[0].reset();
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
    //Add CCType

    // VieW CCType
    var viewccType =  $('#view-ccType').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/costcenter/cctype',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'remarks', name: 'remarks' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "200px"
            },
            {
                targets: 5,
                width: "250px"
            }
        ]
    });

    viewccType.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewccType.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewccType.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View CCType

    // Update CCType Status
    $(document).on('click', '.ccType_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/costcentertype/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-ccType').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update CCType Status

     //Update CCType Modal
    $(document).on('click', '.edit-cctype', function() {
        var ccTypeId = $(this).data('cctype-id');
        var url = '/costcenter/updateCCtype/' + ccTypeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.ccType-id').val(response.id);
                $('.cctype-name').val(response.type);
                $('.cc-remarks').val(response.remarks);
                $('.u_ordering').html("<option selected value='"+response.orderingid+"'>" + response.ordering + "</option>");
                $('.u_performing').html("<option selected value='"+response.performingid+"'>" + response.performing + "</option>");
                var orderingnext = response.orderingid == '1' ? 'Disabled' : 'Enabled';
                var orderingnextid = response.orderingid == '1' ? 0 : 1;
                var performingnext = response.performingid == '1' ? 'Disabled' : 'Enabled';
                var performingnextid = response.performingid == '1' ? 0 : 1;
                $('.u_ordering').append('<option value="' + orderingnextid + '">' + orderingnext + '</option>');
                $('.u_performing').append('<option value="' + performingnextid + '">' + performingnext + '</option>');
                $('#edit-cctype').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update CCType Modal

    //Update CCType
    $('#update_ccType').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ccTypeId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ccType-id') {
                ccTypeId = formData[i].value;
                break;
            }
        }
        var url = '/update-cctype/' + ccTypeId;
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
                            // console.log('result conformed');
                            $('#edit-cctype').modal('hide');
                            $('#view-ccType').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_ccType')[0].reset();
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
    //Update CCType


});
