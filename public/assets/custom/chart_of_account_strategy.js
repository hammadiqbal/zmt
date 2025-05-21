// Chart Of Accounts Strategy
$(document).ready(function() {
    //Add Chart Of Accounts Strategy
    $('#add_accountStrategy').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        // var data = $(this).serializeArray();
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
                url: "/finance/addaccountstrategy",
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
                                $('#add-accountStrategy').modal('hide');
                                $('#view-accountStrategy').DataTable().ajax.reload();
                                $('#add_accountStrategy').find('select').val($('#add_accountStrategy').find('select option:first').val()).trigger('change');
                                $('#add_accountStrategy')[0].reset();
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
                                $('#add_accountStrategy').find('select').val($('#add_accountStrategy').find('select option:first').val()).trigger('change');
                                $('#add_accountStrategy')[0].reset();
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
    //Add Chart Of Accounts Strategy

    // View Chart Of Accounts Strategy Data
    var viewAccountStrategy =  $('#view-accountStrategy').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/accountstrategydata',
        order: [[0, 'asc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'remarks',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    viewAccountStrategy.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewAccountStrategy.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewAccountStrategy.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Chart Of Accounts Strategy Data

    // Update Chart Of Accounts Strategy Status
    $(document).on('click', '.accountstrategy_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/accountstrategy-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(response.info)
            {
                $('#ajax-loader').hide();
                Swal.fire({
                    text: response['info'],
                    icon: 'info',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        
                        $('#view-accountStrategy').DataTable().ajax.reload();
                    }
                });
            }
            else if(status == 200)
                {
                    $('#view-accountStrategy').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Chart Of Accounts Strategy Status

    //Update Chart Of Accounts Strategy Modal
    $(document).on('click', '.edit-accountStrategy', function() {
        var accountStrategyId = $(this).data('accountstrategy-id');
        var url = '/finance/updateaccountstrategy/' + accountStrategyId;
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
                $('.as-id').val(response.id);
                $('.u_accountStrategy').val(response.desc);
                $('.u_as_remarks').val(response.remarks);
                $('#edit-accountStrategy').modal('show');
                $('#ajax-loader').hide();
                const Levels = ["1", "2", "3", "4","5", "6", "7", "8","9", "10"];
                const ordinals = ["First Level", "Second Level", "Third Level", "Fourth Level", "Fifth Level", "Sixth Level", 
                                "Seventh Level", "Eighth Level", "Ninth Level", "Tenth Level"];
                let LevelOptions = '<option selected value="'+response.Level+'">' + response.HierarchyLevel + '</option>';
                for (let Level of Levels) {
                    if (Level != response.Level) {
                        console.log(Level,response.Level);
                        let ordinalLabel = ordinals[parseInt(Level) - 1];
                        LevelOptions += '<option value="'+Level+'">' + ordinalLabel + '</option>';
                    }
                }
                $('.u_as_level').html(LevelOptions);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Chart Of Accounts Strategy Modal

    //Update Chart Of Accounts Strategy
    $('#u_accountStrategy').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'as-id') {
                Id = formData[i].value;
                break;
            }
        }
        var url = 'finance/update-accountstrategy/' + Id;
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
                else if (fieldName == 'info')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-accountStrategy').modal('hide');
                            $('#view-accountStrategy').DataTable().ajax.reload();
                        }
                    });
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
                            $('#edit-accountStrategy').modal('hide');
                            $('#view-accountStrategy').DataTable().ajax.reload(); // Refresh DataTable
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
    //Update Chart Of Accounts Strategy

});
// Chart Of Accounts Strategy

