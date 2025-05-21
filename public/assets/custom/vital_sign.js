
// Vital Signs
$(document).ready(function() {
    var typingTimer;
    var doneTypingInterval = 200;
    var minLength = 7;
    var viewVitalSign = null;
    $('#vs_history').hide();

    $('#vs_mr').change(function() {
        clearTimeout(typingTimer);
        const selectedMr = $(this).val();
        if (selectedMr && selectedMr.length >= minLength) { // Assuming a valid MR number has at least 9 characters
            typingTimer = setTimeout(FetchVitalSignRecord, doneTypingInterval);

        } else {
            $('#vs_history').hide();
        }
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
                                        $('#vs_history').hide();
                                        // $('#vital_details').removeClass('d-flex flex-column justify-content-end align-items-end');
                                        $('#vs_pname').val('');
                                        $('#vs_gender').val('');
                                        $('#vs_age').val('');
                                        $('#vs_emp').val('');
                                        $('#vs_bcc').val('');
                                        $('#vs_pcc').val('');
                                        $('#sevice_id').val('');
                                        $('#billingcc_id').val('');
                                        $('#servicemode_id').val('');
                                    }
                                });
                            }
                            else if (!response.patientInOutStatus)
                            {
                                $('.patientArrivedvs').hide();
                                $('#vs_pname').val(response.patientName);
                                $('#vs_gender').val(response.genderName);
                                $('#vs_age').val(response.patientDOB);
                                if (viewVitalSign) {
                                    viewVitalSign.destroy();
                                }
                                $('#vs_history').show();
                                fetchVitalSignData(mrNumber) 
                            }
                            else{
                                $('.patientArrivedvs').show();
                                var siteName = response.siteName;
                                var serviceMode = response.serviceMode;
                                var serviceName = response.serviceName;
                                var serviceType = response.serviceType;
                                var serviceGroup = response.serviceGroup;
                                $('#vital_details').show();
                                $('#vital_details').addClass('d-flex flex-column justify-content-end align-items-end');
                                $('#vital_site').html('<b>Site</b> : ' + siteName);
                                $('#vital_smt').html('<b>Service Mode & Type</b> : ' + serviceMode + ' & ' + serviceType);
                                $('#vital_sgb').html('<b>Service Group & Booking</b> : ' + serviceGroup + ' & Booked');
                                $('#vital_service').html('<b>Service</b> : ' + serviceName);
                                $('#sevice_id').val(response.serviceId);
                                $('#billingcc_id').val(response.billingCCId);
                                $('#servicemode_id').val(response.serviceModeId);
                                $('#vs_pname').val(response.patientName);
                                $('#vs_gender').val(response.genderName);
                                $('#vs_age').val(response.patientDOB);
                                $('#vs_emp').val(response.empName);
                                $('#vs_bcc').val(response.billingCCName);
                                $('#vs_pcc').val(response.performingCCName);
        
                                if (viewVitalSign) {
                                    viewVitalSign.destroy();
                                }
                                $('#vs_history').show();
                                fetchVitalSignData(mrNumber) 
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
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'vs_nursingnotes') 
            && (field.name != 'billingcc_id') && (field.name != 'servicemode_id') && (field.name != 'sevice_id'))
            {
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
                                    $('#add_vitalsign').find('input[type=text], input[type=number], textarea').not('#vs_mr, #vs_pname, #vs_gender, #vs_age, #vs_bcc, #vs_emp, #vs_pcc').val('');

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
    
});
// Vital Signs