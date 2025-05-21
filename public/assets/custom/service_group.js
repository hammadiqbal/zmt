$(document).ready(function() {
        //Add Service Group
        $('#add_servicegroup').submit(function(e) {
            e.preventDefault();
            var data = $(this).serializeArray();
            var sg_type = $('#sg_type').val();
            data.push({ name: 'sg_type', value: sg_type });
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
                    // $( 'select[name= "' +FieldName +'"' ).addClass('requirefield');
                    // $( 'select[name= "' +FieldName +'"' ).focus(function() {
                    //     $(FieldID).text("");
                    //     $('select[name= "' +FieldName +'"' ).removeClass("requirefield");
                    // });
                    resp = false;
                }
            });
    
            if(resp != false)
            {
                $.ajax({
                    url: "/services/addservicegroup",
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
                                    $('#add-servicegroup').modal('hide');
                                    $('#view-servicegroup').DataTable().ajax.reload();
                                    $('#add_servicegroup').find('select').val($('#add_servicegroup').find('select option:first').val()).trigger('change');
                                    $('#add_servicegroup')[0].reset();
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
                                    $('#add_servicegroup').find('select').val($('#add_servicegroup').find('select option:first').val()).trigger('change');
                                    $('#add_servicegroup')[0].reset();
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
        //Add Service Group
    
        // View Service Group Data
        var viewserviceGroup =  $('#view-servicegroup').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/services/servicegroup',
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
                { data: 'type_name', name: 'type_name',render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
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
    
        viewserviceGroup.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewserviceGroup.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewserviceGroup.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Service Group Data
    
        // Update Service Group Status
        $(document).on('click', '.servicegroup_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
            $.ajax({
                url: '/services/sg-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-servicegroup').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        });
        // Update Service Group Status
    
        //Update Service Group Modal
        $(document).on('click', '.edit-servicegroup', function() {
            var ServiceGroupId = $(this).data('servicegroup-id');
            var url = '/services/updateservicegroup/' + ServiceGroupId;
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
                    $('.sg-id').val(response.id);
                    $('.u_sg').val(response.name);
                    $('.u_sg_type').html("<option selected value='"+response.typeid+"'>" + response.serviceType + "</option>");
                    $.ajax({
                        url: 'services/getservicetype',
                        type: 'GET',
                        data: {
                            serviceTypeId: response.typeid,
                            serviceType: response.serviceType,
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_sg_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
    
                    $('#edit-servicegroup').modal('show');
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update Service Group Modal
    
        //Update Service Group
        $('#u_servicegroup').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var sgId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'sg-id') {
                    sgId = formData[i].value;
                    break;
                }
            }
            var url = 'services/update-servicegroup/' + sgId;
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
                                $('#edit-servicegroup').modal('hide');
                                $('#view-servicegroup').DataTable().ajax.reload(); // Refresh DataTable
                                $('#u_servicegroup')[0].reset();
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
        //Update Service Group
});