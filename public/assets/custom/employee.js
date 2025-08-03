$(document).ready(function() {
    //Add Employee
    $('#emp_cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', true);
    $('#emp_position').html("<option selected disabled value=''>Select Position</option>").prop('disabled', true);
    $('#emp_site').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', true);
    $('#emp_cc').html("<option selected disabled value=''>Select Head Count Cost Center</option>").prop('disabled', true);

    OrgChangeCadre('#emp_org', '#emp_cadre', '#add_employee');
    OrgChangePosition('#emp_org', '#emp_position', '#add_employee');
    OrgChangeSites('#emp_org', '#emp_site', '#add_employee');
    SiteChangeCostCenter('#emp_site', '#emp_cc', '#add_employee');
    //Open Employee Setup
    $(document).on('click', '.add-employee', function() {
        var orgId = $('#emp_org').val();
        if(orgId)
        {
            $('#emp_cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', false);
            fetchEmployeeCadre(orgId, '#emp_cadre', function(data) {
                if (data.length > 0) {
                    $.each(data, function(key, value) {
                        $('#emp_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            }, function(error) {
                console.log(error);
            });
            $('#emp_position').html("<option selected disabled value=''>Select Position</option>").prop('disabled', false);
            fetchEmployeePosition(orgId, '#emp_position', function(data) {
                if (data.length > 0) {
                    $.each(data, function(key, value) {
                        $('#emp_position').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            }, function(error) {
                console.log(error);
            });
            $('#emp_site').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', false);
            fetchOrganizationSites(orgId, '#emp_site', function(data) {
                $('#emp_site').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        
        $('#add-employee').modal('show');
    });
    //Open Employee Setup
    $('#division_name').html("<option selected value=''>Select Division</option>").prop('disabled', true);
    ProvinceChangeDivision('#province_name', '#division_name', '#add_patient');
    // ProvinceChangeDivision('#patient_province', '#patient_division', '#add_patient', 'Karachi');

    $('#district_name').html("<option selected value=''>Select District</option>").prop('disabled', true);
    DivisionChangeDistrict('#division_name', '#district_name', '#add_patient');

    $('#add_employee').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_employee')[0]);
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#division_name').val();
        var districtValue = $('#district_name').val();
        var GenderValue = $('#emp_gender').val();

        var Org = $('#emp_org').val();
        var Site = $('#emp_site').val();
        var CostCenter = $('#emp_cc').val();
        var Prefix = $('#emp_prefix').val();
        var Cadre = $('#emp_cadre').val();
        var reportTo = $('#emp_reportto').val();
        var qualificationLevel = $('#emp_qual_lvl').val();
        var EmpStatus = $('#emp_status').val();
        var WorkingStatus = $('#emp_working_status').val();
        var EmpPosition = $('#emp_position').val();
        var EmpGuardianRelation = $('#emp_guardian_relation').val();
        var EmpNextOfKinRelation = $('#emp_nextofkin_relation').val();
        var EmpReligion = $('#emp_religion').val();
        var EmpmaritalStatus = $('#emp_marital_status').val();
        var Language = $('#emp_language').val();

        var imgValue = $('#emp_img')[0].files[0];
        formData.append('emp_guardian_relation', EmpGuardianRelation);
        formData.append('emp_nextofkin_relation', EmpNextOfKinRelation);
        formData.append('emp_religion', EmpReligion);
        formData.append('emp_marital_status', EmpmaritalStatus);
        formData.append('emp_province', provinceValue);
        formData.append('emp_division', divisionValue);
        formData.append('emp_district', districtValue);
        formData.append('emp_gender', GenderValue);
        formData.append('emp_org', Org);
        formData.append('emp_site', Site);
        formData.append('emp_cc', CostCenter);
        formData.append('emp_prefix', Prefix);
        formData.append('emp_cadre', Cadre);
        formData.append('emp_reportto', reportTo);
        formData.append('emp_qual_lvl', qualificationLevel);
        formData.append('emp_status', EmpStatus);
        formData.append('emp_working_status', WorkingStatus);
        formData.append('emp_position', EmpPosition);
        formData.append('emp_language', Language);
        formData.append('emp_img', imgValue);
        var resp = true;
        var firstErrorElement = null;

        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if ((fieldValue == '' || fieldValue == 'null' || fieldValue === 'undefined') && ((fieldName != 'emp_oldcode') && (fieldName != 'emp_landline') &&  (fieldName != 'emp_additionalcell') &&  (fieldName != 'emp_email')))
            {
                var FieldName = fieldName;
                
                if((FieldName == 'start_time') || (FieldName == 'end_time'))
                {
                    if(fieldValue == '')
                    {
                        $('#week_hrs_error').text("Please Select Employee Week Hours");
                        $( 'input[name= "' +FieldName +'"' ).focus(function() {
                            $('#week_hrs_error').text("");
                            $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                        })
                    }

                }
                var FieldID = '#'+FieldName + "_error";

                if (!firstErrorElement) {
                    firstErrorElement = FieldName;
                }

                $(FieldID).text("This field is required");
                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                $( 'input[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');

                $('input[name="' + FieldName + '"][type="file"]').parent().addClass('requirefield');
                $( 'input[name="' + FieldName + '"][type="file"]').click(function() {
                    $(FieldID).text("");

                    $('input[name="' + FieldName + '"][type="file"]').parent().removeClass('requirefield');
                })
                $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
                    $(FieldID).text("");
                    $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                });
                resp = false;
            }
        });

        if (firstErrorElement) {
            $('input[name="' + firstErrorElement + '"], textarea[name="' + firstErrorElement + '"], select[name="' + firstErrorElement + '"]').focus().addClass('requirefield');
        }
        
        if(resp != false)
        {
            $.ajax({
                url: "/hr/addemployee",
                method: "POST",
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
                                $('#add-employee').modal('hide');
                                $('#view-employee').DataTable().ajax.reload(); // Refresh DataTable
                                $('#add_employee').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_employee')[0].reset();
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
                                var imgReset = $('#emp_img').dropify();
                                imgReset = imgReset.data('dropify');
                                imgReset.resetPreview();
                                imgReset.clearElement();
                                $('#add_employee').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_employee')[0].reset();
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
    //Add Employee

    // View Employee
    var viewemployee =  $('#view-employee').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewemployee',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'placement', name: 'placement' },
            { data: 'workStatus', name: 'workStatus'},
            { data: 'contactDetails', name: 'contactDetails'},
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
                width: "300px"
            },
            {
                targets: 3,
                width: "300px"
            },
            {
                targets: 4,
                width: "300px"
            },
            {
                targets: 4,
                width: "200px"
            }
        ]
    });

    viewemployee.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewemployee.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewemployee.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee

    // Print Employee Card
    $(document).on('click', '.print-card', function() {
        var empId = $(this).data('emp-id');
        var url = '/hr/print-card/' + empId;
        window.open(url, '_blank', 'width=400,height=600');
    });
    // Print Employee Card

    // Update Employee Status
    $(document).on('click', '.employee_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/employee-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {

                var status = xhr.status;
            if(status == 200)
            {
                $('#view-employee').DataTable().ajax.reload();
            }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Status

    //Employee Detail Modal
    $(document).on('click', '.employee-detail', function() {
        var empId = $(this).data('emp-id');
        var url = '/hr/employeedetail/' + empId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var imgPath = response.Image;
                $('#empImg').attr('src', imgPath);
                $('#empName').text(response.Prefix + ' ' + response.empName);      
                $('#empAddress').text(response.Address);
                $('#empMailingAddress').text(response.MailingAddress);
                $('#empOldcode').text(response.OldCode);
                $('#empEmail').text(response.Email);
                $('#empContact').text(response.contact);
                $('#empAdditionalCell').text(response.Additionalcontact);
                $('#empProvince').text(response.Province);
                $('#empGuardian').text(response.GuardianName);
                $('#empRelation').text(response.GuardianRelation);
                $('#empNextOfKin').text(response.NextOfKin);
                $('#empNextOfKinRelation').text(response.NextOfKinRelation);
                $('#empLanguage').text(response.Language);
                $('#empReligion').text(response.Religion);
                $('#empMaritalStatus').text(response.MaritalStatus);
                $('#empDivision').text(response.Division);
                $('#empDistrict').text(response.District);
                $('#empOrg').text(response.Organization);
                $('#empSite').text(response.Site);
                $('#empGender').text(response.Gender);
                $('#empCC').text(response.CostCenter);
                $('#empCadre').text(response.Cadre);
                $('#empPosition').text(response.Position);
                $('#empQualification').text(response.Qualification);
                $('#empStatus').text(response.EmpStatus);
                $('#workingStatus').text(response.WorkingStatus);

                $('#weekHrs').text(response.WeekHrs);
                $('#empManager').text(response.Manager);
                $('#empJD').text(response.JoiningDate);
                $('#empCnic').text(response.cnic);
                $('#empCnicExpiry').text(response.cnicExpiry);
                $('#empDOB').text(response.DateOfBirth);

                $('#employee-detail').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Employee Detail Modal

    // Update Employee Modal
    $(document).on('click', '.edit-employee', function() {
        var employeeId = $(this).data('employee-id');
        $('#edit_employee')[0].reset();
        $('#ajax-loader').show();
        var url = '/hr/updatemodal/' + employeeId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var empname = response.empName;
                var GuardianName = response.GuardianName;
                var GuardianRelation = response.GuardianRelation;
                var NextOfKin = response.empNextOfKin;
                var NextOfKinRelation = response.empNextOfKinRelation;
                var empCode = response.OldCode;
                var gender = response.Gender;
                var Religion = response.Religion;
                var MaritalStatus = response.MaritalStatus;
                var dob = response.empDOB;
                var org = response.Organization;
                var site = response.Site;
                var cc = response.CostCenter;
                var cadre = response.Cadre;
                var position = response.Position;
                var Manager = response.Manager;
                var Qualification = response.Qualification;
                var empStatus = response.EmpStatus;
                var workingStatus = response.WorkingStatus;
                var doj = response.empDOJ;
                var cnicExpiry = response.cnicExpiry;
                var dol = response.empDOL;
                var province = response.Province;
                var division = response.Division;
                var district = response.District;
                var cnic = response.cnic;
                var cell = response.cell;
                var AdditionalCell = response.AdditionalCell;
                var landline = response.landline;
                var email = response.Email;
                var address = response.Address;
                var MailingAddress = response.MailingAddress;
                var image = response.Image;
                // var language = response.language;
                $('#u_emp_prefix').val(response.Prefix).change();
                const languages = ["Urdu", "English", "Sindhi", "Balochi", "Punjabi", "Pashto", "Hindko", "Siraiki", "Memoni", "Gujrati", "Brahui", "Shina", "Burushaski", "Wakhi", "Balti", "Kashmiri", "Khowar"];
                let languageOptions = '<option selected>' + response.language + '</option>';
                for (let language of languages) {
                    if (language !== response.language) {
                        languageOptions += '<option>' + language + '</option>';
                    }
                }
                $('#u_emp_language').html(languageOptions);
                // $('.u_emp_language').val(language);


                var imgName = image.trim().substring(image.lastIndexOf('/') + 1);

                var emp_img_input = $('#u_empImg');
                var dropifyRenderImg = emp_img_input.closest('.dropify-wrapper').find('.dropify-render');

                dropifyRenderImg.find('img').attr('src', image);

                var imgdropifyInfos = emp_img_input.closest('.dropify-wrapper').find('.dropify-infos');
                var imgfilenameInner = imgdropifyInfos.find('.dropify-filename-inner');
                imgfilenameInner.text(imgName);

                emp_img_input.attr('data-default-file', image);
                emp_img_input.dropify('destroy');
                emp_img_input.dropify();

                $('.u_emp_code').val(empCode);
                $('.u_emp_cnic').val(cnic);
                $('.u_emp_cell').val(cell);
                $('.u_emp_additional_cell').val(AdditionalCell);
                $('.u_emp_landline').val(landline);
                $('.u_emp_email').val(email);
                // if (email) {
                //     $('.u_emp_email').prop('readonly', true);
                // } else {
                //     $('.u_emp_email').prop('readonly', false);
                // }
                $('.u_emp_address').val(address);
                $('.u_emp_mailingaddress').val(MailingAddress);
                $('.u_emp_name').val(empname);
                $('.u_guardian_name').val(GuardianName);
                $('.u_emp_nextofkin').val(NextOfKin);
                $('#u_emp_weekHrs').val(response.WeekHrs);
                $('.u-emp-id').val(response.id);

                const guardianRelations = ["Father", "Husband"];
                let guardianOptions = '<option selected>' + GuardianRelation + '</option>';
                for (let guardianRelation of guardianRelations) {
                    if (guardianRelation !== GuardianRelation) {
                        guardianOptions += '<option>' + guardianRelation + '</option>';
                    }
                }
                $('#u_guardian_relation').html(guardianOptions);

                const nextofkinRelations = ["Father", "Mother", "Brother", "Sister", "Spouse", "Child", "Grandparent", "Grandchild", "Uncle", "Aunt", "Niece", "Nephew", "Cousin", "Legal Guardian", "Friend", "Partner"];
                let nextofkinOptions = '<option selected>' + NextOfKinRelation + '</option>';
                for (let nextofkinRelation of nextofkinRelations) {
                    if (nextofkinRelation !== NextOfKinRelation) {
                        nextofkinOptions += '<option>' + nextofkinRelation + '</option>';
                    }
                }
                $('#u_nextofkin_relation').html(nextofkinOptions);

                const Religions = ["Islam", "Hindu", "Chiristian", "Sikh"];
                let religionOptions = '<option selected>' + Religion + '</option>';
                for (let religion of Religions) {
                    if (religion !== Religion) {
                        religionOptions += '<option>' + religion + '</option>';
                    }
                }
                $('#u_emp_religion').html(religionOptions);

                // $('#u_emp_marital_status').html("<option selected>" + MaritalStatus + "</option>");

                const MaritalStatuses = ["Single", "Married", "Divorced", "Widowed"];
                let maritalStatusOptions = '<option selected>' + MaritalStatus + '</option>';
                for (let maritalStatus of MaritalStatuses) {
                    if (maritalStatus !== MaritalStatus) {
                        maritalStatusOptions += '<option>' + maritalStatus + '</option>';
                    }
                }
                $('#u_emp_marital_status').html(maritalStatusOptions);

                $('#u_emp_gender').html("<option selected value='"+response.GenderID+"'>" + gender + "</option>");
                $('#u_emp_org').html("<option selected value='"+response.OrganizationID+"'>" + org + "</option>");
                $('#u_emp_site').html("<option selected value='"+response.SiteID+"'>" + site + "</option>");
                $('#u_emp_cc').html("<option selected value='"+response.CostCenterID+"'>" + cc + "</option>");
                $('#u_emp_cadre').html("<option selected value='"+response.CadreID+"'>" + cadre + "</option>");
                $('#u_emp_position').html("<option selected value='"+response.PositionID+"'>" + position + "</option>");
                $('#u_emp_reportto').html("<option selected value='"+response.GenderID+"'>" + gender + "</option>");
                $('#u_qualification').html("<option selected value='"+response.QualificationID+"'>" + Qualification + "</option>");
                $('#u_emp_status').html("<option selected value='"+response.EmpStatusID+"'>" + empStatus + "</option>");
                $('#u_working_status').html("<option selected data-jobstatus='"+response.jobContinue+"' value='"+response.WorkingStatusID+"'>" + workingStatus + "</option>");
                $('#u_emp_province').html("<option selected value='"+response.ProvinceID+"'>" + province + "</option>");
                $('#u_emp_division').html("<option selected value='"+response.DivisionID+"'>" + division + "</option>");
                $('#u_emp_district').html("<option selected value='"+response.DistrictID+"'>" + district + "</option>");
                $('#u_emp_reportto').html("<option selected value='"+response.ManagerID+"'>" + Manager + "</option>");
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.u_emp_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                if(response.jobContinue == 0)
                {
                    $('#u_emp_dol').attr('required', true);
                    var formattedDOL = moment(dol).format('YYYY-MM-DD');
                    $('#u_emp_dol').each(function() {
                        var dolElement = $(this);
                        dolElement.val(formattedDOL);
                    });
                    $('#date_of_leaving').show();
                }
                else{
                    $('#u_emp_dol').attr('required', false);
                    $('#date_of_leaving').hide();
                }

                var formattedDOJ = moment(doj).format('YYYY-MM-DD');
                $('#u_emp_doj').each(function() {
                    var dojElement = $(this);
                    dojElement.val(formattedDOJ);
                });

                var formattedcnicExpiry = moment(cnicExpiry).format('YYYY-MM-DD');
                $('#u_cnic_expiry').each(function() {
                    var cnicExpiryElement = $(this);
                    cnicExpiryElement.val(formattedcnicExpiry);
                });

                var formattedDOB = moment(dob).format('YYYY-MM-DD');
                $('#u_emp_dob').each(function() {
                    var dobElement = $(this);
                    dobElement.val(formattedDOB);
                });

                fetchOrganizations(response.OrganizationID,org,'#u_emp_org', function(data) {
                    $('#u_emp_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_emp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $.ajax({
                    url: 'hr/getgender',
                    type: 'GET',
                    data: {
                        genderID: response.GenderID,
                    },
                    beforeSend: function() {
                        $('#u_emp_gender').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_emp_gender').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#u_emp_gender').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                fetchEmployeeCadre(response.OrganizationID, '.u_emp_cadre', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(value.id != response.CadreID )
                            {
                                $('.u_emp_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                });

                fetchEmployeePosition(response.OrganizationID, '.u_emp_position', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(value.id != response.PositionID )
                            {
                                $('.u_emp_position').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                });

                fetchQualificationLevel(response.QualificationID,'.u_qualification', function(data) {
                   $.each(data, function(key, value) {
                       $('.u_qualification').append('<option value="' + value.id + '">' + value.name + '</option>');
                   });
                });

                $.ajax({
                    url: 'hr/getempstatus',
                    type: 'GET',
                    data: {
                        empstatusid: response.EmpStatusID,
                    },
                    beforeSend: function() {
                        $('.u_emp_status').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('.u_emp_status').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('.u_emp_status').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $.ajax({
                    url: 'hr/getworkingstatus',
                    type: 'GET',
                    data: {
                        workingstatusid: response.WorkingStatusID,
                    },
                    beforeSend: function() {
                        $('.u_working_status').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('.u_working_status').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('.u_working_status').append('<option data-jobstatus="' + value.job_continue + '" value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#u_working_status').off('change').on('change', function() {
                    var jobStatus = $(this).find('option:selected').data('jobstatus');
                    if(jobStatus == 0)
                    {
                        $('#u_emp_dol').attr('required', true);
                        $('#date_of_leaving').show();
                        var currentDate = new Date();
                        $('#u_emp_dol').bootstrapMaterialDatePicker('setDate', currentDate);
                    }
                    else{
                        $('#u_emp_dol').attr('required', false);
                        $('#u_emp_dol').val('');
                        $('#date_of_leaving').hide();
                    }
                });

                fetchEmployees(response.ManagerID, '#u_emp_reportto', function(data) {
                    $.each(data, function(key, value) {
                        $('#u_emp_reportto').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });

                    if(Manager != 'N/A')
                    {
                        $('#u_emp_reportto').append('<option value="0">N/A</option>');
                    }

                }, function(error) {
                    console.log(error);
                });

                if (response.OrganizationID) {
                    fetchSites(response.OrganizationID, '#u_emp_site', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-employee').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    },response.SiteID);

                    $('#u_emp_org').off('change').on('change', function() {
                        $('#u_emp_site').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_emp_site', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');
                                    }
                                });
                            }

                        }, function(error) {
                            console.log(error);
                        });
                    });

                    fetchActivatedCostCenters(response.SiteID, '#u_emp_cc', function(data) {
                        $.each(data, function(key, value) {
                            $('#u_emp_cc').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }, function(error) {
                        console.log(error);
                    },response.CostCenterID);

                    $('#u_emp_site').off('change').on('change', function() {
                        $('#u_emp_cc').empty();
                        var siteId = $(this).val();
                        fetchActivatedCostCenters(siteId, '#u_emp_cc', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_emp_cc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else{
                                Swal.fire({
                                    text: 'Cost Centers are not Activated for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');

                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });

                    });

                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: response.ProvinceID,
                        },
                        beforeSend: function() {
                            $('#u_emp_province').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_province').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            provinceId: response.ProvinceID,
                            divisionId: response.DivisionID,
                        },
                        beforeSend: function() {
                            $('#u_emp_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_division').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            districtId: response.DistrictID,
                        },
                        beforeSend: function() {
                            $('#u_emp_district').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_district').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    // $('#u_emp_province').change(function() {
                    $('#u_emp_province').off('change.uEmpProvince').on('change.uEmpProvince', function(){
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                            },
                            beforeSend: function() {
                                $('#u_emp_division').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#u_emp_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_emp_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });

                    // $('#u_emp_division').change(function() {
                    $('#u_emp_division').off('change.uEmpDivision').on('change.uEmpDivision', function(){
                        var divisionid = $(this).val();
                        $.ajax({
                            url: 'territory/updatedistrict',
                            type: 'GET',
                            data: {
                                divisionId: divisionid,
                            },
                            beforeSend: function() {
                                $('#u_emp_district').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                $('#u_emp_district').html("<option selected disabled value=''>Select District</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_emp_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });

                }

                $('#edit-employee').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Employee Modal

    //Update Employee
    $('#edit_employee').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#edit_employee')[0]);
        var gender = $('#u_emp_gender').val();
        var org = $('#u_emp_org').val();
        if(org)
        {
            formData.append('u_emp_org', org);
        }
      
        var site = $('#u_emp_site').val();
        var costcenter = $('#u_emp_cc').val();
        var cadre = $('#u_emp_cadre').val();
        var position = $('#u_emp_position').val();
        var manager = $('#u_emp_reportto').val();
        var qualificationLevel = $('#u_qualification').val();
        var empStatus = $('#u_emp_status').val();
        var workingStatus = $('#u_working_status').val();
        var province = $('#u_emp_province').val();
        var division = $('#u_emp_division').val();
        var district = $('#u_emp_district').val();
        var empId = $('.u-emp-id').val();

        formData.append('u_emp_gender', gender);
       
        formData.append('u_emp_site', site);
        formData.append('u_emp_cc', costcenter);
        formData.append('u_emp_cadre', cadre);
        formData.append('u_emp_position', position);
        formData.append('u_emp_reportto', manager);
        formData.append('u_qualification', qualificationLevel);
        formData.append('u_emp_status', empStatus);
        formData.append('u_working_status', workingStatus);
        formData.append('u_emp_province', province);
        formData.append('u_emp_division', division);
        formData.append('u_emp_district', district);

        var empImg = $('#u_empImg')[0].files[0];
        if(empImg != 'undefined')
        {
            formData.append('u_empImg', empImg);
        }
        var url = '/edit-employee/' + empId;
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
                else if (fieldName == 'info')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.u_emp_email').val('');
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    Swal.fire({
                        html: fieldErrors,
                        icon: fieldName,
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-employee').modal('hide');
                            $('#view-employee').DataTable().ajax.reload(); // Refresh DataTable
                            $('#edit_employee')[0].reset();
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
    //Update Employee
});