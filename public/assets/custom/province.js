$(document).ready(function() {
      //Add Province
      $('#add_province').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
            $(data).each(function(i, field){
                if(field.value == '')
                {
                    var FieldName = field.name;
                    var FieldID = '#'+FieldName + "_error";
                    $(FieldID).text("This field is required");
                    $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                    $( 'input[name= "' +FieldName +'"' ).focus(function() {
                        $(FieldID).text("");
                        $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                    })
                    resp = false;
                }
                else{
                    resp = true;
                }
                // console.log('up');
            });
            if(resp != false)
            {
                $.ajax({
                    url: "/territory/addprovince",
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
                                    $('#add-province').modal('hide');
                                    $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
                                    $('#add_province')[0].reset();
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
                                    $('#add_province')[0].reset();
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
    //Add Province

    // VieW Province
    var viewprovince =  $('#view-province').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/territory/province',
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

    viewprovince.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewprovince.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewprovince.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Province

    // Update Province Status
    $(document).on('click', '.province_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/province/update-status', // Replace with the actual URL for updating the status
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
            }
            },
            error: function(xhr, status, error) {
            // Handle any AJAX errors
            console.log(error);
            }
        });

    });
    // Update Province Status

    
    //Update Province Modal
    $(document).on('click', '.edit-province', function() {
        var provinceId = $(this).data('province-id');
        $('#ajax-loader').show();
        var url = '/territory/province/' + provinceId;
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
                $('.province-id').val(response.id);
                $('.province-name').val(response.name);  // Adjust these based on the actual structure of your response data
                $('#edit-province').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Province Modal

        //Update Province
        $('#update_province').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var provinceId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'province-id') {
                    provinceId = formData[i].value;
                    break;
                }
            }
            var url = '/update-province/' + provinceId;
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
                                $('#edit-province').modal('hide');
                                $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
                                $('#update_province')[0].reset();
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
        //Update Province
});