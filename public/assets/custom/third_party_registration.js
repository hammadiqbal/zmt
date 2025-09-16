// Third Party Registration Setup
$(document).ready(function() {
    //Open Third Party Registration Setup
    $(document).on('click', '.tp-registration', function() {
        var orgId = $('#tp_org').val();
        if(!orgId)
        {
            $('#tp_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#tp_org', function(data) {
                $('#tp_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#tp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('.tp_corporate').hide();
        $('#vendor_cat').change(function() {
            var selectedType = $(this).val();
            if (selectedType === 'c') {
                $('.tp_corporate').show();
            } else {
                $('.tp_corporate').hide();
            }
        });

        $('#tp-registration').modal('show');
    });
    //Open Third Party Registration Setup

    //Add Third Party Registration
    $('#register_tp').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'tp_landline') && (field.name != 'tp_remarks')  && (field.name != 'tp_corporate_name'))
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

        var VendorCat = $('#vendor_cat').val();
        if (VendorCat === 'c') {
            var CorporateField = $('input[name="tp_corporate_name"]');
            if (CorporateField.val() == '' || CorporateField.val() == null) {
                $('#tp_corporate_name_error').text("This field is required");
                CorporateField.addClass('requirefield');
                CorporateField.focus(function() {
                    $('#tp_corporate_name_error').text("");
                    $(this).removeClass("requirefield");
                });
                resp = false;
            }
        }

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addthirdpartyregistration",
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
                                $('#tp-registration').modal('hide');
                                $('#view-tpregistration').DataTable().ajax.reload();
                                $('#register_tp')[0].reset();
                                $('#register_tp').find('select').each(function(){
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
                                $('#register_tp').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#register_tp')[0].reset();
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
    //Add Third Party Registration

    // View Third Party Data
    var viewThirdParty =  $('#view-tpregistration').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/thirdpartyregistration',
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
                width: "300px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });

    viewThirdParty.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewThirdParty.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewThirdParty.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Third Party Data

    // Update Third Party Status
    $(document).on('click', '.tp_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/tp-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-tpregistration').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Third Party Status

    //Update Third Party Modal
    $(document).on('click', '.edit-tp', function() {
        var tpId = $(this).data('tp-id');
        var url = '/inventory/updatetpregistration/' + tpId;
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
                $('.u_tp-id').val(response.id);
                $('#u_tp_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_tp_org', function(data) {
                    $('#u_tp_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_tp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                if(response.vendorCat == 'c'){
                    $('.ucorporateName').show();
                    $('.u_tp_corporate_name').prop('required', true);
                }
                else{
                    $('.ucorporateName').hide();
                    $('.u_tp_corporate_name').prop('required', false);
                }
                $('.u_registration_type').val(response.registrationType).change();
                $('#u_tp_prefix').val(response.prefixId).change();

                $('.u_vendor_cat').val(response.vendorCat).change();
                $('.u_vendor_cat').change(function() {
                    var selectedType = $(this).val();
                    if (selectedType === 'c') {
                        $('.ucorporateName').show();
                        $('.u_tp_corporate_name').prop('required', true);

                    } else {
                        $('.ucorporateName').hide();
                        $('.u_tp_corporate_name').prop('required', false);
                    }
                });
                $('.u_tp_corporate_name').val(response.corporateName);
                $('.u_tp_name').val(response.personName);
                $('.u_tp_email').val(response.personEmail);
                $('.u_tp_cell').val(response.cellNo);
                $('.u_tp_landline').val(response.landlineNo);
                $('.u_tp_address').val(response.address);
                $('.u_tp_remarks').val(response.remarks);
                $('#edit-tp').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Third Party Modal

    //Update Third Party Registration
    $('#u_register_tp').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var tpId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_tp-id') {
                tpId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-tpregistration/' + tpId;
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
                            $('#edit-tp').modal('hide');
                            $('#view-tpregistration').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_register_tp')[0].reset();
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
    //Update Third Party Registration
});
// Third Party Registration Setup