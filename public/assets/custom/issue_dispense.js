$(document).ready(function() {
    //Open Issue & Dispense Modal
    $(document).on('click', '.add-issuedispense', function() {
        $('#id_dl,#id_sl,.serviceDetails,.brand_details').hide();
        $('input[name="id_reference_document"]').val('').prop('disabled', false);
        $('.req_only').show();
        $('#id_mr').closest('.col-md-6').show();
        $('#transaction-info-row').empty();
        $('#mrService,.mr-dependent').hide();
        $('#id_mr').val('');
        $('.id_brand').off('change.BrandChangeBatch');
        $('.id_qty').attr('max', 0);
        $('.id_qty').attr('placeholder', 'Transaction Qty..');
        batchCheckInProgress = false;
        $('.id_brand').off('change.BrandChangeBatch');
        $('.id_qty').attr('max', 0);
        $('.id_qty').attr('placeholder', 'Transaction Qty..');
        batchCheckInProgress = false;
        // $('.text-danger').text('');  
        // $('.requirefield').removeClass('requirefield');  
        // $('.select2-selection').removeClass('requirefield'); 

         if ($('#source_type').length) {
                $('#source_type').val('material');
            } else {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'source_type',
                    id: 'source_type',
                    value: 'material'
                }).appendTo('#add_issuedispense');
            }

        $(".id_frequency, .id_route").each(function() {
            $(this).val($(this).find("option:first").val()).change();
        });
        var orgId = $('#id_org').val();
        $('#id_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
        if(orgId)
        {
            $('#id_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',false);
            fetchOrganizationSites(orgId, '#id_site', function(data) {
                $.each(data, function(key, value) {
                    $('#id_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
            fetchOrgPatient(orgId, '#id_mr', function(data) {
                $('#id_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#id_mr').append('<option value="' + value.mr_code + '">' + value.mr_code + ' - ' + value.name +'</option>');
                });
            });
            $('.id_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', false);
            fetchOrganizationItemGeneric(orgId, '.id_generic', function(data) {
                $.each(data, function(key, value) {
                    $('.id_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#id_org').html("<option selected disabled value=''>Select Organization</option>").prop('disabled',false);
            fetchOrganizations('null', '','#id_org', function(data) {
                $('#id_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#id_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            
            $('#id_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#id_org', '#id_site', '#add_issuedispense');
            
            $('#id_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
            SiteChangeMRCode('#id_site', '#id_mr', null);
            
            $('.id_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#id_org', '.id_generic', '#add_issuedispense');
        }
        SiteChangeMaterialManagementTransactionTypes('#id_site','#id_org', '#id_transactiontype', '#add_issuedispense','issue_dispense','n');

        $(document).off('change', '#id_mr').on('change', '#id_mr', function() {
            $('#source_type').val('medication');

            $('.req_only').hide();
            let MRno = $(this).val();
            toggleDuplicateFieldsBasedOnMR(MRno);
            fetchMRServices(MRno, '#id_service', function(data) {
                if (data && data.length > 0) {
                    $('#id_service').empty();
                    let $service = $('#id_service')
                    .empty()
                    .prop('required', true)
                    .prop('disabled', false);
                    $.each(data, function(key, values) {
                        // $('#id_service').append('<option value="' + values.id + '">' + values.name + '</option>').prop('required', true).prop('disabled',false);
                        $service.append(
                            '<option value="' + values.id + '">' + values.name + '</option>'
                        );
                    });
                    $service.trigger('change');
                    $('#mrService').show();
                } 
                else
                {
                    $('#mrService,.serviceDetails').hide();
                    $('#id_service')
                        .html('<option selected disabled value="">Select Service</option>')
                        .prop('disabled', true)
                        .prop('required', true);
                    // $('#id_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);

                
                    Swal.fire({
                        icon: 'info',
                        title: 'No Services Found',
                        text: 'No active services found for this MR#.',
                    });
                }
            });
            $.ajax({
                url: 'patient/fetchpatientdetails',
                type: 'GET',
                data: {
                    MRno: MRno
                },
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(resp) {
                    let patientInfoHtml = `
                            <div class="col-12 mt-1 mb-1 patient-block">
                                <div class="card shadow-sm border mb-0">
                                    <div class="card-body py-2 px-3">
                                        <div class="row align-items-center text-center">
                                            <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                <small class="text-muted">Patient Name:</small><br>
                                                <strong class="text-primary">${resp.name || '-'}</strong>
                                            </div>
                                            <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                <small class="text-muted">Gender:</small><br>
                                                <strong class="text-primary">${resp.gender || '-'}</strong>
                                            </div>
                                            <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                <small class="text-muted">Age:</small><br>
                                                <strong class="text-primary">${resp.Age || '-'}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;


                    $('#transaction-info-row').find('.patient-block').remove();
                    $('#transaction-info-row')
                    .append(patientInfoHtml)   // leave off the .empty() here
                    .show();
                    $('#ajax-loader').hide();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });

        if ($('#id_mr').val()) {
            toggleDuplicateFieldsBasedOnMR($('#id_mr').val());
        }
       
        $(document).off('change', '#id_transactiontype').on('change', '#id_transactiontype', function() {
            let transactionTypeID = $(this).val();
            let siteId = $('#id_site').val();  // you must already have a selected site
            $('#mr-optional').show();
            if (!siteId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Site Required',
                    text: 'Please select a site before choosing the transaction type.'
                });
                return;
            }
        
            Swal.fire({
                title: "Processing",
                allowOutsideClick: false,
                willOpen: () => {
                    Swal.showLoading();
                },
                showConfirmButton: false
            });
        
            $.ajax({
                url: 'inventory/gettransactiontypeim',
                url: 'inventory/gettransactiontypeim',
                type: 'GET',
                data: {
                    transactionTypeId: transactionTypeID,
                    siteId: siteId
                },
                success: function(resp) {
                    Swal.close();

                    if (resp.success === false) {
                        Swal.fire({
                            text: resp.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-issuedispense').modal('hide');
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

                    let infoHtml = `
                            <div class="col-12 mt-1 mb-1 transaction-block">
                                <div class="card shadow-sm border mb-0">
                                    <div class="card-body py-2 px-3">
                                        <div class="row align-items-center text-center">
                                            <div class="col-md-6 col-12 mb-2 mb-md-0">
                                                <small class="text-muted">Source:</small><br>
                                                <strong class="text-primary source">${resp.Source || '-'}</strong>
                                            </div>
                                            <div class="col-md-6 col-12 mb-2 mb-md-0">
                                                <small class="text-muted">Destination:</small><br>
                                                <strong class="text-primary destination">${resp.Destination || '-'}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;

                    $('#transaction-info-row').find('.transaction-block').remove();
                    $('#transaction-info-row')
                    .append(infoHtml)
                    .show();
        
                    let sourceType = (resp.Source || '').toLowerCase();
                    if (sourceType.includes('location')) {
                        $('#id_sl').show();
                        $('#id_sl label').text('Inventory Source Location');
                    }
                    else if (sourceType.includes('patient')) {
                        $('#id_sl').show();
                        $('#id_sl label').text('Inventory Source Patient');
                    }
                    else {
                        $('#id_sl').hide();
                    }
        
                    let destType = (resp.Destination || '').toLowerCase();
                    if (destType.includes('location')) {
                        $('#id_dl').show();
                        $('#id_dl label').text('Inventory Destination Location');
                    }
                    else if (destType.includes('patient')) {
                        $('#id_dl').show();
                        $('#id_dl label').text('Inventory Destination Patient');
                    }
                    else {
                        $('#id_dl').hide();
                    }
                    
                    $('#id_source')
                        .empty()
                        .append('<option selected disabled value="">Select Source</option>');
        
                    if (resp.sourceData && resp.sourceData.length > 0) {
                        resp.sourceData.forEach(function(item) {
                            let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                            $('#id_source').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });
                        $('#id_source').prop('disabled', false);
                    } else {
                        $('#id_source').prop('disabled', true);
                    }
        
                    $('#id_destination')
                        .empty()
                        .append('<option selected disabled value="">Select Destination</option>');
        
                    if (resp.destinationData && resp.destinationData.length > 0) {
                        resp.destinationData.forEach(function(item) {
                            let displayText = item.name || item.person_name || item.patient_name ||'Unnamed';
                            $('#id_destination').append(
                                '<option value="' + item.id + '">' + displayText + '</option>'
                            );
                        });
                        $('#id_destination').prop('disabled', false);
                    } else {
                        $('#id_destination').prop('disabled', true);
                    }

                    let mrSelected = $('#id_mr').val(); // Get selected MR number
                    let sourceTypenew = (resp.Source || '').toLowerCase();
                    let destinationTypenew = (resp.Destination || '').toLowerCase();

                    if (sourceTypenew.includes('patient')) {
                        if (mrSelected) {
                            $('#id_source').html(`<option value="${mrSelected}" selected>${$('#id_mr option:selected').text()}</option>`);
                            $('#id_source').prop('disabled', true);
                        } else {
                            $('#mr-optional').hide();
                            Swal.fire({
                                icon: 'warning',
                                title: 'MR Required',
                                text: 'Please select a MR # first because the Source is Patient.',
                            });
                            // return;
                            $('#id_source').html('<option selected disabled value="">Select Source</option>');
                            $('#id_source').prop('disabled', true);
                        }
                    }

                    if (destinationTypenew.includes('patient')) {
                        if (mrSelected) {
                            $('#id_destination').html(`<option value="${mrSelected}" selected>${$('#id_mr option:selected').text()}</option>`);
                            $('#id_destination').prop('disabled', true);
                        } else {
                            $('#mr-optional').hide();
                            Swal.fire({
                                icon: 'warning',
                                title: 'MR Required',
                                text: 'Please select a MR # first because the Destination is Patient.',
                            });
                            // return;
                            $('#id_destination').html('<option selected disabled value="">Select Destination</option>');
                            $('#id_destination').prop('disabled', true);
                        }
                    }

                    if (!sourceTypenew.includes('patient')) {
                        $('#id_source').prop('disabled', false);
                    }
                    if (!destinationTypenew.includes('patient')) {
                        $('#id_destination').prop('disabled', false);
                    }

                    $('#id_mr').off('change.idMr').on ('change.idMr', function(){
                        let mrSelectedNow = $(this).val();
                        let mrSelectedText = $('#id_mr option:selected').text();
                        let sourceTypeNow = (sourceTypenew || '').toLowerCase();
                        let destinationTypeNow = (destinationTypenew || '').toLowerCase();
                    
                        if (sourceTypeNow.includes('patient')) {
                            $('#id_source').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                            $('#id_source').prop('disabled', true);
                        }
                        if (destinationTypeNow.includes('patient')) {
                            $('#id_destination').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                            $('#id_destination').prop('disabled', true);
                        }
                      
                    });
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });

        $(document).off('change', '#id_service').on('change', '#id_service', function() {
            let serviceId = $(this).val();
            let mrId = $('#id_mr').val(); 
            $('.serviceDetails').show();

            $.ajax({
                url: 'services/getservicedetails',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    serviceId: serviceId,
                    mrId:mrId
                },
                
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(resp) {
                    $('#ajax-loader').hide();
                    if (!resp || resp.length === 0) {
                        $('.serviceDetails').hide();
                        return;
                    }
                    const d = resp[0];
                    $('#id_servicemode').append('<option value="' + d.ServiceModeId + '">' + d.ServiceMode + '</option>').prop('required', true).prop('disabled',true);
                    $('#id_physician').append('<option value="' + d.PhysicianId + '">' + d.Physician + '</option>').prop('required', true).prop('disabled',true);
                    $('#id_billingcc').append('<option value="' + d.BillingCCId + '">' + d.BillingCC + '</option>').prop('required', true).prop('disabled',true);
                    $('input[name="id_servicetype"]').val(d.ServiceType|| '').prop('readonly',true);
                    $('input[name="id_servicegroup"]').val(d.ServiceGroup|| '').prop('readonly',true);


                    $('.serviceDetails').show();

                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });
       
        $('.id_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);

        $(document).off('change.newIssueBrand').on('change.newIssueBrand', '.id_brand', function(e) {
        $(document).off('change.newIssueBrand').on('change.newIssueBrand', '.id_brand', function(e) {
            e.stopPropagation();
            
            const currentRow = $(this).closest('.duplicate');
            const orgId = $('#id_org').val();
            const siteId = $('#id_site').val();
            const genericId = currentRow.find('.id_generic').val();
            const brandId = $(this).val();
            const $brand = $(this);

            if (!orgId || !siteId || !genericId || !brandId) {
                Swal.fire(
                    'Missing Information',
                    'Please select Organization, Site, Generic and Brand before proceeding.',
                    'warning'
                );
                $brand
                    .prop('disabled', false)
                    .children('option[value=""]').remove().end()
                    .prepend('<option value="" disabled>Select Brand</option>')
                    .val('');
                return;
            }

            handleBatchNumberCheck(orgId, siteId, genericId, brandId, $brand, currentRow, 'newIssue');
        });

        $('#add-issuedispense').modal('show');
    });
  
    $(document).off('change', '.id_generic').on('change', '.id_generic', function() {
        var genericId = $(this).val();
        var currentRow = $(this).closest('.duplicate'); 
        var currentRowBrandSelect = currentRow.find('.id_brand'); 
    
        if (genericId) {
            fetchGenericItemBrand(genericId, currentRowBrandSelect, function(data) {
                if (data.length > 0) {
                    currentRowBrandSelect.empty();
                    currentRowBrandSelect.append('<option selected disabled value="">Select Brand</option>');
                    $.each(data, function(key, value) {
                        currentRowBrandSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowBrandSelect.find('option:contains("Loading...")').remove();
                    currentRowBrandSelect.prop('disabled', false);
                } else {
                    Swal.fire({
                        text: 'Brands are not available for the selected Item Generic',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentRowBrandSelect.empty();
                            currentRow.find('.brand_details').hide();
                            currentRowBrandSelect.html("<option selected disabled value=''>Select Brand</option>").prop('disabled', true);
                            var $qty = currentRowBrandSelect.closest('.duplicate').find('.id_qty');
                            if ($qty.length) {
                                $qty.removeAttr('max');
                                $qty.attr('placeholder', 'Transaction Qty...');
                            }

                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });
        } else {
            currentRowBrandSelect.empty();
            currentRowBrandSelect.html("<option selected disabled value=''>Select Brand</option>").prop('disabled', true);
        }
    });

    // View Issue Dispense
    var viewIssueDispense =  $('#view-issuedispense').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/issuedispense',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'patientDetails', name: 'patientDetails' },
            { data: 'InventoryDetails', name: 'InventoryDetails' },
            // { data: 'transaction_details', name: 'transaction_details' },
            // { data: 'status', name: 'status' },
            // { data: 'action', name: 'action', orderable: false, searchable: false }
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
            }
        ]
    });

    viewIssueDispense.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewIssueDispense.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewIssueDispense.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Issue Dispense

    $(document).off('input', '.id_qty').on('input', '.id_qty', function() {
        const currentRow = $(this).closest('.duplicate');
        const batchNo = currentRow.find('.id_batch').val();
        
        if (batchNo) {
            // Find all other rows with the same batch number
            $('.duplicate').not(currentRow).each(function() {
                const $row = $(this);
                if ($row.find('.id_batch').val() === batchNo) {
                    const $qty = $row.find('.id_qty');
                    const orgId = $('#id_org').val();
                    const siteId = $('#id_site').val();
                    const genericId = $row.find('.id_generic').val();
                    const brandId = $row.find('.id_brand').val();
                    
                    // Refresh the max quantity for this row
                    $.getJSON('inventory/getbatchno', { orgId, siteId, genericId, brandId })
                        .then(resp => {
                            if (resp && resp.site_balance !== undefined) {
                                const usedQty = calculateUsedQuantityForBatch(batchNo, $row);
                                const availableQty = resp.site_balance - usedQty;
                                
                                $qty.attr('max', availableQty);
                                $qty.attr('placeholder', `Max: ${availableQty} (Available: ${resp.site_balance}, Used: ${usedQty})`);
                            }
                        });
                }
            });
        }
    });

    // Event listener for respond button
    $('#view-issuedispense').on('click', '.respond-btn', function() {
         $('#ajax-loader').show();
        //  $('.text-danger').text('');  
        //  $('.requirefield').removeClass('requirefield');  
        //  $('.select2-selection').removeClass('requirefield'); 
        const txId     = $(this).data('id');
        const genId    = $(this).data('generic-id');
        const source   = $(this).data('source'); 
        
        $.getJSON('inventory/respond-issuedispense', {
            id:        txId,
            genericId: genId,
            source:    source 
        })
        .fail(() => Swal.fire('Error','Could not load data','error'))
        .done(data => {
            // $('#id_dl,#id_sl,.serviceDetails').show();
            // $('#mrService,.mr-dependent').show();
            // if (data.source === 'material' && !data.mr_code) {
            if (data.source === 'material') {
                $('#id_mr').closest('.col-md-6').hide();
                $('#id_sl, #id_dl, .serviceDetails, #mrService, .mr-dependent').hide();
                $('.req_only').show();
            }
            else {
                $('#id_mr').closest('.col-md-6').show();
                $('#id_sl, #id_dl, .serviceDetails, #mrService, .mr-dependent').show();
            }
            $('#add_issuedispense')[0].reset();

            if ($('#source_type').length) {
                $('#source_type').val(data.source);
            } else {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'source_type',
                    id: 'source_type',
                    value: data.source
                }).appendTo('#add_issuedispense');
            }

            $('#addMoreBtn, #removeBtn').hide();

            $('#id_org')
                .html(`<option selected value="${data.org_id}">${data.org_name}</option>`)
                .prop('disabled', true);

            $('#id_site')
                .html(`<option selected value="${data.site_id}">${data.site_name}</option>`)
                .prop('disabled', true);

            // if (data.mr_code) {
            //     $('#id_mr')
            //         .html(`<option selected value="${data.mr_code}">${data.mr_code} – ${data.patient_name}</option>`)
            //         .prop('disabled', true).trigger('change');
            // }

            if (data.mr_code) {
                $('#id_mr')
                    .html(`<option selected value="${data.mr_code}">${data.mr_code} – ${data.patient_name}</option>`)
                    .prop('disabled', true);
                
                // Trigger change event after a short delay
                setTimeout(() => {
                    $('#id_mr').trigger('change');
                }, 100);
            }

            $(document).off('change', '#id_mr').on('change', '#id_mr', function() {
                // $('.req_only').hide();
                let MRno = $(this).val();
                $.ajax({
                    url: 'patient/fetchpatientdetails',
                    type: 'GET',
                    data: {
                        MRno: MRno
                    },
                    success: function(resp) {
                        let patientInfoHtml = `
                                <div class="col-12 mt-1 mb-1 patient-block">
                                    <div class="card shadow-sm border mb-0">
                                        <div class="card-body py-2 px-3">
                                            <div class="row align-items-center text-center">
                                                <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                    <small class="text-muted">Patient Name:</small><br>
                                                    <strong class="text-primary">${resp.name || '-'}</strong>
                                                </div>
                                                <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                    <small class="text-muted">Gender:</small><br>
                                                    <strong class="text-primary">${resp.gender || '-'}</strong>
                                                </div>
                                                <div class="col-md-4 col-6 mb-2 mb-md-0">
                                                    <small class="text-muted">Age:</small><br>
                                                    <strong class="text-primary">${resp.Age || '-'}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;


                        $('#transaction-info-row').find('.patient-block').remove();
                        $('#transaction-info-row')
                        .append(patientInfoHtml)   // leave off the .empty() here
                        .show();
                        $('#ajax-loader').hide();
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log(error);
                    }
                });
            });

            
            // $('#id_transactiontype').html(`<option selected value="${data.transaction_type_id}">${data.transaction_type_name}</option>`).prop('disabled', true).trigger('change');
            $('#id_transactiontype')
                .html(`<option selected value="${data.transaction_type_id}">${data.transaction_type_name}</option>`)
                .prop('disabled', true);
            
            // Trigger change event after a short delay
            setTimeout(() => {
                $('#id_transactiontype').trigger('change');
            }, 50);

            $(document).off('change', '#id_transactiontype').on('change', '#id_transactiontype', function() {
                let transactionTypeID = $(this).val();
                let siteId = $('#id_site').val();  // you must already have a selected site

                $('#mr-optional').show();
                if (!siteId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Site Required',
                        text: 'Please select a site before choosing the transaction type.'
                    });
                    return;
                }
                $.ajax({
                    url: 'inventory/gettransactiontypeim',
                    url: 'inventory/gettransactiontypeim',
                    type: 'GET',
                    data: {
                        transactionTypeId: transactionTypeID,
                        siteId: siteId
                    },
                    success: function(resp) {
                        if (resp.success === false) {
                            Swal.fire({
                                text: resp.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#add-issuedispense').modal('hide');
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

                        let infoHtml = `
                                <div class="col-12 mt-1 mb-1 transaction-block">
                                    <div class="card shadow-sm border mb-0">
                                        <div class="card-body py-2 px-3">
                                            <div class="row align-items-center text-center">
                                                <div class="col-md-6 col-12 mb-2 mb-md-0">
                                                    <small class="text-muted">Source:</small><br>
                                                    <strong class="text-primary source">${resp.Source || '-'}</strong>
                                                </div>
                                                <div class="col-md-6 col-12 mb-2 mb-md-0">
                                                    <small class="text-muted">Destination:</small><br>
                                                    <strong class="text-primary destination">${resp.Destination || '-'}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;

                        $('#transaction-info-row').find('.transaction-block').remove();
                        $('#transaction-info-row')
                        .append(infoHtml)
                        .show();
            
                        let sourceType = (resp.Source || '').toLowerCase();
                        if (sourceType.includes('location')) {
                            $('#id_sl').show();
                            $('#id_sl label').text('Inventory Source Location');
                        }
                        else if (sourceType.includes('patient')) {
                            $('#id_sl').show();
                            $('#id_sl label').text('Inventory Source Patient');
                        }
                        else {
                            $('#id_sl').hide();
                        }
            
                        let destType = (resp.Destination || '').toLowerCase();
                        if (destType.includes('location')) {
                            $('#id_dl').show();
                            $('#id_dl label').text('Inventory Destination Location');
                        }
                        else if (destType.includes('patient')) {
                            $('#id_dl').show();
                            $('#id_dl label').text('Inventory Destination Patient');
                        }
                        else {
                            $('#id_dl').hide();
                        }
                        
                        $('#id_source')
                            .empty()
                            .append('<option selected disabled value="">Select Source</option>');
            
                        if (resp.sourceData && resp.sourceData.length > 0) {
                            resp.sourceData.forEach(function(item) {
                                let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                $('#id_source').append(
                                    '<option value="' + item.id + '">' + displayText + '</option>'
                                );
                            });
                            $('#id_source').prop('disabled', false);
                        } else {
                            $('#id_source').prop('disabled', true);
                        }
            
                        $('#id_destination')
                            .empty()
                            .append('<option selected disabled value="">Select Destination</option>');
            
                        if (resp.destinationData && resp.destinationData.length > 0) {
                            resp.destinationData.forEach(function(item) {
                                let displayText = item.name || item.person_name || item.patient_name ||'Unnamed';
                                $('#id_destination').append(
                                    '<option value="' + item.id + '">' + displayText + '</option>'
                                );
                            });
                            $('#id_destination').prop('disabled', false);
                        } else {
                            $('#id_destination').prop('disabled', true);
                        }

                        let mrSelected = $('#id_mr').val(); // Get selected MR number
                        let sourceTypenew = (resp.Source || '').toLowerCase();
                        let destinationTypenew = (resp.Destination || '').toLowerCase();

                        if (sourceTypenew.includes('patient')) {
                            if (mrSelected) {
                                $('#id_source').html(`<option value="${mrSelected}" selected>${$('#id_mr option:selected').text()}</option>`);
                                $('#id_source').prop('disabled', true);
                            } else {
                                $('#mr-optional').hide();
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'MR Required',
                                    text: 'Please select a MR # first because the Source is Patient.',
                                });
                                // return;
                                $('#id_source').html('<option selected disabled value="">Select Source</option>');
                                $('#id_source').prop('disabled', true);
                            }
                        }

                        if (destinationTypenew.includes('patient')) {
                            if (mrSelected) {
                                $('#id_destination').html(`<option value="${mrSelected}" selected>${$('#id_mr option:selected').text()}</option>`);
                                $('#id_destination').prop('disabled', true);
                            } else {
                                $('#mr-optional').hide();
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'MR Required',
                                    text: 'Please select a MR # first because the Destination is Patient.',
                                });
                                // return;
                                $('#id_destination').html('<option selected disabled value="">Select Destination</option>');
                                $('#id_destination').prop('disabled', true);
                            }
                        }

                        if (!sourceTypenew.includes('patient')) {
                            $('#id_source').prop('disabled', false);
                        }
                        if (!destinationTypenew.includes('patient')) {
                            $('#id_destination').prop('disabled', false);
                        }

                        $('#id_mr').off('change.idMr').on ('change.idMr', function(){
                            let mrSelectedNow = $(this).val();
                            let mrSelectedText = $('#id_mr option:selected').text();
                            let sourceTypeNow = (sourceTypenew || '').toLowerCase();
                            let destinationTypeNow = (destinationTypenew || '').toLowerCase();
                        
                            if (sourceTypeNow.includes('patient')) {
                                $('#id_source').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                                $('#id_source').prop('disabled', true);
                            }
                            if (destinationTypeNow.includes('patient')) {
                                $('#id_destination').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                                $('#id_destination').prop('disabled', true);
                            }
                        
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log(error);
                    }
                });
            });
           
            $('#id_service').html(`<option selected value="${data.service_id}">${data.service_name}</option>`).prop('disabled', true);
            $('#id_servicemode').html(`<option selected value="${data.service_mode_id}">${data.service_mode_name}</option>`).prop('disabled', true);
            $('#id_physician').html(`<option selected value="${data.physician_id}">${data.physician_name}</option>`).prop('disabled', true);
            $('#id_billingcc').html(`<option selected value="${data.billing_cc}">${data.billing_cc_name}</option>`).prop('disabled', true);
            $('input[name="id_servicetype"]').val(data.service_type_name).prop('readonly', true);
            $('input[name="id_servicegroup"]').val(data.service_group_name).prop('readonly', true);
            $('textarea[name="id_remarks"]').val(data.remarks).prop('disabled', false);
            $('input[name="id_reference_document"]').val(data.code).prop('disabled', true);

            $('.duplicate').not(':first').remove();
            let $row = $('.duplicate').first();

            $row.find('.id_generic').html(`<option selected value="${data.generic_id}">${data.generic_name}</option>`).prop('disabled', true);
            var currentBrandField = $row.find('.id_brand');
            fetchGenericItemBrand(data.generic_id, currentBrandField, function(data) {
                if (data.length > 0) {
                    currentBrandField.empty();
                    // currentBrandField.append('<option selected disabled value="">Select Brand</option>');
                    $.each(data, function(key, value) {
                        currentBrandField.append('<option value="' + value.id + '">' + value.name + '</option>').trigger('change');
                    });
                    currentBrandField.find('option:contains("Loading...")').remove();
                    currentBrandField.prop('disabled', false);
                } 
                else {
                    Swal.fire({
                        text: 'Brands are not available for the selected Item Generic',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentBrandField.empty();
                            currentBrandField.html("<option selected disabled value=''>Select Brand</option>").prop('disabled', true);
                            $('#add-issuedispense').modal('hide');

                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });

             // Add this to clean up when modal closes
            $('#add-issuedispense').one('hidden.bs.modal', function() {
                // Unbind all brand change events
                $('.id_brand').off('change.BrandChangeBatch');
                // Reset any other state if needed
                batchCheckInProgress = false;
            });

             // Add this to clean up when modal closes
            $('#add-issuedispense').one('hidden.bs.modal', function() {
                // Unbind all brand change events
                $('.id_brand').off('change.BrandChangeBatch');
                // Reset any other state if needed
                batchCheckInProgress = false;
            });

            
            const maxQty = parseFloat(data.max_qty);
            // console.log(maxQty);
            const $qtyInput = $row.find('.id_qty');
            // $row.find('.id_brand').html(`<option selected value="${data.brand_id}">${data.brand_name}</option>`).prop('disabled', true);
            if (data.source === 'medication') {
                var respond = 'respondMedication'; 
                $('.mr-dependent').show();
                $('.req_only').hide();
                $row.find('.id_dose').val(data.dose).prop('disabled', true);
                $row.find('.id_route').html(`<option selected value="${data.route_id}">${data.route_name}</option>`).prop('disabled', true);
                $row.find('.id_frequency').html(`<option selected value="${data.frequency_id}">${data.frequency_name}</option>`).prop('disabled', true);
                $row.find('input[name="id_duration[]"]').val(data.days).prop('disabled', true);
                $row.find('.id_batch, .id_expiry, input[name="id_qty[]"]').val('').prop('disabled', false);
                // $qtyInput.attr('max', 0);
                // $qtyInput.attr('placeholder', `Transaction Qty...`).prop('disabled', false);
            } 
            else {
                var respond = 'respondMaterial'; 
                $('.mr-dependent').hide();
                $('.req_only').show();

                const demandQty = parseFloat(data.demand_qty);
               
                // Set demand qty display first
                $row.find('.id_demand_qty').val(data.demand_qty).prop('disabled', true);
                $qtyInput.val('').prop('disabled', false);
                // console.log(maxQty);
            
                if (demandQty < maxQty) {
                    $qtyInput.attr('max', demandQty);
                    $qtyInput.attr('placeholder', `Max: ${demandQty} (Demand Qty)`);
                }
                else {
                    $qtyInput.attr('max', demandQty);
                    $qtyInput.attr('placeholder', `Max: ${demandQty} (Demand Qty)`);
                }
                 

                // $qtyInput.val('').prop('disabled', false);
                // $row.find('.id_demand_qty').val(data.demand_qty).prop('disabled', true);
                // $row.find('input[name="id_qty[]"]').val('').prop('disabled', false);
            }

            BrandChangeBatchAndExpiry(
                '#id_org',  
                '#id_site',  
                $row.find('.id_generic'),
                $row.find('.id_brand'),
                $row.find('.id_batch'),
                $row.find('.id_expiry'),
                respond,
                '#add-issuedispense'
            );

            

            $('#add-issuedispense').modal('show');
            setTimeout(function(){
                $('#ajax-loader').hide();
                }, 1000);        
                }, 1000);        
            });
    });
    // Event listener for respond button

    // whenever the Add/Issue modal closes, reset everything back to New‐Issue state
    $('#add-issuedispense').on('hidden.bs.modal', function() {
        // $('#add_issuedispense')[0].reset();
        $('#addMoreBtn, #removeBtn').show();
        $('.duplicate').not(':first').remove();
        // $('#id_org')
        //     .prop('disabled', false)
        //     .html('<option selected disabled value="">Select Organization</option>');

        $('#id_site')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Site</option>');

        $('#id_mr')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select MR #</option>');

        $('#id_transactiontype')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Transaction Type</option>');

        $('.id_generic')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Item Generic</option>');
        $('.id_brand')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Item Brand</option>');

        $('.id_batch').val('').prop('disabled', false);
        $('.id_expiry').val('').prop('disabled', false);
        $('.id_demand_qty').val('').prop('disabled', false);

        $('#id_dl, #id_sl, .serviceDetails').hide();
        $('.mr-dependent').hide();
        $('.req_only').show();
        $('#transaction-info-row').empty();
    });

    $('#add_issuedispense').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        var mrSelected = $('#id_mr').val();
        var serviceAvailable = $('#mrService').is(':visible');
        var sourceType = $('#transaction-info-row .source').first().text().toLowerCase();
        var destinationType = $('#transaction-info-row .destination').last().text().toLowerCase();
        var isMRRequired = sourceType.includes('patient') || destinationType.includes('patient');
        if (isMRRequired && !mrSelected) {
            $('#id_mr')
                .next('.select2-container')
                .find('.select2-selection')
                .addClass('requirefield');
            $('#id_mr_error').text("MR # is required when source or destination is patient");
            resp = false;
        }

        if (mrSelected) {
            if (sourceType.includes('patient') && $('#id_source').val() !== mrSelected) {
                $('#id_source_error').text("Source patient must match selected MR#");
                resp = false;
            }
            if (destinationType.includes('patient') && $('#id_destination').val() !== mrSelected) {
                $('#id_destination_error').text("Destination patient must match selected MR#");
                resp = false;
            }
        }
        var sourceType = $('#source_type').val(); 
        var hasSourceType = $('#source_type').length > 0; 
        $(".duplicate").each(function() {
            var row = $(this);
            
          const fieldsToValidate = 
            (!isMRRequired && !mrSelected)
            ? 'input:not(.mr-dependent input), textarea:not(.mr-dependent textarea), select:not(.mr-dependent select)'
            : 'input:not([name="id_demand_qty[]"]), textarea, select';

            row.find(fieldsToValidate).each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');

                if (!serviceAvailable && 
                    ['id_service', 'id_servicemode', 'id_physician', 'id_billingcc'].includes(fieldName)) {
                    return true;
                }

                if (!isMRRequired && !mrSelected && 
                    ['id_dose', 'id_route', 'id_frequency', 'id_duration'].includes(fieldName)) {
                    return true;
                }

                    
                if (hasSourceType) {
                    if (sourceType === 'material') {
                        if (['id_dose', 'id_route', 'id_frequency', 'id_duration'].includes(fieldName)) {
                            return true;
                        }
                    } else {
                        if (['id_demand_qty'].includes(fieldName)) {
                            return true;
                        }
                    }
                }
                else{
                    if (!isMRRequired && !mrSelected && 
                        ['id_dose', 'id_route', 'id_frequency', 'id_duration'].includes(fieldName)) {
                        return true;
                    }
                    else{
                        if (!isMRRequired && !mrSelected && 
                            ['id_demand_qty'].includes(fieldName)) {
                            return true;
                        }

                    }
                }

                if (!value || value === "" || (elem.is('select') && value === null)) {
                    errorField.text("This field is required");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').addClass('requirefield');
                        elem.on('select2:open', function() {
                            errorField.text("");
                            elem.next('.select2-container').find('.select2-selection').removeClass("requirefield");
                        });
                    } else {
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

        // Validate non-array fields
        var excludedFields = ['id_reference_document', 'id_remarks'];
        
        if (!serviceAvailable) {
            excludedFields = excludedFields.concat([
                'id_service',
                'id_servicemode',
                'id_servicetype',
                'id_servicegroup',
                'id_physician',
                'id_billingcc'
            ]);
        }

    
        if (hasSourceType) {
            // Only modify excludedFields if we're handling a response (source_type exists)
            if (sourceType === 'material') {
                // For material source, exclude medication-related fields
                excludedFields = excludedFields.concat([
                    'id_dose',
                    'id_route',
                    'id_frequency',
                    'id_duration'
                ]);
            } else {
                // For medication source, exclude material-related fields
                excludedFields = excludedFields.concat([
                    'id_demand_qty'
                ]);
            }
        }
        else{
            if (!isMRRequired && !mrSelected) {
                excludedFields = excludedFields.concat([
                    'id_dose',
                    'id_route',
                    'id_frequency',
                    'id_duration'
                ]);
            }
            else{
                excludedFields = excludedFields.concat([
                    'id_demand_qty'
                ]);
            }
        }

      
    
        if (!isMRRequired) {
            excludedFields.push('id_mr');
        }

        $(data).each(function(i, field) {
            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true;
            }
            
            if ((field.value == '') || (field.value == null)) {
                var FieldName = field.name;
                var FieldName = FieldName.replace('[]', '');
                var FieldID = '#' + FieldName + "_error";

                $(FieldID).text("This field is required");
                $('input[name="' + FieldName + '"]').addClass('requirefield');
                $('input[name="' + FieldName + '"]').focus(function() {
                    $(FieldID).text("");
                    $('input[name="' + FieldName + '"]').removeClass("requirefield");
                });

                $('select[name="' + FieldName + '"]').next('.select2-container').find('.select2-selection').addClass('requirefield');
                $('select[name="' + FieldName + '"]').on('select2:open', function() {
                    $(FieldID).text("");
                    $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                });
                
                resp = false;
            }
           
        });

        // If validation passes, submit the form
        if (resp) {
            $.ajax({
                url: "/inventory/addissuedispense",
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
                    if (response.error) {
                        Swal.fire({
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (response.success) {
                        Swal.fire({
                            text: response.success,
                            icon: 'success',
                            allowOutsideClick: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-issuedispense').modal('hide');
                                $('#view-issuedispense').DataTable().ajax.reload();
                                $('#add_issuedispense')[0].reset();
                                $('.text-danger').hide();
                            }
                        });
                    } else if (response.info) {
                        Swal.fire({
                            text: response.info,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-issuedispense').modal('hide');
                            }
                        });
                    }
                },
                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        $('.text-danger').show();
                        var errors = error.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            var sanitizedField = field.replace(/\[\]/g, '');
                            var errorSelector = '#' + sanitizedField + '_error';
                            $(errorSelector).text(messages.join(' '));
                        });
                        Swal.close();
                    }
                }
            });
        }
    });

});