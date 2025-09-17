$(document).ready(function() {
    $('#isEmployee').on('switchChange.bootstrapSwitch', function(event, state) {
        if (state) {
            $('#userEmployee,#enable_site').show();
            $('#userDetails').hide();
            $('#useremail_error').hide();
            $('#username_error').hide();
            $('#userEmp').val($('#userEmp option:first').val()).change().prop('disabled', false);
            $('#empStatus').val('on');
            $('#siteStatus').val('0');
            
        } else {
            $('#userDetails').show();
            $('#userEmployee,#enable_site').hide();
            $('#username_error').show();
            $('input[name="username"]').val('').attr('readonly', false);
            $('#useremail_error').show();
            $('#userEmp').val($('#userEmp option:first').val('0')).change().prop('disabled', true);
            // $('#userEmp').prepend("<option selected value='0'>Select Employee</option>").prop('disabled', true);
            $('input[name="useremail"]').val('').attr('readonly', false);
            $('#empStatus').val('off');
            $('#siteStatus').val('1');
        }
    });
    
    // Site Enabled checkbox functionality for Add User modal
    $('#siteEnabled').on('switchChange.bootstrapSwitch', function(event, state) {
        if (state) {
            $('#siteStatus').val('1');
        } else {
            $('#siteStatus').val('0');
        }
    });
    
    // Site Enabled checkbox functionality for Edit User modal
    $('#u_siteEnabled').on('switchChange.bootstrapSwitch', function(event, state) {
        if (state) {
            $('#u_siteStatus').val('1');
        } else {
            $('#u_siteStatus').val('0');
        }
    });
    
    $(document).on('change', '#userOrg', function() {
        var orgId = $(this).val();
       
        fetchOrganizationEmployees(orgId, '#userEmp', function(data) {
            if (data.length > 0) {
                $('#userEmp').empty();
                $('#userEmp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#userEmp').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            }
            else {
                Swal.fire({
                    text: 'No employees are currently available for the selected organization.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#userEmp').html("<option selected disabled value=''>N/A</option>").prop('disabled', true);
                        // $('#add-user').modal('hide');
                    }
                });
            }
        }, function(error) {
            console.log(error);
        });
    });
    
    //Open Add User Setup
    $(document).on('click', '.adduser', function() {
        var orgId = $('#usereOrg').val();
          $('#siteStatus').val('0');
        $('#userEmp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', true);
        $('#add-user').modal('show');
        if (orgId) {
            fetchOrganizationEmployees(orgId, '#userEmp', function(data) {
                if (data.length > 0) {
                    $('#userEmp').empty();
                    $('#userEmp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('#userEmp').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
                else {
                    $('#userEmp').html("<option selected disabled value=''>N/A</option>").prop('disabled', true);
                    Swal.fire({
                        text: 'Employees are not available for your Organization',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }, function(error) {
                console.log(error);
            });
        }
    });
    //Open Add User Setup
    
    $('#userEmp').change(function() {
        var empId = $(this).val();
        fetchEmployeeDetails(empId, '#userEmp', function(data) {
            $.each(data, function(key, value) {
                $('#userDetails').show();
                $('#nameLabel').hide();
                $('input[name="username"]').val(value.name).attr('readonly', true).removeAttr('placeholder');;
                $('#emailLabel').hide();
                $('input[name="useremail"]').val(value.email).attr('readonly', true).removeAttr('placeholder');;
            });
    
        }, function(error) {
            console.log(error);
        });
    });

    //Add User
    $('#add_user').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                // if((FieldName != 'userEmp') && (FieldName != 'username') && (FieldName != 'useremail'))
                // {
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
                // }

            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/user/adduser",
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
                        var errorElemId = '#' + fieldName + '_error';
                        $(errorElemId).text(fieldErrors);
                        $('select[name= "' +fieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                        (function(currentFieldName, currentErrorElemId) {
                            $('select[name= "' + currentFieldName +'"' ).on('select2:open', function() {
                                $(currentErrorElemId).text("");
                                $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                            });
                        })(fieldName, errorElemId);
                        Swal.close();
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
                                location.reload();
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
                                location.reload();
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
    //Add User

    // VieW User
    var viewuser =  $('#view-user').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/user/data',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name', render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'email', name: 'email' },
            { data: 'rolename', name: 'rolename',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            // { data: 'empname', name: 'empname',render: function(data, type, row) {
            //     if (data === null) {
            //         return "N/A";
            //     } else {
            //         return data.charAt(0).toUpperCase() + data.slice(1);
            //     }
            // }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            emptyTable: "No data available"
        },
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 6,
                width: "300px"
            }
        ]
    });

    viewuser.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });

    // Show the loader before an AJAX request is made
    viewuser.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewuser.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View User

    // Update Status
    $(document).on('click', '.user_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/user/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-user').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Status

    //Update User Modal
    $(document).on('click', '.edit-user', function() {
        $('#ajax-loader').show();
        var userId = $(this).data('user-id');
        var url = '/user/editdata/' + userId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var roleId = response.roleId;
                var empId = response.empId;
                var roleName = response.rolename;
                var empName = response.empName;
                var orgId = response.orgId;
                var orgName = response.orgName;
                $('.u_user_role').html("<option selected value='"+roleId+"'>" + roleName + "</option>");
                $('.u_user_org').html("<option selected value='"+orgId+"'>" + orgName + "</option>");
                $('.user-name').val(response.name).attr('readonly',true);
                $('.user-email').val(response.email).attr('readonly',true);
                // Set site enabled checkbox
                if (response.siteEnabled == 1) {
                    $('#u_siteEnabled').bootstrapSwitch('state', true);
                    $('#u_siteStatus').val('1');
                } else {
                    $('#u_siteEnabled').bootstrapSwitch('state', false);
                    $('#u_siteStatus').val('0');
                }
                
                $('#u_employee').hide();
                if (roleId) {
                    $.ajax({
                        url: 'user/updaterole',
                        type: 'GET',
                        data: {
                            roleId: roleId,
                            roleName: roleName,
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_user_role').append('<option value="' + value.id + '">' + value.role + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    fetchOrganizations(orgId,orgName,'.u_user_org', function(data) {
                        $('.u_user_org').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            $('.u_user_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        });
                    });
                    if(empId == '0')
                    {
                        $('.u_user_emp').append('<option selected value="0"></option>');
                        $('.u_user_emp').prop('required', false);
                    }
                    else{
                        $('.u_user_emp').prop('required', true);
                        $('.u_user_emp').html("<option selected value='"+empId+"'>" + empName + "</option>").prop('disabled',true);
                        
                        $(document).on('change', '.u_user_org', function() {
                            var orgId = $(this).val();
                            fetchOrganizationEmployees(orgId, '.u_user_emp', function(data) {
                                if (data.length > 0) {
                                    $('.u_user_emp').empty();
                                    $('.u_user_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', false);
                                    $.each(data, function(key, value) {
                                        $('.u_user_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    });
                                }
                                else {
                                    Swal.fire({
                                        text: 'Employees are not available for selected Organization',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#update_user').modal('hide');
                                        }
                                    });
                                }
                            }, function(error) {
                                console.log(error);
                            });
                        });
        
                        $('.u_user_emp').change(function() {
                            var empId = $(this).val();
                            fetchEmployeeDetails(empId, '.u_user_emp', function(data) {
                                $.each(data, function(key, value) {
                                    $('input[name="user_name"]').val(value.name).attr('readonly', true);
                                    $('input[name="user_email"]').val(value.email).attr('readonly', true);
                                });
        
                            }, function(error) {
                                console.log(error);
                            });
                        });
                        // fetchEmployees(empId, '.u_user_emp', function(data) {
                        //     $.each(data, function(key, value) {
                        //         if(value.id != empId)
                        //         {
                        //             $('.u_user_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        //         }
                        //     });

                        // }, function(error) {
                        //     console.log(error);
                        // });
                    }
                }

                

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.user-id').val(userId);

                $('#edit-user').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update User Modal

    //Update User
    $('#update_user').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        // var formData = $(this).serializeArray();
        var userId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'user-id') {
                userId = formData[i].value;
                break;
            }
        }
        var url = '/update-user/' + userId;
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
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-user').modal('hide');
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
                            $('#edit-user').modal('hide');
                            $('#view-user').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_user')[0].reset();
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
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update User

    // Update Image
    $('#u_img').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        console.log('ok');
        var formData = new FormData($('#u_img')[0]);
        var userId = $('#user-id').val();
        var userImg = $('#userImg')[0].files[0];
        formData.append('userImg', userImg);

        var url = '/userImg/' + userId;
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
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
                            $('.text-danger').hide();
                            window.location.href = 'profile';
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });

        return;

    });
    // Update Image
});