$(document).ready(function() {
    //Open Add Employee Modal Salary
    $(document).on('click', '.add_empSalary', function() {
        var orgId = $('#es-org').val();
        $('#amount').val('').trigger('input');
        // $('#es-org').empty();
        $('#es-org').select2();
        $('#es-sorgite').html("<option selected disabled value=''>Select Organization</option>").prop('disabled',true);
        $('#es-site').empty();
        $('#es-site').select2();
        $('#es-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#es-site', function(data) {
                $('#es-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#es-site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            fetchOrganizations(null,null,'#es-org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#es-org').html(options.join('')).trigger('change'); // This is for Select2
            });
           
            OrgChangeSites('#es-org', '#es-site', '#add_qualificationSetup');
        }
        $('#show_emp').empty();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#es-site', '#show_emp', '#add_empSalary');
        $('#add-empSalary').modal('show');
    });
    //Open Add Employee Salary Modal

    //Add Employee Salary
    $('#add_empSalary').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'salary_remarks'))
            {
                var FieldName = field.name;
                FieldName = field.name.replace('[]', '');
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
                url: "/hr/addempsalary",
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
                                $('#add-empSalary').modal('hide');
                                $('#view-empSalary').DataTable().ajax.reload();
                                $('#add_empSalary').find('select').val($('#add_empSalary').find('select option:first').val()).trigger('change');
                                $('#add_empSalary')[0].reset();
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
                                $('#add_empSalary').find('select').val($('#add_empSalary').find('select option:first').val()).trigger('change');
                                $('#add_empSalary')[0].reset();
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
    //Add Employee Salary

    // View Employee Salary Data
    var viewempSalary =  $('#view-empSalary').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewemployeesalary',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'additions', name: 'additions' },
            { data: 'deductions', name: 'deductions' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "230px"
            },
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 3,
                width: "350px"
            },
            {
                targets: 5,
                width: "350px"
            }
        ]
    });

    viewempSalary.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempSalary.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempSalary.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Salary Data

    // Update Employee Salary Status
    $(document).on('click', '.empsalary_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empsalary-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empSalary').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Salary Status

    //Update Employee Salary Modal
    $(document).on('click', '.edit-empSalary', function() {
        var empSalaryId = $(this).data('empsalary-id');
        var url = '/hr/updatesalarymodal/' + empSalaryId;
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
                $('#usalary-id').val(response.id);
                $.each(response.additions, function(name, value) {
                    $('#' + name).val(value);
                    // $('#' + name).siblings('.amount_conversion').text(value);
                });
        
                // Populate deductions
                $.each(response.deductions, function(name, value) {
                    $('#' + name).val(value);
                    // $('#' + name).siblings('.amount_conversion').text(value);
                });
                // $('.uempSalary').val(response.salary).trigger('input');
                $('#edit-empSalary').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Salary Modal

    //Update Employee Salary
    $('#u_empSalary').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var salaryId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'usalary-id') {
                salaryId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empsalary/' + salaryId;
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
                            $('#edit-empSalary').modal('hide');
                            $('#view-empSalary').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empSalary')[0].reset();
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
    //Update Employee Salary
});