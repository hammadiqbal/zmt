// Service Rates
$(document).ready(function() {
    $('#srate_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
    var orgId = $('#srate_org').val();
    if(orgId)
    {
        fetchOrganizationSites(orgId, '#srate_site', function(data) {
            $('#srate_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
            $.each(data, function(key, value) {
                $('#srate_site').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        });
    }
    else{
        OrgChangeSites('#srate_org', '#srate_site', '#fetchservicerates');
    }

    // Show Activated Services Table
    $('#fetchservicerates').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        ShowActivatedServiceData(formData);
    });
    // Show Activated Services Table

    // Add Service Rates Modal
    $(document).on('click', '.add-servicerate', function() {
        var servicemodeId = $(this).data('servicemode-id');
        var activatedserviceId = $(this).data('activatedservice-id');
        var siteId = $(this).data('site-id');
        $('input[name="mode_id"]').val(servicemodeId);
        $('input[name="activated_id"]').val(activatedserviceId);
        $('input[name="site_id"]').val(siteId);

        $('#add-servicerate').modal('show');
    });
    // Add Service Rates Modal

    //Add Service Rates
    $('#add_servicerates').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        var siteId = '';

        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
            {
                var FieldName = field.name;
                var FieldID = '#'+FieldName + "_error";

                $(FieldID).text("This field is required");
                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                $( 'input[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                resp = false;
            }

            if (field.name === 'site_id') {
                siteId = field.value;
            }
        });
        if(resp != false)
        {
            $.ajax({
                url: "/finance/addservicerates",
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
                    if (fieldName == 'info')
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
                                    ShowActivatedServiceData({ srate_site: siteId }); 
                                    $('#add_servicerates')[0].reset();
                                    $('#add-servicerate').modal('hide');

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
    //Add Service Rates

    //Update Service Rates Modal
    $(document).on('click', '.edit-servicerates', function() {
        var poId = $(this).data('servicerate-id');
        var url = '/finance/updateservicerates/' + poId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#u_servicerate_id').val(response.id);
                $('#u_rate_unitCost').val(response.unitCost);
                $('#u_rate_billedAmount').val(response.billedAmount);
                $('#siteId').val(response.siteId);
                $('#edit-servicerates').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });

    });
    //Update Service Rates Modal

    //Update Service Rates
    $('#u_servicerates').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id;
        var siteId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_servicerate_id') {
                Id = formData[i].value;
            } else if (formData[i].name === 'siteId') {
                siteId = formData[i].value;
            }
            if (Id && siteId) break;
        }
        var url = '/finance/update-servicerates/' + Id;
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
                else if (fieldName == 'info')
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
                            $('#edit-servicerates').modal('hide');
                            ShowActivatedServiceData({ srate_site: siteId }); 
                            $('#u_servicerates')[0].reset();
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
    //Update Service Rates
});
// Service Rates