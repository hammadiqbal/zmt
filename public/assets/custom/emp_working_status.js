$(document).ready(function() {
    //Add Employee Working Status
    $('#add_workingStatus').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addworkingStatus",
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
                    console.log(response);
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
                                $('#add-workingStatus').modal('hide');
                                $('#view-workingStatus').DataTable().ajax.reload();
                                $('#add_workingStatus')[0].reset();
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
                                $('#add_workingStatus')[0].reset();
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
    //Add Employee Working Status

    // View Employee Working Status Data
    var viewworkingStatus =  $('#view-workingStatus').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/workingStatusdata',
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
            { data: 'jobContinuation', name: 'jobContinuation' },
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

    viewworkingStatus.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewworkingStatus.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewworkingStatus.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Working Status Data

    // Update Employee Working Status
    $(document).on('click', '.working_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/working-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-workingStatus').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Working Status

    //Update Employee Working Status Modal
    $(document).on('click', '.edit-workingStatus', function() {
        var workingStatusId = $(this).data('workingstatus-id');
        var url = '/hr/updateworkingStatus/' + workingStatusId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                if(response.jobContinue == 0) {
                    $('#u_jobcontinue').bootstrapSwitch('state', false);
                } else {
                    $('#u_jobcontinue').bootstrapSwitch('state', true);
                }
                $('.ews-id').val(response.id);
                $('.u_ews').val(response.name);
                $('#edit-workingStatus').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Working Status Modal

    //Update Employee Working Status Data
    $('#u_workingStatus').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ewsId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ews-id') {
                ewsId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-workingStatus/' + ewsId;
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
                            $('#edit-workingStatus').modal('hide');
                            $('#view-workingStatus').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_workingStatus')[0].reset();
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
    //Update Employee Working Status Data
});