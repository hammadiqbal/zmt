// Donor Registration
$(document).ready(function() {
    $(document).on('click', '.donor-registration', function() {
        $('#donor-registration').modal('show');
        $('.donor_corporate').hide();
        $('#donor_type').change(function() {
            var selectedType = $(this).val();
            if (selectedType === 'corporate') {
                $('.donor_corporate').show();
            } else {
                $('.donor_corporate').hide();
            }
        });

    });
    //Register Donor
    $('#register_donor').submit(function(e) {
        e.preventDefault(); 
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'donor_corporate' && field.name != 'donor_landline') 
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
        var donorType = $('#donor_type').val();
        if (donorType === 'corporate') {
            var donorCorporateField = $('input[name="donor_corporate"]');
            if (donorCorporateField.val() == '' || donorCorporateField.val() == null) {
                $('#donor_corporate_error').text("This field is required");
                donorCorporateField.addClass('requirefield');
                donorCorporateField.focus(function() {
                    $('#donor_corporate_error').text("");
                    $(this).removeClass("requirefield");
                });
                resp = false;
            }
        }

        if(resp != false)
        {
            $.ajax({
                url: "/finance/donor_registration",
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
                                $('#donor-registration').modal('hide');
                                $('#view-donors').DataTable().ajax.reload();
                                $('#register_donor').find('select').val($('#register_donor').find('select option:first').val()).trigger('change');
                                $('#register_donor')[0].reset();
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
                                $('#register_donor').find('select').val($('#register_donor').find('select option:first').val()).trigger('change');
                                $('#register_donor')[0].reset();
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
    //Register Donor

    // View Donor
    var viewDonor =  $('#view-donors').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/donorsdata',
        order: [[0, 'asc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'address',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
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
                targets: 5,
                width: "300px"
            }
        ]
    });

    viewDonor.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewDonor.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewDonor.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Donor

    // Update Donor Status
    $(document).on('click', '.donor_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/donor-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-donors').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Donor Status

    //Update Donor Modal
    $(document).on('click', '.edit-donor', function() {
        var DonorID = $(this).data('donor-id');
        var url = '/finance/updatedonor/' + DonorID;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#date-format1').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_donor-id').val(response.id);
                $('.u_donor_corporate').val(response.CorporateName);
                $('.u_donor_name').val(response.PersonName);
                $('.u_donor_email').val(response.PersonEmail);
                $('.u_donor_cell').val(response.Cell);
                $('.u_donor_landline').val(response.landline);
                $('.u_donor_address').val(response.Address);
                $('.u_donor_remarks').val(response.Remarks);
                if(response.Type == 'Corporate'){
                    $('.ucorporateName').show();
                    $('.u_donor_corporate').prop('required', true);
                }
                else{
                    $('.ucorporateName').hide();
                    $('.u_donor_corporate').prop('required', false);
                }
                const corporateTypes = ["Corporate", "Individual"];
                let corporateTypeOptions = '<option selected value="' + response.Type + '">' + response.Type + '</option>';
                for (let corporateType of corporateTypes) {
                    if (response.Type !== corporateType) {
                        corporateTypeOptions += '<option value="' + corporateType + '">' + corporateType + '</option>';
                    }
                }
                $('.u_donor_type').html(corporateTypeOptions);
                $('.u_donor_type').change(function() {
                    var selectedType = $(this).val();
                    if (selectedType === 'Corporate') {
                        $('.ucorporateName').show();
                        $('.u_donor_corporate').prop('required', true);

                    } else {
                        $('.ucorporateName').hide();
                        $('.u_donor_corporate').prop('required', false);
                    }
                });

                $('.u_donor_org').html("<option selected value='"+response.orgId+"'>" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'.u_pd_org', function(data) {
                    $('.u_donor_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('.u_donor_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });
                $('#edit-donor').modal('show');
                $('#ajax-loader').hide();
              
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Donor Modal

    //Update Donor
    $('#u_register_donor').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_donor-id') {
                Id = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-donor/' + Id;
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
                            $('#edit-donor').modal('hide');
                            $('#view-donors').DataTable().ajax.reload();
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
                            $('#edit-donor').modal('hide');
                            $('#view-donors').DataTable().ajax.reload(); // Refresh DataTable
                            $('.text-danger').hide();
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Donor
});
// Donor Registration