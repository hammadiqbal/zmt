$(document).ready(function() {
    
    //Add District
    $('#add_district').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#district_division').val();
        data.push({ name: 'province', value: provinceValue });
        data.push({ name: 'division', value: divisionValue });
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
                    url: "/territory/adddistrict",
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
                                    $('#add-district').modal('hide');
                                    $('#view-district').DataTable().ajax.reload(); 
                                    $('#add_district').find('select').val($('#add_district').find('select option:first').val()).trigger('change');
                                    $('#add_district')[0].reset();
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
                                    $('#add_district').find('select').val($('#add_district').find('select option:first').val()).trigger('change');
                                    $('#add_district')[0].reset();
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
    //Add District

    //Open Add District Setup
    $(document).on('click', '.adddistrict', function() {
        $('#district_division').html("<option selected disabled value=''>Select District</option>").prop('disabled', true);
        ProvinceChangeDivision('#province_name', '#district_division', '#add_district');
        $('#add-district').modal('show');

    });
    //Open Add District Setup
    $('#district_division').html("<option selected disabled value=''>Select District</option>").prop('disabled', true);

         // View District
         var viewdistrict =  $('#view-district').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/territory/district',
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' ,render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
                { data: 'division_name', name: 'division_name',render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'province_name', name: 'province_name',render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 1,
                    width: "200px"
                },
                {
                    targets: 6,
                    width: "300px"
                }
            ]
        });
    
        viewdistrict.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewdistrict.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewdistrict.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View District

        
    // Update District Status
    $(document).on('click', '.district_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/district/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-district').DataTable().ajax.reload();
            }
            },
            error: function(xhr, status, error) {
            console.log(error);
            }
        });

    });
    // Update District Status

    
    //Update District Modal
    $(document).on('click', '.edit-district', function() {

        var districtId = $(this).data('district-id');
        var url = '/territory/district/' + districtId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var province_name = response.province_name;
                var provinceId = response.province_id;
                var divisionName = response.division_name;
                var divisionId = response.division_id;

                $('.u_province_name').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                $('.u_district_division').html("<option selected value='"+divisionId+"'>" + divisionName + "</option>");

                if (provinceId) {
                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                        },
                        success: function(resp) {

                            $.each(resp, function(key, value) {
                                $('.u_province_name').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            provinceId: provinceId,
                            divisionId: divisionId,
                        },
                        beforeSend: function() {
                            $('.u_district_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_district_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                                $('.u_district_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    // $('.u_province_name').change(function() {
                    $('.u_province_name').off('change.uProvinceName').on('change.uProvinceName', function(){
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                            },
                            success: function(resp) {
                                 $('.u_district_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('.u_district_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                // $('#district_division').prop('disabled', false);
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });
                }//

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.district-id').val(response.id);
                $('.district-name').val(response.name);
                $('#edit-district').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update District Modal

    

    //Update District
    $('#update_district').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var districtId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'district-id') {
                districtId = formData[i].value;
                break;
            }
        }
        var url = '/update-district/' + districtId;
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
                            $('#edit-district').modal('hide');
                            $('#view-district').DataTable().ajax.reload(); // Refresh DataTable
                            $('#district')[0].reset();
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
    //Update District
});