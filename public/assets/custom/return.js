$(document).ready(function() {

    // View Return
    var viewReturn =  $('#view-return').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/return',
        order: [[0, 'desc']],
        columns: [
            { data: 'return_raw', name: 'return_raw', visible: false },
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
                width: "200px"
            },
            {
                targets: 2,
                width: "200px"
            },
            {
                targets: 3,
                width: "500px"
            }
        ]
    });

    viewReturn.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewReturn.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewReturn.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Issue Dispense

    // $(document).off('input', '.return_qty').on('input', '.return_qty', function() {
    //     const currentRow = $(this).closest('.duplicate');
    //     const batchNo = currentRow.find('.return_batch').val();
        
    //     if (batchNo) {
    //         // Find all other rows with the same batch number
    //         $('.duplicate').not(currentRow).each(function() {
    //             const $row = $(this);
    //             if ($row.find('.return_batch').val() === batchNo) {
    //                 const $qty = $row.find('.return_qty');
    //                 const orgId = $('#return_org').val();
    //                 const siteId = $('#return_site').val();
    //                 const genericId = $row.find('.return_generic').val();
    //                 const brandId = $row.find('.return_brand').val();
                    
    //                 // Refresh the max quantity for this row
    //                 $.getJSON('inventory/getbatchno', { orgId, siteId, genericId, brandId })
    //                     .then(resp => {
    //                         if (resp && resp.site_balance !== undefined) {
    //                             const usedQty = calculateUsedQuantityForBatch(batchNo, $row);
    //                             const availableQty = resp.site_balance - usedQty;
                                
    //                             $qty.attr('max', availableQty);
    //                             $qty.attr('placeholder', `Max: ${availableQty} (Available: ${resp.site_balance}, Used: ${usedQty})`);
    //                         }
    //                     });
    //             }
    //         });
    //     }
    // });

    // Event listener for respond button
    $('#view-return').on('click', '.respond-btn', function() {
        $('#return_dl,#return_sl,#mrService,.serviceDetails,.brand_details').hide();
         $('#ajax-loader').show();
        //  $('.text-danger').text('');  
        //  $('.requirefield').removeClass('requirefield');  
        //  $('.select2-selection').removeClass('requirefield'); 
        const txId     = $(this).data('id');
        const genId    = $(this).data('generic-id');
        const brand   = $(this).data('brand-id'); 
        const batchNo   = $(this).data('batch-no'); 
        const expiry   = $(this).data('expiry'); 
        const issuedQty   = $(this).data('issue-qty'); 
        
        $.getJSON('inventory/respond-return', {
            id:        txId,
            genericId: genId,
            brand: brand,
            batchNo: batchNo,
            expiry: expiry,
            issuedQty: issuedQty
        })
        .fail(() => Swal.fire('Error','Could not load data','error'))
        .done(data => {
        
            $('#add_return')[0].reset();
            $('#addMoreBtn, #removeBtn').hide();
            if (!data.mr_code) {
                $('#return_mr').closest('.col-md-6').hide();
            }
            else
            {
                $('#return_mr').closest('.col-md-6').show();
            }

            $('#return_org')
                .html(`<option selected value="${data.org_id}">${data.org_name}</option>`)
                .prop('disabled', true);

            $('#return_site')
                .html(`<option selected value="${data.site_id}">${data.site_name}</option>`)
                .prop('disabled', true);

            setTimeout(() => {
                $('#return_site').trigger('change');
            }, 100);

            if (data.mr_code) { 
                skipMRLoad = true; 
                $('#return_mr')
                    .html(`<option selected value="${data.mr_code}">${data.mr_code} – ${data.patient_name}</option>`)
                    .prop('disabled', true);
                
                setTimeout(() => {
                    $('#return_mr').trigger('change');
                }, 100);
                
            }
            // else{
            //     $('#transaction-info-row').find('.patient-block').remove();
            //     skipMRLoad = false; 
            //     $('#return_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
            //     SiteChangeMRCode('#return_site', '#return_mr', null);
            // }
            if (data.service_mode_id || data.service_id || data.physician_id || data.billing_cc || data.service_group_name || data.service_type_name ) {
                $('.serviceDetails,#mrService').show();
                $('#return_service').append('<option value="' + data.service_id + '">' + data.service_name + '</option>').prop('required', true).prop('disabled',true);
                $('#return_servicemode').append('<option value="' + data.service_mode_id + '">' + data.service_mode_name + '</option>').prop('required', true).prop('disabled',true);
                $('#return_physician').append('<option value="' + data.physician_id + '">' + data.physician_name + '</option>').prop('required', true).prop('disabled',true);
                $('#return_billingcc').append('<option value="' + data.billing_cc + '">' + data.billing_cc_name + '</option>').prop('required', true).prop('disabled',true);
                $('input[name="return_servicetype"]').val(data.service_type_name|| '').prop('readonly',true);
                $('input[name="return_servicegroup"]').val(data.service_group_name|| '').prop('readonly',true);
            }
            
            $(document).off('change', '#return_service').on('change', '#return_service', function() {
                let serviceId = $(this).val();
                let mrId = $('#return_mr').val(); 
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
                        $('#return_servicemode').append('<option value="' + d.ServiceModeId + '">' + d.ServiceMode + '</option>').prop('required', true).prop('disabled',true);
                        $('#return_physician').append('<option value="' + d.PhysicianId + '">' + d.Physician + '</option>').prop('required', true).prop('disabled',true);
                        $('#return_billingcc').append('<option value="' + d.BillingCCId + '">' + d.BillingCC + '</option>').prop('required', true).prop('disabled',true);
                        $('input[name="return_servicetype"]').val(d.ServiceType|| '').prop('readonly',true);
                        $('input[name="return_servicegroup"]').val(d.ServiceGroup|| '').prop('readonly',true);


                        $('.serviceDetails').show();

                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log(error);
                    }
                });
            });

            // $(document).off('change', '#return_mr').on('change', '#return_mr', function() {
            //     let MRno = $(this).val();
            //     // toggleDuplicateFieldsBasedOnMR(MRno);
            //     fetchMRServices(MRno, '#return_service', function(data) {
            //         if (data && data.length > 0) {
            //             $('#return_service').empty();
            //             let $service = $('#return_service')
            //             .empty()
            //             .prop('required', true)
            //             .prop('disabled', false);
            //             $.each(data, function(key, values) {
            //                 // $('#return_service').append('<option value="' + values.id + '">' + values.name + '</option>').prop('required', true).prop('disabled',false);
            //                 $service.append(
            //                     '<option value="' + values.id + '">' + values.name + '</option>'
            //                 );
            //             });
            //             $service.trigger('change');
            //             $('#mrService').show();
            //         } 
            //         else
            //         {
            //             $('#mrService,.serviceDetails').hide();
            //             $('#return_service')
            //                 .html('<option selected disabled value="">Select Service</option>')
            //                 .prop('disabled', true)
            //                 .prop('required', true);
            //             // $('#return_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
            //             Swal.fire({
            //                 icon: 'info',
            //                 title: 'No Services Found',
            //                 text: 'No active services found for this MR#.',
            //             });
            //         }
            //     });
            //     $.ajax({
            //         url: 'patient/fetchpatientdetails',
            //         type: 'GET',
            //         data: {
            //             MRno: MRno
            //         },
            //         beforeSend: function() {
            //             $('#ajax-loader').show();
            //         },
            //         success: function(resp) {
            //             let patientInfoHtml = `
            //                 <div class="col-12 mt-1 mb-1 patient-block">
            //                     <div class="card shadow-sm border mb-0">
            //                         <div class="card-body py-2 px-3">
            //                             <div class="row align-items-center text-center">
            //                                 <div class="col-md-4 col-6 mb-2 mb-md-0">
            //                                     <small class="text-muted">Patient Name:</small><br>
            //                                     <strong class="text-primary">${resp.name || '-'}</strong>
            //                                 </div>
            //                                 <div class="col-md-4 col-6 mb-2 mb-md-0">
            //                                     <small class="text-muted">Gender:</small><br>
            //                                     <strong class="text-primary">${resp.gender || '-'}</strong>
            //                                 </div>
            //                                 <div class="col-md-4 col-6 mb-2 mb-md-0">
            //                                     <small class="text-muted">Age:</small><br>
            //                                     <strong class="text-primary">${resp.Age || '-'}</strong>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>
            //                 </div>
            //                 `;

            //             $('#transaction-info-row').find('.patient-block').remove();
            //             $('#transaction-info-row')
            //             .append(patientInfoHtml)   // leave off the .empty() here
            //             .show();
            //             $('#ajax-loader').hide();
            //         },
            //         error: function(xhr, status, error) {
            //             Swal.close();
            //             console.log(error);
            //         }
            //     });
            // });

            SiteChangeMaterialManagementTransactionTypes('#return_site','#return_org', '#return_transactiontype', '#add_return','inventory_return','null');

            $(document).off('change', '#return_transactiontype').on('change', '#return_transactiontype', function() {
                let transactionTypeID = $(this).val();
                let siteId = $('#return_site').val();  // you must already have a selected site

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
                                    $('#add-return').modal('hide');
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
                        

                        
                        if (resp.sourceData && resp.sourceData.length > 0) {
                             $('#return_source')
                            .empty()
                            .append('<option selected disabled value="">Select Source</option>').prop('disabled', false);
                            resp.sourceData.forEach(function(item) {
                                let displayText = item.name || item.person_name || item.patient_name || 'Unnamed';
                                $('#return_source').append(
                                    '<option value="' + item.id + '">' + displayText + '</option>'
                                );
                            });
                            
                        } else {
                            $('#return_source')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                        }

                        if (resp.destinationData && resp.destinationData.length > 0) {
                            $('#return_destination')
                                .empty()
                                .append('<option selected disabled value="">Select Destination</option>').prop('disabled', false);
                            resp.destinationData.forEach(function(item) {
                                let displayText = item.name || item.person_name || item.patient_name ||'Unnamed';
                                $('#return_destination').append(
                                    '<option value="' + item.id + '">' + displayText + '</option>'
                                );
                            });
                
                        } else {
                            $('#return_destination')
                            .empty()
                            .append('<option selected disabled value="">No Data Found</option>').prop('disabled', true);
                        }

                        let sourceType = (resp.Source || '').toLowerCase();
                        if (sourceType.includes('location')) {
                            $('#return_sl').show();
                            $('#return_sl label').text('Inventory Source Location');
                            $('#source_applicable').val('1');
                        }
                        else if (sourceType.includes('patient')) {
                            $('#return_sl').show();
                            $('#return_sl label').text('Inventory Source Patient');
                            $('#source_applicable').val('1');
                        }
                        else {
                            $('#return_sl').hide();
                            $('#source_applicable').val('0');
                        }
                        
                        let destType = (resp.Destination || '').toLowerCase();
                        if (destType.includes('location')) {

                            $('#return_dl').show();
                            $('#return_dl label').text('Inventory Destination Location');
                            // $('#return_destination')
                            // .empty()
                            // .append(`<option selected value="${data.inv_location_id}">${data.location_name}</option>`)
                            // .prop('disabled', true);
                            $('#destination_applicable').val('1');
                        }
                        else if (destType.includes('patient')) {
                            $('#return_dl').show();
                            $('#return_dl label').text('Inventory Destination Patient');
                            $('#destination_applicable').val('1');
                        }
                        else {
                            $('#return_dl').hide();
                            $('#destination_applicable').val('0');
                        }

                        let mrSelected = $('#return_mr').val(); // Get selected MR number
                        let sourceTypenew = (resp.Source || '').toLowerCase();
                        let destinationTypenew = (resp.Destination || '').toLowerCase();
                        
                        if (sourceTypenew.includes('patient')) {
                            if (mrSelected) {
                                $('#return_source').html(`<option value="${mrSelected}" selected>${$('#return_mr option:selected').text()}</option>`);
                                $('#return_source').prop('disabled', true);
                            } else {
                                // skipMRLoad = false; 
                                $('#return_source').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
                                // SiteChangeMRCode('#return_site', '#return_source', null);
                                fetchPatientMR(data.site_id, '#return_source', null, function(data) {
                                    $.each(data, function(key, value) {
                                        $('#return_source').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
                                    });
                                });
                                // $('#mr-optional').hide();
                                // Swal.fire({
                                //     icon: 'warning',
                                //     title: 'MR Required',
                                //     text: 'Please select a MR # first because the Source is Patient.',
                                // });
                                // // return;
                                // $('#return_source').html('<option selected disabled value="">Select Source</option>');
                                // $('#return_source').prop('disabled', true);
                            }
                        }

                        if (destinationTypenew.includes('patient')) {
                            if (mrSelected) {
                                $('#return_destination').html(`<option value="${mrSelected}" selected>${$('#return_mr option:selected').text()}</option>`);
                                $('#return_destination').prop('disabled', true);
                            } else {
                                // $('#mr-optional').hide();
                                $('#return_destination').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', false);
                                fetchPatientMR(data.site_id, '#return_destination', null, function(data) {
                                    $.each(data, function(key, value) {
                                        $('#return_source').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
                                    });
                                });
                                
                                // Swal.fire({
                                //     icon: 'warning',
                                //     title: 'MR Required',
                                //     text: 'Please select a MR # first because the Destination is Patient.',
                                // });
                                // // return;
                                // $('#return_destination').html('<option selected disabled value="">Select Destination</option>');
                                // $('#return_destination').prop('disabled', true);
                            }
                        }

                        // if (!sourceTypenew.includes('patient')) {
                        //     $('#return_source').prop('disabled', false);
                        // }
                        // if (!destinationTypenew.includes('patient')) {
                        //     $('#return_destination').prop('disabled', false);
                        // }

                        // $('#return_mr').off('change.idMr').on ('change.idMr', function(){
                        //     let mrSelectedNow = $(this).val();
                        //     let mrSelectedText = $('#return_mr option:selected').text();
                        //     let sourceTypeNow = (sourceTypenew || '').toLowerCase();
                        //     let destinationTypeNow = (destinationTypenew || '').toLowerCase();
                        
                        //     if (sourceTypeNow.includes('patient')) {
                        //         $('#return_source').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                        //         $('#return_source').prop('disabled', true);
                        //     }
                        //     if (destinationTypeNow.includes('patient')) {
                        //         $('#return_destination').html(`<option value="${mrSelectedNow}" selected>${mrSelectedText}</option>`);
                        //         $('#return_destination').prop('disabled', true);
                        //     }
                        
                        // });
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log(error);
                    }
                });
            });

            $('textarea[name="return_remarks"]').val(data.remarks).prop('disabled', false);
            $('input[name="return_reference_document"]').val(data.code).prop('disabled', true);

            $('.duplicate').not(':first').remove();
            let $row = $('.duplicate').first();
            if (data.generic_id && data.brand_id && data.batchNo && data.expiry ) {
                $('.brand_details').show();
                $row.find('.return_generic').html(`<option selected value="${data.generic_id}">${data.generic_name}</option>`).prop('disabled', true);
                // var currentBrandField = $row.find('.return_brand');
                $row.find('.return_brand').html(`<option selected value="${data.brand_id}">${data.brand_name}</option>`).prop('disabled', true);
                $row.find('.return_batch').val(data.batchNo).prop('disabled', true);
                $row.find('.return_expiry').val(data.expiry).prop('disabled', true);
                $row.find('.issue_qty').val(data.issue_qty).prop('disabled', true);
                $row.find('.return_qty').attr('max', data.issue_qty).attr('placeholder', `Max: ${data.issue_qty}`); 
            }

            if (data.demand_qty > 0) {
                $('.mr-dependent').hide();
                $('.mr-nt-dependent').show();
                $('#source_type').val('material');

                $row.find('.return_demand_qty').val(data.demand_qty).prop('disabled', true);
            }
            else if (data.dose && data.days && data.route_id && data.frequency_id && data.demand_qty == 0) {
                $('.mr-dependent').show();
                $('.mr-nt-dependent').hide();
                $('#source_type').val('medication');

                $row.find('.return_dose').val(data.dose).prop('disabled', true);
                $row.find('.return_duration').val(data.days).prop('disabled', true);
                $row.find('.return_route').html(`<option selected value="${data.route_id}">${data.route_name}</option>`).prop('disabled', true);
                $row.find('.return_frequency').html(`<option selected value="${data.frequency_id}">${data.frequency_name}</option>`).prop('disabled', true);
            }
             // Add this to clean up when modal closes
            // $('#add-return').one('hidden.bs.modal', function() {
            //     // Unbind all brand change events
            //     $('.return_brand').off('change.BrandChangeBatch');
            //     // Reset any other state if needed
            //     batchCheckInProgress = false;
            // });

            $('#add-return').modal('show');
            setTimeout(function(){
                $('#ajax-loader').hide();
                }, 1000);        
            });
    });

    // Event listener for respond button
    // $('#add-return').on('hidden.bs.modal', function() {
    //     // $('#add_return')[0].reset();
    //     $('#addMoreBtn, #removeBtn').show();
    //     $('.duplicate').not(':first').remove();
       
    //     $('#return_site')
    //         .prop('disabled', true)
    //         .html('<option selected disabled value="">Select Site</option>');

    //     $('#return_mr')
    //         .prop('disabled', true)
    //         .html('<option selected disabled value="">Select MR #</option>');

    //     $('#return_transactiontype')
    //         .prop('disabled', true)
    //         .html('<option selected disabled value="">Select Transaction Type</option>');

    //     $('.return_generic')
    //         .prop('disabled', true)
    //         .html('<option selected disabled value="">Select Item Generic</option>');
    //     $('.return_brand')
    //         .prop('disabled', true)
    //         .html('<option selected disabled value="">Select Item Brand</option>');

    //     $('.return_batch').val('').prop('disabled', false);
    //     $('.return_expiry').val('').prop('disabled', false);
    //     $('.return_demand_qty').val('').prop('disabled', false);

    //     $('#return_dl, #return_sl, .serviceDetails').hide();
    //     $('.mr-dependent').hide();
    //     $('.req_only').show();
    //     $('#transaction-info-row').empty();
    // });

    $('#add_return').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        var mrSelected = $('#return_mr').val();
        var serviceAvailable = $('#mrService').is(':visible');
        var sourceType = $('#transaction-info-row .source').first().text().toLowerCase();
        var destinationType = $('#transaction-info-row .destination').last().text().toLowerCase();
        var isMRRequired = sourceType.includes('patient') || destinationType.includes('patient');
        if (isMRRequired && !mrSelected) {
            $('#return_mr')
                .next('.select2-container')
                .find('.select2-selection')
                .addClass('requirefield');
            $('#return_mr_error').text("MR # is required when source or destination is patient");
            resp = false;
        }

        if (mrSelected) {
            if (sourceType.includes('patient') && $('#return_source').val() !== mrSelected) {
                $('#return_source_error').text("Source patient must match selected MR#");
                resp = false;
            }
            if (destinationType.includes('patient') && $('#return_destination').val() !== mrSelected) {
                $('#return_destination_error').text("Destination patient must match selected MR#");
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
            : 'input:not([name="return_demand_qty[]"]), textarea, select';

            row.find(fieldsToValidate).each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');

                var errorField = row.find('.' + fieldName + '_error');

                if (!serviceAvailable && 
                    ['return_service', 'return_servicemode', 'return_physician', 'return_billingcc'].includes(fieldName)) {
                    return true;
                }


                if (hasSourceType) {
                    if (sourceType === 'material') {
                        if (['return_dose', 'return_route', 'return_frequency', 'return_duration'].includes(fieldName)) {
                            return true;
                        }
                    } else {
                        if (['return_demand_qty'].includes(fieldName)) {
                            return true;
                        }
                    }
                }

                if (!value || value === "" || (elem.is('select') && value === null)) {
                    console.log(fieldName);
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
        var excludedFields = ['return_reference_document', 'return_remarks'];

        if ($('#return_dl').is(':hidden')) {
            excludedFields.push('return_destination');
        }
        if ($('#return_sl').is(':hidden')) {
            excludedFields.push('return_source');
        }
        
        if (!serviceAvailable) {
            excludedFields = excludedFields.concat([
                'return_service',
                'return_servicemode',
                'return_servicetype',
                'return_servicegroup',
                'return_physician',
                'return_billingcc'
            ]);
        }

    
        if (hasSourceType) {
            if (sourceType === 'material') {
                excludedFields = excludedFields.concat([
                    'return_dose',
                    'return_route',
                    'return_frequency',
                    'return_duration',
                    'return_mr'
                ]);
            } else {
                excludedFields = excludedFields.concat([
                    'return_demand_qty'
                ]);
            }
        }
      
        $(data).each(function(i, field) {
            var originalFieldName = field.name;
            var sanitizedFieldName = originalFieldName.replace(/\[\]/g, '');
            if (excludedFields.indexOf(sanitizedFieldName) !== -1) {
                return true;
            }
            if ((field.value == '') || (field.value == null)) {
                var FieldName = field.name;
                    console.log(FieldName);

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
                url: "/inventory/addreturn",
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
                                $('#add-return').modal('hide');
                                $('#view-return').DataTable().ajax.reload();
                                $('#add_return')[0].reset();
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
                                $('#add-return').modal('hide');
                                $('#view-return').DataTable().ajax.reload();
                                $('#add_return')[0].reset();
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