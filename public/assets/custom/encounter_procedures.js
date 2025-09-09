
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
                                    $('#ep_pcc').html('<option selected disabled value="">Select Performing Cost Center</option>').prop('disabled', true);
                                    // Clear global service performing CCs
                                    window.currentServicePerformingCCs = null;
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
                                    $('.sp_head').text('Medical Codes');
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

                            // $('.encounterModal, .procedureModal, .investigationModal').each(function () {
                            //     $(this)
                            //         .prop('disabled', true)
                            //         .css({
                            //             'cursor': 'not-allowed',
                            //             'opacity': '0.65'
                            //         })
                            //         .attr('title', 'Disabled');
                            // });

                            // $('#order-medication-link button')
                            // .prop('disabled', true)
                            // .css({
                            //     'cursor': 'not-allowed',
                            //     'opacity': '0.65'          
                            // })
                            // .attr('title', 'Disabled');

                            // $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
                            //         .prop('disabled', false)
                            //         .css({
                            //             'cursor': 'pointer',
                            //             'opacity': ''
                            //         })
                            //         .removeAttr('title');

                            //     $('#order-medication-link').css({
                            //         'pointer-events': 'auto',
                            //         'cursor': ''
                            //     }).removeAttr('title');

                            $('.sevice_id').val('');
                            $('.add_complain').removeAttr('data-serviceid');
                            $('.load-more').removeAttr('data-serviceid');
                            $('#icd-search').removeAttr('data-serviceid');
                            $('.sp_head').text('Medical Codes');
                            $('.billingcc_id').val('');
                            $('.servicemode_id').val('');
                            $('#ep_emp').val('');
                            // $('#ep_user').val('');
                            $('#ep_bcc').val('');
                            $('#ep_pcc').html('<option selected disabled value="">Select Performing Cost Center</option>').prop('disabled', true);
                            // Clear global service performing CCs
                            window.currentServicePerformingCCs = null;
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
                            if((response.performingCostCenters.length) == 0)
                            {
                                $('#ajax-loader').hide();
                                $('#ep_details, #ep_table, #ep_history,#billing_details_section,.addep').hide();
                                 Swal.fire({
                                    title: 'No Performing Cost Centers Assigned',
                                    text: 'No performing cost centers are assigned to you yet. Please contact your administrator.',
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }

                            var performingCCId = response.performingCostCenters[0].id;
                            if (response.billingCCId && performingCCId && response.billingCCId === performingCCId) {
                                // $('.addep').show();
                                  $('#add_reqe,#add_reqp,#add_reqi').show();
                                // $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
                                //     .prop('disabled', false)
                                //     .css({
                                //         'cursor': '',
                                //         'opacity': ''
                                //     })
                                //     .removeAttr('title');

                                // $('#order-medication-link').css({
                                //     'pointer-events': 'auto',
                                //     'cursor': ''
                                // }).removeAttr('title');
                            } else {
                                // $('.addep').hide();
                                // Disable buttons visually and functionally
                                  $('#add_reqe,#add_reqp,#add_reqi').hide();
                                // $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
                                //     .prop('disabled', true)
                                //     .css({
                                //         'cursor': 'not-allowed',
                                //         'opacity': '0.65'
                                //     })
                                //     .attr('title', 'Disabled');

                                // $('#order-medication-link').css({
                                //     'pointer-events': 'none',
                                //     'cursor': 'not-allowed'
                                // }).attr('title', 'Disabled');
                            }

                            var siteName = response.siteName;
                            var serviceMode = response.serviceMode;
                            var serviceName = response.serviceName;
                            var serviceType = response.serviceType;
                            var serviceTypeCode = response.serviceTypeCode;
                            if (serviceTypeCode === 'p') {
                                $('.sp_head').text('Procedures');
                            } else if (serviceTypeCode === 'e') {
                                $('.sp_head').text('Symptoms');
                            } else {
                                $('.sp_head').text('Medical Codes');
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
                            $('#ep_bcc').val(response.billingCCName);
                            
                            // Handle performing cost centers
                            populatePerformingCostCenters(response.performingCostCenters);
                            
                            // Check if selected performing cost center matches service's allowed performing cost centers
                            if (response.servicePerformingCCs) {
                                // Store service performing CCs globally for the change event handler
                                window.currentServicePerformingCCs = response.servicePerformingCCs.split(',');
                                
                                var selectedPerformingCC = $('#ep_pcc').val();
                                
                                // Check if selected performing CC is in the service's allowed list
                                var isPerformingCCAllowed = window.currentServicePerformingCCs.includes(selectedPerformingCC);
                                
                                if (!isPerformingCCAllowed) {
                                    // Disable the visit based save button if performing CC doesn't match
                                    $('.vbd').prop('disabled', true)
                                        .css({
                                            'cursor': 'not-allowed',
                                            'opacity': '0.65'
                                        })
                                        .attr('title', 'Selected performing cost center is not allowed for this service');
                                } else {
                                    // Enable the button if performing CC matches
                                    $('.vbd').prop('disabled', false)
                                        .css({
                                            'cursor': '',
                                            'opacity': ''
                                        })
                                        .removeAttr('title');
                                }
                            }
                            $('.sevice_id').val(response.serviceId);
                            $('.add_complain').attr('data-serviceid', response.serviceId);
                            $('.load-more').attr('data-serviceid', response.serviceId);
                            $('#icd-search').attr('data-serviceid', response.serviceId);

                            $('.billingcc_id').val(response.billingCCId);
                            $('#service_type').val(response.serviceTypeCode);
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

    // Handle performing cost center dropdown change
    $(document).on('change', '#ep_pcc', function() {
        $('#ajax-loader').show();
        var selectedCCId = $(this).val();
        
        // Check if the selected performing CC is allowed for the current service
        var servicePerformingCCs = window.currentServicePerformingCCs; // We'll store this globally
        
        if (servicePerformingCCs && selectedCCId) {
            var isPerformingCCAllowed = servicePerformingCCs.includes(selectedCCId);
            
            if (!isPerformingCCAllowed) {
                // Disable the visit based save button if performing CC doesn't match
                $('.vbd').prop('disabled', true)
                    .css({
                        'cursor': 'not-allowed',
                        'opacity': '0.65'
                    })
                    .attr('title', 'Selected performing cost center is not allowed for this service');
            } else {
                // Enable the button if performing CC matches
                $('.vbd').prop('disabled', false)
                    .css({
                        'cursor': '',
                        'opacity': ''
                    })
                    .removeAttr('title');
            }
        }
        
        // Check if billing CC and performing CC are the same to enable/disable buttons
        var billingCCId = $('#reqp_billingcc').val() || $('#reqi_billingcc').val();
        if (billingCCId && selectedCCId && billingCCId === selectedCCId) {
            $('#add_reqe,#add_reqp,#add_reqi').show();
            // Enable buttons
            // $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
            //     .prop('disabled', false)
            //     .css({
            //         'cursor': '',
            //         'opacity': ''
            //     })
            //     .removeAttr('title');

            // $('#order-medication-link').css({
            //     'pointer-events': 'auto',
            //     'cursor': ''
            // }).removeAttr('title');
        } else {
            // $('.addep').hide();
            $('#add_reqe,#add_reqp,#add_reqi').hide();

            // Disable buttons visually and functionally
            // $('.encounterModal, .procedureModal, .investigationModal, #order-medication-link button')
            //     .prop('disabled', true)
            //     .css({
            //         'cursor': 'not-allowed',
            //         'opacity': '0.65'
            //     })
            //     .attr('title', 'Disabled');

            // $('#order-medication-link').css({
            //     'pointer-events': 'none',
            //     'cursor': 'not-allowed'
            // }).attr('title', 'Disabled');
        }
        
        setTimeout(function() {
            $('#ajax-loader').hide();
        }, 300);

    });

    // Function to populate performing cost centers dropdown
    function populatePerformingCostCenters(costCenters) {
        var $dropdown = $('#ep_pcc');
        $dropdown.find('option:not(:first)').remove(); // Remove existing options except first
        
        if (costCenters && costCenters.length > 0) {
            costCenters.forEach(function(cc) {
                $dropdown.append('<option value="' + cc.id + '">' + cc.name + '</option>');
            });
            
            // Select the first cost center by default
            var firstCCId = costCenters[0].id;
            $dropdown.val(firstCCId);
            $dropdown.prop('disabled', false);
        } else {
            // No cost centers found - show SweetAlert message
            Swal.fire({
                title: 'No Performing Cost Centers Assigned',
                text: 'No performing cost centers are assigned to you yet. Please contact your administrator.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
             $('#ep_details, #ep_table, #ep_history, #billing_details_section').hide();

            
            // Clear the dropdown
            $dropdown.html('<option selected disabled value="">No Cost Centers Available</option>');
        }
    }

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
                    const servic_Type = ($('[name="service_type"]').val() || '').trim().toLowerCase();
                    console.log(servic_Type);
                    // choose the right message
                    let msg;
                    if (servic_Type === 'p') {
                    msg = 'Please select at least one procedure.';
                    } else if (servic_Type === 'e') {
                    msg = 'Please select at least one symptom.';
                    } else {
                    msg = 'Please select at least one complaint.';
                    }
                    alert(msg);     
                    // alert('Please select at least one complaint.');
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

        // Clear the first dynamic row
        var firstRow = $('.duplicate').first();
        firstRow.find('.reqi_service').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
        firstRow.find('.reqi_servicemode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);

        // Clear any existing rows that might have pre-selected values
        clearServiceModeDropdowns();
        
        // Update remove button visibility
        updateRemoveButtonVisibility();

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

    // Handle Service change in dynamic rows
    $(document).on('change', '.reqi_service', function() {
        var serviceId = $(this).val();
        var currentRow = $(this).closest('.duplicate');
        var currentRowServiceModeSelect = currentRow.find('.reqi_servicemode');
        if (serviceId) {
            var siteId = $('#reqi_site').val();
            fetchSiteServiceMode(siteId, serviceId, currentRowServiceModeSelect, function(data) {
                if (data && data.length > 0) {
                    currentRowServiceModeSelect.empty();
                    // currentRowServiceModeSelect.append('<option selected disabled value="">Select Service Mode</option>');
                    // Add service modes with prices
                    data.forEach(function(item) {
                        const formattedPrice = item.sell_price ? Number(item.sell_price).toLocaleString() : '0';
                        currentRowServiceModeSelect.append('<option value="' + item.id + '">' + item.name + ' - (Rs ' + formattedPrice + ')</option>');
                    });
                    
                    currentRowServiceModeSelect.find('option:contains("Loading...")').remove();
                    currentRowServiceModeSelect.prop('disabled', false);
                } else {
                    currentRowServiceModeSelect.empty();
                    currentRowServiceModeSelect.html("<option selected disabled value=''>No Service Modes Available</option>").prop('disabled', true);
                }
            }, function(error) {
                console.log('Error fetching service modes:', error);
            });
        } else {
            currentRowServiceModeSelect.empty();
            currentRowServiceModeSelect.html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
        }
    });

    // Initialize first row when site changes
    $(document).on('change', '#reqi_site', function() {
        var siteId = $(this).val();
        
        // Remove all duplicate rows except the first one
        $('.duplicate').not(':first').remove();
        
        if (siteId) {
            // Populate services in the first row
            var firstRowServiceSelect = $('.duplicate').first().find('.reqi_service');
            var firstRowServiceModeSelect = $('.duplicate').first().find('.reqi_servicemode');
            
            // Clear service modes first
            firstRowServiceModeSelect.empty();
            firstRowServiceModeSelect.html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
            
            fetchSiteServicesEPI(siteId, firstRowServiceSelect, 'i', function(data) {
                if (data && data.length > 0) {
                    firstRowServiceSelect.empty();
                    firstRowServiceSelect.append('<option selected disabled value="">Select Service</option>');
                    $.each(data, function(key, value) {
                        firstRowServiceSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    firstRowServiceSelect.find('option:contains("Loading...")').remove();
                    firstRowServiceSelect.prop('disabled', false);
                } else {
                    firstRowServiceSelect.empty();
                    firstRowServiceSelect.html("<option selected disabled value=''>No Services Available</option>").prop('disabled', true);
                }
            }, function(error) {
                console.log(error);
            });
        } else {
            // Clear both service and service mode dropdowns if no site is selected
            var firstRowServiceSelect = $('.duplicate').first().find('.reqi_service');
            var firstRowServiceModeSelect = $('.duplicate').first().find('.reqi_servicemode');
            
            firstRowServiceSelect.empty();
            firstRowServiceSelect.html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
            
            firstRowServiceModeSelect.empty();
            firstRowServiceModeSelect.html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
        }
    });

    // Remove button handler for investigation form
    $(document).on('click', '#removeReqi', function(e) {
        
        // Check if we're in the investigation modal
        if ($('#add-reqi').length && $('#add-reqi').is(':visible')) {
            e.preventDefault(); // Prevent default behavior
            e.stopPropagation(); // Stop event propagation
            
            // Count total duplicate rows
            var totalRows = $('.duplicate').length;
            
            // Don't remove if only one row remains
            if (totalRows <= 1) {
                return false;
            }
            
            // Remove the last duplicate row
            var lastRow = $('.duplicate').last();
            
            // Destroy select2 on the row being removed
            lastRow.find('select.selecter').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
            
            // Remove the row
            lastRow.remove();
            
            // Reinitialize select2 on the remaining last row
            $('.duplicate').last().find('select.selecter').select2();
            
            // Update remove button visibility after removing row
            updateRemoveButtonVisibility();
            
            
            return false;
        } 
    });

    // Function to update remove button visibility
    function updateRemoveButtonVisibility() {
        var totalRows = $('.duplicate').length;
        
        $('.duplicate').each(function(index) {
            var $row = $(this);
            var $removeBtn = $row.find('#removeReqi');
            
            // Hide remove button on first row (index 0), show on others
            if (index === 0) {
                $removeBtn.hide();
            } else {
                $removeBtn.show();
            }
        });
    }

    // Function to clear service mode dropdowns in all rows except the first
    function clearServiceModeDropdowns() {
        $('.duplicate').not(':first').each(function() {
            var $row = $(this);
            var $serviceModeSelect = $row.find('.reqi_servicemode');
            var $serviceSelect = $row.find('.reqi_service');
            
            // If no service is selected, clear and disable service mode dropdown
            if (!$serviceSelect.val()) {
                $serviceModeSelect.empty().html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
            }
        });
    }

    // Custom Add More button handler for investigation form - Higher priority
    $(document).off('click.addMoreInvestigation').on('click.addMoreInvestigation', '#addMoreReqi', function(e) {
        
        // Check if we're in the investigation modal
        if ($('#add-reqi').length && $('#add-reqi').is(':visible')) {
            e.preventDefault(); // Prevent default behavior
            e.stopPropagation(); // Stop event propagation
            
            var isFilled = true;
            
            // Check if all fields in existing rows are filled
            $('.duplicate').each(function() {
                const $row = $(this);
                $row.find('select').each(function() {
                    const $f = $(this);
                    const name = $f.attr('name');
                    if (!$f.val()) {
                        isFilled = false;
                        if ($f.is('select')) {
                            $f.next('.select2-container').find('.select2-selection').addClass('requirefield');
                            $f.off('select2:open.requirefield').on('select2:open.requirefield', function () {
                                $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                            });
                        }
                    }
                });
            });

            if (isFilled) {
                
                // Destroy select2 on the last row
                $('.duplicate').last().find('select.selecter').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                var clonedRow = $('.duplicate').last().clone();
                
                // Clear all values
                clonedRow.find('input').val('');
                clonedRow.find('textarea').val('');
                clonedRow.find('select').prop('selectedIndex', 0).trigger('change');
                
                // Specifically clear and disable service mode dropdown
                clonedRow.find('.reqi_servicemode').empty().html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
                

                $('.duplicate').last().after(clonedRow);
                
                // Reinitialize select2 on the new row
                $('.duplicate').last().find('select.selecter').select2();
                $('.duplicate').last().prev().find('select.selecter').select2();
                
                // Update remove button visibility after adding new row
                updateRemoveButtonVisibility();
                
            } 
            return false;
        } 
    });

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
                            $('.view-reqi').DataTable().ajax.reload(); // Refresh DataTable
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
