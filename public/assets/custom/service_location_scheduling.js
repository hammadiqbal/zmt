$(document).ready(function() {
    $('#ss_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#ss_org', '#ss_site', '#add_servicelocationscheduling');

    //Open Service Location Scheduling Setup
    $(document).on('click', '.add-locationscheduling', function() {
        var orgId = $('#ss_org').val();
        $('.open_schedule').val('');
        $('.day-check').prop('checked', false);
        if(orgId)
        {
            // fetchServiceLocations(orgId, '#ss_location', function(data) {
            //     $('#ss_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', false);
            //     $.each(data, function(key, value) {
            //         $('#ss_location').append('<option value="' + value.id + '">' + value.name + '</option>');
            //     });
            // });
            fetchOrganizationSites(orgId, '#ss_site', function(data) {
                $('#ss_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#ss_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{

            $('#ss_org').empty();
            $('#ss_org').select2();
            fetchOrganizations(null,null,'#ss_org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#ss_org').html(options.join('')).trigger('change');
            });
            $('#ss_site').empty();
            $('#ss_site').select2();
            $('#ss_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#ss_org', '#ss_site', '#add_servicelocation');


        }
        $('#ss_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
        SiteChangeActivatedServiceLocation('#ss_site','#ss_location', '#add_locationscheduling',false, false );

            // OrgChangeServiceLocation('#ss_org', '#ss_location', '#add_locationscheduling');
        $('#add-locationscheduling').modal('show');
    });

    $(document).on('click', '.open_schedule', function() {
        $('#daySelectorModal').modal({
            backdrop: 'static',
            keyboard: false
        }).modal('show');
    });

    $(document).on('click', '#saveDays', function () {
        let selectedDays = [];
        $('.day-check:checked').each(function () {
            selectedDays.push($(this).val());
        });

        if (selectedDays.length === 0) {
            $('#dayError').removeClass('d-none');
            return;
        } else {
            $('#dayError').addClass('d-none');
        }
        // Set selected days into readonly input
        // $('#ss_pattern').val(selectedDays.join(', '));
        if ($('#edit-locationscheduling').hasClass('show')) {
            $('#u_sspattern').val(selectedDays.join(', '));
        } else {
            $('#ss_pattern').val(selectedDays.join(', '));
        }

        // Close modal
        $('#daySelectorModal').modal('hide');

    });

    $('#daySelectorModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        // $('.modal-backdrop').remove();
    });
    //Open Service Location Scheduling Setup

    //Add Service Location Scheduling
    $('#add_locationscheduling').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && ((field.name != 'total_patient') && (field.name != 'new_patient') && (field.name != 'followup_patient') && (field.name != 'routine_patient') && (field.name != 'urgent_patient') && (field.name != 'ss_emp')))
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
                url: "/services/addlocationscheduling",
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
                                $('#add-locationscheduling').modal('hide');
                                $('#view-locationscheduling').DataTable().ajax.reload();
                                $('#add_locationscheduling').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_locationscheduling')[0].reset();
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
                                $('#add_locationscheduling').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_locationscheduling')[0].reset();
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
    //Add Service Location Scheduling

    // View Service Location Scheduling
    var ServiceLocationScheduling =  $('#view-locationscheduling').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/viewlocationscheduling',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'timingnvenue', name: 'timingnvenue' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'otherdetails', name: 'otherdetails' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "250px"
            },
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 5,
                width: "250px"
            }
        ]
    });

    ServiceLocationScheduling.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceLocationScheduling.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ServiceLocationScheduling.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Location Scheduling

    // Update Service Location Scheduling Status
    $(document).on('click', '.locationscheduling', function() {
        console.log('click');
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/locationscheduling-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-locationscheduling').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Service Location Scheduling Status

    // Update Service Location Scheduling Modal
    $(document).on('click', '.edit-locationscheduling', function() {
        var locationschedulingId = $(this).data('locationscheduling-id');
        $('#u_ssorg').empty();
        $('#u_sssite').empty();
        $('#u_sspattern').empty();
        $('#u_sslocation').empty();
        $('#u_ssemp').empty();
        $('#update_locationscheduling')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/locationschedulingmodal/' + locationschedulingId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            // success: function(response) {
            //     $('#ajax-loader').hide();
            //     var name = response.name;
            //     var empName = response.empName;
            //     var orgName = response.orgName;
            //     var orgID = response.orgID;
            //     var siteName = response.siteName;
            //     var siteId = response.siteId;
            //     var locationName = response.locationName;
            //     var locationId = response.locationId;
            //     var schedulePattern = response.schedulePattern;

            //     var startFormatted = moment.unix(response.startdateTime).format('MM/DD/YYYY h:mm A');
            //     var endFormatted = moment.unix(response.enddateTime).format('MM/DD/YYYY h:mm A');
            //     $('#day_time').data('daterangepicker').setStartDate(startFormatted);
            //     $('#day_time').data('daterangepicker').setEndDate(endFormatted);
            //     $('#u_ssorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
            //     $('#u_sssite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
            //     $('#u_sslocation').html("<option selected value='"+locationId+"'>" + locationName + "</option>");
            //     var Pattern = schedulePattern.replace(/\b\w/g, function(match) {
            //         return match.toUpperCase();
            //     });
            //     $('#u_sspattern').html("<option selected>" + Pattern + "</option>");

            //     if(empName != null)
            //     {
            //         $('#u_ssemp').html("<option selected value='"+response.emp+"'>" + empName + "</option>");
            //     }
            //     $('.u_service_schedule').val(name);
            //     $('.u_total_patient').val(response.TotalPatientLimit);
            //     $('.u_new_patient').val(response.NewPatientLimit);
            //     $('.u_followup_patient').val(response.FollowUpPatientLimit);
            //     $('.u_routine_patient').val(response.RoutinePatientLimit);
            //     $('.u_urgent_patient').val(response.UrgentPatientLimit);
            //     $('.u_slocation_id').val(locationschedulingId);

            //     var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
            //     $('.uedt').each(function() {
            //         var edtElement = $(this);
            //         edtElement.val(formattedDateTime);
            //     });


            //     fetchOrganizations(orgID,orgName,'#u_ssorg', function(data) {
            //         $('#u_ssorg').find('option:contains("Loading...")').remove();
            //         $.each(data, function(key, value) {
            //             $('#u_ssorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
            //         });
            //     });

            //     fetchSites(orgID, '#u_sssite', function(data) {
            //         if (data.length > 0) {
            //             $.each(data, function(key, value) {
            //                 $('#u_sssite').append('<option value="' + value.id + '">' + value.name + '</option>');
            //             });
            //         }
            //         else {
            //             Swal.fire({
            //                 text: 'Sites are not available for selected Organization',
            //                 icon: 'error',
            //                 confirmButtonText: 'OK'
            //             }).then((result) => {
            //                 if (result.isConfirmed) {
            //                     $('#edit-locationscheduling').modal('hide');
            //                 }
            //             });
            //         }
            //     }, function(error) {
            //         console.log(error);
            //     },siteId);

            //     $('#u_ssorg').off('change').on('change', function() {
            //         $('#u_sssite').empty();
            //         var organizationId = $(this).val();
            //         fetchSites(organizationId, '#u_sssite', function(data) {
            //             if (data.length > 0) {
            //                 $('#u_sssite').html("<option selected disabled value=''>Select Site</option>");
            //                 $.each(data, function(key, value) {
            //                     $('#u_sssite').append('<option value="' + value.id + '">' + value.name + '</option>');
            //                 });
            //             }
            //             else {
            //                 Swal.fire({
            //                     text: 'Sites are not available for selected Organization',
            //                     icon: 'error',
            //                     confirmButtonText: 'OK'
            //                 }).then((result) => {
            //                     if (result.isConfirmed) {
            //                         $('#edit-locationscheduling').modal('hide');
            //                     }
            //                 });
            //             }
            //         }, function(error) {
            //             console.log(error);
            //         });
            //         fetchServiceLocations(organizationId, '#u_sslocation', function(data) {
            //             if (data.length > 0) {
            //                 $('#u_sslocation').empty();
            //                 $('#u_sslocation').append('<option selected disabled value="">Select Service Location</option>');
            //                 $.each(data, function(key, value) {
            //                     $('#u_sslocation').append('<option value="' + value.id + '">' + value.name + '</option>');
            //                 });
            //                 $('#u_sslocation').find('option:contains("Loading...")').remove();
            //                 $('#u_sslocation').prop('disabled', false);
            //             }
            //             else{
            //                 Swal.fire({
            //                     text: 'Service Locations are not available for selected Organization',
            //                     icon: 'error',
            //                     confirmButtonText: 'OK',
            //                     allowOutsideClick: false
            //                 }).then((result) => {
            //                     if (result.isConfirmed) {
            //                         $('#edit-locationscheduling').modal('hide');
            //                     }
            //                 });

            //             }
            //         }, function(error) {
            //             console.log(error);
            //         });
            //     });

            //     fetchServiceLocations(orgID, '#u_sslocation', function(data) {
            //         if (data.length > 0) {
            //             $.each(data, function(key, value) {
            //                 if(locationId != value.id)
            //                 {
            //                     $('#u_sslocation').append('<option value="' + value.id + '">' + value.name + '</option>');
            //                 }
            //             });
            //         }
            //     }, function(error) {
            //         console.log(error);
            //     });

            //     var SchedulePatternOptions = [
            //         {value: "none", label: "None"},
            //         {value: "daily", label: "Daily"},
            //         {value: "weekly", label: "Weekly"},
            //         {value: "monday to saturday", label: "Monday To Saturday"}
            //     ];

            //     var filteredOptions = SchedulePatternOptions.filter(function(option) {
            //         return option.value !== schedulePattern;
            //     });

            //     filteredOptions.forEach(function(option) {
            //         $("#u_sspattern").append(new Option(option.label, option.value));
            //     });

            //     if(response.emp != null)
            //     {
            //         $('#u_ssemp').append('<option value="">N/A</option>');
            //         fetchEmployees(response.emp, '#u_ssemp', function(data) {
            //             $.each(data, function(key, value) {
            //                 $('#u_ssemp').append('<option value="' + value.id + '">' + value.name + '</option>');
            //             });
            //         }, function(error) {
            //             console.log(error);
            //         });
            //     }
            //     else{
            //         $('#u_ssemp').append('<option value="">N/A</option>');
            //         fetchEmployees(0, '#u_ssemp', function(data) {
            //             $.each(data, function(key, value) {
            //                 $('#u_ssemp').append('<option value="' + value.id + '">' + value.name + '</option>');
            //             });

            //         }, function(error) {
            //             console.log(error);
            //         });
            //     }



            //     $('#edit-locationscheduling').modal('show');
            // },
            success: function(response) {
                $('#ajax-loader').hide();

                $('#u_ssorg').html("<option selected value='"+response.orgID+"'>" + response.orgName + "</option>");
                $('#u_sssite').html("<option selected value='"+response.siteId+"'>" + response.siteName + "</option>");
                fetchSites(response.orgID, '#u_sssite', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(value.id != response.siteId)
                            {
                                $('#u_sssite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);

                $('#u_sslocation').html("<option selected value='"+response.locationId+"'>" + response.locationName + "</option>");
                fetchActiveSL(response.siteId, '#u_sslocation', false, false, function(data) {
                    $.each(data, function(key, value) {
                        if(value.location_id != response.locationId)
                        {
                            $('#u_sslocation').append('<option value="' + value.location_id + '">' + value.name + '</option>');
                        }
                    });
                });
                SiteChangeActivatedServiceLocation('#u_sssite','#u_sslocation', '#update_locationscheduling',false, false );

                // Start and End time
                $('#u_start_time').val(response.startTime);
                $('#u_end_time').val(response.endTime);

                // Set schedule pattern (comma-separated days)
                $('#u_sspattern').val(response.schedulePattern);

                // Pre-check checkboxes in modal
                // let days = response.schedulePattern.split(',').map(d => d.trim());
                // $('.day-check').each(function () {
                //     $(this).prop('checked', days.includes($(this).val()));
                // });

                let days = response.schedulePattern.split(',').map(d => d.trim().toLowerCase());

                $('.day-check').each(function () {
                    $(this).prop('checked', days.includes($(this).val().toLowerCase()));
                });

                // Fill other fields
                $('.u_service_schedule').val(response.name);
                $('.u_total_patient').val(response.TotalPatientLimit);
                $('.u_new_patient').val(response.NewPatientLimit);
                $('.u_followup_patient').val(response.FollowUpPatientLimit);
                $('.u_routine_patient').val(response.RoutinePatientLimit);
                $('.u_urgent_patient').val(response.UrgentPatientLimit);
                $('.u_slocation_id').val(response.id);

                $('.uedt').val(response.effective_timestamp);

                // Handle emp dropdown
                if (response.empName != null) {
                    $('#u_ssemp').html("<option selected value='"+response.emp+"'>" + response.empName + "</option>");
                } else {
                    $('#u_ssemp').html('<option value="">N/A</option>');
                }

                $('#edit-locationscheduling').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Service Location Scheduling Modal

    //Update Service Location Scheduling
    $('#update_locationscheduling').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('.u_slocation_id').val();
        var url = '/services/update-locationschedule/' + Id;
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
                        confirmButtonText: 'OK',
                        allowOutsideClick:false
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
                            $('#edit-locationscheduling').modal('hide');
                            $('#view-locationscheduling').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_locationscheduling')[0].reset();
                            // $('.text-danger').hide();
                        }
                        // $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update Service Location Scheduling

    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack')
                .css('z-index', zIndex - 1)
                .addClass('modal-stack');
        }, 0);
    });


});
