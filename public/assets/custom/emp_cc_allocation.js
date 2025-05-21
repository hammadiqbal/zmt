$(document).ready(function() {
    $(document).on('change', '#empcc_org', function() {
        var orgId = $(this).val();
        var currentRowSiteSelect = $(this).closest('.duplicate').find('#empcc_site');
        if (orgId) {
            fetchOrganizationSites(orgId,'#empcc_site', function(data) {
                if (data.length > 0) {

                    currentRowSiteSelect.empty();
                    currentRowSiteSelect.append('<option selected disabled value="">Select Site</option>');
                    $.each(data, function(key, value) {
                        currentRowSiteSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowSiteSelect.find('option:contains("Loading...")').remove();
                    currentRowSiteSelect.prop('disabled', false);
                }
                else {
                    Swal.fire({
                        text: 'Sites are not available for selected Organization',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#add_empCC')[0].reset();
                        }
                    });
                }
            });
        }
    });

    $(document).on('change', '.u_empcc_site', function() {
        var siteId = $(this).val();
        var currentRow = $(this).closest('.loadempcc'); // Find the current row
        var currentRowCCSelect = currentRow.find('.u_empcc'); // Find the cost center dropdown in the current row
    
        if (siteId) {
            fetchActivatedCostCenters(siteId, currentRowCCSelect, function(data) {
                if (data.length > 0) {
                    currentRowCCSelect.empty();
                    currentRowCCSelect.append('<option selected disabled value="">Select Cost Center</option>');
                    $.each(data, function(key, value) {
                        currentRowCCSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowCCSelect.find('option:contains("Loading...")').remove();
                    currentRowCCSelect.prop('disabled', false);
                } else {
                    Swal.fire({
                        text: 'Cost Centers are not activated for the selected Head Count Site',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentRowCCSelect.empty();
                            currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });
        } else {
            currentRowCCSelect.empty();
            currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
        }
    });

    $(document).on('change', '.empcc_site', function() {
        var siteId = $(this).val();
        var currentRow = $(this).closest('.duplicate'); // Find the current row
        var currentRowCCSelect = currentRow.find('.emp_costcenter'); // Find the cost center dropdown in the current row
    
        if (siteId) {
            fetchActivatedCostCenters(siteId, currentRowCCSelect, function(data) {
                if (data.length > 0) {
                    currentRowCCSelect.empty();
                    currentRowCCSelect.append('<option selected disabled value="">Select Cost Center</option>');
                    $.each(data, function(key, value) {
                        currentRowCCSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowCCSelect.find('option:contains("Loading...")').remove();
                    currentRowCCSelect.prop('disabled', false);
                } else {
                    Swal.fire({
                        text: 'Cost Centers are not activated for the selected Head Count Site',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentRowCCSelect.empty();
                            currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });
        } else {
            currentRowCCSelect.empty();
            currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
        }
    });
    
    //Open Add Employee CC Setup
    $(document).on('click', '.addEmpCC', function() {
        var orgId = $('#empcc_org').val();
        if(orgId)
        {
            $('#emp_headcountsite').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', false);
            fetchOrganizationSites(orgId, '#emp_headcountsite', function(data) {
                $.each(data, function(key, value) {
                    $('#emp_headcountsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
            $('.empcc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
            fetchOrganizationSites(orgId, '.empcc_site', function(data) {
                $.each(data, function(key, value) {
                    $('.empcc_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#empcc_org').empty();
            $('#empcc_org').select2();
            fetchOrganizations(null,null,'#empcc_org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                // $('#empcc_org').html(options.join('')).trigger('change');
                $('#empcc_org').html(options.join(''));
            });
            $('#emp_headcountsite').empty();
            $('#emp_headcountsite').select2();
            $('#emp_headcountsite').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled',true);
            // OrgChangeSites('#empcc_org', '#emp_headcountsite', '#add_medicalLicense');
            OrgChangeSites('#empcc_org', '#emp_headcountsite', '#add_medicalLicense', 'EmpHeadcountSite');

            $('.empcc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            // OrgChangeSites('#empcc_org', '.empcc_site', '#add_medicalLicense');
            OrgChangeSites('#empcc_org', '.empcc_site', '#add_medicalLicense', 'EmpCostCenterSite');

        }
        $('#show_emp').empty();
        $('#show_emp').select2();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#emp_headcountsite', '#show_emp', '#add_empCC');
        $('.emp_costcenter').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
        $('#add-empcc').modal('show');
    });
    //Open Add Employee CC Setup

    //Add Employee CC
    $('#add_empCC').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);

        var resp = true;
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
                url: "/hr/addempcc",
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
                        // }).then((result) => {
                        //     if (result.isConfirmed) {
                        //         $('#add_empCC').find('select').each(function() {
                        //             $(this).val($(this).find('option:first').val()).trigger('change');
                        //         });
                        //         $('#add_empCC')[0].reset();
                        //     }
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
    //Add Employee CC

    //View Employee CC
    $('#viewempCC').on('change', function() {
        var EmployeeId = $(this).val();
        LoadEmployeeCostCenter(EmployeeId);
    });
    //View Employee CC

    //Update Employee CC
    $('#updateEmpCC').submit(function(e) {
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
            url: "/hr/updateempcc",
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
                            $('#updateEmpCC')[0].reset();
                            $('.profiletimeline').empty();
                            LoadEmployeeCostCenter(empId);
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
            error: function(error) {
                console.log(error);
            }
        });

    });
    //Update Employee CC
});