$(document).ready(function() {
    //Open Medication Frequency Setup
    $(document).on('click', '.add-medicationfrequency', function() {
        var orgId = $('#medicationfrequency_org').val();
        if(!orgId)
        {
            $('#medicationfrequency_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#medicationfrequency_org', function(data) {
                $('#medicationfrequency_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#medicationfrequency_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('#add-medicationfrequency').modal('show');
    });
    //Open Medication Frequency Setup

    //Add Medication Frequency
    $('#add_medicationfrequency').submit(function(e) {
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
                url: "/inventory/addmedicationfrequency",
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
                                $('#add-medicationfrequency').modal('hide');
                                $('#view-medicationfrequency').DataTable().ajax.reload();
                                $('#add_medicationfrequency')[0].reset();
                                $('#add_medicationfrequency').find('select').each(function(){
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
                                $('#add_medicationfrequency').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_medicationfrequency')[0].reset();
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
    //Add Medication Frequency

    // View Medication Frequency Data
    var viewmedicationFrequency =  $('#view-medicationfrequency').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/medicationfrequency',
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
                targets: 1,
                width: "300px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    viewmedicationFrequency.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewmedicationFrequency.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewmedicationFrequency.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Medication Frequency Data

    // Update Medication Frequency Status
    $(document).on('click', '.medicationfrequency_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/medicationfrequency-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-medicationfrequency').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Medication Frequency Status

    //Update Medication Frequency Modal
    $(document).on('click', '.edit-medicationfrequency', function() {
        var medicationFrequencyId = $(this).data('medicationfrequency-id');
        var url = '/inventory/updatemedicationfrequency/' + medicationFrequencyId;
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
                $('.u_medicationfrequency-id').val(response.id);
                $('.u_medicationfrequency').val(response.name);
                $('#u_medicationfrequency_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_medicationfrequency_org', function(data) {
                    $('#u_medicationfrequency_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_medicationfrequency_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('#edit-medicationfrequency').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Medication Frequency Modal

    //Update Medication Frequency
    $('#update_medicationfrequency').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var medicationFrequencyId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_medicationfrequency-id') {
                medicationFrequencyId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-medicationfrequency/' + medicationFrequencyId;
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
                            $('#edit-medicationfrequency').modal('hide');
                            $('#view-medicationfrequency').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_medicationfrequency')[0].reset();
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
    //Update Medication Frequency
});