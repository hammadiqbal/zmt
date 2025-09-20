
// Vital Signs
$(document).ready(function() {
    var typingTimer;
    var doneTypingInterval = 200;
    var minLength = 7;
    var viewVitalSign = null;
    $('.vs_history,.patientArrivedvs').hide();

    $('#vs_mr').change(function() {
        clearTimeout(typingTimer);
        const selectedMr = $(this).val();
        if (selectedMr && selectedMr.length >= minLength) { // Assuming a valid MR number has at least 9 characters
            typingTimer = setTimeout(FetchVitalSignRecord, doneTypingInterval);

        } else {
            $('.vs_history').hide();
        }
    });

    // Function to update field labels based on patient age
    function updateFieldLabelsBasedOnAge() {
        var patientAge = parseFloat($('.vs_age').val()) || 0;
        var isUnder16 = patientAge < 16;
        
        // Update SBP label
        var sbpLabel = $('input[name="vs_sbp"]').closest('.col-md-2').find('.main_label');
        if (isUnder16) {
            if (!sbpLabel.find('small').length) {
                sbpLabel.append(' <small class="text-danger" style="font-size:11px;">(Optional)</small>');
            }
        } else {
            sbpLabel.find('small').remove();
        }
        
        // Update DBP label
        var dbpLabel = $('input[name="vs_dbp"]').closest('.col-md-2').find('.main_label');
        if (isUnder16) {
            if (!dbpLabel.find('small').length) {
                dbpLabel.append(' <small class="text-danger" style="font-size:11px;">(Optional)</small>');
            }
        } else {
            dbpLabel.find('small').remove();
        }
        
        // Update Pain Score label
        var scoreLabel = $('input[name="vs_score"]').closest('.col-md-3').find('.main_label');
        if (isUnder16) {
            if (!scoreLabel.find('small').length) {
                scoreLabel.append(' <small class="text-danger" style="font-size:11px;">(Optional)</small>');
            }
        } else {
            scoreLabel.find('small').remove();
        }
    }

    // Call the function when age field changes
    $(document).on('change', '.vs_age', function() {
        updateFieldLabelsBasedOnAge();
    });

    
    function FetchVitalSignRecord(selectedService) {
        $('#ajax-loader').show();
        var mrNumber = $("#vs_mr").val();
        if (mrNumber === "") {
            $('#vs_mr').addClass('requirefield');
            $('#ajax-loader').hide();
        }
        else
        {
            $('#vs_mr').find('.requirefield').removeClass('requirefield');
            var url = 'medicalrecord/patient-record/' + mrNumber;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                data: { serviceId: selectedService }, 
                success: function(response) {
                    if (response.info && response.info == '200') {
                        $('#ajax-loader').hide();
                        let modalContent = "";
                        response.services.forEach(service => {
                            modalContent += `<button class="service-btn btn btn-primary btn-block mb-2 text-left" data-serviceid="${service.serviceId}">
                                ${service.serviceName} 
                            </button>`;
                        });
        
                        $('#service-modal .modal-body').html(modalContent);
                        $('#service-modal').modal('show');
        
                        $('.service-btn').click(function () {
                            let selectedServiceId = $(this).attr('data-serviceid');
                            FetchVitalSignRecord(selectedServiceId); 
                            $('#service-modal').modal('hide');
                        });
                    }
                    else{
                            if(response.error && response.error == '404')
                            {
                          
                                $('.patientArrivedvs').hide();
                                Swal.fire({
                                    text: 'Invald MR #',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#vital_details').hide();
                                        $('.vs_history').hide();
                                        // $('#vital_details').removeClass('d-flex flex-column justify-content-end align-items-end');
                                        $('#vs_pname').val('');
                                        $('#vs_gender').val('');
                                        $('.vs_age').val('');
                                        $('#vs_emp').val('');
                                        $('#vs_bcc').val('');
                                        $('#vs_pcc').val('');
                                        $('.sevice_id').val('');
                                        $('.billingcc_id').val('');
                                        $('.servicemode_id').val('');
                                        $('.patientmr').val(mrNumber);
                                          //     $('.billingcc_id').val(response.billingCCId);
                                        // $('.servicemode_id').val(response.serviceModeId);
                                        // $('.sevice_id').val(response.serviceId);
                                        // $('.ep_age').val(response.patientDOB);
                                    }
                                });
                            }
                            else if (!response.patientInOutStatus)
                            {
                                $('.patientArrivedvs').hide();
                                $('#vs_pname').val(response.patientName);
                                $('#vs_gender').val(response.genderName);
                                $('.vs_age').val(response.patientDOB);
                                
                                // Update field labels based on patient age
                                updateFieldLabelsBasedOnAge();
                                
                                if (viewVitalSign) {
                                    viewVitalSign.destroy();
                                }
                                $('.vs_history').show();
                                fetchVitalSignData(mrNumber) 

                                fetchAllergyHistory(mrNumber);
                                fetchImmunizationHistory(mrNumber);
                                fetchDrugHistory(mrNumber);
                                fetchPastHistory(mrNumber);
                                fetchObstericHistory(mrNumber);
                                fetchSocialHistory(mrNumber);
                            }
                            else{
                                var siteName = response.siteName;
                                var serviceMode = response.serviceMode;
                                var serviceName = response.serviceName;
                                var serviceType = response.serviceType;
                                var serviceGroup = response.serviceGroup;
                                 if((response.performingCostCenters.length) == 0)
                                {
                                    $('#ajax-loader').hide();
                                    $('#vital_details,.patientArrivedvs').hide();
                                    Swal.fire({
                                        title: 'No Performing Cost Centers Assigned',
                                        text: 'No performing cost centers are assigned to you yet. Please contact your administrator.',
                                        icon: 'warning',
                                        confirmButtonText: 'OK'
                                    });
                                    return;
                                }

                                $('.patientArrivedvs').show();
                                $('#vital_details').show();
                                $('#vital_details').addClass('d-flex flex-column justify-content-end align-items-end');
                                $('#vital_site').html('<b>Site</b> : ' + siteName);
                                $('#vital_smt').html('<b>Service Mode & Type</b> : ' + serviceMode + ' & ' + serviceType);
                                $('#vital_sgb').html('<b>Service Group & Booking</b> : ' + serviceGroup + ' & Booked');
                                $('#vital_service').html('<b>Service</b> : ' + serviceName);
                                $('.sevice_id').val(response.serviceId);
                                $('.billingcc_id').val(response.billingCCId);
                                $('.servicemode_id').val(response.serviceModeId);
                                $('.patientmr').val(mrNumber);
                                $('#vs_pname').val(response.patientName);
                                $('#vs_gender').val(response.genderName);
                                $('.vs_age').val(response.patientDOB);
                                $('#vs_emp').val(response.empName);
                                $('#vs_bcc').val(response.billingCCName);
                                $('#vs_pcc').val(response.performingCostCenters[0].name);
        
                                // Update field labels based on patient age
                                updateFieldLabelsBasedOnAge();
        
                                if (viewVitalSign) {
                                    viewVitalSign.destroy();
                                }
                                $('.vs_history').show();
                                fetchVitalSignData(mrNumber) 

                                fetchAllergyHistory(mrNumber);
                                fetchImmunizationHistory(mrNumber);
                                fetchDrugHistory(mrNumber);
                                fetchPastHistory(mrNumber);
                                fetchObstericHistory(mrNumber);
                                fetchSocialHistory(mrNumber);
                            }
                    }
                    
                    $('#ajax-loader').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });

        }
    }

    //Add Vital Sign
    $('#add_vitalsign').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        
        // Get patient age to determine validation rules
        var patientAge = parseFloat($('.vs_age').val()) || 0;
        console.log(patientAge);
        var isUnder16 = patientAge < 16;
        
        // Define fields that are optional for patients under 16
        var optionalFieldsForUnder16 = ['vs_sbp', 'vs_dbp', 'vs_score'];
        
        $(data).each(function(i, field){
            var isOptionalField = isUnder16 && optionalFieldsForUnder16.includes(field.name);
            
            // Debug logging for each field
            console.log('Field:', field.name, 'Value:', field.value, 'IsOptional:', isOptionalField);
            
            // Skip validation for nursing notes and optional fields for under 16
            if (((field.value == '') || (field.value == null)) && (field.name != 'vs_nursingnotes') && !isOptionalField)
            {
                console.log('Validation Error for field:', field.name);

                var FieldName = field.name;
                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                $( 'input[name= "' +FieldName +'"' ).focus(function() {
                    $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
                    $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                });
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });
        console.log(resp);

        if(resp != false)
        {
            $.ajax({
                url: "/medicalrecord/addvitalsign",
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
                    let successMessageShown = false;
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
                        if (!successMessageShown) {
                            Swal.fire({
                                text: fieldErrors,
                                icon: fieldName,
                                allowOutsideClick: false,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#view-vitalsign').DataTable().ajax.reload();
                                    // $('#add_vitalsign')[0].reset();
                                    $('#add_vitalsign').find('input[type=text], input[type=number], textarea').not('#vs_mr, #vs_pname, #vs_gender, .vs_age, #vs_bcc, #vs_emp, #vs_pcc, #vs_user').val('');

                                    $('.text-danger').hide();
                                }
                            });
                    
                            successMessageShown = true; 
                        }
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
    //Add Vital Sign

    // Update Vital Sign Status
    $(document).on('click', '.vs_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/medicalrecord/vitalsign-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-vitalsign').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Vital Sign Status

    //Update Vital Sign Modal
    $(document).on('click', '.edit-vs', function() {
        var vsId = $(this).data('vs-id');
        var url = '/medicalrecord/updatevitalsign/' + vsId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#uvs_edt').val(formattedDateTime);
                $('#uvs-id').val(response.id);
                $('#uvs_sbp').val(response.SBP);
                $('#uvs_dbp').val(response.DBP);
                $('#uvs_pulse').val(response.Pulse);
                $('#uvs_temp').val(response.Temp);
                $('#uvs_rrate').val(response.RespiratoryRate);
                $('#uvs_weight').val(response.Weight);
                $('#uvs_height').val(response.Height);
                $('#uvs_score').val(response.Score);
                $('#uvs_o2saturation').val(response.o2_Saturation);
                $('#uvs_nursingnotes').val(response.Details);
                $('#edit-vitalsign').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Vital Sign Modal
    
    //Update Vital Sign
    $('#update_vitalsign').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var vsId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'uvs-id') {
                vsId = formData[i].value;
                break;
            }
        }
        var url = '/medicalrecord/update-vitalsign/' + vsId;
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
                            $('#edit-vitalsign').modal('hide');
                            $('#view-vitalsign').DataTable().ajax.reload();
                            $('#update_vitalsign')[0].reset();
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
    //Update Vital Sign


    // Open Allergies Diagnosis History
    $(document).on('click', '.add_allergieshistory', function() {
        $('#al_sincedate').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });

        $('#add-allergieshistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Allergies Diagnosis History

    //Add Allergies History
    $('#add_allergieshistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/addallergieshistory",
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
                let successMessageShown = false;
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
                            $('#add_allergieshistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-al').DataTable().ajax.reload();
                                $('#add_allergieshistory')[0].reset();
                                $('#add-allergieshistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Allergies History

    // Open Immunization Diagnosis History
    $(document).on('click', '.add_immunizationhistory', function() {
        $('#ih_date').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });

        $('#add-immunizationhistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Immunization History

    //Add Immunization History
    $('#add_immunizationhistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/addimmunizationhistory",
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
                let successMessageShown = false;
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
                            $('#add_immunizationhistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-ih').DataTable().ajax.reload();
                                $('#add_immunizationhistory')[0].reset();
                                $('#add-immunizationhistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Immunization History

    // Open Drug  History
    $(document).on('click', '.add_drughistory', function() {
        $('#add-drughistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Drug History

    //Add Drug History
    $('#add_drughistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/adddrughistory",
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
                let successMessageShown = false;
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
                            $('#add_drughistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-dh').DataTable().ajax.reload();
                                $('#add_drughistory')[0].reset();
                                $('#add-drughistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Drug History

    // Open Past  History
    $(document).on('click', '.add_pasthistory', function() {
        $('#ph_date').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });

        $('#add-pasthistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Past History

    //Add Past History
    $('#add_pasthistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/addpasthistory",
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
                let successMessageShown = false;
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
                            $('#add_pasthistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-ph').DataTable().ajax.reload();
                                $('#add_pasthistory')[0].reset();
                                $('#add-pasthistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Past History

    // Open Obsteric  History
    $(document).on('click', '.add_obsterichistory', function() {
        $('#oh_date').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });

        $('#add-obsterichistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Obsteric History

    //Add Obsteric History
    $('#add_obsterichistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/addobsterichistory",
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
                let successMessageShown = false;
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
                            $('#add_obsterichistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-oh').DataTable().ajax.reload();
                                $('#add_obsterichistory')[0].reset();
                                $('#add-obsterichistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Obsteric History

    // Open Social  History
    $(document).on('click', '.add_socialhistory', function() {
        $('#sh_date').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });
        $('#add-socialhistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Social History

    //Add Social History
    $('#add_socialhistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/addsocialhistory",
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
                let successMessageShown = false;
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
                            $('#add_socialhistory')[0].reset();
                        }
                    });
                }
                else if (fieldName == 'success')
                {
                    if (!successMessageShown) {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#view-sh').DataTable().ajax.reload();
                                $('#add_socialhistory')[0].reset();
                                $('#add-socialhistory').modal('hide');
                                $('.text-danger').hide();
                            }
                        });

                        successMessageShown = true;
                    }
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
    });
    //Add Social History

    
});
// Vital Signs