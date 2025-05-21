//Patient Arrival & Departure
$(document).ready(function() {
    var filterOrgId = $('#pad_org').val();
    if(filterOrgId)
    {
        fetchOrganizationSites(filterOrgId, '#pad_site', function(data) {
            $('#pad_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
            $.each(data, function(key, value) {
                $('#pad_site').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        });  

        fetchOrgPatient(filterOrgId, '#pad_mrno', function(data) {
            $('#pad_mrno').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
            $.each(data, function(key, value) {
                $('#pad_mrno').append('<option value="' + value.mr_code + '">' + value.mr_code + ' - ' + value.name + '</option>');
            });
        });
    }
    else{
        $('#pad_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#pad_org', '#pad_site', null);
        
        $('#pad_mrno').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
        OrgChangeMRCode('#pad_org', '#pad_mrno', null);
    }

    //Open Patient Arrival & Departure Setup
    function getEncryptedParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const mr = urlParams.get('mr');
        const billedamount = urlParams.get('billedamount');
        const orgname = urlParams.get('orgname');
        const orgid = urlParams.get('orgid');

        const sitename = urlParams.get('sitename');
        const siteid = urlParams.get('siteid');
        const servicemode = urlParams.get('servicemode');
        const servicemodeId = urlParams.get('smid');
        const empname = urlParams.get('empname');
        const empId = urlParams.get('eid');
        const service = urlParams.get('service');
        const serviceId = urlParams.get('sid');
        const billingcc = urlParams.get('billingcc');
        const billingccId = urlParams.get('bcid');
        const patientstatusval = urlParams.get('patientstatusval');
        const patientstatus = urlParams.get('patientstatus');
        const patientpriorityval = urlParams.get('patientpriorityval');
        const patientpriority = urlParams.get('patientpriority');
        const locationname = urlParams.get('locationname');
        const locationid = urlParams.get('locationid');
        const schedulename = urlParams.get('schedulename');
        const scheduleid = urlParams.get('scheduleid');
    
        return { mr, billedamount, orgid, orgname, sitename, siteid, servicemode, servicemodeId, empname, empId, service, serviceId, billingcc, billingccId, 
            patientstatusval, patientstatus, patientpriorityval, patientpriority, locationname, locationid, schedulename, scheduleid};
    }
    
    const encryptedParams = getEncryptedParams();
    const hasEmptyParam = Object.values(encryptedParams).some(param => param === null || param === '');
        if (!hasEmptyParam) {
        
        fetch('/decrypt-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(encryptedParams)
        })
        .then(response => response.json())
        .then(data => {
            openPatientInOutModal(
                data.mr, 
                data.billedamount, 
                data.orgname, 
                data.orgid, 
                data.sitename, 
                data.siteid, 
                data.servicemode, 
                data.servicemodeId, 
                data.empname, 
                data.empId, 
                data.service, 
                data.serviceId, 
                data.billingcc, 
                data.billingccId,
                data.patientstatusval,
                data.patientstatus,
                data.patientpriorityval,
                data.patientpriority,
                data.locationname,
                data.locationid,
                data.schedulename,
                data.scheduleid
            );
        })
        .catch(error => console.error('Error:', error));
    } 
    
    $(document).on('click', '.add-patientinout', function() {
        openPatientInOutModal();
    });
    

    $('#enterMR').change(function() {
        clearTimeout(typingTimer);
        const selectedMr = $(this).val();
        
        if (selectedMr && selectedMr.length >= 9) { 
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        } else {
            $("#pio_mr_error").text("Please select a valid MR #.");
            $("#patientArrivalDetails").hide();
            // $("#booking-status, #patientArrivalDetails").hide();
            $('#ajax-loader').hide();
        }
    });

    //Open Patient Arrival & Departure Setup

    //Add Patient Arrival & Departure
    $('#add_patientinout').submit(function(e) {
        console.log('submit');
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

    // View Patient Arrival & Departure
    var viewpatientArrivalDeparture =  $('#view-patientinout').DataTable({
        processing: true,
        serverSide: true,
        // ajax: '/patient/patientarrivaldeparture',
        ajax: {
            url: '/patient/patientarrivaldeparture',
            data: function (d) {
                d.site_id = $('#pad_site').val();  
                d.mr_no = $('#pad_mrno').val();    
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'serviceBooking', name: 'serviceBooking' },
            { data: 'serviceDetails', name: 'serviceDetails' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "25%"
            },
            {
                targets: 2,
                width: "25%"
            },
            {
                targets: 3,
                width: "25%"
            },
            {
                targets: 5,
                width: "25%"
            }
        ]
        // columns: [
        //     { data: 'id_raw', name: 'id_raw', visible: false },
        //     { data: 'id', name: 'id' },
        //     { data: 'bookingdetails', name: 'bookingdetails' },
        //     { data: 'patientarivalDetails', name: 'patientarivalDetails' },
        //     { data: 'status', name: 'status' },
        //     { data: 'action', name: 'action', orderable: false, searchable: false }
        // ],
        // columnDefs: [
        //     {
        //         targets: 1,
        //         width: "350px"
        //     },
        //     {
        //         targets: 2,
        //         width: "350px"
        //     },
        //     {
        //         targets: 3,
        //         width: "350px"
        //     }
           
        // ]
    });

     $('#pad_site, #pad_mrno').on('change', function () {
        viewpatientArrivalDeparture.ajax.reload();  
    });

    $('.clearFilter').on('click', function () {
        $('#pad_site').val($('#pad_site option:first').val()).change();
        $('#pad_mrno').val($('#pad_mrno option:first').val()).change();
        viewpatientArrivalDeparture.ajax.reload();   
    });

    viewpatientArrivalDeparture.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewpatientArrivalDeparture.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewpatientArrivalDeparture.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Patient Arrival & Departure

    // Update Patient Arrival & Departure Status
    $(document).on('click', '.pio_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/patient/patientinout-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-patientinout').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Patient Arrival & Departure Status

    // End Patient Arrival & Departure
    $(document).on('click', '#endService', function() {
        $('#endServiceModal').modal('show');
        // $("#date-format1").val("");
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            currentDate: new Date() 
        });
        var id = $(this).data('id');
        var servicemodeid = $(this).data('servicemode-id');
        $('#pio_servicemode_id').val(servicemodeid);
        $('#pio_id').val(id);

        var arrivalId     = $(this).data('id');
        var serviceModeId = $(this).data('servicemodeId'); 
        var billingCcId   = $(this).data('billingccId'); 
        var serviceId     = $(this).data('serviceId');   
        var empId         = $(this).data('empId');         
        var mrCode        = $(this).data('mr');         

        $('#pio_id').val(arrivalId);
        $('#pio_servicemode_id').val(serviceModeId);
        $('#pio_billingcc_id').val(billingCcId);
        $('#pio_service_id').val(serviceId);
        $('#pio_emp_id').val(empId);
        $('#pio_mr_code').val(mrCode);
    });

    $('#end_service').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        console.log(data);
        $.ajax({
            url: '/patient/serviceend',
            method: 'POST',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                for (var fieldName in response) {
                    var fieldErrors = response[fieldName];
                }
                if (fieldName == 'success')
                {
                    $('#ajax-loader').hide();
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#add-patientinout').modal('hide');
                            $('#view-patientinout').DataTable().ajax.reload();
                            $('.text-danger').hide();
                            $('#endServiceModal').modal('hide');
                        }
                    });
                }
                else if (fieldName == 'error')
                {
                    $( 'input[name= "pio_serviceEnd"' ).addClass('requirefield');
                    $( 'input[name= "pio_serviceEnd"' ).focus(function() {
                        $('#pio_serviceEnd_error').text("");
                        $('input[name= "pio_serviceEnd"' ).removeClass("requirefield");
                    })
                    $('#pio_serviceEnd_error').text(fieldErrors);
                    $('#ajax-loader').hide();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // End Patient Arrival & Departure

    //Update Patient Arrival & Departure Modal
    $(document).on('click', '.edit-patientinout', function() {
        var patientinoutId = $(this).data('patientinout-id');
        var url = '/patient/updatepatientinout/' + patientinoutId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#patientinout_id').val(patientinoutId);
                $('#u_pio_org').html("<option selected value="+response.orgId+">" + response.orgName + "</option>").prop('disabled',true);
                $('#u_pio_site').html("<option selected value="+response.siteId+">" + response.siteName + "</option>").prop('disabled',true);
                $('#u_pio_status').html("<option selected>" + response.patientStatus + "</option>").prop('disabled',true);
                $('#u_pio_priority').html("<option selected>" + response.patientPriority + "</option>").prop('disabled',true);
                $('#u_pio_location').html("<option selected>" + response.locationName + "</option>").prop('disabled',true);
                $('#u_pio_schedule').html("<option selected>" + response.locationSchedule + "</option>").prop('disabled',true);
                $('#u_pio_emp').html("<option selected>" + response.empName + "</option>").prop('disabled',true);
                var startFormatted = moment.unix(response.start_timestamp).format('MM/DD/YYYY h:mm A');
                var endFormatted = moment.unix(response.end_timestamp).format('MM/DD/YYYY h:mm A');
                $('#u_pio_scheduleDatetime').data('daterangepicker').setStartDate(startFormatted);
                $('#u_pio_scheduleDatetime').data('daterangepicker').setEndDate(endFormatted);
                $('#u_pio_scheduleDatetime').prop('disabled',true);
                $('#u_pio_mr').val(response.mrNo).prop('disabled',true);
                $('#u_pio_service').html("<option selected value="+response.serviceID+">" + response.serviceName + "</option>").prop('disabled',true);
                $('#u_pio_serviceMode')
                .html("<option selected value='" + response.serviceModeID + "'>" + response.service_modeName + " (Rs " + response.billedAmount + ")</option>")
                .prop('disabled', true);
                $('#u_pio_billingCC').html("<option selected value="+response.CCID+">" + response.CCName + "</option>").prop('disabled',true);
                var formattedstartTime = moment(response.StartTime, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_pio_serviceStart').val(formattedstartTime);

                if(response.EndTime != null)
                {
                    var formattedendTime = moment(response.EndTime, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('#u_pio_serviceEnd').val(formattedendTime);
                }
                else{
                    $('#u_pio_serviceEnd').attr('placeholder', 'Select Service End Time');
                }

                // var serviceSelector = '#u_pio_service';
                // fetchSiteServices(response.siteId, serviceSelector, function(data) {
                //     const $Service = $(serviceSelector);
                //     $Service.empty()
                //     .append('<option selected value='+ response.serviceID +'>'+ response.serviceName +'</option>')
                //     .append(
                //         data.map(({ id, name }) => {
                //             if (id != response.serviceID) {
                //                 return `<option value="${id}">${name}</option>`;
                //             }
                //         }).join(''))
                //     .prop('disabled', false)
                //     .find('option:contains("Loading...")').remove();
                //     $Service.trigger('change');
                // });

                // var serviceModeSelector = '#u_pio_serviceMode';
                // fetchSiteServiceMode(response.siteId, response.serviceID, serviceModeSelector, function(data) {
                //     const $ServiceMode = $(serviceModeSelector);
                //     $ServiceMode.off('change'); // Remove any existing 'change' event listeners
                //     $ServiceMode.empty()
                //         .append('<option selected value='+ response.serviceModeID +'>'+ response.service_modeName +'</option>')
                //         .append(
                //         data.map(({ id, name }) => {
                //             if (id != response.serviceModeID) {
                //                 return `<option value="${id}">${name}</option>`;
                //             }
                //         }).join(''))
                //         .prop('disabled', false)
                //         .find('option:contains("Loading...")').remove();
                // });
                // ServiceChangeServiceModes('#u_pio_site', '#u_pio_service', '#u_pio_serviceMode', '#update_patientinout');

                // var CCSelector = '#u_pio_billingCC';
                // fetchServiceCostCenter(response.siteId, response.serviceID, CCSelector, function(data) {
                //     const $CostCenter = $(CCSelector);
                //     $CostCenter.empty()
                //             .append('<option selected value='+ response.CCID +'>'+ response.CCName +'</option>')
                //             .append(
                //             data.map(({ id, name }) => {
                //                 if (id != response.CCID) {
                //                     return `<option value="${id}">${name}</option>`;
                //                 }
                //             }).join(''))
                //             .prop('disabled', false)
                //             .find('option:contains("Loading...")').remove();
                // });

                // ServiceChangeCostCenter('#u_pio_site', '#u_pio_service', '#u_pio_billingCC', '#update_patientinout');

                $('#u_pio_payMode').html("<option selected>" + response.paymentMode + "</option>");
                var PaymentMode = [
                    {value: "Cash", label: "Cash"},
                    {value: "Card", label: "Card"}
                ];
                var PaymentMode = PaymentMode.filter(function(option) {
                    return option.value !== response.paymentMode;
                });
                PaymentMode.forEach(function(option) {
                    $("#u_pio_payMode").append(new Option(option.label, option.value));
                });

                $('#u_pio_amount').val(response.Amount);

                $('#edit-patientinout').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Patient Arrival & Departure Modal

    //Update Patient Arrival & Departure
    $('#update_patientinout').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var patientInOutId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'patientinout_id') {
                patientInOutId = formData[i].value;
                break;
            }
        }
        var url = 'patient/update-patientinout/' + patientInOutId;
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
                            $('#edit-patientinout').modal('hide');
                            $('#view-patientinout').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_patientinout')[0].reset();
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
    //Update Patient Arrival & Departure

});
//Patient Arrival & Departure