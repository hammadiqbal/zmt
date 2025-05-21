$(document).ready(function() {
        //Open Add Employee Medical License Setup
        $(document).on('click', '.addmedicalLicense', function() {
            var orgId = $('#em-org').val();
            if(orgId)
            {
                fetchOrganizationSites(orgId, '#em-site', function(data) {
                    $('#em-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('#em-site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
            else{
                $('#em-org').empty();
                $('#em-org').select2();
                fetchOrganizations(null,null,'#em-org', function(data) {
                    var options = ["<option selected disabled value=''>Select Organization</option>"];
                    $.each(data, function(key, value) {
                        options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                    $('#em-org').html(options.join('')).trigger('change');
                });
                $('#em-site').empty();
                $('#em-site').select2();
                $('#em-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
                OrgChangeSites('#em-org', '#em-site', '#add_medicalLicense');
            }
            $('#show_emp').empty();
            $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
            SiteChangeEmployees('#em-site', '#show_emp', '#add_medicalLicense');
            $('#add-medicalLicense').modal('show');
        });
        //Open Add Employee Medical License Setup
    
        //Add Employee Medical License
        $('#add_medicalLicense').submit(function(e) {
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
                        elem.addClass('requirefield');
                        elem.focus(function() {
                            errorField.text("");
                            elem.removeClass("requirefield");
                        });
                        resp = false;
                    }
                    else {
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
                    url: "/hr/addmedical-license",
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
                                    $('#add-medicalLicense').modal('hide');
                                    $('#add_medicalLicense').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#add_medicalLicense')[0].reset();
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
                                    $('#add_medicalLicense').find('select').each(function() {
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#add_medicalLicense')[0].reset();
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
        //Add Employee Medical License
    
    
        //View Medical License Setup
        $('#viewempMedicalLicense').on('change', function() {
            var EmployeeId = $(this).val();
            LoadEmployeeMedicalLicense(EmployeeId);
    
        });
        //View Medical License Setup
    
        //Update Medical License Setup
        $('#updateMedicalLicense').submit(function(e) {
            e.preventDefault();
            var data = $(this).serializeArray();
            var empId = null;
            for (var i = 0; i < data.length; i++) {
                if (data[i].name === 'empId') {
                    empId = data[i].value;
                    break;
                }
            }
            $.ajax({
                url: "/hr/updatemedical-license",
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
                                $('#updateMedicalLicense')[0].reset();
                                $('.profiletimeline').empty();
                                LoadEmployeeMedicalLicense(empId);
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
                                $('#updateMedicalLicense')[0].reset();
                            }
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
    
        });
        //Update Medical License Setup
});