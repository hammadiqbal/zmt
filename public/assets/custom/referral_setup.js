// Referral Site Setup
$(document).ready(function() {
    //Open Referral Site Setup
    $(document).on('click', '.add-referralsite', function() {
        $('.text-danger').show();
        var orgId = $('#rf_org').val();
        if(!orgId)
        {
            $('#rf_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#rf_org', function(data) {
                $('#rf_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#rf_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('#rf_province').trigger('change');
        // $('#rf_division').html("<option selected value=''>Select Division</option>").prop('disabled', true);
    
        $('#add-referralsite').modal('show');
    });
    ProvinceChangeDivision('#rf_province', '#rf_division', '#add_referralsite', 'Karachi');
    $('#rf_district').html("<option selected value=''>Select District</option>").prop('disabled', true);
    DivisionChangeDistrict('#rf_division', '#rf_district', '#add_referralsite');
    //Open Referral Site Setup

    //Add Referral Site
    $('#add_referralsite').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            // if (((field.value == '') || (field.value == null)))
            if ((field.value == '' || field.value == null) && (field.name != 'rf_cell') && (field.name != 'rf_landline') && (field.name != 'rf_remarks'))
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
                url: "/orgSetup/addreferralsite",
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
                                $('#add-referralsite').modal('hide');
                                $('#view-referralsite').DataTable().ajax.reload();
                                $('#add_referralsite')[0].reset();
                                $('#add_referralsite').find('select').each(function(){
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
                                $('#add_referralsite').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_referralsite')[0].reset();
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
    //Add Referral Site

    // View Referral Site
    var viewReferralSite =  $('#view-referralsite').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/orgSetup/viewreferralsite',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'address', name: 'address' },
            { data: 'contact', name: 'contact' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "250px"
            },
            {
                targets: 5,
                width: "300px"
            }
            
        ]
    });

    viewReferralSite.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewReferralSite.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewReferralSite.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Referral Site

    // Update Referral Site Status
    $(document).on('click', '.rs_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/orgSetup/rs-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-referralsite').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Referral Site Status

    //Update Referral Site Modal
    $(document).on('click', '.edit-rs', function() {
        $('.text-danger').show();
        var rsId = $(this).data('cg-id');
        var url = '/orgSetup/updatereferralsite/' + rsId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.u_rf-id').val(response.id);
                
                if($('#u_rf_org').length) {
                    $('#u_rf_org').html("<option selected value="+ response.org_id +">" + response.org_name + "</option>");
                    fetchOrganizations('null', '','#u_rf_org', function(data) {
                        $('#u_rf_org').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            if(response.org_id != value.id) {
                                $('#u_rf_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                            }
                        });
                    });
                }
                $('#u_rf_province').html("<option selected value='"+response.province_id+"'>" + response.province_name + "</option>");
                $('#u_rf_division').html("<option selected value='"+response.division_id+"'>" + response.division_name + "</option>");
                $('#u_rf_district').html("<option selected value='"+response.district_id+"'>" + response.district_name + "</option>");

                $.ajax({
                    url: 'territory/updateprovince',
                    type: 'GET',
                    data: {
                        provinceId: response.province_id,
                    },
                    beforeSend: function() {
                        $('#u_rf_province').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_rf_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#u_rf_province').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $.ajax({
                    url: 'territory/updatedivision',
                    type: 'GET',
                    data: {
                        provinceId: response.province_id,
                        divisionId: response.division_id,
                    },
                    beforeSend: function() {
                        $('#u_rf_division').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_rf_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#u_rf_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $.ajax({
                    url: 'territory/updatedistrict',
                    type: 'GET',
                    data: {
                        districtId: response.district_id,
                    },
                    beforeSend: function() {
                        $('#u_rf_district').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_rf_district').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#u_rf_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#u_rf_province').off('change.uRFProvince').on('change.uRFProvince', function(){
                    var province_id = $(this).val();
                    $.ajax({
                        url: 'territory/updatedivision',
                        type: 'GET',
                        data: {
                            provinceId: province_id,
                        },
                        beforeSend: function() {
                            $('#u_rf_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                                $('#u_rf_division').html("<option selected disabled value=''>Select Division</option>");
                            $.each(resp, function(key, value) {
                                $('#u_rf_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
                });

                $('#u_rf_division').off('change.uRFDivision').on('change.uRFDivision', function(){
                    var divisionid = $(this).val();
                    $.ajax({
                        url: 'territory/updatedistrict',
                        type: 'GET',
                        data: {
                            divisionId: divisionid,
                        },
                        beforeSend: function() {
                            $('#u_rf_district').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_rf_district').html("<option selected disabled value=''>Select District</option>");
                            $.each(resp, function(key, value) {
                                $('#u_rf_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
                });

                $('.u_rf_desc').val(response.name);
                $('.u_rf_cell').val(response.cell);
                $('.u_rf_landline').val(response.landline);
                $('.u_rf_remarks').val(response.remarks);
                $('#date-format1').val(formattedDateTime);
                
                $('#edit-referralsite').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });

    //Update Referral Site
    $('#u_referralsite').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var rsId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_rf-id') {
                rsId = formData[i].value;
                break;
            }
        }
        var url = '/orgSetup/updatereferralsite/' + rsId;
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
                }
                if (fieldName == 'error') {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    });
                } else if (fieldName == 'success') {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-referralsite').modal('hide');
                            $('#view-referralsite').DataTable().ajax.reload();
                            $('#u_referralsite')[0].reset();
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
    //Update Referral Site
});
// Referral Site Setup