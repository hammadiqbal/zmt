
//Encounters And Procedures
$(document).ready(function() {
    var typingTimer;
    var doneTypingInterval = 200;
    var minLength = 7;

    $('#ep_details, #ep_table, #ep_history').hide();

    $('#ep_mr').change(function() {('#ep_details').addClas
        $('.addep').hide();
        clearTimeout(typingTimer);
        const selectedMr = $(this).val();
        if (selectedMr && selectedMr.length >= minLength) {
            typingTimer = setTimeout(function () {
                FetchEncounterProcedureRecord();
                $(document).off('click', '.add_complain').on('click', '.add_complain', function () {
                    var serviceId = $(this).attr('data-serviceid');
                    currentPage = 1;
                    searchQuery = '';
                    $('#icd-search').val('');
                    fetchSymptomsICDCodes(serviceId);
                    $('#add-complain').modal('show');
                });

                $('#icd-search').off('keyup').on('keyup', function () {
                    var serviceId = $(this).attr('data-serviceid');

                    searchQuery = $(this).val();
                    currentPage = 1;

                    fetchSymptomsICDCodes(serviceId,currentPage, searchQuery);
                });

                $(document).off('click', '.load-more').on('click', '.load-more', function () {
                    var serviceId = $(this).attr('data-serviceid');
                    fetchSymptomsICDCodes(serviceId,currentPage, searchQuery);
                });

            }, doneTypingInterval);


            clearDataTable('#view-complain');
            $('#icd-codes-container').empty();
            selectedICDIds.clear();
            selectedICDCodes = [];
            table.clear().draw();
            $('input[id="icdIDs"]').val('');
        }
    });

    function FetchEncounterProcedureRecord(selectedService) {
        $('#ajax-loader').show();
        var mrNumber = $("#ep_mr").val();
        if (mrNumber === "") {
            $('#ep_mr').addClass('requirefield');
            $('#ajax-loader').hide();
        }
        else
        {
            $('#ep_mr').find('.requirefield').removeClass('requirefield');
            var orderMedicationLink = $('#order-medication-link');
            var investigationtrackingLink = $('#investigation-tracking');
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
                        $('#service-modal').modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $('#service-modal').modal('show');

                        $('.service-btn').click(function () {
                            let selectedServiceId = $(this).attr('data-serviceid');
                            FetchEncounterProcedureRecord(selectedServiceId);
                            $('#service-modal').modal('hide');
                        });
                    }
                    else
                    {
                        if(response.error && response.error == '404')
                        {
                            $('.addep').hide();
                            Swal.fire({
                                text: 'Invald MR #',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    orderMedicationLink.attr('href', '#');
                                    investigationtrackingLink.attr('href', '#');
                                    $('#ep_details, #ep_table, #ep_history').hide();
                                    $('#ep_pname').val('');
                                    $('#ep_gender').val('');
                                    $('.ep_age').val('');
                                    $('#ep_emp').val('');
                                    $('#ep_bcc').val('');
                                    $('#ep_pcc').val('');
                                    $('#ep_sbp').val('');
                                    $('#ep_dbp').val('');
                                    $('#ep_pulse').val('');
                                    $('#ep_temp').val('');
                                    $('#ep_rrate').val('');
                                    $('#ep_weight').val('');
                                    $('#ep_height').val('');
                                    $('#ep_score').val('');
                                    $('.sevice_id').val('');
                                    $('.add_complain').removeAttr('data-serviceid');
                                    $('.load-more').removeAttr('data-serviceid');
                                    $('#icd-search').removeAttr('data-serviceid');
                                    $('#sp_head').text('Medical Codes');
                                    $('.billingcc_id').val('');
                                    $('.servicemode_id').val('');
                                    $('#icdIDs').val('');
                                    // $('#ep_details').removeClass('d-flex flex-column justify-content-end align-items-end');
                                    // $('#ep_details').hide(); // instead of .removeClass(...)

                                }
                            });
                        }
                        else if (!response.patientInOutStatus)
                        {
                            $('.addep').hide();
                            $('#ep_details, #billing_details_section').hide();
                            $('#ep_table, #ep_history').show();

                            $('.encounterModal, .procedureModal, .investigationModal').each(function () {
                                $(this)
                                    .prop('disabled', true)
                                    .css({
                                        'cursor': 'not-allowed',
                                        'opacity': '0.65'
                                    })
                                    .attr('title', 'Disabled');
                            });

                            $('#order-medication-link button')
                            .prop('disabled', true)
                            .css({
                                'cursor': 'not-allowed',
                                'opacity': '0.65'          
                            })
                            .attr('title', 'Disabled');

                            $('.sevice_id').val('');
                            $('.add_complain').removeAttr('data-serviceid');
                            $('.load-more').removeAttr('data-serviceid');
                            $('#icd-search').removeAttr('data-serviceid');
                            $('#sp_head').text('Medical Codes');
                            $('.billingcc_id').val('');
                            $('.servicemode_id').val('');
                            $('#ep_emp').val('');
                            // $('#ep_user').val('');
                            $('#ep_bcc').val('');
                            $('#ep_pcc').val('');
                            $('#ep_site').html('');
                            $('#ep_smt').html('');
                            $('#ep_sgb').html('');
                            $('#ep_service').html('');

                            $('#ep_pname').val(response.patientName);
                            $('#ep_gender').val(response.genderName);
                            $('.ep_age').val(response.patientDOB);

                            $('.encounterModal, .investigationModal, .procedureModal').attr('data-mr', mrNumber);


                            fetchLatestVitalSignRecord(mrNumber);
                            fetchMedicalHistory(mrNumber);
                            fetchAllergyHistory(mrNumber);
                            fetchImmunizationHistory(mrNumber);
                            fetchDrugHistory(mrNumber);
                            fetchPastHistory(mrNumber);
                            fetchObstericHistory(mrNumber);
                            fetchSocialHistory(mrNumber);
                            fetchVisitBasedDetails(mrNumber);
                            var newHref = '/req-medication-consumption/' + mrNumber;
                            orderMedicationLink.attr('href', newHref);
                            var  investigationtrackingHref = '/investigationtracking/' + mrNumber;
                            investigationtrackingLink.attr('href', investigationtrackingHref);

                            // $('.p_attachmentModal').attr('data-mr-number', mrNumber);

                            // if (viewVitalSign) {
                            //     viewVitalSign.destroy();
                            // }
                            // $('#ajax-loader').hide();

                            // fetchVitalSignData(mrNumber)
                        }
                        else{
                            $('.addep').show();
                            $('#ep_details, #ep_table, #ep_history,#billing_details_section').show();
                            if (response.billingCCId && response.performingCCId && response.billingCCId === response.performingCCId) {
                                // Enable buttons
                                $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
                                    .prop('disabled', false)
                                    .css({
                                        'cursor': '',
                                        'opacity': ''
                                    })
                                    .removeAttr('title');

                                $('#order-medication-link').css({
                                    'pointer-events': 'auto',
                                    'cursor': ''
                                }).removeAttr('title');
                            } else {
                                // Disable buttons visually and functionally
                                $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
                                    .prop('disabled', true)
                                    .css({
                                        'cursor': 'not-allowed',
                                        'opacity': '0.65'
                                    })
                                    .attr('title', 'Disabled');

                                $('#order-medication-link').css({
                                    'pointer-events': 'none',
                                    'cursor': 'not-allowed'
                                }).attr('title', 'Disabled');
}

                            var siteName = response.siteName;
                            var serviceMode = response.serviceMode;
                            var serviceName = response.serviceName;
                            var serviceType = response.serviceType;
                            var serviceTypeCode = response.serviceTypeCode;
                            if (serviceTypeCode === 'p') {
                                $('#sp_head').text('Procedures');
                            } else if (serviceTypeCode === 'e') {
                                $('#sp_head').text('Symptoms');
                            } else {
                                $('#sp_head').text('Medical Codes');
                            }
                            var serviceGroup = response.serviceGroup;
                            $('#ep_details').addClass('d-flex flex-column justify-content-end align-items-end');
                            $('#ep_site').html('<b>Site</b> : ' + siteName);
                            $('#ep_smt').html('<b>Service Mode & Type</b> : ' + serviceMode + ' & ' + serviceType);
                            $('#ep_sgb').html('<b>Service Group & Booking</b> : ' + serviceGroup + ' & Booked');
                            $('#ep_service').html('<b>Service</b> : ' + serviceName);
                            let remarks = response.patientInOutRemarks?.trim() || 'N/A';
                            $('#ep_remarks').html('<b>Remarks</b> : ' + remarks);
                            $('#ep_pname').val(response.patientName);
                            $('#ep_gender').val(response.genderName);
                            $('.ep_age').val(response.patientDOB);
                            $('#ep_emp').val(response.empName);
                            if( response.empID )
                            {
                                $('#reqp_physician, #reqi_physician').html(
                                    "<option selected value='" + response.empID + "'>" + response.empName + "</option>"
                                ).prop('disabled', true);
                            }
                            if( response.billingCCId )
                                {
                                    $('#reqp_billingcc, #reqi_billingcc').html(
                                        "<option selected value='" + response.billingCCId + "'>" + response.billingCCName + "</option>"
                                    ).prop('disabled', true);
                                }
                            // response.billingCCName
                            // response.billingCCId
                            // response.empID
                            // response.empName
                            console.log(response);
                            $('#ep_bcc').val(response.billingCCName);
                            $('#ep_pcc').val(response.performingCCName);
                            $('.sevice_id').val(response.serviceId);
                            $('.add_complain').attr('data-serviceid', response.serviceId);
                            $('.load-more').attr('data-serviceid', response.serviceId);
                            $('#icd-search').attr('data-serviceid', response.serviceId);

                            $('.billingcc_id').val(response.billingCCId);
                            $('.servicemode_id').val(response.serviceModeId);
                            $('.empid').val(response.empID);
                            $('.patientmr').val(mrNumber);
                            $('.physician').val(response.empID);

                            $('.encounterModal, .investigationModal, .procedureModal').attr('data-mr', mrNumber);

                            var newHref = '/req-medication-consumption/' + mrNumber;
                            orderMedicationLink.attr('href', newHref);
                            var investigationtrackingHref = '/investigationtracking/' + mrNumber;
                            investigationtrackingLink.attr('href', investigationtrackingHref);

                            // $('.p_attachmentModal').attr('data-mr-number', mrNumber);
                            fetchLatestVitalSignRecord(mrNumber);
                            fetchMedicalHistory(mrNumber);
                            fetchAllergyHistory(mrNumber);
                            fetchImmunizationHistory(mrNumber);
                            fetchDrugHistory(mrNumber);
                            fetchPastHistory(mrNumber);
                            fetchObstericHistory(mrNumber);
                            fetchSocialHistory(mrNumber);
                            fetchVisitBasedDetails(mrNumber);
                            // $('#ajax-loader').hide();
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });

        }
    }

    // Open Tracking Visit
    $(document).on('click', '.viewVisitDetails', function() {
        var Id = $(this).data('id');
        $('#view-visitDetails').modal('show');
        $.ajax({
            url: 'medicalrecord/gettrackingvisit/' + Id,
            type: 'GET',
            success: function(resp) {
                if (resp.length > 0) {
                    var visitDetails = resp[0];
                    $('#visit_clinical_notes').text(visitDetails.clinical_notes);
                    var icdCodes = visitDetails.ICDCodes.split(',');
                    var icdDescriptions = visitDetails.ICDDescriptions.split(',');
                    var complaintsList = $('#complaints-list');
                    complaintsList.empty();
                    for (var i = 0; i < icdCodes.length; i++) {
                        complaintsList.append('<li><b>' + icdCodes[i] + '</b>: ' + icdDescriptions[i] + '</li>');
                    }
                }
            },
            error: function(xhr, status, error) {
                if (typeof errorCallback === "function") {
                    errorCallback(error);
                }
            }
        });
        $('#ajax-loader').hide();
    });
    // Open Tracking Visit

    // Open Medical Diagnosis History Modal
    $(document).on('click', '.add_diagnosehistory', function() {
        $('#m_sincedate').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });
        $('#m_tilledate').bootstrapMaterialDatePicker({ weekStart : 0, time: false,  maxDate: new Date() });
        $('#icd_desc').hide();
        // $('#m_icddiagnose').html("<option selected disabled value=''>Select ICD Diagnosis</option>").prop('disabled',false);
        $('#m_icddiagnose').select2({
            placeholder: 'Select Medical Diagnosis',
            ajax: {
                url: 'medicalrecord/getdiagnosisicdcode',
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.data.map(function (item) {
                            return { id: item.id, text: `${item.code} - ${item.description}` };
                        }),
                        pagination: {
                            more: data.next_page_url !== null
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        // fetchDiagnosisICDCodes('#m_icddiagnose', function(data) {
        //     $.each(data, function(key, value) {
        //         $('#m_icddiagnose').append('<option value="' + value.id + '">' + value.code + '</option>');
        //     });
        // }, function(error) {
        //     console.log(error);
        // });
        $('#add-diagnosishistory').modal('show');
        $('#ajax-loader').hide();
    });
    // Open Medical Diagnosis History Modal

    //Add Medical Diagnosis History
    $('#add_diagnosishistory').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $.ajax({
            url: "/medicalrecord/adddiagnosishistory",
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
                            $('#add_diagnosishistory')[0].reset();
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
                                $('#view-mdh').DataTable().ajax.reload();
                                $('#add_diagnosishistory')[0].reset();
                                $('#add-diagnosishistory').modal('hide');
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
    //Add Medical Diagnosis History

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


    $(document).on('change', '#add-complain input[type="checkbox"]', function () {
        console.log('click');
        const icdId = $(this).data('id');
        if ($(this).is(':checked')) {
            selectedICDIds.add(icdId);
        } else {
            selectedICDIds.delete(icdId);
        }
    });
    // Open Complaint Modal

    // Complain Table
    var table = $('#view-complain').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        columns: [
            { title: "Code" },
            { title: "Description" }
        ]
    });


    // Global array to track selected ICD codes
    let selectedICDCodes = [];

    $(document).on('change', '#add-complain input[type="checkbox"]', function () {
        const icdId = $(this).data('id');
        const icdCode = $(this).data('code');
        const description = $(this).data('name');

        if ($(this).is(':checked')) {
            if (!selectedICDCodes.some(complaint => complaint.icdId === icdId)) {
                selectedICDCodes.push({
                    icdId: icdId,
                    icdCode: icdCode,
                    description: description
                });
            }
        } else {
            selectedICDCodes = selectedICDCodes.filter(complaint => complaint.icdId !== icdId);
        }
        updateTable();
    });

    function updateTable() {
        table.clear().draw();
        selectedICDCodes.forEach(function (complaint) {
            table.row.add([
                complaint.icdCode,
                complaint.description
            ]).draw(false);
        });

        const icdIDs = selectedICDCodes.map(complaint => complaint.icdId).join(',');
        $('input[id="icdIDs"]').val(icdIDs);
    }

    $('#add-complain .done').on('click', function () {
        updateTable();
    });

    // Complain Table

    //Add Visit Based Details
    $('#add_visitdetails').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (field.name == 'Complaints[]'){
                if(field.value == '')
                {
                    alert('Please select at least one complaint.');
                    resp = false;
                }
            }
        });
        if(resp != false)
        {
            $.ajax({
                url: "/medicalrecord/addvisitbaseddetails",
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
                                $('#add_visitdetails')[0].reset();
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
                                    var table = $('#view-complain').DataTable();
                                    table.clear().draw();
                                    table.destroy();
                                    $('#view-complain').DataTable({
                                        paging: false,
                                        searching: false,
                                        info: false,
                                        ordering: false,
                                    });
                                    $('#view-vbd').DataTable().ajax.reload();
                                    $('input[name="selectedicd[]"]').prop('checked', false);
                                    $('#add_visitdetails')[0].reset();
                                    $('#add-visitdetails').modal('hide');
                                    var doneTypingInterval = 200;
                                    setTimeout(FetchEncounterProcedureRecord, doneTypingInterval);
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
    //Add Visit Based Details

    // View Requisition For EPI
    var viewRequisitionEPI;
    function reqEPIDataTable(act, mrNumber) {
        // if ($.fn.DataTable.isDataTable('.view-reqepi')) {
        //     $('.view-reqepi').DataTable().destroy();
        // }
        var tablename;
        if(act == 'e'){
            tablename = '#view-reqe';
        }
        else  if(act == 'i'){
            tablename = '#view-reqi';
        }
        else if(act == 'p'){
            tablename = '#view-reqp';
        }

        if ($.fn.DataTable.isDataTable(tablename)) {
            $(tablename).DataTable().clear().destroy();
            $(tablename + ' tbody').empty(); // Optional: Reset tbody
        }


        viewRequisitionEPI = $(tablename).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/medicalrecord/viewreqepi',
                data: function(d) {
                    d.act = act;
                    d.mr = mrNumber;
                }
            },
            pageLength: 3,
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'mr', name: 'mr' },
                { data: 'remarks', name: 'remarks' },
                { data: 'service', name: 'service' },
                { data: 'billingCC', name: 'billingCC' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            columnDefs: [
                { targets: 1, width: "300px" },
                { targets: 2, width: "300px" },
                { targets: 3, width: "300px" },
                { targets: 4, width: "200px" },
                { targets: 6, width: "350px" },
            ]
        });

        viewRequisitionEPI.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewRequisitionEPI.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewRequisitionEPI.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
    }
    // View Requisition For EPI

    // Open Requisition For Encounter Modal
    $(document).on('click', '.encounterModal', function() {
        // $('#add_reqe')[0].reset();
        $('#ajax-loader').show();
        let act = $(this).data('act');
        // let mrNumber = $(this).data('mr');  // ✅ Get the data-mr value
        let mrNumber = $('#ep_mr').val();
        var orgId = $('#reqe_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#reqe_site', function(data) {
                $('#reqe_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#reqe_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#reqe_site').empty();
            $('#reqe_site').select2();
            $('#reqe_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#reqe_org', '#reqe_site', '#add_reqe');
        }

        $('#reqe_sevice').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
        SiteChangeServiceEPI('#reqe_site', '#reqe_sevice', 'e', '#add_reqe');

        $('#reqe_servicemode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
        ServiceChangeServiceModes('#reqe_site', '#reqe_sevice', '#reqe_servicemode', '#add_reqe');

        $('#reqe_physician').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
        SiteChangeEmployees('#reqe_site', '#reqe_physician', '#add_reqe');

        $('#reqe_billingcc').html("<option selected disabled value=''>Select Speciality (Billing CC)</option>").prop('disabled', true);
        ServiceChangeCostCenter('#reqe_site', '#reqe_sevice', '#reqe_billingcc', '#add_reqe');

        reqEPIDataTable(act,mrNumber);

        $('#ajax-loader').hide();
        $('#add_reqe .text-danger').each(function () {
            if ($(this).text().trim() !== '(Optional)') {
                $(this).text('');
            }
        });
        $('#add_reqe').find('.requirefield').removeClass('requirefield');
        $('#add_reqe').find('.select2-selection').removeClass('requirefield');
        $('#add-reqe').modal('show');

    });
    // Open Requisition For Encounter Modal

    //Add Requisition For Encounter
    $('#add_reqe').submit(function(e) {
        e.preventDefault();
        handleRequisitionEPISubmission('#add_reqe','#add-reqe');
    });
    //Add Requisition For Encounter

    // Open Requisition For Procedure Modal
    $(document).on('click', '.procedureModal', function() {
        // $('#add_reqp')[0].reset();
        let act = $(this).data('act');
        let mrNumber = $('#ep_mr').val();

        $('#add_reqp .text-danger').each(function () {
            if ($(this).text().trim() !== '(Optional)') {
                $(this).text('');
            }
        });
        $('#add_reqp').find('.requirefield').removeClass('requirefield');
        $('#add_reqp').find('.select2-selection').removeClass('requirefield');
        $('#add-reqp').modal('show');
        var orgId = $('#reqe_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#reqp_site', function(data) {
                $('#reqp_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#reqp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#reqp_site').empty();
            $('#reqp_site').select2();
            $('#reqp_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#reqp_org', '#reqp_site', '#add_reqp');
        }

        $('#reqp_sevice').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
        SiteChangeServiceEPI('#reqp_site', '#reqp_sevice', 'p', '#add_reqp');

        $('#reqp_servicemode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
        ServiceChangeServiceModes('#reqp_site', '#reqp_sevice', '#reqp_servicemode', '#add_reqp');

        // let physicianVal = $('#reqp_physician').find(':selected').val();
        // if (!physicianVal || physicianVal === null) {
        //     $('#reqp_physician').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
        //     SiteChangeEmployees('#reqp_site', '#reqp_physician', '#add_reqp');
        // }

        // $('#reqp_billingcc').html("<option selected disabled value=''>Select Speciality (Billing CC)</option>").prop('disabled', true);
        // ServiceChangeCostCenter('#reqp_site', '#reqp_sevice', '#reqp_billingcc', '#add_reqp');

        reqEPIDataTable(act,mrNumber);

        $('#ajax-loader').hide();

    });
    // Open Requisition For Procedure Modal

    //Add Requisition For Procedure
    $('#add_reqp').submit(function(e) {
        e.preventDefault();
        handleRequisitionEPISubmission('#add_reqp','#add-reqp');
    });
    //Add Reqisition For Procedure

    // Open Requisition For Investigation Modal
    $(document).on('click', '.investigationModal', function() {
        // $('#add_reqi')[0].reset();
        let act = $(this).data('act');
        // let mrNumber = $(this).data('mr');
        let mrNumber = $('#ep_mr').val();
        // $('#add_reqi').find('.text-danger').text('');
        $('#add_reqi .text-danger').each(function () {
            if ($(this).text().trim() !== '(Optional)') {
                $(this).text('');
            }
        });
        $('#add_reqi').find('.requirefield').removeClass('requirefield');
        $('#add_reqi').find('.select2-selection').removeClass('requirefield');
        var orgId = $('#reqi_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#reqi_site', function(data) {
                $('#reqi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#reqi_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#reqi_site').empty();
            $('#reqi_site').select2();
            $('#reqi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#reqi_org', '#reqi_site', '#add_reqi');
        }

        $('#reqi_sevice').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
        SiteChangeServiceEPI('#reqi_site', '#reqi_sevice', 'i', '#add_reqi');

        $('#reqi_servicemode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
        ServiceChangeServiceModes('#reqi_site', '#reqi_sevice', '#reqi_servicemode', '#add_reqi');

        let physicianVal = $('#reqi_physician').find(':selected').val();
        if (!physicianVal || physicianVal === null) {

            $('#reqi_physician').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
            SiteChangeEmployees('#reqi_site', '#reqi_physician', '#add_reqi');
        }


        // $('#reqi_billingcc').html("<option selected disabled value=''>Select Speciality (Billing CC)</option>").prop('disabled', true);
        // ServiceChangeCostCenter('#reqi_site', '#reqi_sevice', '#reqi_billingcc', '#add_reqi');
        reqEPIDataTable(act,mrNumber);
        $('#ajax-loader').hide();
        $('#add-reqi').modal('show');

    });
    // Open Requisition For Investigation Modal

    //Add Requisition For Investigation
    $('#add_reqi').submit(function(e) {
        e.preventDefault();
        handleRequisitionEPISubmission('#add_reqi','#add-reqi');
    });
    //Add Requisition For Investigation

    // Update Requisition For EPI Status
    $(document).on('click', '.reqepi', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/medicalrecord/reqepi-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('.view-reqepi').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Requisition For EPI Status

    //Update Requisition For EPI Modal
    $(document).on('click', '.edit-reqepi', function() {
        var reqepId = $(this).data('reqepiid');
        var url = '/medicalrecord/updatereqepi/' + reqepId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_repi_edt').val(formattedDateTime);
                $('#req_epiID').val(response.id);
                $('#u_repi_remarks').val(response.Remarks);
                // $('.modal').modal('hide');
                $('#edit-reqi').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Requisition For EPI Modal

    //Update Requisition For EPI
    $('#update_reqi').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'req_epiID') {
                Id = formData[i].value;
                break;
            }
        }
        var url = '/medicalrecord/update-reqepi/' + Id;
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
                            $('#edit-reqi').modal('hide');
                            $('.view-reqepi').DataTable().ajax.reload(); // Refresh DataTable
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
    //Update Requisition For EPI

    // Open Patient Attachments Modal
    $(document).on('click', '.p_attachmentModal', function() {
        var mrNumber = $('#ep_mr').val();
        $('#ajax-loader').show();
        $('#view-pattachment').DataTable().destroy();
        if ($.fn.DataTable.isDataTable('#view-pattachment')) {
            $('#view-pattachment').DataTable().destroy();
        }
        $('#file-names').empty();
        loadPatientAttachmentDataTable(mrNumber);
        $('#add-pattachments').modal('show');
        // $('#ajax-loader').hide();
        // setTimeout(function() {
        //     $('#ajax-loader').hide();
        // }, 2000);
    });

    $(document).on('click', '.downloadattachements', function () {
        var attachmentPaths = $(this).data('path'); // Get the file paths from the button's data attribute
        var id = $(this).data('id'); // Get the ID from the button's data attribute

        if (attachmentPaths) {
            // Split the attachmentPaths by commas
            var files = attachmentPaths.split(',');

            // Iterate over each file and trigger a download
            files.forEach(function (file) {
                var tempLink = document.createElement('a');
                tempLink.href = '/assets/patientattachment/' + id + '_' + file.trim(); // Combine the ID and file name
                console.log(tempLink.href); // Log the file path for debugging
                tempLink.download = file.trim(); // Set the download file name
                tempLink.target = '_blank'; // Open in a new tab if necessary
                tempLink.click(); // Trigger the download
            });
        } else {
            Swal.fire({
                text: 'No attachments available for download.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
    // Open Patient Attachments Modal

    //Add Patient Attachments
    $('#add_patientattachment').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('.text-danger').text('');

        $.ajax({
            url: "/medicalrecord/addpatientattachment",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
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
                }
                if (fieldName === 'success') {
                    Swal.fire({
                        text: fieldErrors,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#add_patientattachment')[0].reset();
                        $('#view-pattachment').DataTable().ajax.reload();
                        $('.dropify').dropify();
                        $('#file-names').empty();
                    });
                } else {
                    Swal.fire({
                        text: fieldErrors,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (error) {
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
                    for (var fieldName in errors) {
                        var fieldErrors = errors[fieldName];
                        $('#' + fieldName + '_error').text(fieldErrors.join(', '));
                    }
                }
                Swal.close();
            }
        });
    });
    //Add Patient Attachments
});
//Encounters And Procedures
