$(document).ready(function() {
    
    //Open Add Employee Qualification Setup
    $(document).on('click', '.addqualificationSetup', function() {
        $('#emp-info-row').hide();
        $('#show_emp').empty();
        var orgId = $('#eq-org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#eq-site', function(data) {
                $('#eq-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#eq-site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#eq-org').empty();
            $('#eq-org').select2();
            fetchOrganizations(null,null,'#eq-org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#eq-org').html(options.join('')).trigger('change'); // This is for Select2
            });
            $('#eq-site').empty();
            $('#eq-site').select2();

            $('#eq-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#eq-org', '#eq-site', '#add_qualificationSetup');
        }

        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#eq-site', '#show_emp', '#add_qualificationSetup');
        $('#add-qualificationSetup').modal('show');

        $('#show_emp').change(function() {
            var empId = $(this).val();
            fetchEmployeeDetails(empId, '#show_emp', function(data) {
                $.each(data, function(key, value) {
                    let infoHtml = `
                        <div class="col-12 mt-1 mb-1 emp-block">
                            <div class="card shadow-sm border mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="row align-items-center text-center">
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Organization:</small><br>
                                            <strong class="text-primary source">${value.orgName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Site:</small><br>
                                            <strong class="text-primary destination">${value.siteName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">HeadCount CC:</small><br>
                                            <strong class="text-primary source">${value.ccName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Position:</small><br>
                                            <strong class="text-primary destination">${value.positionName || '-'}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;

                    $('#emp-info-row').find('.emp-block').remove();
                    $('#emp-info-row')
                    .append(infoHtml)
                    .show();

                    // $('#userDetails').show();
                    // $('#nameLabel').hide();
                    // $('input[name="username"]').val(value.name).attr('readonly', true);
                    // $('#emailLabel').hide();
                    // $('input[name="useremail"]').val(value.email).attr('readonly', true);
                });
        
            }, function(error) {
                console.log(error);
            });
        });

    });
    //Open Add Employee Qualification Setup

    //Add Qualification Setup
    $('#add_qualificationSetup').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
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
            }
        });
        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');
                if (!value || value === "" || (elem.is('select') && value === null)) {
                    errorField.text("This field is required");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').addClass('requirefield');
                        elem.on('select2:open', function() {
                            errorField.text("");
                            elem.next('.select2-container').find('.select2-selection').removeClass("requirefield");
                        });
                    }
                    else {
                        elem.addClass('requirefield');
                        elem.focus(function() {
                            errorField.text("");
                            elem.removeClass("requirefield");
                        });
                    }
                    resp = false;
                } else {
                    errorField.text("");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').removeClass('requirefield');
                    } else {
                        elem.removeClass('requirefield');
                    }
                }
            });
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addqualification-setup",
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
                                $('#add-qualificationSetup').modal('hide');
                                $('#add_qualificationSetup').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_qualificationSetup')[0].reset();
                                location.reload();
                                //refresh here
                                // $('.text-danger').hide();
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
                                $('#add_qualificationSetup').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_qualificationSetup')[0].reset();
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
    //Add Qualification Setup

    //View Qualification Setup
    $('#viewempQualification').on('change', function() {
        var EmployeeId = $(this).val();
        LoadEmployeeQualification(EmployeeId);

    });
    //View Qualification Setup

    //Update Qualification Setup
    $('#updateQualification').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var empId = null;
        for (var i = 0; i < data.length; i++) {
            if (data[i].name === 'empId') {
                empId = data[i].value;
                break; // Once you find the first occurrence of empId, you can break out of the loop.
            }
        }
        $.ajax({
            url: "/hr/updatequalification-setup",
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
                            $('#updateQualification')[0].reset();
                            $('.profiletimeline').empty();
                            LoadEmployeeQualification(empId);
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
                            $('#updateQualification')[0].reset();
                        }
                    });
                }
            },
            error: function(error) {
                console.log(error);
            }
        });

    });
    //Update Qualification Setup
});