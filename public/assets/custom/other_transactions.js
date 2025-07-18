$(document).ready(function() {
    //Open Other Transaction Modal
    $(document).on('click', '.add-othertransaction', function() {
        $('.od_d,.od_s,.brand_details').hide();
        $('input[name="ot_reference_document"]').val('').prop('disabled', false);
        $('.req_only').show();
        $('#transaction-info-row').empty();
        $('.ot_brand').off('change.BrandChangeBatch');
        $('.ot_qty').attr('max', 0);
        $('.ot_qty').attr('placeholder', 'Transaction Qty..');
        batchCheckInProgress = false;

        if ($('#source_type').length) {
                $('#source_type').val('material');
        } else {
            $('<input>').attr({
                type: 'hidden',
                name: 'source_type',
                id: 'source_type',
                value: 'material'
            }).appendTo('#add_othertransaction');
        }
   
        var orgId = $('#ot_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#ot_source_site', function(data) {
                $('#ot_source_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',false);
                $.each(data, function(key, value) {
                    $('#ot_source_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchOrganizationSites(orgId, '#ot_destination_site', function(data) {
                $('#ot_destination_site').html("<option selected disabled value=''>Select Destination Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#ot_destination_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('.ot_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', false);
            fetchOrganizationItemGeneric(orgId, '.ot_generic', function(data) {
                $.each(data, function(key, value) {
                    $('.ot_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            $('#ot_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',false);
            fetchMaterialManagementTransactionTypes(orgId, '#ot_transactiontype','other_transaction','y', function(data) {
                $.each(data, function(key, value) {
                    $('#ot_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#ot_org').html("<option selected disabled value=''>Select Organization</option>").prop('disabled',false);
            fetchOrganizations('null', '','#ot_org', function(data) {
                $('#ot_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ot_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });

            $('#ot_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
            SiteChangeMaterialManagementTransactionTypes('#ot_org','#ot_org', '#ot_transactiontype', '#add_othertransaction','other_transaction','n');
            
            $('#ot_source_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#ot_org', '#ot_source_site', '#add_othertransaction' ,'otSourceSite');

            $('#ot_destination_site').html("<option selected disabled value=''>Select Destination Site</option>").prop('disabled',true);
            OrgChangeSites('#ot_org', '#ot_destination_site', '#add_othertransaction', 'otDestinationSite');
        
            $('.ot_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
            OrgChangeInventoryGeneric('#ot_org', '.ot_generic', '#add_othertransaction');
        }
       
        $(document).off('change', '#ot_transactiontype').on('change', '#ot_transactiontype', function() {
            let transactionTypeID = $(this).val();
            // let siteId = $('#ot_source_site').val();  
            let siteId = null;

            $('#mr-optional').show();
            // if (!siteId) {
            //     Swal.fire({
            //         icon: 'warning',
            //         title: 'Site Required',
            //         text: 'Please select a site before choosing the transaction type.'
            //     });
            //     return;
            // }
        
            // Swal.fire({
            //     title: "Processing",
            //     allowOutsideClick: false,
            //     willOpen: () => {
            //         Swal.showLoading();
            //     },
            //     showConfirmButton: false
            // });
        
            $.ajax({
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
                                $('#add-othertransaction').modal('hide');
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
                        $('.od_s').show();
                        // $('.od_s label').text('Inventory Source Location');
                        $('#source_applicable').val('1');
                        if (resp.sourceData && resp.sourceData.length > 0) {
                            $('#ot_source_location')
                            .empty()
                            .append('<option selected disabled value="">Select Source</option>').prop('disabled', false);
                            resp.sourceData.forEach(function(item) {
                                let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                $('#ot_source_location').append(
                                    '<option value="' + item.id + '">' + displayText + '</option>'
                                );
                            });
                        } else {
                            $('#ot_source_location')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                        }
                    }
                    else {
                        $('.od_s').hide();
                        $('#source_applicable').val('0');
                        $('#ot_source_location')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                    }
        
        
                    let destType = (resp.Destination || '').toLowerCase();
                    if (destType.includes('location')) {
                        $('.od_d').show();
                        // $('.od_d label').text('Inventory Destination Location');
                        $('#destination_applicable').val('1');
                        if (resp.destinationData && resp.destinationData.length > 0) {
                            $('#ot_destination_location')
                                .empty()
                                .append('<option selected disabled value="">Select Destination</option>').prop('disabled', false);

                                resp.destinationData.forEach(function(item) {
                                    let displayText = item.name || item.person_name || item.patient_name ||'Unnamed';
                                    $('#ot_destination_location').append(
                                        '<option value="' + item.id + '">' + displayText + '</option>'
                                    );
                                });
                        } else {
                                $('#ot_destination_location')
                                .empty()
                                .append('<option selected disabled value="">Select Destination</option>').prop('disabled', true);
                        }
                    }
                    else {
                        $('.od_d').hide();
                        $('#destination_applicable').val('0');
                        $('#ot_destination_location')
                        .empty()
                        .append('<option selected disabled value="">Select Destination</option>').prop('disabled', true);
                    }
                 
                   
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.log(error);
                }
            });
        });

        $('.ot_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);

        $(document).off('change.newIssueBrand').on('change.newIssueBrand', '.ot_brand', function(e) {
            e.stopPropagation();
            
            const currentRow = $(this).closest('.duplicate');
            const orgId = $('#ot_org').val();
            const siteId = $('#ot_source_site').val();
            const genericId = currentRow.find('.ot_generic').val();
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
            handleBatchNumberCheck(orgId, siteId, genericId, brandId, currentRow, 'newOtherTransaction', '#add-othertransaction', {batchSelector: '.ot_batch',brandSelector: '.ot_brand', qtySelector: '.ot_qty', expirySelector: '.ot_expiry'});
        });

        $('#add-othertransaction').modal('show');
    });
  
    $(document).off('change', '.ot_generic').on('change', '.ot_generic', function() {
        var genericId = $(this).val();
        var currentRow = $(this).closest('.duplicate'); 
        var currentRowBrandSelect = currentRow.find('.ot_brand'); 
    
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
                            var $qty = currentRowBrandSelect.closest('.duplicate').find('.ot_qty');
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

    // View Other Transaction
    var viewIssueDispense =  $('#view-othertransaction').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/othertransaction',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'InventoryDetails', name: 'InventoryDetails' },
            // { data: 'transaction_details', name: 'transaction_details' },
            // { data: 'status', name: 'status' },
            // { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "250px"
            },
            {
                targets: 2,
                width: "350px"
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
    // View Other Transaction

    $(document).off('input', '.ot_qty').on('input', '.ot_qty', function() {
        const currentRow = $(this).closest('.duplicate');
        const batchNo = currentRow.find('.ot_batch').val();
        
        if (batchNo) {
            // Find all other rows with the same batch number
            $('.duplicate').not(currentRow).each(function() {
                const $row = $(this);
                if ($row.find('.ot_batch').val() === batchNo) {
                    const $qty = $row.find('.ot_qty');
                    const orgId = $('#ot_org').val();
                    const siteId = $('#ot_source_site').val();
                    const genericId = $row.find('.ot_generic').val();
                    const brandId = $row.find('.ot_brand').val();
                    
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
    $('#view-othertransaction').on('click', '.respond-btn', function() {
         $('#ajax-loader').show();
        //  $('.text-danger').text('');  
        //  $('.requirefield').removeClass('requirefield');  
        //  $('.select2-selection').removeClass('requirefield'); 
        const txId     = $(this).data('id');
        const genId    = $(this).data('generic-id');
        
        $.getJSON('inventory/respond-othertransaction', {
            id:        txId,
            genericId: genId
        })
        // .fail(() => Swal.fire('Error','Records Not Found','error'))
        .fail(() => {
            $('#ajax-loader').hide();
            Swal.fire('Error', 'Records Not Found', 'error');
        })
        .done(data => {
            // $('#mrService,.mr-dependent').show();
            // if (data.source === 'material' && !data.mr_code) {
            $('.od_s, .od_d').hide();
            $('#add_othertransaction')[0].reset();

            // if ($('#source_type').length) {
            //     $('#source_type').val(data.source);
            // } else {
            //     $('<input>').attr({
            //         type: 'hidden',
            //         name: 'source_type',
            //         id: 'source_type',
            //         value: data.source
            //     }).appendTo('#add_othertransaction');
            // }

            $('#addMoreBtn, #removeBtn').hide();

            $('#ot_org')
                .html(`<option selected value="${data.org_id}">${data.org_name}</option>`)
                .prop('disabled', true);

            $('#ot_source_site')
                .html(`<option selected value="${data.source_site}">${data.sourceSiteName}</option>`)
                .prop('disabled', true);

            $('#ot_source_location')
                .html(`<option selected value="${data.source_location}">${data.sourceLocationName}</option>`)
                .prop('disabled', true);

            $('#ot_destination_site')
                .html(`<option selected value="${data.source_site}">${data.sourceSiteName}</option>`)
                .prop('disabled', true);

            $('#ot_destination_location')
                .html(`<option selected value="${data.destination_location}">${data.destinationLocationName}</option>`)
                .prop('disabled', true);

            
            // $('#ot_transactiontype').html(`<option selected value="${data.transaction_type_id}">${data.transaction_type_name}</option>`).prop('disabled', true).trigger('change');
            $('#ot_transactiontype')
                .html(`<option selected value="${data.transaction_type_id}">${data.transaction_type_name}</option>`)
                .prop('disabled', true);
            
            // Trigger change event after a short delay
            setTimeout(() => {
                $('#ot_transactiontype').trigger('change');
            }, 50);

            let approvedSiteId = '';

            $(document).off('change', '#ot_transactiontype').on('change', '#ot_transactiontype', function() {
                let transactionTypeID = $(this).val();
                let siteId = $('#ot_source_site').val();  // you must already have a selected site

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
                                    $('#add-othertransaction').modal('hide');
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

                        // if (resp.sourceData && resp.sourceData.length > 0) {
                        //     $('#ot_source')
                        //     .empty()
                        //     .append('<option selected disabled value="">Select Source</option>').prop('disabled', false);
                        //     resp.sourceData.forEach(function(item) {
                        //         let displayText = item.name || 'Unnamed';
                        //         $('#ot_source').append(
                        //             '<option value="' + item.id + '">' + displayText + '</option>'
                        //         );
                        //     });
                        // } else {
                        //     $('#ot_source').prop('disabled', true);
                        // }
            
                        // if (resp.destinationData && resp.destinationData.length > 0) {
                        //     $('#ot_destination')
                        //     .empty()
                        //     .append('<option selected disabled value="">Select Destination</option>').prop('disabled', false);
                        //     resp.destinationData.forEach(function(item) {
                        //         let displayText = item.name ||'Unnamed';
                        //         $('#ot_destination').append(
                        //             '<option value="' + item.id + '">' + displayText + '</option>'
                        //         );
                        //     });
                        // } else {
                        //     $('#ot_destination').prop('disabled', true);
                        // }


                        let sourceType = (resp.Source || '').toLowerCase();
                        if (sourceType.includes('location')) {
                            $('.od_s').show();
                            $('#source_applicable').val('1');
                            if(resp.source_action == 's')
                            {
                                approvedSiteId = '#ot_source_site';
                                console.log(approvedSiteId);
                            }
                            // $('.od_s label').text('Inventory Source Location');
                        }
                        else {
                            $('.od_s').hide();
                            $('#source_applicable').val('0');
                            $('#ot_source_site,#ot_source_location').empty();
                        }
            
                        let destType = (resp.Destination || '').toLowerCase();
                        if (destType.includes('location')) {
                            $('.od_d').show();
                            $('#destination_applicable').val('1');
                            if(resp.destination_action == 's')
                            {
                                approvedSiteId = '#ot_destination_site';
                                console.log(approvedSiteId);
                            }
                        }
                        else {
                            $('.od_d').hide();
                            $('#destination_applicable').val('0');
                            $('#ot_destination_site,#ot_destination_location').empty();
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log(error);
                    }
                });
            });
           
            $('textarea[name="ot_remarks"]').val(data.remarks).prop('disabled', false);
            $('input[name="ot_reference_document"]').val(data.code).prop('disabled', true);

            $('.duplicate').not(':first').remove();
            let $row = $('.duplicate').first();

            $row.find('.ot_generic').html(`<option selected value="${data.generic_id}">${data.generic_name}</option>`).prop('disabled', true);
            var currentBrandField = $row.find('.ot_brand');
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
                            $('#add-othertransaction').modal('hide');

                        }
                    });
                }
            }, function(error) {
                console.log(error);
            });

             // Add this to clean up when modal closes
            $('#add-othertransaction').one('hidden.bs.modal', function() {
                $('.ot_brand').off('change.BrandChangeBatch');
                batchCheckInProgress = false;
            });
            
            const maxQty = parseFloat(data.max_qty);
            const $qtyInput = $row.find('.ot_qty');
            var respond = 'respondROT'; 
            const demandQty = parseFloat(data.demand_qty);

            $row.find('.ot_demand_qty').val(data.demand_qty).prop('disabled', true);
            $qtyInput.val('').prop('disabled', false);
        
            if (demandQty < maxQty) {
                $qtyInput.attr('max', demandQty);
                $qtyInput.attr('placeholder', `Max: ${demandQty} (Demand Qty)`);
            }
            else {
                $qtyInput.attr('max', demandQty);
                $qtyInput.attr('placeholder', `Max: ${demandQty} (Demand Qty)`);
            }
                // console.log(approvedSiteId);

            BrandChangeBatchAndExpiry(
                '#ot_org',  
                '#ot_source_site',  
                $row.find('.ot_generic'),
                $row.find('.ot_brand'),
                $row.find('.ot_batch'),
                $row.find('.ot_qty'),
                $row.find('.ot_expiry'),
                respond,
                {batchSelector: '.ot_batch',brandSelector: '.ot_brand', qtySelector: '.ot_qty', expirySelector: '.ot_expiry'},
                '#add-othertransaction'
            );
            
            $('#add-othertransaction').modal('show');
            setTimeout(function(){
                $('#ajax-loader').hide();
                }, 1000);        
            });
    });
    // Event listener for respond button

    // whenever the Add Other Transaction modal closes, reset everything back to New‐Issue state
    $('#add-othertransaction').on('hidden.bs.modal', function() {
        $('#addMoreBtn, #removeBtn').show();
        $('.duplicate').not(':first').remove();

        $('#ot_source_site')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Site</option>');

        $('#ot_transactiontype')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Transaction Type</option>');

        $('.ot_generic')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Item Generic</option>');
        $('.ot_brand')
            .prop('disabled', true)
            .html('<option selected disabled value="">Select Item Brand</option>');

        $('.ot_batch').val('').prop('disabled', false);
        $('.ot_expiry').val('').prop('disabled', false);
        $('.ot_demand_qty').val('').prop('disabled', false);

        $('.od_d, .od_s').hide();
        $('#transaction-info-row').empty();
    });

    $('#add_othertransaction').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        var sourceType = $('#transaction-info-row .source').first().text().toLowerCase();
        var destinationType = $('#transaction-info-row .destination').last().text().toLowerCase();
      
        var sourceType = $('#source_type').val(); 
        var hasSourceType = $('#source_type').length > 0; 

        $(".duplicate").each(function() {
            var row = $(this);
            
        //   const fieldsToValidate = 
        //     (!isMRRequired && !mrSelected)
        //     ? 'input:not(.mr-dependent input), textarea:not(.mr-dependent textarea), select:not(.mr-dependent select)'
        //     : 'input:not([name="ot_demand_qty[]"]), textarea, select';

            // row.find(fieldsToValidate).each(function() {
            row.find('input, textarea, select').each(function() {

                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');

                    
                // if (hasSourceType) {
                //     if (sourceType === 'material') {
                //         if (['id_dose', 'id_route', 'id_frequency', 'id_duration'].includes(fieldName)) {
                //             return true;
                //         }
                //     } else {
                //         if (['ot_demand_qty'].includes(fieldName)) {
                //             return true;
                //         }
                //     }
                // }
                // else{
                //     if (!isMRRequired && !mrSelected && 
                //         ['id_dose', 'id_route', 'id_frequency', 'id_duration'].includes(fieldName)) {
                //         return true;
                //     }
                //     else{
                //         if (!isMRRequired && !mrSelected && 
                //             ['ot_demand_qty'].includes(fieldName)) {
                //             return true;
                //         }

                //     }
                // }

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

        var excludedFields = ['ot_reference_document', 'ot_remarks'];

        if ($('.od_d').is(':hidden')) {
            excludedFields.push('ot_destination_site');
            excludedFields.push('ot_destination_location');
        }
        if ($('.od_s').is(':hidden')) {
            excludedFields.push('ot_source_location');
            excludedFields.push('ot_source_site');
        }
    
        // if (hasSourceType) {
        //     // Only modify excludedFields if we're handling a response (source_type exists)
        //     if (sourceType === 'material') {
        //         // For material source, exclude medication-related fields
        //         excludedFields = excludedFields.concat([
        //             'id_dose',
        //             'id_route',
        //             'id_frequency',
        //             'id_duration'
        //         ]);
        //     } else {
        //         // For medication source, exclude material-related fields
        //         excludedFields = excludedFields.concat([
        //             'ot_demand_qty'
        //         ]);
        //     }
        // }
        // else{
        //     if (!isMRRequired && !mrSelected) {
        //         excludedFields = excludedFields.concat([
        //             'id_dose',
        //             'id_route',
        //             'id_frequency',
        //             'id_duration'
        //         ]);
        //     }
        //     else{
        //         excludedFields = excludedFields.concat([
        //             'ot_demand_qty'
        //         ]);
        //     }
        // }
      
    
        // if (!isMRRequired) {
        //     excludedFields.push('ot_mr');
        // }

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
        console.log(resp);

        // If validation passes, submit the form
        if (resp) {
            $.ajax({
                url: "/inventory/addothertransaction",
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
                                $('#add-othertransaction').modal('hide');
                                $('#view-othertransaction').DataTable().ajax.reload();
                                $('#add_othertransaction')[0].reset();
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
                                $('#add-othertransaction').modal('hide');
                                $('#view-othertransaction').DataTable().ajax.reload();
                                $('#add_othertransaction')[0].reset();
                            }
                        });
                    }
                    else if (response.msg) {
                        Swal.fire({
                            text: response.msg,
                            icon: 'info',
                            confirmButtonText: 'OK'
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