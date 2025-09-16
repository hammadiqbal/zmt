//Patient Registration
$(document).ready(function() {
    //Open Patient Registration Setup
    $(document).on('click', '.add-patient', function() {
        var orgId = $('#patient_org').val();
        $('#patient_province').trigger('change');
        $('#age_display').hide();
        $('.text-danger').show();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#patient_site', function(data) {
                $('#patient_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#patient_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{

            $('#patient_org').empty();
            $('#patient_org').select2();
            fetchOrganizations(null,null,'#patient_org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#patient_org').html(options.join('')).trigger('change');
            });
            $('#patient_site').empty();
            $('#patient_site').select2();
            $('#patient_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#patient_org', '#patient_site', '#add_servicelocation');
        }
        $('#add-patient').modal('show');
    });
    //Open Patient Registration Setup

    $('#patient_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#patient_org', '#patient_site', '#add_patient');
    // $('#patient_division').html("<option selected disabled value=''>Select Division</option>").prop('disabled', true);
    // ProvinceChangeDivision('#patient_province', '#patient_division', '#add_patient');
    ProvinceChangeDivision('#patient_province', '#patient_division', '#add_patient', 'Karachi');

    $('#patient_district').html("<option selected disabled value=''>Select District</option>").prop('disabled', true);
    DivisionChangeDistrict('#patient_division', '#patient_district', '#add_patient');
    
    // Age validation helper
    $('#patient_age').on('input', function() {
        var inputValue = $(this).val();
        var ageDisplay = $('#age_display');
        
        // Clear any previous error styling
        $(this).removeClass('requirefield');
        
        if (inputValue === '') {
            ageDisplay.hide();
            return;
        }
        // Custom age parsing for your specific format
        var age = parseCustomAge(inputValue);
        
        if (age === null) {
            ageDisplay.text('Invalid age format').show();
            $(this).addClass('requirefield');
            return;
        }
        
        if (age < 0) {
            ageDisplay.text('Age cannot be negative').show();
            $(this).addClass('requirefield');
        } else if (age > 140) {
            ageDisplay.text('Age cannot exceed 140 years').show();
            $(this).addClass('requirefield');
        } else if (age > 0 && age < 1) {
            var months = Math.floor(age * 12);
            ageDisplay.text('(' + months + ' months)').show();
        } else if (age >= 1) {
            var years = Math.floor(age);
            var remainingMonths = Math.round((age - years) * 12);
            if (remainingMonths > 0) {
                ageDisplay.text('(' + years + ' years ' + remainingMonths + ' months)').show();
            } else {
                ageDisplay.text('(' + years + ' years)').show();
            }
        } else {
            ageDisplay.hide();
        }
    });
    
    // Custom age parsing function
    function parseCustomAge(inputValue) {
        // Remove any spaces
        inputValue = inputValue.trim();
        
        // Handle your specific format: 0.1-0.12, then 1, then 1.1-1.12, then 2, etc.
        if (inputValue.match(/^0\.(1[0-2]|[1-9])$/)) {
            // Format: 0.1 to 0.12 (only these specific values)
            // Convert to actual age: 0.1=1month, 0.2=2months, 0.10=10months, 0.11=11months, 0.12=12months
            var decimalPart = inputValue.substring(2);
            var months = parseInt(decimalPart);
            return months / 12; // Convert months to years for calculation
        } else if (inputValue.match(/^[1-9]\d*\.(1[0-2]|[1-9])$/)) {
            // Format: 1.1 to 1.12, 2.1 to 2.12, etc.
            var parts = inputValue.split('.');
            var years = parseInt(parts[0]);
            var months = parseInt(parts[1]);
            return years + (months / 12);
        } else if (inputValue.match(/^[1-9]\d*$/)) {
            // Format: 1, 2, 3, etc. (whole years)
            return parseInt(inputValue);
        }
        
        return null; // Invalid format - reject anything else
    }
    
    // Add Patient
    $('#add_patient').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_patient')[0]);
        var relation = $('#relation').val();
        var GuardianRelation = $('#guardian_relation').val();
        var religion = $('#religion').val();
        var maritalStatus = $('#marital_status').val();
        var Gender = $('#patient_gender').val();
        var Org = $('#patient_org').val();
        var Site = $('#patient_site').val();
        var Province = $('#patient_province').val();
        var Division = $('#patient_division').val();
        var District = $('#patient_district').val();
        var Language = $('#language').val();

        var imgValue = $('#patient_img')[0].files[0];
        formData.append('relation', relation);
        formData.append('guardian_relation', GuardianRelation);
        formData.append('religion', religion);
        formData.append('language', Language);
        formData.append('marital_status', maritalStatus);
        formData.append('patient_gender', Gender);
        formData.append('patient_org', Org);
        formData.append('patient_site', Site);
        formData.append('patient_province', Province);
        formData.append('patient_division', Division);
        formData.append('patient_district', District);
        formData.append('patient_img', imgValue);

        var resp = true;
        const excludedFields = ['old_mrcode', 'familyno', 'next_of_kin', 'relation', 'patient_cnic', 'patient_additionalcell', 'patient_landline', 'patient_email', 'patient_img', 'patient_houseno', 'patient_cell'];
        var firstErrorElement = null;

        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if (((fieldValue == '') || (fieldValue == 'null') || (fieldValue === 'undefined')) && !excludedFields.includes(fieldName))
            {
                var FieldName = fieldName;
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
                $( 'input[name="' + FieldName + '"][type="file"]').focus(function() {
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
        // If we found an error element, focus on it
        if (firstErrorElement) {
            $('input[name="' + firstErrorElement + '"], textarea[name="' + firstErrorElement + '"], select[name="' + firstErrorElement + '"]').focus().addClass('requirefield');
        }
        if(resp != false)
        {
            $.ajax({
                url: "/patient/addpatient",
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
                    if (response.error)
                    {
                        Swal.fire({
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        })
                    }
                    if (response.success)
                    {
                        Swal.fire({
                            text: response.success,
                            icon: 'success',
                            allowOutsideClick: false,
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Register Only',
                            denyButtonText: 'Register & Confirm Arrival',
                            cancelButtonText: 'Register & Book Appointment'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-patient').modal('hide');
                                $('#view-patient').DataTable().ajax.reload(); // Refresh DataTable
                                $('#add_patient').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patient')[0].reset();
                                $('.text-danger').hide();
                            }
                            else if (result.isDenied) {
                                $('#add-patient').modal('hide');
                                
                                $('#pio_org').html("<option selected value='"+ response.org_id +"'>"+ response.orgName +"</option>").prop('disabled',true);
                                $('#pio_site').html("<option selected value='"+ response.site_id +"'>"+ response.siteName +"</option>").prop('disabled',true);
                                // fetchServiceLocations(response.org_id, '#pio_serviceLocation', function(data) {
                                //     const $serviceLocation = $('#pio_serviceLocation');
                                //     if (data && data.length > 0) {
                                //         $serviceLocation.empty()
                                //              .append('<option selected disabled value="">Select Service Location</option>')
                                //              .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                                //              .prop('disabled', false)
                                //              .find('option:contains("Loading...")').remove();
                                //     } else {
                                //         Swal.fire({
                                //             text: 'Service Locations are not availables for selected Organization',
                                //             icon: 'error',
                                //             confirmButtonText: 'OK'
                                //         }).then((result) => {
                                //             if (result.isConfirmed) {
                                //                 $serviceLocation.empty()
                                //                 .append('<option selected disabled value="">Select Service Location</option>')
                                //                 .prop('disabled', true);
                                //             }
                                //         });

                                //     }
                                // });

                                fetchActiveSL(response.site_id, '#pio_serviceLocation', false, false, function(data) {
                                    const $serviceLocation = $('#pio_serviceLocation');
                                    if (data && data.length > 0) {
                                        $serviceLocation
                                        // $serviceLocation.empty()
                                            .append('<option selected disabled value="">Select Service Location</option>')
                                            .append(data.map(({location_id, name}) => `<option value="${location_id}">${name}</option>`).join(''))
                                            .prop('disabled', false)
                                            .find('option:contains("Loading...")').remove();
                                    } else {
                                        Swal.fire({
                                            text: 'Service Locations are not available for selected Organization',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $serviceLocation.empty()
                                                .append('<option selected disabled value="">No Locations Found</option>')
                                                .prop('disabled', true)
                                            }
                                        });
                                    }
                                });
                                fetchPhysicians(response.site_id, '#pio_emp', function(data) {
                                    const $emp = $('#pio_emp');
                                    if (data && data.length > 0) {
                                        $emp.empty()
                                            .append('<option selected disabled value="">Select Physician</option>')
                                            .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                                            .prop('disabled', false)
                                            .find('option:contains("Loading...")').remove();
                                            $emp.trigger('change');
                                    } else {
                                            Swal.fire({
                                                text: 'There are no physicians available for this location.',
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                allowOutsideClick: false
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $emp.off('change');
                                                }
                                            });
                                    }
                                });
                                // SiteChangeService('#pio_site', '#pio_service', '#add_patientinout');
                                fetchSiteServices(response.site_id, '#pio_service', function(data) {
                                    if (data && data.length > 0) {
                                        const $Service = $('#pio_service');
                                        $Service.empty()
                                            .append('<option selected disabled value="">Select Service</option>')
                                            .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                                            .prop('disabled', false)
                                            .find('option:contains("Loading...")').remove();
                                            $Service.trigger('change');
                                    } else {
                                            Swal.fire({
                                                text: 'Services are not Activated for selected Site',
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                allowOutsideClick: false
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $Service.off('change');
                                                    $Service.prop('disabled', true);
                                                }
                                            });
                                    }
                                });

                                $('#pio_serviceSchedule').html("<option selected disabled>Select Service Location Schedule</option>").prop('disabled',true);
                                LocationChangeServiceScheduling('#pio_serviceLocation', '#pio_site', '#pio_serviceSchedule', '#add_patientinout');

                                $('#pio_serviceMode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled',true);
                                ServiceChangeServiceModes('#pio_site', '#pio_service', '#pio_serviceMode', '#add_patientinout');
                                $('#pio_billingCC').html("<option selected disabled value=''>Select Billing Cost Center</option>").prop('disabled',true);
                                ServiceChangeCostCenter('#pio_site', '#pio_service', '#pio_billingCC', '#add_patientinout');
                                // $('#pio_serviceStart').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm A' });
                                $('#pio_serviceStart').bootstrapMaterialDatePicker({
                                    format: 'dddd DD MMMM YYYY - hh:mm A',
                                    currentDate: new Date() 
                                });
                                // $('input[name="booking_id"]').val('00');
                                openPatientInOutModal(response.mr_code);


                                // $('#add-patientinout').modal({
                                //     backdrop: 'static',
                                //     keyboard: false
                                // }).modal('show');
                            }
                            else {
                                $('#add-patient').modal('hide');
                                $('#pOrg').text(response.orgName);
                                $('#pSite').text(response.siteName);
                                $('#pMrno').text(response.mr_code);
                                $('.pb_org').val(response.org_id);
                                $('.pb_site').val(response.site_id);
                                $('.pb_mr').val(response.mr_code);
                                $('#sb_schedule').html("<option selected disabled value=''>Select Service Location Schedule</option>").prop('disabled', true);

                                fetchActiveSL(response.org_id, '#sb_location', false, false, function(data) {
                                    const $serviceLocation = $('#sb_location');
                                    if (data && data.length > 0) {
                                        $serviceLocation.empty()
                                            .append('<option selected disabled value="">Select Service Location</option>')
                                            .append(data.map(({location_id, name}) => `<option value="${location_id}">${name}</option>`).join(''))
                                            .prop('disabled', false)
                                            .find('option:contains("Loading...")').remove();
                                    } else {
                                        Swal.fire({
                                            text: 'Service Locations are not available for selected Organization',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                });

                                // fetchServiceLocations(response.org_id, '#sb_location', function(data) {
                                //     const $serviceLocation = $('#sb_location');
                                //     if (data && data.length > 0) {
                                //         $serviceLocation.empty()
                                //             .append('<option selected disabled value="">Select Service Location</option>')
                                //             .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                                //             .prop('disabled', false)
                                //             .find('option:contains("Loading...")').remove();
                                //     } else {
                                //         Swal.fire({
                                //             text: 'Service Locations are not available for selected Organization',
                                //             icon: 'error',
                                //             confirmButtonText: 'OK'
                                //         }).then((result) => {
                                //             if (result.isConfirmed) {
                                //                 $('#add-servicebooking').modal('hide');
                                //                 $('#view-patient').DataTable().ajax.reload(); // Refresh DataTable
                                //                 $('#add_patient').find('select').each(function(){
                                //                     $(this).val($(this).find('option:first').val()).trigger('change');
                                //                 });
                                //                 $('#add_patient')[0].reset();
                                //                 $('.text-danger').hide();
                                //             }
                                //         });
                                //     }
                                // });
                                // Show Service Scheduling
                                LocationChangeServiceScheduling('#sb_location', '#sb_site', '#sb_schedule', '#add_servicebooking');
                                // Show Service Scheduling
                                fetchPhysicians(response.site_id, '#sb_emp', function(data) {
                                    $('#sb_emp').html("<option selected disabled value=''>Select Designated Physician</option>");
                                    $.each(data, function(key, value) {
                                        $('#sb_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    });
                                    $('#sb_emp').find('option:contains("Loading...")').remove();
                                    $('#sb_emp').prop('disabled', false);
                                }, function(error) {
                                    console.log(error);
                                });


                                fetchSiteServices(response.site_id, '#sb_service', function(data) {
                                    const $Service = $('#sb_service');
                                    $Service.empty()
                                    .append("<option selected disabled value=''>Select Service</option>")
                                    .append(
                                        data.map(({ id, name }) => {
                                            if (id != response.serviceID) {
                                                return `<option value="${id}">${name}</option>`;
                                            }
                                        }).join(''))
                                    .prop('disabled', false)
                                    .find('option:contains("Loading...")').remove();
                                });

                                $('#sb_serviceMode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
                                ServiceChangeServiceModes('#sb_site', '#sb_service', '#sb_serviceMode', '#add_servicebooking');
                                
                                $('#sb_billingCC').html("<option selected disabled value=''>Select Billing Cost Center</option>").prop('disabled', true);
                                ServiceChangeCostCenter('#sb_site', '#sb_service', '#sb_billingCC', '#add_servicebooking');

                                $('#add-servicebooking').css('overflow', 'auto').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                }).modal('show');
                                

                            }
                        });
                    }
                    if (response.info)
                    {
                        Swal.fire({
                            text: response.info,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var imgReset = $('#patient_img').dropify();
                                imgReset = imgReset.data('dropify');
                                imgReset.resetPreview();
                                imgReset.clearElement();
                                $('#add_patient').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patient')[0].reset();
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
    // Add Patient

    // View Patient
    var viewpatient =  $('#view-patient').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/patient/patientdata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'patient_detail', name: 'patient_detail' },
            { data: 'identity', name: 'identity' },
            { data: 'contact', name: 'contact' },
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
                width: "350px"
            },
            {
                targets: 5,
                width: "350px"
            }
        ]
    });

    viewpatient.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewpatient.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewpatient.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Patient

    // Patient Details
    $(document).on('click', '.patient-detail', function() {
        var patientId = $(this).data('patient-id');
        var url = '/patient/patientdetail/' + patientId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#patient-detail').modal('show');
                $('#ajax-loader').hide();
                var imgPath = response.Image;
                $('#patientImg').attr('src', imgPath);
                $('#patientName').text(response.patientName);
                $('#patientAddress').html(response.patientAddress);
                $('#mr_no').text(response.MR);
                $('#guardianName').text(response.patientGuardianName);
                $('#guardianRelation').text(response.patientGuardianRelation);
                $('#nextofKin').text(response.patientNextOfKin?.trim() ? response.patientNextOfKin : 'N/A');
                $('#nextofkinRelation').text(response.patientNextOfKinRelation?.trim() ? response.patientNextOfKinRelation : 'N/A');
                
                $('#patientLanguage').text(response.Language);
                $('#patientReligion').text(response.Religion);
                $('#patientMaritalStatus').text(response.MaritalStatus);
                $('#patientoldMR').text(response.oldMR);
                $('#patientGender').text(response.Gender);
                $('#patientDOB').text(response.DateOfBirth);
                $('#patientOrg').text(response.Org);
                $('#patientSite').text(response.Site);
                $('#patientProvince').text(response.Province);
                $('#patientDivision').text(response.Division);
                $('#patientDistrict').text(response.District);
                $('#patientCNIC').text(response.CNIC);
                $('#patientFamilyNo').text(response.familyNo);
                $('#patientCell').text(response.CellNo);
                $('#patientAdditionalCell').text(response.AdditionalCellNo);
                $('#patientLandline').text(response.Landline);
                $('#patientEmail').text(response.Email);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Patient Details

    // Update Patient Status
    $(document).on('click', '.patient_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/patient/patient-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-patient').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Patient Status

    //Update Patient Modal
    $(document).on('click', '.edit-patient', function() {
        $('.text-danger').show();
        var patientId = $(this).data('patient-id');
        var url = '/patient/updatepatient/' + patientId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#up-id').val(patientId);
                $('#up_name').val(response.patientName);
                $('#up_guardianName').val(response.guardianName);
                const guardianRelations = ["Father", "Husband"];
                let guardianRelationOptions = '<option selected>' + response.guardianRelation + '</option>';
                for (let guardianRelation of guardianRelations) {
                    if (guardianRelation !== response.guardianRelation) {
                        guardianRelationOptions += '<option>' + guardianRelation + '</option>';
                    }
                }
                $('#up_guardianRelation').html(guardianRelationOptions);
                $('#up_nextofkin').val(response.noxtofKin);

                const nextofkinRelations = [ "Father", "Mother", "Brother", "Sister", "GrandParent", "Spouse", "Child", "Grandparent", "Grandchild", "Uncle", "Aunt", "Niece", "Nephew", "Cousin", "Legal Guardian", "Friend", "Partner"];
                let nextofkinRelationOptions = '';
                if (!response.noxtofKinRelation || response.noxtofKinRelation.trim() === '') {
                    nextofkinRelationOptions = '<option selected disabled value="">Select Next of Kin Relation</option>';
                } else {
                    nextofkinRelationOptions = '<option selected>' + response.noxtofKinRelation + '</option>';
                }
                
                for (let nextofkinRelation of nextofkinRelations) {
                    if (nextofkinRelation !== response.noxtofKinRelation) {
                        nextofkinRelationOptions += '<option>' + nextofkinRelation + '</option>';
                    }
                }

                const languages = ["Urdu", "Sindhi", "Balochi", "Punjabi", "Pashto", "Hindko", "Siraiki", "Memoni", "Gujrati", "Brahui", "Shina", "Burushaski", "Wakhi", "Balti", "Kashmiri", "Khowar"];
                let languageOptions = '<option selected>' + response.language + '</option>';
                for (let language of languages) {
                    if (language !== response.language) {
                        languageOptions += '<option>' + language + '</option>';
                    }
                }
                $('#up_language').html(languageOptions);
                // $('#up_language').val(response.language);
                
                
                $('#up_nextofkinRelation').html(nextofkinRelationOptions);
                $('#up_oldmr').val(response.oldMRNo);

                $('#up_gender').html("<option selected value="+response.genderId+">" + response.genderName + "</option>");
                $.ajax({
                    url: 'hr/getgender',
                    type: 'GET',
                    data: {
                        genderID: response.genderId,
                    },
                    beforeSend: function() {
                        $('#up_gender').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#up_gender').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#up_gender').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });


                const Religions = ["Islam", "Hindu", "Christian", "Sikh"];
                let ReligionsOptions = '<option selected>' + response.Religion + '</option>';
                for (let Religion of Religions) {
                    if (Religion !== response.Religion) {
                        ReligionsOptions += '<option>' + Religion + '</option>';
                    }
                }
                $('#up_religion').html(ReligionsOptions);

                const MaritalStatuses = ["Single", "Married", "Divorced", "Widowed"];
                let MaritalStatusOptions = '<option selected>' + response.MaritalStatus + '</option>';
                for (let MaritalStatus of MaritalStatuses) {
                    if (MaritalStatus !== response.MaritalStatus) {
                        MaritalStatusOptions += '<option>' + MaritalStatus + '</option>';
                    }
                }
                $('#up_maritalStatus').html(MaritalStatusOptions);

                var formattedDOB = moment(response.DOB).format('YYYY-MM-DD');
                $('#up_dob').each(function() {
                    var dobElement = $(this);
                    dobElement.val(formattedDOB);
                });

                $('#up_org').html("<option selected value="+response.orgId+">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#up_org', function(data) {
                    $('#up_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#up_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#up_site').html("<option selected value="+response.siteId+">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#up_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#up_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Sites are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-patient').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);

                $('#up_org').off('change').on('change', function() {
                    $('#up_site').empty();
                    var organizationId = $(this).val();
                    fetchSites(organizationId, '#up_site', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#up_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-patient').modal('hide');
                                }
                            });
                        }

                    }, function(error) {
                        console.log(error);
                    });
                });

                $('#up_province').html("<option selected value="+response.provinceId+">" + response.provinceName + "</option>");
                $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: response.provinceId,
                        },
                        beforeSend: function() {
                            $('#up_province').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#up_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#up_province').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                });

                $('#up_division').html("<option selected value="+response.divisionId+">" + response.divisionName + "</option>");
                $.ajax({
                    url: 'territory/updatedivision',
                    type: 'GET',
                    data: {
                        provinceId: response.provinceId,
                        divisionId: response.divisionId,
                    },
                    beforeSend: function() {
                        $('#up_division').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#up_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#up_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
                $('#up_district').html("<option selected value="+response.districtId+">" + response.districtName + "</option>");
                $.ajax({
                    url: 'territory/updatedistrict',
                    type: 'GET',
                    data: {
                        districtId: response.districtId,
                    },
                    beforeSend: function() {
                        $('#up_district').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#up_district').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#up_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                // $('#up_province').change(function() {
                $('#up_province').off('change.upProvince').on('change.upProvince', function(){
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                            },
                            beforeSend: function() {
                                $('#up_division').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#up_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('#up_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                });

                // $('#up_division').change(function() {
                $('#up_division').off('change.upDivision').on('change.upDivision', function(){
                    var divisionid = $(this).val();
                    $.ajax({
                        url: 'territory/updatedistrict',
                        type: 'GET',
                        data: {
                            divisionId: divisionid,
                        },
                        beforeSend: function() {
                            $('#up_district').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                                $('#up_district').html("<option selected disabled value=''>Select District</option>");
                            $.each(resp, function(key, value) {
                                $('#up_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
                });

                $('#up_cnic').val(response.cnic);
                $('#up_familyno').val(response.familyNo);
                $('#up_cell').val(response.cellNo);
                $('#up_additionalCell').val(response.additionalCell);
                $('#up_landline').val(response.Landline);
                $('#up_email').val(response.Email);
                $('#up_houseno').val(response.HouseNo);
                $('#up_address').val(response.Address);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.up_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                var image = response.Image;
                var patient_img_input = $('#u_patientImg');
                if(image)
                {
                    var imgName = image.trim().substring(image.lastIndexOf('/') + 1);
                    var dropifyRenderImg = patient_img_input.closest('.dropify-wrapper').find('.dropify-render');

                    dropifyRenderImg.find('img').attr('src', image);

                    var imgdropifyInfos = patient_img_input.closest('.dropify-wrapper').find('.dropify-infos');
                    var imgfilenameInner = imgdropifyInfos.find('.dropify-filename-inner');
                    imgfilenameInner.text(imgName);

                    patient_img_input.attr('data-default-file', image);
                    patient_img_input.dropify('destroy');
                    patient_img_input.dropify();
                }
                else{
                    var dropifyRenderImg = patient_img_input.closest('.dropify-wrapper').find('.dropify-render');
                   
                    dropifyRenderImg.find('img').attr('src', '');

                    var imgdropifyInfos = patient_img_input.closest('.dropify-wrapper').find('.dropify-infos');
                    var imgfilenameInner = imgdropifyInfos.find('.dropify-filename-inner');
                    imgfilenameInner.text('Upload Your Image');

                    patient_img_input.attr('data-default-file', '');
                    patient_img_input.dropify('destroy');
                    patient_img_input.dropify();
                }

                $('#edit-patient').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Patient Modal

    //Update Patient Details
    $('#update_patient').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        var patientId = $('#up-id').val();
        var img = $('#u_patientImg')[0].files[0];
        formData.append('u_patientImg', img);
        var org = $('#up_org').val();
        if(org)
        {
            formData.append('up_org', org);
        }

        var patientId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'up-id') {
                patientId = formData[i].value;
                break;
            }
        }

        var url = 'patient/update-patient/' + patientId;
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
                            $('#edit-patient').modal('hide');
                            $('#view-patient').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_patient')[0].reset();
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
    //Update Patient Details

    //Add Patient Arrival & Departure
    $('#add_patientinout').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'pio_remarks') 
            // if (((field.value == '') || (field.value == null)))
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
                url: "/patient/addpatientarrival",
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
                    else if (fieldName == 'info')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#pio_location').empty();
                                $('#pio_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                $('#pio_site').empty();
                                $('#pio_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#add_patientinout').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patientinout')[0].reset();
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
                                // $('#add-patientinout').modal('hide');
                                // $('#sb_location').empty();
                                // $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                // $('#sb_site').empty();
                                // $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                // $('#view-patientinout').DataTable().ajax.reload();
                                // $('#add_patientinout').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
                                // $('#add_patientinout')[0].reset();
                                // $('.text-danger').hide();
                                const url = new URL(window.location);
                                url.search = ''; // Clear all query parameters
                                history.replaceState(null, '', url); // Update the URL without reloading
                                // const url = new URL(window.location);
                                // url.searchParams.delete('mr'); // Remove the 'mr' parameter
                                // history.replaceState(null, '', url); // Update the URL without reloading
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
    //Add Patient Arrival & Departure
    
    //Add Service Booking
    $('#add_servicebooking').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        const excludedFields = ['sb_remarks'];
        // if (((field.value == '') || (field.value == null)))

        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && !excludedFields.includes(field.name))
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
                url: "/services/addservicebooking",
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
                                // $('#add-servicebooking').modal('hide');
                                // $('#sb_location').empty();
                                // $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                // $('#sb_site').empty();
                                // $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                // $('#view-servicebooking').DataTable().ajax.reload();
                                // $('#add_servicebooking').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
                                // $('#add_servicebooking')[0].reset();
                                // $('.text-danger').hide();
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
                                $('#sb_location').empty();
                                $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                $('#sb_site').empty();
                                $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#add_servicebooking').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_servicebooking')[0].reset();
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
    //Add Service Booking
});
//Patient Registration