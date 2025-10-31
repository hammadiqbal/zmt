$(document).ready(function() {
    // Service booking
    var filterOrgId = $('#fb_org').val();
    if(filterOrgId)
    {
        fetchOrganizationSites(filterOrgId, '#fb_site', function(data) {
            $('#fb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
            $.each(data, function(key, value) {
                $('#fb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        });  

        // Initialize MR search with AJAX and infinite scrolling
        initializeMRSearch('#fb_mrno', filterOrgId);
        
    }
    else{
        $('#fb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#fb_org', '#fb_site', null);
        
        $('#fb_mrno').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
        // Set up change handler for organization to initialize MR search
        $('#fb_org').on('change', function() {
            var orgId = $(this).val();
            if (orgId) {
                initializeMRSearch('#fb_mrno', orgId);
            } else {
                $('#fb_mrno').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
            }
        });
    }
    // Service booking


    // Open Service Booking
    $(document).on('click', '.add-servicebooking', function() {
        // var orgId = $('#sb_org').val();
        // var orgId = $(this).data('orgid'); 
        var orgId = $('#sb_org').val() || $(this).data('orgid');
        var mrCode = $(this).data('mr');
        var orgName = $(this).data('orgname'); 
        var siteName = $(this).data('sitename'); 
        var siteId = $(this).data('siteid'); 
        var ServiceMode = $(this).data('servicemode'); 
        var ServiceModeId = $(this).data('servicemodeid'); 
        var Physician = $(this).data('empname'); 
        var PhysicianId = $(this).data('empid'); 
        var Service = $(this).data('service'); 
        var ServiceId = $(this).data('serviceid'); 
        var billingCC = $(this).data('billingcc'); 
        var billingCCId = $(this).data('billingccid'); 
        if(orgId)
        {
            if (mrCode && orgId && orgName && siteId && siteName && ServiceModeId && ServiceMode && PhysicianId && Physician && ServiceId && Service && billingCCId && billingCC) {
                $('#sb_org').html('<option value="' + orgId + '" selected>' + orgName + '</option>').prop('disabled', true);
                $('#sb_site').html('<option value="' + siteId + '" selected>' + siteName + '</option>').prop('disabled', true);
                $('#sb_mr').html('<option value="' + mrCode + '" selected>' + mrCode + '</option>').prop('disabled', true);
                $('#sb_serviceMode').html('<option value="' + ServiceModeId + '" selected>' + ServiceMode + '</option>').prop('disabled', true);
                $('#sb_emp').html('<option value="' + PhysicianId + '" selected>' + Physician + '</option>').prop('disabled', true);
                $('#sb_service').html('<option value="' + ServiceId + '" selected>' + Service + '</option>').prop('disabled', true);
                $('#sb_billingCC').html('<option value="' + billingCCId + '" selected>' + billingCC + '</option>').prop('disabled', true);
            } 
            else {
                fetchOrganizationSites(orgId, '#sb_site', function(data) {
                    $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('#sb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });

                // Initialize MR search with AJAX and infinite scrolling
                initializeMRSearch('#sb_mr', orgId);
                            
                $('#sb_emp').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
                SiteChangeEmployees('#sb_site', '#sb_emp', '#add_servicebooking');

                $('#sb_service').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
                SiteChangeService('#sb_site', '#sb_service', '#add_servicebooking');

                $('#sb_serviceMode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
                ServiceChangeServiceModes('#sb_site', '#sb_service', '#sb_serviceMode', '#add_servicebooking');
                
                $('#sb_billingCC').html("<option selected disabled value=''>Select Billing Cost Center</option>").prop('disabled', true);
                ServiceChangeCostCenter('#sb_site', '#sb_service', '#sb_billingCC', '#add_servicebooking');
            }
            
            // fetchServiceLocations(orgId, '#sb_location', function(data) {
            //     $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', false);
            //     $.each(data, function(key, value) {
            //         $('#sb_location').append('<option value="' + value.id + '">' + value.name + '</option>');
            //     });
            // });
        }
        else{
            $('#sb_org').empty();
            $('#sb_org').select2();
            fetchOrganizations(null,null,'#sb_org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#sb_org').html(options.join('')).trigger('change');
            });
            $('#sb_site').empty();
            $('#sb_site').select2();
            $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#sb_org', '#sb_site', '#add_servicebooking');

            if (mrCode && orgId && orgName && siteId && siteName && ServiceModeId && ServiceMode && PhysicianId && Physician && ServiceId && Service && billingCCId && billingCC) {
                $('#sb_org').html('<option value="' + orgId + '" selected>' + orgName + '</option>').prop('disabled', true);
                $('#sb_site').html('<option value="' + siteId + '" selected>' + siteName + '</option>').prop('disabled', true);
                $('#sb_mr').html('<option value="' + mrCode + '" selected>' + mrCode + '</option>').prop('disabled', true);
                $('#sb_serviceMode').html('<option value="' + ServiceModeId + '" selected>' + ServiceMode + '</option>').prop('disabled', true);
                $('#sb_emp').html('<option value="' + PhysicianId + '" selected>' + Physician + '</option>').prop('disabled', true);
                $('#sb_service').html('<option value="' + ServiceId + '" selected>' + Service + '</option>').prop('disabled', true);
                $('#sb_billingCC').html('<option value="' + billingCCId + '" selected>' + billingCC + '</option>').prop('disabled', true);
                
                // fetchServiceLocations(orgId, '#sb_location', function(data) {
                //     $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', false);
                //     $.each(data, function(key, value) {
                //         $('#sb_location').append('<option value="' + value.id + '">' + value.name + '</option>');
                //     });
                // });
            } 
            else {
                $('#sb_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
                // Set up change handler for organization to initialize MR search
                $('#sb_org').on('change', function() {
                    var orgId = $(this).val();
                    if (orgId) {
                        initializeMRSearch('#sb_mr', orgId);
                    } else {
                        $('#sb_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
                    }
                });

                $('#sb_emp').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
                SiteChangeEmployees('#sb_site', '#sb_emp', '#add_servicebooking');

                $('#sb_service').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
                SiteChangeService('#sb_site', '#sb_service', '#add_servicebooking');

                $('#sb_serviceMode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled', true);
                ServiceChangeServiceModes('#sb_site', '#sb_service', '#sb_serviceMode', '#add_servicebooking');
                
                $('#sb_billingCC').html("<option selected disabled value=''>Select Billing Cost Center</option>").prop('disabled', true);
                ServiceChangeCostCenter('#sb_site', '#sb_service', '#sb_billingCC', '#add_servicebooking');

                // $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                // OrgChangeServiceLocation('#sb_org', '#sb_location', '#add_servicebooking');
            }
        }

        $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
        SiteChangeActivatedServiceLocation('#sb_site','#sb_location', '#add_servicebooking',false, false );
        // Show Service Scheduling
        $('#sb_schedule').html("<option selected disabled value=''>Select Service Location Schedule</option>").prop('disabled', true);
        LocationChangeServiceScheduling('#sb_location', '#sb_site', '#sb_schedule', '#add_servicebooking');
        // Show Service Scheduling

        $('#add-servicebooking').modal('show');
    });
    // Open Service Booking

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

    // View Service Booking
    var ServiceBooking =  $('#view-servicebooking').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/services/viewservicebooking',
            data: function (d) {
                d.site_id = $('#fb_site').val();  
                d.mr_no = $('#fb_mrno').val();
                d.date_filter = $('#fb_date_filter').val();
            }
        },
        // ajax: '/services/viewservicebooking',
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
                width: "100px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });

    // Initialize date filter state based on site and MR selection
    function updateDateFilterState() {
        var siteSelected = $('#fb_site').val() && $('#fb_site').val() !== '' && $('#fb_site').val() !== 'Select Site';
        var mrSelected = $('#fb_mrno').val() && $('#fb_mrno').val() !== '' && $('#fb_mrno').val() !== 'Select MR #';
        
        if (siteSelected || mrSelected) {
            // If site or MR is selected, unselect date filter but keep it enabled
            $('#fb_date_filter').val('').prop('disabled', false);
        } else {
            // If neither site nor MR is selected, enable date filter and set to today
            $('#fb_date_filter').val('today').prop('disabled', false);
        }
    }

    // Call on page load
    updateDateFilterState();

    // Handle change events for site and MR number (using event delegation for Select2)
    $('#fb_site').on('change', function () {
        handleFilterChange();
    });

    // Handle Select2 change event for MR number
    $(document).on('change', '#fb_mrno', function () {
        handleFilterChange();
    });

    function handleFilterChange() {
        // Check if site or MR number is selected
        var siteSelected = $('#fb_site').val() && $('#fb_site').val() !== '' && $('#fb_site').val() !== 'Select Site';
        var mrSelected = $('#fb_mrno').val() && $('#fb_mrno').val() !== '' && $('#fb_mrno').val() !== 'Select MR #';
        
        if (siteSelected || mrSelected) {
            // If site or MR is selected, unselect date filter but keep it enabled
            $('#fb_date_filter').val('').prop('disabled', false).change();
        } else {
            // If neither site nor MR is selected, enable date filter and set to today
            $('#fb_date_filter').val('today').prop('disabled', false);
        }
        
        ServiceBooking.ajax.reload();  
    }

    // Handle date filter selection
    $('#fb_date_filter').on('change', function() {
        ServiceBooking.ajax.reload();
    });

    $('.clearFilter').on('click', function () {
        $('#fb_site').val($('#fb_site option:first').val()).change();
        // Clear Select2 dropdown
        if ($('#fb_mrno').hasClass('select2-hidden-accessible')) {
            $('#fb_mrno').val(null).trigger('change');
        } else {
            $('#fb_mrno').val($('#fb_mrno option:first').val()).change();
        }
        $('#fb_date_filter').val('today').prop('disabled', false).change();
        ServiceBooking.ajax.reload();   
    });

    ServiceBooking.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceBooking.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the DataTable has finished rendering
    ServiceBooking.on('draw.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Booking

    // Update Service Booking Status
    $(document).on('click', '.servicebooking', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/servicebooking-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicebooking').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Service Booking Status

    // Update Service Booking Modal
    $(document).on('click', '.edit-servicebooking', function() {
        var servicebookingId = $(this).data('servicebooking-id');
        $('#u_sb_org').empty();
        $('#u_sb_site').empty();
        $('#u_sb_location').empty();
        $('#u_sb_cc').empty();
        $('#u_sb_sm').empty();
        $('#u_sb_service').empty();
        $('#u_sb_schedule').empty();
        $('#u_sb_emp').empty();
        $('#u_sbp_status').empty();
        $('#u_sbp_priority').empty();
        $('#update_servicebooking')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/servicebookingmodal/' + servicebookingId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var remarks = response.remarks;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var locationName = response.locationName;
                var locationId = response.locationId;
                var locationScheduleId = response.locationScheduleId;
                var locationScheduleName = response.locationScheduleName;
                var MR = response.MRno;
                var PatientStatus = response.PatientStatus;
                var PatientPriority = response.PatientPriority;
                var empName = response.empName;
                var empId = response.empId;
                var empName = response.empName;
                
                var serviceName = response.serviceName;
                var serviceID = response.serviceID;
                var servicemodeName = response.servicemodeName;
                var serviceModeID = response.serviceModeID;
                var CCName = response.CCName;
                var CCid = response.CCid;

                var startFormatted = moment.unix(response.startdateTime).format('h:mm A');
                var endFormatted = moment.unix(response.enddateTime).format('h:mm A');

                // Capitalize first letter of each day and format pattern
                const formattedPattern = response.schedulePattern.split(', ').map(day => 
                    day.charAt(0).toUpperCase() + day.slice(1)
                ).join(', ');

                $('#u_sb_org').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_sb_site').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_sb_location').html("<option selected value='"+locationId+"'>" + locationName + "</option>");
                $('#u_sb_schedule').html("<option selected value='" + locationScheduleId + "'>" + locationScheduleName + " (StartTime: " + startFormatted + " EndTime: " + endFormatted + ") - " + formattedPattern + "</option>");
                $('#u_sbp_status').html("<option selected value='"+PatientStatus+"'>" + capitalizeFirstLetterOfEachWord(PatientStatus) + "</option>");
                $('#u_sbp_priority').html("<option selected value='"+PatientPriority+"'>" + capitalizeFirstLetterOfEachWord(PatientPriority) + "</option>");
                $('#u_sb_emp').html("<option selected value='"+empId+"'>" + capitalizeFirstLetterOfEachWord(empName) + "</option>");
                $('#u_sb_mr').html("<option selected value='"+MR+"'>" + capitalizeFirstLetterOfEachWord(MR) + "</option>");

                $('#u_sb_service').html("<option selected value='"+serviceID+"'>" + serviceName + "</option>");
                $('#u_sb_sm').html("<option selected value='"+serviceModeID+"'>" + servicemodeName + "</option>");
                $('#u_sb_cc').html("<option selected value='"+CCid+"'>" + CCName + "</option>");

                $('.u_sb_remarks').val(remarks);
                $('.u_sbooking_id').val(servicebookingId);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_sb_org', function(data) {
                    $('#u_sb_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_sb_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                fetchSites(orgID, '#u_sb_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_sb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Sites are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-servicebooking').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },siteId);

                $('#u_sb_org').off('change').on('change', function() {
                    $('#u_sb_site').empty();
                    $('#u_sb_site').append('<option selected disabled value="">Select Site</option>');
                    var organizationId = $(this).val();
                    fetchSites(organizationId, '#u_sb_site', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_sb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-servicebooking').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });

                    fetchServiceLocations(organizationId, '#u_sb_location', function(data) {
                        if (data.length > 0) {
                            $('#u_sb_location').empty();
                            $('#u_sb_location').append('<option selected disabled value="">Select Service Location</option>');
                            $.each(data, function(key, value) {
                                $('#u_sb_location').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            $('#u_sb_location').find('option:contains("Loading...")').remove();
                            $('#u_sb_location').prop('disabled', false);
                        }
                        else{
                            Swal.fire({
                                text: 'Service Locations are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-servicebooking').modal('hide');
                                }
                            });

                        }
                    }, function(error) {
                        console.log(error);
                    });
                });

                
                fetchActiveSL(siteId, '#u_sb_location', false, false, function(data) {
                    const $serviceLocation = $('#u_sb_location');
                    if (data && data.length > 0) {
                        $.each(data, function(key, value) {
                            if(locationId != value.location_id)
                            {
                                $('#u_sb_location').append('<option value="' + value.location_id + '">' + value.name + '</option>');
                            }
                        });
                    } else {
                        Swal.fire({
                            text: 'Service Locations are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    $('#u_sb_location').find('option:contains("Loading...")').remove();
                    $('#u_sb_location').prop('disabled', false);
                });

                
                fetchServiceScheduling(locationId, siteId, '#u_sb_schedule', function(data) {
                    $.each(data, function(key, value) {
                        if(locationScheduleId != value.id)
                        {
                            const startDate = new Date(value.start_timestamp * 1000); 
                            const endDate = new Date(value.end_timestamp * 1000); 

                            const startHours = startDate.getHours();
                            const startMinutes = startDate.getMinutes();
                            const startAmPm = startHours >= 12 ? 'PM' : 'AM';
                            const start12Hour = startHours % 12 || 12;
                            const formattedStartTime = `${start12Hour}:${(startMinutes < 10 ? '0' : '') + startMinutes} ${startAmPm}`;

                            const endHours = endDate.getHours();
                            const endMinutes = endDate.getMinutes();
                            const endAmPm = endHours >= 12 ? 'PM' : 'AM';
                            const end12Hour = endHours % 12 || 12;
                            const formattedEndTime = `${end12Hour}:${(endMinutes < 10 ? '0' : '') + endMinutes} ${endAmPm}`;
                            const formattedPattern = schedule_pattern.split(', ').map(day => 
                                day.charAt(0).toUpperCase() + day.slice(1)
                            ).join(', ');

                            // const startDate = new Date(value.start_timestamp * 1000); // Convert to milliseconds
                            // const endDate = new Date(value.end_timestamp * 1000); // Convert to milliseconds

                            // const formattedStartDate = `${startDate.getMonth() + 1}-${startDate.getDate()}-${startDate.getFullYear()} ${startDate.getHours()}:${(startDate.getMinutes() < 10 ? '0' : '') + startDate.getMinutes()} ${startDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                            // const formattedEndDate = `${endDate.getMonth() + 1}-${endDate.getDate()}-${endDate.getFullYear()} ${endDate.getHours()}:${(endDate.getMinutes() < 10 ? '0' : '') + endDate.getMinutes()} ${endDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                            $('#u_sb_schedule').append(
                                `<option value="${value.id}">${value.name}  (StartTime: ${formattedStartTime} - EndTime: ${formattedEndTime}) - ${formattedPattern}</option>`
                            );
                            // $('#u_sb_schedule').append('<option value="' + value.id + '">' + value.name + ' </option>');
                        }
                    });
                    $('#u_sb_schedule').find('option:contains("Loading...")').remove();
                    $('#u_sb_schedule').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });

                // $('#u_sb_location').html("<option selected disabled value=''>Select Service Location Schedule</option>").prop('disabled', true);
                LocationChangeServiceScheduling('#u_sb_location', '#u_sb_site', '#u_sb_schedule', '#update_servicebooking');


                // $('#u_sb_location').off('change').on('change', function() {
                //     var locationId = $(this).val();
                //     if (locationId) {
                //         var siteId = $('#u_sb_site').val();
                //         fetchServiceScheduling(locationId, siteId, '#u_sb_schedule', function(data) {
                //             if (data.length > 0) {
                //                 $('#u_sb_schedule').empty();
                //                 $('#u_sb_schedule').append('<option selected disabled value="">Select Schedule</option>');
                //                 $.each(data, function(key, value) {
                //                     const startDate = new Date(value.start_timestamp * 1000); // Convert to milliseconds
                //                     const endDate = new Date(value.end_timestamp * 1000); // Convert to milliseconds

                //                     const formattedStartDate = `${startDate.getMonth() + 1}-${startDate.getDate()}-${startDate.getFullYear()} ${startDate.getHours()}:${(startDate.getMinutes() < 10 ? '0' : '') + startDate.getMinutes()} ${startDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                //                     const formattedEndDate = `${endDate.getMonth() + 1}-${endDate.getDate()}-${endDate.getFullYear()} ${endDate.getHours()}:${(endDate.getMinutes() < 10 ? '0' : '') + endDate.getMinutes()} ${endDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                //                     $('#u_sb_schedule').append(
                //                         `<option value="${value.id}">${value.name} (StartTime: ${formattedStartDate} - EndTime: ${formattedEndDate})</option>`
                //                     );
                //                 });
                //                 $('#u_sb_schedule').find('option:contains("Loading...")').remove();
                //                 $('#u_sb_schedule').prop('disabled', false);
                //             }
                //             else {
                //                 Swal.fire({
                //                     text: 'Service Locations Schedules are not available for selected Service Location',
                //                     icon: 'error',
                //                     confirmButtonText: 'OK'
                //                 }).then((result) => {
                //                     if (result.isConfirmed) {
                //                         $('#edit-servicebooking').modal('hide');
                //                     }
                //                 });
                //             }

                //         }, function(error) {
                //             console.log(error);
                //         });
                //     }
                // });

                fetchPhysicians(siteId, '#u_sb_emp', function(data) {
                    $.each(data, function(key, value) {
                        if(empId != value.id)
                        {
                            $('#u_sb_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                    $('#u_sb_emp').find('option:contains("Loading...")').remove();
                    $('#u_sb_emp').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });
                SiteChangeEmployees('#u_sb_site', '#u_sb_emp', '#update_servicebooking');

                var serviceSelector = '#u_sb_service';
                fetchSiteServices(siteId, serviceSelector, function(data) {
                    const $Service = $(serviceSelector);
                    $Service.empty()
                    .append('<option selected value='+ response.serviceID +'>'+ response.serviceName +'</option>')
                    .append(
                        data.map(({ id, name }) => {
                            if (id != response.serviceID) {
                                return `<option value="${id}">${name}</option>`;
                            }
                        }).join(''))
                    .prop('disabled', false)
                    .find('option:contains("Loading...")').remove();
                    // $Service.trigger('change');
                });
                SiteChangeService('#u_sb_site', '#u_sb_service', '#update_servicebooking');

                var serviceModeSelector = '#u_sb_sm';
                fetchSiteServiceMode(siteId, response.serviceID, serviceModeSelector, function(data) {
                const $ServiceMode = $(serviceModeSelector);
                    $ServiceMode.empty()
                        .append('<option selected value='+ response.serviceModeID +'>'+ response.servicemodeName +'</option>')
                        .append(
                        data.map(({ id, name }) => {
                            if (id != response.serviceModeID) {
                                return `<option value="${id}">${name}</option>`;
                            }
                        }).join(''))
                        .prop('disabled', false)
                        .find('option:contains("Loading...")').remove();
                        // $ServiceMode.trigger('change');
                });
                ServiceChangeServiceModes('#u_sb_site', '#u_sb_service', '#u_sb_sm', '#update_servicebooking');

                var CCSelector = '#u_sb_cc';
                fetchServiceCostCenter(siteId, response.serviceID, CCSelector, function(data) {
                    const $CostCenter = $(CCSelector);
                    if (data && data.length > 0) {
                        $CostCenter.empty()
                                .append('<option selected value='+ response.CCid +'>'+ response.CCName +'</option>')
                                .append(
                                data.map(({ id, name }) => {
                                    if (id != response.CCid) {
                                        return `<option value="${id}">${name}</option>`;
                                    }
                                }).join(''))
                                .prop('disabled', false)
                                .find('option:contains("Loading...")').remove();
                                $CostCenter.trigger('change');
                    } 
                });
                ServiceChangeCostCenter('#u_sb_site', '#u_sb_service', '#u_sb_cc', '#update_servicebooking');

                fetchPatientMR(siteId, '#u_sb_mr', 'serviceBooking', function(data) {
                    $.each(data, function(key, value) {
                        if(MR != value.mr_code)
                        {
                            $('#u_sb_mr').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
                        }
                    });
                    $('#u_sb_mr').find('option:contains("Loading...")').remove();
                    $('#u_sb_mr').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });
                SiteChangeMRCode('#u_sb_site', '#u_sb_mr', '#update_servicebooking', 'serviceBooking');

                var patientStatusOptions = [
                    {value: "new", label: "New"},
                    {value:     "follow up", label: "Follow Up"}
                ];
                var patientStatus = patientStatusOptions.filter(function(option) {
                    return option.value !== PatientStatus;
                });
                patientStatus.forEach(function(option) {
                    $("#u_sbp_status").append(new Option(option.label, option.value));
                });

                var patientPriorityOptions = [
                    {value: "routine", label: "Routine"},
                    {value: "urgent", label: "Urgent"},
                    {value: "emergency", label: "Emergency"}

                ];
                var patientPriority = patientPriorityOptions.filter(function(option) {
                    return option.value !== PatientPriority;
                });
                patientPriority.forEach(function(option) {
                    $("#u_sbp_priority").append(new Option(option.label, option.value));
                });

                $('#edit-servicebooking').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Service Booking Modal

    //Update Service Booking
    $('#update_servicebooking').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('.u_sbooking_id').val();
        var url = '/services/update-servicebooking/' + Id;
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
                            $('#edit-servicebooking').modal('hide');
                            $('#view-servicebooking').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_servicebooking')[0].reset();
                            $('.text-danger').hide();
                        }
                        $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update Service Booking
});