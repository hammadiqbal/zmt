$(document).ready(function() {
    //Open Add Requisition For Other Transactions
    $(document).on('click', '.add-reqmaterialtransfer', function() {
        $('.duplicate:not(:first)').remove();
        $('.text-danger').show();
        $('.s_data,.d_data').hide();
        var orgId = $('#rmt_org').val();
        if(!orgId)
        {
            $('#rmt_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#rmt_org', function(data) {
                $('#rmt_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#rmt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#rmt_source_site').html("<option selected disabled value=''>Select Source Site</option>").prop('disabled',true);
            OrgChangeSites('#rmt_org', '#rmt_source_site', '#add_reqmaterialtransfer', 'rmtSourceSite');

            $('#rmt_destination_site').html("<option selected disabled value=''>Select Destination Site</option>").prop('disabled',true);
            OrgChangeSites('#rmt_org', '#rmt_destination_site', '#add_reqmaterialtransfer', 'rmtDestinationSite');

            $('.rmt_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#rmt_org', '.rmt_itemgeneric', '#add_reqmaterialtransfer');

            $('#rmt_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
            SiteChangeMaterialManagementTransactionTypes('#rmt_org','#rmt_org', '#rmt_transactiontype', '#add_reqmaterialtransfer','material_transfer','y');

        }
        else{
            console.log(orgId);
            fetchOrganizationSites(orgId, '#rmt_source_site', function(data) {
                $('#rmt_source_site').html("<option selected disabled value=''>Select Source Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#rmt_source_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchOrganizationSites(orgId, '#rmt_destination_site', function(data) {
                $('#rmt_destination_site').html("<option selected disabled value=''>Select Destination Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#rmt_destination_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

             $('.rmt_itemgeneric').html("<option selected disabled value=''>Select Item Generic</option>");
            fetchOrganizationItemGeneric(orgId, '.rmt_itemgeneric', function(data) {
                $.each(data, function(key, value) {
                    $('.rmt_itemgeneric').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('#rmt_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',false);
            fetchMaterialManagementTransactionTypes(orgId, '#rmt_transactiontype','material_transfer','y', function(data) {
                $.each(data, function(key, value) {
                    $('#rmt_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }

        $(document).off('change', '#rmt_transactiontype').on('change', '#rmt_transactiontype', function() {
            let transactionTypeID = $(this).val();
            $('#rmt_source_site').prop('selectedIndex', 0).trigger('change');
            $('#rmt_destination_site').prop('selectedIndex', 0).trigger('change');
            // First call without site to learn Source/Destination types; we'll fetch lists per site change below

            $.ajax({
                url: 'inventory/gettransactiontypeim',
                type: 'GET',
                data: {
                    transactionTypeId: transactionTypeID,
                    siteId: null,
                    transactionType: 'requisition'
                },
                success: function(resp) {
                    if (resp.success === false) {
                        Swal.fire({
                            text: resp.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-reqmaterialtransfer').modal('hide');
                            }
                        });
                    }
                    else if (!resp.Source && !resp.Destination) {
                        Swal.fire({
                            icon: 'error',
                            text: 'No transaction type data found.'
                        });
                        return;
                    }

                    // Persist actions for later decisions (e.g., which site to check batches against)
                    $('#add_reqmaterialtransfer')
                        .data('sourceAction', (resp.source_action || '').toLowerCase())
                        .data('destinationAction', (resp.destination_action || '').toLowerCase());

                    let sourceType = (resp.Source || '').toLowerCase();
                    if (sourceType.includes('location')) {
                        $('.s_data').show();
                        $('#source_applicable').val('1');
                        // Wait for source site selection to fetch location list
                        $('#rmt_source_location')
                            .empty()
                            .append('<option selected disabled value="">Select Source</option>')
                            .prop('disabled', true);
                    } else {
                        $('.s_data').hide();
                        $('#source_applicable').val('0');
                        $('#rmt_source_location')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>')
                            .prop('disabled', true);
                    }

                    let destType = (resp.Destination || '').toLowerCase();
                    if (destType.includes('location')) {
                        $('.d_data').show();
                        $('#destination_applicable').val('1');
                        // Wait for destination site selection to fetch location list
                        $('#rmt_destination_location')
                            .empty()
                            .append('<option selected disabled value="">Select Destination</option>')
                            .prop('disabled', true);
                    } else {
                        $('.d_data').hide();
                        $('#destination_applicable').val('0');
                        $('#rmt_destination_location')
                            .empty()
                            .append('<option selected disabled value="">Select Destination</option>')
                            .prop('disabled', true);
                    }

                    // When source site changes, fetch locations constrained to that site
                    $(document).off('change.rmtSourceSite').on('change.rmtSourceSite', '#rmt_source_site', function() {
                        if (!sourceType.includes('location')) { return; }
                        let selectedSiteId = $(this).val();
                        if (!selectedSiteId) { return; }
                        $.ajax({
                            url: 'inventory/gettransactiontypeim',
                            type: 'GET',
                            data: { 
                                transactionTypeId: transactionTypeID, 
                                siteId: selectedSiteId,
                                transactionType: 'requisition'
                            },
                            success: function(r) {
                                $('#rmt_source_location')
                                    .empty()
                                    .append('<option selected disabled value="">Select Source</option>');
                                if (r.sourceData && r.sourceData.length > 0) {
                                    r.sourceData.forEach(function(item) {
                                        let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                        $('#rmt_source_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                                    });
                                    $('#rmt_source_location').prop('disabled', false);
                                } else {
                                    $('#rmt_source_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                                }
                            }
                        });
                    });

                    // When destination site changes, fetch locations constrained to that site
                    $(document).off('change.rmtDestinationSite').on('change.rmtDestinationSite', '#rmt_destination_site', function() {
                        if (!destType.includes('location')) { return; }
                        let selectedSiteId = $(this).val();
                        if (!selectedSiteId) { return; }
                        $.ajax({
                            url: 'inventory/gettransactiontypeim',
                            type: 'GET',
                            data: { 
                                transactionTypeId: transactionTypeID, 
                                siteId: selectedSiteId,
                                transactionType: 'requisition'
                            },
                            success: function(r) {
                                $('#rmt_destination_location')
                                    .empty()
                                    .append('<option selected disabled value="">Select Destination</option>');
                                if (r.destinationData && r.destinationData.length > 0) {
                                    r.destinationData.forEach(function(item) {
                                        let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                        $('#rmt_destination_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                                    });
                                    $('#rmt_destination_location').prop('disabled', false);
                                } else {
                                    $('#rmt_destination_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                                }
                            }
                        });
                    });

                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });

        // $('#rmt_source_location').html("<option selected disabled value=''>Select Source Location</option>").prop('disabled', true);
        // SiteChangeActivatedServiceLocation('#rmt_source_site','#rmt_source_location', '#add_reqmaterialtransfer',true );

        // $('#rmt_destination_location').html("<option selected disabled value=''>Select Destination Location</option>").prop('disabled', true);
        // SiteChangeActivatedServiceLocation('#rmt_destination_site','#rmt_destination_location', '#add_reqmaterialtransfer',true );


        $('#add-reqmaterialtransfer').modal('show');
    });
    //Open Add Requisition For Other Transactions

    // Add Requisition For Other Transactions
    $('#add_reqmaterialtransfer').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');
                if (!value || value === "" || (elem.is('select') && value === null)) {
                    errorField.text("This field is required");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').addClass('requirefield');
                        elem.on('select2:open', function() {
                            errorField.text("");
                            elem.next('.select2-container').find('.select2-selection').removeClass("requirefield");
                        });
                    }
                    else {
                        elem.addClass('requirefield');
                        elem.focus(function() {
                            errorField.text("");
                            elem.removeClass("requirefield");
                        });
                    }
                    resp = false;
                } else {
                    errorField.text("");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').removeClass('requirefield');
                    } else {
                        elem.removeClass('requirefield');
                    }
                }
            });
        });

        var excludedFields = ['rmt_remarks'];
        if ($('.s_data').is(':hidden')) {
            excludedFields.push('rmt_source_site');
            excludedFields.push('rmt_source_location');
        }
        if ($('.d_data').is(':hidden')) {
            excludedFields.push('rmt_destination_site');
            excludedFields.push('rmt_destination_location');
        }

        $(data).each(function(i, field){
            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true;
            }
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                var FieldName = FieldName.replace('[]', '');
                console.log(FieldName);

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
        console.log(resp);
        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addreqmaterialtransfer",
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
                                $('#add-reqmaterialtransfer').modal('hide');
                                $('#view-reqmaterialtransfer').DataTable().ajax.reload();
                                $('#add_reqmaterialtransfer')[0].reset();
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
                                $('#add-reqmaterialtransfer').modal('hide');
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
    //Add Requisition For Other Transactions

    // View Requisition For Other Transactions Data
    var viewRequisitionMaterialTransfer =  $('#view-reqmaterialtransfer').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/reqmaterialtransfer',
        order: [[1, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'requisition_detail', name: 'requisition_detail' },
            { data: 'InventoryDetails', name: 'InventoryDetails' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "400px"
            },
              {
                targets: 2,
                width: "300px"
            },
            {
                targets: 4,
                width: "300px"
            }
        ]
    });

    viewRequisitionMaterialTransfer.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewRequisitionMaterialTransfer.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewRequisitionMaterialTransfer.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Requisition For Other Transactions Data

    // Update Requisition For Other Transactions Status
    $(document).on('click', '.rmt_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/reqmaterialtransfer-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-reqmaterialtransfer').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Requisition For Other Transactions Status

    //Update Requisition For Other Transactions Modal
    $(document).on('click', '.edit-reqmaterialtransfer', function() {
        var RequisitionId = $(this).data('rmt-id');
        $('.text-danger').show();
        var url = '/inventory/updatereqmaterialtransfer/' + RequisitionId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('.u_rmt-id').val(response.id);
                $('#u_rmt_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_rmt_org', function(data) {
                    $('#u_rmt_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_rmt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                $('#u_rmt_source_site').html("<option selected value='"+response.sourcesiteId+"'>" + response.sourceSite + "</option>");
                fetchSites(response.orgId, '#u_rmt_source_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_rmt_source_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.sourcesiteId);
                OrgChangeSites('#u_rmt_org', '#u_rmt_source_site', '#update_reqmaterialtransfer', 'updateSourceSite');



                $('#u_rmt_destination_site').html("<option selected value='"+response.destinationsiteId+"'>" + response.destinationSite + "</option>");
                fetchSites(response.orgId, '#u_rmt_destination_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_rmt_destination_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.destinationsiteId);
                OrgChangeSites('#u_rmt_org', '#u_rmt_destination_site', '#update_reqmaterialtransfer', 'updateDestinationSite');


                $('#u_rmt_source_location').html("<option selected value="+ response.SourceLocationId +">" + response.SourcelocationName + "</option>");
                // Use the same route as add modal to respect employee location filtering
                $.ajax({
                    url: 'inventory/gettransactiontypeim',
                    type: 'GET',
                    data: { 
                        transactionTypeId: response.transactionTypeId, 
                        siteId: response.sourcesiteId,
                        transactionType: 'requisition'
                    },
                    success: function(resp) {
                        if (resp.sourceData && resp.sourceData.length > 0) {
                            resp.sourceData.forEach(function(item) {
                                if (item.id != response.SourceLocationId) {
                                    $('#u_rmt_source_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                }
                            });
                        }
                    }
                });
                // SiteChangeActivatedServiceLocation('#u_rmt_source_site','#u_rmt_source_location', '#update_reqmaterialtransfer',true,true );


                $('#u_rmt_destination_location').html("<option selected value="+ response.DestinationLocationId +">" + response.DestinationlocationName + "</option>");
                // Use the same route as add modal to respect employee location filtering
                $.ajax({
                    url: 'inventory/gettransactiontypeim',
                    type: 'GET',
                    data: { 
                        transactionTypeId: response.transactionTypeId, 
                        siteId: response.destinationsiteId,
                        transactionType: 'requisition'
                    },
                    success: function(resp) {
                        if (resp.destinationData && resp.destinationData.length > 0) {
                            resp.destinationData.forEach(function(item) {
                                if (item.id != response.DestinationLocationId) {
                                    $('#u_rmt_destination_location').append('<option value="' + item.id + '">' + item.name + '</option>');
                                }
                            });
                        }
                    }
                });
                // SiteChangeActivatedServiceLocation('#u_rmt_destination_site','#u_rmt_destination_location', '#update_reqmaterialtransfer',true, true );



                $('#u_rmt_transactiontype').html("<option selected value="+ response.transactionTypeId +">" + response.transactionType + "</option>");
                fetchMaterialManagementTransactionTypes(response.orgId, '#u_rmt_transactiontype','material_transfer','y', function(data) {
                    $.each(data, function(key, value) {
                        if(value.id != response.transactionTypeId)
                        {
                            $('#u_rmt_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                let selectedTransactionType = response.transactionTypeId;
                $(document).off('change', '#u_rmt_transactiontype').on('change', '#u_rmt_transactiontype', function() {
                    let transactionTypeID = $(this).val();
                    selectedTransactionType = $(this).val();
                    $('#u_rmt_source_site').prop('selectedIndex', 0).trigger('change');
                    $('#u_rmt_destination_site').prop('selectedIndex', 0).trigger('change');
                });


                $(document).off('change.urmtSourceSite').on('change.urmtSourceSite', '#u_rmt_source_site', function() {
                    let selectedSiteId = $(this).val();
                    if (!selectedSiteId) { return; }
                    $.ajax({
                        url: 'inventory/gettransactiontypeim',
                        type: 'GET',
                        data: { 
                            transactionTypeId: selectedTransactionType, 
                            siteId: selectedSiteId,
                            transactionType: 'requisition'
                        },
                        success: function(r) {
                            $('#u_rmt_source_location')
                                .empty()
                                .append('<option selected disabled value="">Select Source</option>');
                            if (r.sourceData && r.sourceData.length > 0) {
                                r.sourceData.forEach(function(item) {
                                    let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                    $('#u_rmt_source_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                                });
                                $('#u_rmt_source_location').prop('disabled', false);
                            } else {
                                $('#u_rmt_source_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                            }
                        }
                    });
                });

                $(document).off('change.urmtDestinationSite').on('change.urmtDestinationSite', '#u_rmt_destination_location', function() {
                    let selectedSiteId = $(this).val();
                    if (!selectedSiteId) { return; }
                    $.ajax({
                        url: 'inventory/gettransactiontypeim',
                        type: 'GET',
                        data: { 
                            transactionTypeId: selectedTransactionType, 
                            siteId: selectedSiteId,
                            transactionType: 'requisition'
                        },
                        success: function(r) {
                            $('#u_rmt_destination_location')
                                .empty()
                                .append('<option selected disabled value="">Select Destination</option>');
                            if (r.destinationData && r.destinationData.length > 0) {
                                r.destinationData.forEach(function(item) {
                                    let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                    $('#u_rmt_destination_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                                });
                                $('#u_rmt_destination_location').prop('disabled', false);
                            } else {
                                $('#u_rmt_destination_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                            }
                        }
                    });
                });

                // $(document).off('change', '#u_rmt_transactiontype').on('change', '#u_rmt_transactiontype', function() {
                //     let transactionTypeID = $(this).val();
                //     // reset locations
                //     // $('#u_rmt_source_location')
                //     // .append('<option selected disabled value="">Select Source</option>')
                //     // .prop('disabled', false);
                //     // $('#u_rmt_destination_location')
                //     //     .empty()
                //     //     .append('<option selected disabled value="">Select Destination</option>')
                //     //     .prop('disabled', true);
                //     // reset sites as well (select first option, don't append)
                //     //  $('#u_rmt_source_site')
                //     //     .prepend('<option selected disabled value="">Select Site</option>')
                //     //     .prop('disabled', false);
                //     $('#u_rmt_source_site').prop('selectedIndex', 0).trigger('change');
                //     $('#u_rmt_destination_site').prop('selectedIndex', 0).trigger('change');

                //     // Inspect types first
                //     $.ajax({
                //         url: 'inventory/gettransactiontypeim',
                //         type: 'GET',
                //         data: {
                //             transactionTypeId: transactionTypeID,
                //             siteId: null,
                //             transactionType: 'requisition'
                //         },
                //         success: function(resp) {
                //             let sourceType = (resp.Source || '').toLowerCase();
                //             let destType = (resp.Destination || '').toLowerCase();

                //             // If source uses inventory location and site already selected, fetch list for that site
                //             if (sourceType.includes('location')) {
                //                 let sSite = $('#u_rmt_source_site').val();
                //                 if (sSite) {
                //                     $.ajax({
                //                         url: 'inventory/gettransactiontypeim',
                //                         type: 'GET',
                //                         data: { transactionTypeId: transactionTypeID, siteId: sSite, transactionType: 'requisition' },
                //                         success: function(r) {
                //                             $('#u_rmt_source_location').empty().append('<option selected disabled value="">Select Source</option>');
                //                             if (r.sourceData && r.sourceData.length > 0) {
                //                                 r.sourceData.forEach(function(item) {
                //                                     let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                //                                     $('#u_rmt_source_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                //                                 });
                //                                 $('#u_rmt_source_location').prop('disabled', false);
                //                             } else {
                //                                 $('#u_rmt_source_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                //                             }
                //                         }
                //                     });
                //                 }
                //             }

                //             // If destination uses inventory location and site already selected, fetch list for that site
                //             if (destType.includes('location')) {
                //                 let dSite = $('#u_rmt_destination_site').val();
                //                 if (dSite) {
                //                     $.ajax({
                //                         url: 'inventory/gettransactiontypeim',
                //                         type: 'GET',
                //                         data: { transactionTypeId: transactionTypeID, siteId: dSite, transactionType: 'requisition' },
                //                         success: function(r) {
                //                             $('#u_rmt_destination_location').empty().append('<option selected disabled value="">Select Destination</option>');
                //                             if (r.destinationData && r.destinationData.length > 0) {
                //                                 r.destinationData.forEach(function(item) {
                //                                     let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                //                                     $('#u_rmt_destination_location').append('<option value="' + item.id + '">' + displayText + '</option>');
                //                                 });
                //                                 $('#u_rmt_destination_location').prop('disabled', false);
                //                             } else {
                //                                 $('#u_rmt_destination_location').append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                //                             }
                //                         }
                //                     });
                //                 }
                //             }
                //         }
                //     });
                // });
                $('#u_rmt_remarks').val(response.remarks);

                var genericIds = response.genericIds.split(',');
                var genericNames = response.genericNames.split(',');
                var Qty = response.Qty.split(',');

                $('.uduplicate').empty();
                for (var i = 0; i < genericIds.length; i++) {
                    var GenericField = '<div class="col-md-6">' +
                        '<div class="form-group">' +
                        '<label class="control-label">Update Inventory Generic</label>' +
                        '<select class="form-control selecter p-0 u_rmt_itemgeneric" name="u_rmt_itemgeneric[]" id="u_rmt_itemgeneric' + i + '" required style="color:#222d32">' +
                        '<option value="' + genericIds[i] + '"> ' + genericNames[i] + '</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>';

                    (function (index) {
                        fetchInventoryGenerics('#u_rmt_itemgeneric' + index, 'material', function (data) {
                            if (data.length > 0) {
                                $.each(data, function (key, value) {
                                    if ($.inArray(value.id.toString(), genericIds[index]) === -1)
                                    {
                                        $('#u_rmt_itemgeneric' + index).append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                                $('#u_rmt_itemgeneric' + index).select2();
                            }
                        });
                    })(i);

                    var qtyField = '<div class="col-md-6">' +
                        '<div class="form-group row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group has-custom m-b-5">' +
                        '<label class="control-label">Update Demand Qty</label>' +
                        '<input type="number" class="form-control input-sm" required name="u_rmt_qty[]" value="' + Qty[i] + '">' +
                        '</div>' +
                        '<span class="text-danger u_rmt_qty_error"></span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    var row =+ '</div>';
                    $('.uduplicate').append('<div class="row pt-3 pb-1 rmt_details" style="border: 1px solid #939393;">' + GenericField + qtyField +'</div>');
                }


                $('#edit-reqmaterialtransfer').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Requisition For Other Transactions Modal

    //Update Requisition For Other Transactions
    $('#update_reqmaterialtransfer').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var reqMaterialTransferId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_rmt-id') {
                reqMaterialTransferId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-reqmaterialtransfer/' + reqMaterialTransferId;
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
                            $('#edit-reqmaterialtransfer').modal('hide');
                            $('#view-reqmaterialtransfer').DataTable().ajax.reload(); // Refresh DataTable
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
    //Update Requisition For Other Transactions


});
