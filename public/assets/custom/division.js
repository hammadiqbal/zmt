$(document).ready(function() {
        //Add Division
        $('#add_division').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = $(this).serializeArray();
            var selectedValue = $('#p_name').val();
            data.push({ name: 'province', value: selectedValue });
                $(data).each(function(i, field){
                    if (field.value == '' || field.value == null)
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
                    else{
                        resp = true;
                    }
                });
                if(resp != false)
                {
                    $.ajax({
                        url: "/territory/adddivision",
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
                                        $('#add-division').modal('hide');
                                        $('#view-division').DataTable().ajax.reload();
                                        $('#add_division').find('select').val($('#add_division').find('select option:first').val()).trigger('change');
                                        $('#add_division')[0].reset();
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
                                        $('#add_division').find('select').val($('#add_division').find('select option:first').val()).trigger('change');
                                        $('#add_division')[0].reset();
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
        //Add Division


        
    // VieW Division
    var viewdivision =  $('#view-division').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/territory/division',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'name',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'province_name', name: 'province_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 3,
                width: "200px"
            }
        ]
    });

    viewdivision.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewdivision.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewdivision.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Division

    // Update Division Status
    $(document).on('click', '.division_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/division/update-status', // Replace with the actual URL for updating the status
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-division').DataTable().ajax.reload(); // Refresh DataTable
            }
            },
            error: function(xhr, status, error) {
            // Handle any AJAX errors
            console.log(error);
            }
        });

    });
    // Update Division Status

        //Update Division Modal
        $(document).on('click', '.edit-division', function() {
            var divisionId = $(this).data('division-id');
            $('#ajax-loader').show();
            var url = '/territory/division/' + divisionId;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#ajax-loader').hide();
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    var province_name = response.province_name;
                    var provinceId = response.province_id;
                    $('.province_name').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                    if (provinceId) {
                        $.ajax({
                            url: 'territory/updateprovince',
                            type: 'GET',
                            data: {
                                provinceId: provinceId,
                            },
                            success: function(resp) {
    
                                $.each(resp, function(key, value) {
                                    $('.province_name').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                $('#ajax-loader').hide();
                                console.log(error);
                            }
                        });
                    }
                    $('.edt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
                    $('.division-id').val(response.id);
                    $('.division-name').val(response.name);
                    $('#edit-division').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update Division Modal

            //Update Division
    $('#update_division').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var divisionId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'division-id') {
                divisionId = formData[i].value;
                break;
            }
        }
        var url = '/update-division/' + divisionId;
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
                            $('#edit-division').modal('hide');
                            $('#view-division').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_division')[0].reset();
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
    //Update Division
});