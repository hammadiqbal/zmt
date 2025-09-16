// Stock Monitoring Setup
$(document).ready(function() {
    //Open Stock Monitoring Setup
    $(document).on('click', '.add-stockmonitoring', function() {
        var orgId = $('#sm_org').val();
        if(orgId)
        {
            $('#sm_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#sm_org', function(data) {
                $('#sm_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#sm_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#sm_site').html("<option selected disabled value=''>Select Site</option>");
            fetchOrganizationSites(orgId, '#sm_site', function(data) {
                $.each(data, function(key, value) {
                    $('#sm_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('#sm_generic').html("<option selected disabled value=''>Select Item Generic</option>");
            fetchOrganizationItemGeneric(orgId, '#sm_generic', function(data) {
                $.each(data, function(key, value) {
                    $('#sm_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

        }
        else{
            $('#sm_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#sm_org', '#sm_site', '#add_stockmonitoring');

            $('#sm_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#sm_org', '#sm_generic', '#add_stockmonitoring');
        }
        $('#sm_location').html("<option selected disabled value=''>Select Inventory Location</option>").prop('disabled', true);
        SiteChangeActivatedServiceLocation('#sm_site','#sm_location', '#add_stockmonitoring',true, false);

        $('#sm_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
        GenericChangeBrand('#sm_generic', '#sm_brand', '#add_stockmonitoring');

        $('#add-stockmonitoring').modal('show');
    });
    //Open Stock Monitoring Setup

    //Add Stock Monitoring Method
    $('#add_stockmonitoring').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
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
                url: "/inventory/addstockmonitoring",
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
                                $('#add-stockmonitoring').modal('hide');
                                $('#view-stockmonitoring').DataTable().ajax.reload();
                                $('#add_stockmonitoring')[0].reset();
                                $('#add_stockmonitoring').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
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
                                $('#add_stockmonitoring').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_stockmonitoring')[0].reset();
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
    //Add Stock Monitoring Method

    // View Stock Monitoring
    var viewStockMonitoring =  $('#view-stockmonitoring').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/inventory/viewstockmonitoring',
            data: function (d) {
                d.site = $('#fb_site').val();
                d.generic = $('#fb_generic').val();
                d.brand = $('#fb_brand').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'stock_details', name: 'stock_details' },
            { data: 'contact_details', name: 'contact_details' },
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
                width: "350px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });
    $('#fb_site,#fb_generic,#fb_brand').on('change', function () {
        viewStockMonitoring.ajax.reload();
    });

    $('.clearFilter').on('click', function () {
        $('#fb_site,#fb_generic,#fb_brand').each(function() {
            $(this).val($(this).find('option:first').val()).change();
        });
        viewStockMonitoring.ajax.reload();
    });
    viewStockMonitoring.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewStockMonitoring.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewStockMonitoring .on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Stock Monitoring

    // Update Stock Monitoring  Status
    $(document).on('click', '.sm_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/sm-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-stockmonitoring').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Stock Monitoring  Status

    //Update Stock Monitoring Modal
    $(document).on('click', '.edit-sm', function() {
        var smId = $(this).data('sm-id');
        var url = '/inventory/updatestockmonitoring/' + smId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_sm-id').val(response.id);
                $('#u_sm_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_sm_org', function(data) {
                    $('#u_sm_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_sm_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                $('#u_sm_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_sm_site', function(data) {
                    $.each(data, function(key, value) {
                        $('#u_sm_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }, function(error) {
                    console.log(error);
                },response.siteId);
                OrgChangeSites('#u_sm_org', '#u_sm_site', '#u_stockmonitoring');

                $('#u_sm_generic').html("<option selected value="+ response.genericId +">" + response.genericName + "</option>");
                fetchOrganizationItemGeneric(response.orgId, '#u_sm_generic', function(data) {
                    $.each(data, function(key, value) {
                        if(response.genericId != value.id)
                        {
                            $('#u_sm_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeInventoryGeneric('#u_sm_org', '#u_sm_generic', '#u_stockmonitoring');
                if(response.brandId){
                    $('#u_sm_brand').html("<option selected value="+ response.brandId +">" + response.brandName + "</option>");
                }
                else{
                    $('#u_sm_brand').html("<option selected disabled value=''>Select Brand</option>");
                }
                fetchGenericItemBrand(response.genericId, '#u_sm_brand', function(data) {
                    $.each(data, function(key, value) {
                        if(response.brandId != value.id)
                        {
                            $('#u_sm_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                GenericChangeBrand('#u_sm_generic', '#u_sm_brand', '#u_stockmonitoring');

                $('#u_sm_servicelocation').html("<option selected value="+ response.serviceLocationId +">" + response.serviceLocation + "</option>");
                fetchActiveSL(response.siteId, '#u_sm_servicelocation',true, false, function(data) {
                    $.each(data, function(key, value) {
                        if(value.location_id != response.serviceLocationId)
                        {
                            console.log(response.serviceLocationId,value.name,value.id);
                            $('#u_sm_servicelocation').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                SiteChangeActivatedServiceLocation('#u_sm_site','#u_sm_servicelocation', '#u_stockmonitoring',true, false);


                $('#u_sm_min_stock').val(response.minStock);
                $('#u_sm_max_stock').val(response.maxStock);
                $('#u_sm_monthly_consumption').val(response.monthlyConsumption);
                $('#u_sm_min_reorder').val(response.minReorder);
                $('#u_sm_primary_email').val(response.PrimaryEmail);
                $('#u_sm_secondary_email').val(response.secondaryEmail);

                $('#edit-sm').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Stock Monitoring Modal

    //Update Stock Monitoring Method
    $('#u_stockmonitoring').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var smId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_sm-id') {
                smId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-stockmonitoring/' + smId;
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
                            $('#edit-sm').modal('hide');
                            $('#view-stockmonitoring').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_stockmonitoring')[0].reset();
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
    //Update Stock Monitoring Method
});
// Stock Monitoring Setup
