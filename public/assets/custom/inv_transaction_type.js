$(document).ready(function() {

    function resetLocationSelections() {
        // clear display fields
        $('#source_locations_value, #destination_locations_value').val('');
        // clear stored csv ids
        $('input[name="source_locations[]"], input[name="destination_locations[]"]').val('');

        // if the location modal UI already exists, clear its checkboxes + select-all
        if ($('#invLocationModal').length) {
            $('#invLocationModal input[name="selectedSL[]"]').prop('checked', false);
            $('#selectAllInventory, #selectAllNonInventory').prop('checked', false);
        }
    }

    //Open Add Inventory Transaction Type Setup
    $(document).on('click', '.add-invtransactiontype', function() {
        $('#add_invtransactiontype').find('form').trigger('reset');
        var orgId = $('#itt_org').val();
          resetLocationSelections();

        $('#source_action,#destination_action').prop('disabled', true);
        $('#source_locations_value, #destination_locations_value').val('');
        // FetchAllServiceLocation('#source_locations_value', '#add_invtransactiontype');
        // FetchAllServiceLocation('#destination_locations_value', '#add_invtransactiontype');
        if(!orgId)
        {
            $('#itt_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#itt_org', function(data) {
                $('#itt_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            // fetchOrganizationSites(orgId, '#itt_site', function(data) {
            //     $('#itt_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
            //     $.each(data, function(key, value) {
            //         $('#itt_site').append('<option value="' + value.id + '">' + value.name + '</option>');
            //     });
            // });
        }
        // else{
        //     $('#itt_org').html("<option selected disabled value=''>Select Organization</option>");
        //     fetchOrganizations('null', '','#itt_org', function(data) {
        //         $('#itt_org').find('option:contains("Loading...")').remove();
        //         $.each(data, function(key, value) {
        //             $('#itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
        //         });
        //     });
        //     // $('#itt_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        //     // OrgChangeSites('#itt_org', '#itt_site', '#add_invtransactiontype');
        // }
        // $('#request_mandatory').on('change', function() {
        //     var mandatoryValue = $(this).val();
        //     if (mandatoryValue === 'y') {
        //         $('#request_location_mandatory').val('y').prop('disabled', true).trigger('change');
        //     } else if (mandatoryValue === 'n') {
        //         $('#request_location_mandatory').val('').prop('disabled', false);
        //     }
        // });

        $('#source_location_type, #destination_location_type').on('change', function() {
            var selectedText = $(this).find('option:selected').text().trim(); // Get selected text
            var $actionDropdown;
            var selectedOption;
            
            // Determine which dropdown to update
            if ($(this).attr('id') === 'source_location_type') {
                $actionDropdown = $('#source_action');
                selectedOption = "Source";
                $('#source_action').prop('disabled', false);

            } else {
                $actionDropdown = $('#destination_action');
                selectedOption = "Destination";
                $('#destination_action').prop('disabled', false);
            }

            // Update the action dropdown based on the selected location type
            if (selectedText.toLowerCase() === 'inventory location') {
                $actionDropdown.html(`
                    <option selected disabled>Select ${selectedOption} Action</option>
                    <option value="a">Add</option>
                    <option value="s">Subtract</option>
                `).prop('disabled', false);
            } else {
                $actionDropdown.html(`
                    <option selected disabled>Select ${selectedOption} Action</option>
                    <option value="r">Reversal</option>
                    <option value="n">Not Applicable</option>
                `).prop('disabled', false);
            }
        });

        
        $('#add-invtransactiontype').modal('show');
    });
    $(document).on('change', 'input[name="selectedSL[]"]', function() {
        updateHiddenAllServiceLocation();
    });
    //Open Add Inventory Transaction Type Setup

    //Add Inventory Transaction Type
    $('#add_invtransactiontype').submit(function(e) {
        e.preventDefault();
        updateHiddenAllServiceLocation();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '') || (field.value == null))
            {
                // var FieldName = field.name;
                var FieldName = field.name.replace(/\[\]$/, '');
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
                url: "/inventory/addinvtransactiontype",
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
                                $('#add-invtransactiontype').modal('hide');
                                $('#view-invtransactiontype').DataTable().ajax.reload();
                                $('#add_invtransactiontype')[0].reset();
                                $('#add_invtransactiontype').find('select').each(function(){
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
                                $('#add_invtransactiontype').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_invtransactiontype')[0].reset();
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
    //Add Inventory Transaction Type

    // View Inventory Transaction Type Data
    var viewinvtransactiontype =  $('#view-invtransactiontype').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/invtransactiontype',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'sourceDestination', name: 'sourceDestination' },
            { data: 'locationDetails', name: 'locationDetails' },
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
                width: "250px"
            },
            {
                targets: 3,
                width: "250px"
            },
            {
                targets: 5,
                width: "250px"
            }
        ]
    });

    viewinvtransactiontype.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewinvtransactiontype.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewinvtransactiontype.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Transaction Type Data

    // Update Inventory Transaction Type Status
    $(document).on('click', '.invtransactiontype_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invtransactiontype-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invtransactiontype').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Transaction Type Status

    //Update Inventory Transaction Type Modal
    $(document).on('click', '.edit-invtransactiontype', function() {
        var InnventoryTransactionTypeId = $(this).data('invtransactiontype-id');
        $('input[name="uselectedSL[]"]').prop('checked', false);
        var url = '/inventory/updateinvtransactiontype/' + InnventoryTransactionTypeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('#u_itt-id').val(response.id);
                $('#u_description').val(response.name);
                $('#u_itt_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_itt_org', function(data) {
                    $('#u_itt_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('#u_activity_type').val(response.activitytypeId).change();
               
                $('input[name="u_source_locations_value"]').val(response.sourceLocations);

                $.ajax({
                    url: '/services/getallsl',
                    type: 'GET',
                    success: function(resp) {
                        $('#uinvsourceLocationModal .modal-body .row').empty();
                        resp.forEach(function(item) {
                            let checkboxHtml = `
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" name="uselectedSL[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="usl_${item.id}">
                                        <label class="custom-control-label" for="usl_${item.id}">${item.name}</label>
                                    </div>
                                </div>
                            `;
                            $('#uinvsourceLocationModal .modal-body .row').append(checkboxHtml);
                        });

                        var sourcelocationIds = response.sourcelocationId;
                        if (sourcelocationIds) {
                            var sourcelocationIdsArray = sourcelocationIds.split(',');
                            for(var i = 0; i < sourcelocationIdsArray.length; i++) {
                                $('#usl_' + sourcelocationIdsArray[i]).prop('checked', true);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        if (typeof errorCallback === "function") {
                            errorCallback(error);
                        }
                    }
                });

                $('input[name="u_destination_locations_value"]').val(response.destinationLocations);

                $.ajax({
                    url: '/services/getallsl',
                    type: 'GET',
                    success: function(resp) {
                        $('#uinvdestinationLocationModal .modal-body .row').empty();
                        resp.forEach(function(item) {
                            let checkboxHtml = `
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" name="uselectedDL[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="udl_${item.id}">
                                        <label class="custom-control-label" for="udl_${item.id}">${item.name}</label>
                                    </div>
                                </div>
                            `;
                            $('#uinvdestinationLocationModal .modal-body .row').append(checkboxHtml);
                        });

                        var destinationlocationIds = response.destinationlocationId;
                        if (destinationlocationIds) {
                            var destinationlocationIdsArray = destinationlocationIds.split(',');
                            for(var i = 0; i < destinationlocationIdsArray.length; i++) {
                                $('#udl_' + destinationlocationIdsArray[i]).prop('checked', true);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        if (typeof errorCallback === "function") {
                            errorCallback(error);
                        }
                    }
                });


                // $('#u_inventory_location').val(response.serviceLocationId).change();
                $('#u_applicable_location').val(response.ApplicableLocation).change();
                $('#u_transaction_expired_status').val(response.TransactionExpiredStatus).change();

                // $('#u_request_mandatory').on('change', function() {
                //     var mandatoryValue = $(this).val();
                //     if (mandatoryValue === 'y') {
                //         $('#u_request_location_mandatory').val('y').prop('disabled', true).trigger('change');
                //     } else if (mandatoryValue === 'n') {
                //         // $('#u_request_location_mandatory').val('').prop('disabled', false);
                //         $('#u_request_location_mandatory').val(response.requestLocationMandatory).change().prop('disabled', false);

                //     }
                // });
                // console.log(response.empCheckSourceDestination);
                $('#u_request_mandatory').val(response.requestMandatory).change();
                // $('#u_request_location_mandatory').val(response.requestLocationMandatory).change();
                $('#u_request_emp_location').val(response.requisitionEmpCheck).change();
                $('#u_emp_location_check').val(response.empCheckSourceDestination).change();

                $('#u_source_location_type, #u_destination_location_type').on('change', function() {
                    var selectedText = $(this).find('option:selected').text().trim(); // Get selected text
                    var $actionDropdown;
                    var selectedOption;
                    
                    if ($(this).attr('id') === 'u_source_location_type') {
                        $actionDropdown = $('#u_source_action');
                        selectedOption = "Source";
                        $('#u_source_action').prop('disabled', false);
        
                    } else {
                        $actionDropdown = $('#u_destination_action');
                        selectedOption = "Destination";
                        $('#u_destination_action').prop('disabled', false);
                    }
                    console.log(selectedText);
        
                    if (selectedText.toLowerCase() === 'inventory location') {
                        $actionDropdown.html(`
                            <option selected disabled>Select ${selectedOption} Action</option>
                            <option value="a">Add</option>
                            <option value="s">Subtract</option>
                        `).prop('disabled', false);
                    } else {
                        $actionDropdown.html(`
                            <option selected disabled>Select ${selectedOption} Action</option>
                            <option value="r">Reversal</option>
                            <option value="n">Not Applicable</option>
                        `).prop('disabled', false);
                    }
                });
                console.log(response.sourceLocationTypeId);
                console.log(response.sourceAction);

                $('#u_source_location_type').val(response.sourceLocationTypeId).change();
                $('#u_source_action').val(response.sourceAction).change();
                $('#u_destination_location_type').val(response.destinationLocationTypeId).change();
                $('#u_destination_action').val(response.destinationAction).change();

                $('#edit-invtransactiontype').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    $(document).on('change', 'input[name="uselectedSL[]"]', function() {
        updateHiddenUpdatedSourceLocation();
    });

    $(document).on('change', 'input[name="uselectedDL[]"]', function() {
        updateHiddenUpdatedDestinationLocation();
    });
    //Update Inventory Transaction Type Modal

    //Update Inventory Transaction Type
    $('#u_invtransactiontype').on('submit', function (event) {
        event.preventDefault();
        updateHiddenUpdatedSourceLocation();
        updateHiddenUpdatedDestinationLocation();
        var formData = SerializeForm(this);
        var invtransactiontypeId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_itt-id') {
                invtransactiontypeId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invtransactiontype/' + invtransactiontypeId;
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
                            $('#edit-invtransactiontype').modal('hide');
                            $('#view-invtransactiontype').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invtransactiontype')[0].reset();
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
    //Update Inventory Transaction Type
});