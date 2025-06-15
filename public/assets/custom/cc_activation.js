$(document).ready(function() {
    //Open Activate CC Setup
    $('#cc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#cc_org', '#cc_site', '#activate_cc');

    $(document).on('click', '.cc_activation', function() {
        $('#cc_value').prop('disabled', true);  
        $('#cc_value').val('');  
        $('#siteselect').show(); 
        var orgId = $('#cc_org').val();
        if(orgId)
            {
                fetchOrganizationSites(orgId, '#cc_site', function(data) {
                    $('#cc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('#cc_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
        $('#cc_activation').modal('show');
    });

    FetchNotActivatedCC('#cc_site', '#cc_value', '#activate_cc');
    //Open Activate CC Setup

    //Activate Cost Center
    $(document).on('change', 'input[name="selectedCC[]"]', function() {
        updateHiddenCostCenter();
    });

    $('#activate_cc').submit(function(e) {
        e.preventDefault(); 
        updateHiddenCostCenter();
        var data = SerializeForm(this);

        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '' || field.value == null) && field.name != 'cc_name[]') {
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
                url: "/costcenter/activatecc",
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
                                $('#cc_activation').modal('hide');
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
                                $('#activate_cc').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_cc')[0].reset();
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
    //Activate Cost Center

    // View ActivatedCCData
    var ActivatedCCData =  $('#view-ccactivation').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/costcenter/getactivateccdata',
            data: function (d) {
                d.site = $('#fb_site').val();  
                d.costcenter = $('#fb_cc').val();  
                d.cc_type = $('#fb_cct').val();  
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'ccName', name: 'ccName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'cctypeName', name: 'cctypeName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "200px"
            },
            {
                targets: 6,
                width: "250px"
            }
        ]
    });

    
    $('#fb_site,#fb_cc,#fb_cct').on('change', function () {
        ActivatedCCData.ajax.reload();  
    });

    $('.clearFilter').on('click', function () {
        $('#fb_site').val($('#fb_site option:first').val()).change();
        $('#fb_cc').val($('#fb_cc option:first').val()).change();
        $('#fb_cct').val($('#fb_cct option:first').val()).change();
        ActivatedCCData.ajax.reload();   
    });

    ActivatedCCData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedCCData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedCCData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View ActivatedCCData

    // Update ActivateCC Status
    $(document).on('click', '.activatecc', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/costcenter/update-activatecc',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-ccactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update ActivateCC Status

    // Update ActivatedCC Modal
    $(document).on('click', '.edit-activatecc', function() {
        var activateccId = $(this).data('activatecc-id');
        $('#u_ccsite').empty();
        $('#u_costcenter').empty();
        $('#u_ccorg').empty();
        $('#update_ccactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/costcenter/updateactivatecc/' + activateccId;
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
                var ccName = response.ccName;
                var ccID = response.ccID;
                $('#u_ccorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_ccsite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_costcenter').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                $('.u_acc_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_ccorg', function(data) {
                    $('#u_ccorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_ccorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                if (orgID) {
                    $.ajax({
                        url: 'costcenter/getselectedcc',
                        type: 'GET',
                        data: {
                            ccID: ccID,
                            siteId: siteId,
                        },
                        beforeSend: function() {
                            $('#u_costcenter').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_costcenter').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_costcenter').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });


                    fetchSites(orgID, '#u_ccsite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_ccsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-ccactivation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    },siteId);


                    $('#u_ccorg').off('change').on('change', function() {
                        $('#u_ccsite').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_ccsite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_ccsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-ccactivation').modal('hide');
                                    }
                                });
                            }

                        }, function(error) {
                            console.log(error);
                        });


                    });
                }

                $('#edit-ccactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update ActivateCC Modal

    //Update ActivateCC
    $('#update_ccactivation').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('.u_acc_id').val();
        var url = '/update-activatecc/' + Id;
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
                            $('#edit-ccactivation').modal('hide');
                            $('#view-ccactivation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_ccactivation')[0].reset();
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
    //Update ActivateCC

});