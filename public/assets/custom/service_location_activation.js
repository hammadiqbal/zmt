$(document).ready(function() {
        
    //Open Service Location Activation Setup
    $('#sl_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#sl_org', '#sl_site', '#activate_cc');

    $(document).on('click', '.sl_activation', function() {
        $('#sl_value').prop('disabled', true);  
        $('#sl_value').val('');  
        $('#siteselect').show(); 
        var orgId = $('#sl_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#sl_site', function(data) {
                $('#sl_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#sl_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        $('#sl_activation').modal('show');
    });

    FetchNotActivatedServiceLocation('#sl_site', '#sl_value', '#activate_sl');

    $(document).on('change', 'input[name="selectedSL[]"]', function() {
        updateHiddenServiceLocation();
    });
    //Open Service Location Activation Setup

    //Activate Service Location
    $('#activate_sl').submit(function(e) {
        e.preventDefault(); 
        updateHiddenServiceLocation();
        var data = SerializeForm(this);

        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'sl_name[]') {
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
                url: "/services/activatesl",
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
                                $('#sl_activation').modal('hide');
                                window.location.reload();
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
                                $('#activate_sl').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_sl')[0].reset();
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
    //Activate Service Location

    // View ActivatedServiceLocation
    var ActivatedSLData =  $('#view-slactivation').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/getactivatesldata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "400px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    ActivatedSLData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedSLData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedSLData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View ActivatedServiceLocation

    // Update ActivateServiceLocation Status
    $(document).on('click', '.activatesl', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/services/activatesl-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-slactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update ActivateServiceLocation Status

    // Update ActivateServiceLocation Modal
    $(document).on('click', '.edit-activatesl', function() {
        var activateslId = $(this).data('activatesl-id');
        $('#u_slsite').empty();
        $('#ua_servicelocation').empty();
        $('#u_slorg').empty();
        $('#update_slactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/activateslmodal/' + activateslId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var locationName = response.locationName;
                var locationID = response.locationID;
                $('.u_sl_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                $('#u_slorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                fetchOrganizations(orgID,orgName,'#u_slorg', function(data) {
                    $('#u_slorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_slorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                if (orgID) {
                    $('#u_slsite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                    fetchSites(orgID, '#u_slsite', function(data) {
                        $.each(data, function(key, value) {
                            $('#u_slsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }, function(error) {
                        console.log(error);
                    },response.siteId);

                    OrgChangeSites('#u_slorg', '#u_slsite', '#update_slactivation');
                }
                $('#ua_servicelocation').html("<option selected value='"+locationID+"'>" + locationName + "</option>");

                fetchInActiveSL(siteId, locationID, '#ua_servicelocation', function(data) {
                    $('#ua_servicelocation').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#ua_servicelocation').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
                SiteChangeServiceLocation('#u_slsite','#ua_servicelocation', '#update_slactivation');

                $('#edit-slactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update ActivateServiceLocation Modal

    //Update ActivateServiceLocation
    $('#update_slactivation').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('.u_sl_id').val();
        var url = '/services/update-activatesl/' + Id;
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
                            $('#edit-slactivation').modal('hide');
                            $('#view-slactivation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_slactivation')[0].reset();
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
    //Update ActivateServiceLocation
});