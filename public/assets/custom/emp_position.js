$(document).ready(function() {
        //Open Employee Position Setup
        $('#emp-cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', true);
        OrgChangeCadre('#positionOrg', '#emp-cadre', '#add_empPosition');
        $(document).on('click', '.add-empPosition', function() {
            var orgId = $('#positionOrg').val();
            $('#emp-cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', false);
            fetchEmployeeCadre(orgId, '#emp-cadre', function(data) {
                if (data.length > 0) {
                    $.each(data, function(key, value) {
                        $('#emp-cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            }, function(error) {
                console.log(error);
            });
            $('#add-empPosition').modal('show');
        });
        //Open Employee Position Setup
    
        //Add Employee Position
        $('#add_empPosition').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
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
                    url: "/hr/addempposition",
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
                                    $('#add-empPosition').modal('hide');
                                    $('#view-empPosition').DataTable().ajax.reload();
                                    $('#add_empPosition').find('select').val($('#add_empPosition').find('select option:first').val()).trigger('change');
                                    $('#add_empPosition')[0].reset();
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
                                    $('#add_empPosition').find('select').val($('#add_empPosition').find('select option:first').val()).trigger('change');
                                    $('#add_empPosition')[0].reset();
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
        //Add Employee Position
    
        // View Employee Position Data
        var viewempPosition =  $('#view-empPosition').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/hr/emppositiondata',
                data: function (d) {
                    d.cadre = $('#fb_cadre').val();  
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name',render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
                { data: 'empcadre', name: 'empcadre',render: function(data, type, row) {
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

        $('#fb_cadre').on('change', function () {
            viewempPosition.ajax.reload();  
        });

        $('.clearFilter').on('click', function () {
            $('#fb_cadre').each(function() {
                $(this).val($(this).find('option:first').val()).change();
            });
            viewempPosition.ajax.reload();   
        });
    
        viewempPosition.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewempPosition.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewempPosition.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Employee Position Data
    
        // Update Employee Position Status
        $(document).on('click', '.empPosition_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
    
            $.ajax({
                url: '/hr/empposition-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                    var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-empPosition').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
    
        });
        // Update Employee Position Status
    
        //Update Employee Position Modal
        $(document).on('click', '.edit-empPosition', function() {
            var empPositionId = $(this).data('empposition-id');
            $('#u_positionOrg').empty();
            var url = '/hr/emppositionStatus/' + empPositionId;
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
                    $('.ep-id').val(response.id);
                    $('.u_ep').val(response.name);
                    var orgName = response.orgName;
                    var orgID = response.orgId;
                    $('#u_positionOrg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                    $('.u_cadre').html("<option selected value='"+response.cadreid+"'>" + response.cadre + "</option>");
    
                    fetchOrganizations(orgID,orgName,'#u_positionOrg', function(data) {
                        $('#u_positionOrg').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            if(value.id != orgID)
                            {
                                $('#u_positionOrg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                            }
                        });
                    });
    
                    if (orgID) {
                        fetchEmployeeCadre(orgID, '#u_cadre', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    if(value.id != response.cadreid )
                                    {
                                        $('.u_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Cadre are not available for selected Organization & Sites',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-empPosition').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
    
                    }
    
    
                    $('#edit-empPosition').modal('show');
                    $('#ajax-loader').hide();
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update Employee Position Modal
    
        //Update Employee Position
        $('#u_empPosition').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var epId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'ep-id') {
                    epId = formData[i].value;
                    break;
                }
            }
            var url = 'hr/update-empposition/' + epId;
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
                                $('#edit-empPosition').modal('hide');
                                $('#view-empPosition').DataTable().ajax.reload(); // Refresh DataTable
                                $('#u_empPosition')[0].reset();
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
        //Update Employee Position
});