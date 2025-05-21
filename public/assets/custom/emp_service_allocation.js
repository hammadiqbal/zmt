$(document).ready(function() {
    //Open Add Allocate Service Setup
    $('#site_sa').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#org_sa', '#site_sa', '#emp_serviceallocation');

    $(document).on('click', '.emp-serviceallocation', function() {
        var orgId = $('#org_sa').val();
        $('#emp_services').show(); 
        
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#site_sa', function(data) {
                $('#site_sa').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#site_sa').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            fetchOrganizations(null,null,'#org_sa', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#org_sa').html(options.join('')).trigger('change');
            });
        }
        $('#emp_sa').empty();
        $('#service_sa').select2();
        $('#emp_sa').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);

        SiteChangeEmployees('#site_sa', '#emp_sa', '#emp_serviceallocation');

        $('input[name="service_sa_value"]').prop('disabled', true);
        EmployeeChangeMultiSelectService('#emp_sa', '#site_sa', '#service_sa_value', '#emp_serviceallocation');
        
        $('#empserviceallocation').modal('show');
    });
    //Open Add Allocate Service Setup

    //Allocate Service
    $('#multiService').on('change', 'input[name="selectedServices[]"]', function() {
        updateHiddenServices();
    });

    $('#selectAllempServiceAllocation').on('change', function() {
        $('input[name="selectedServices[]"]').prop('checked', $(this).prop('checked')); 
        updateHiddenServices();
    });
    
    $('#emp_serviceallocation').submit(function(e) {
        e.preventDefault();
        updateHiddenServices();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            var FieldName = field.name;
            var fieldValue = field.value;
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined') && field.name != 'service_sa[]')
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
                url: "/hr/allocateemp-service",
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
                                $('#empserviceallocation').modal('hide');
                                $('#view-allocatedservice').DataTable().ajax.reload();
                                $('#emp_serviceallocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#emp_serviceallocation')[0].reset();
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
                                $('#emp_serviceallocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#emp_serviceallocation')[0].reset();
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
    //Allocate Service

    // View Allocated Service
    var AllocateService =  $('#view-allocatedservice').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewallocatedservice',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'services', name: 'services' ,render: function(data, type, row) {
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
                targets: 2,
                width: "500px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    AllocateService.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    AllocateService.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    AllocateService.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Allocated Service

    // Update Allocated Service Status
    $(document).on('click', '.serviceallocation_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/sa-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-allocatedservice').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Allocated Service Status

    // Update Allocated Service Modal
    $('input[name="uselectedServices[]"]').change(function() {
        updateHiddenUpdatedServiceModes();
    });

    $(document).on('click', '.edit-serviceallocation', function() {
        $('input[name="uselectedServices[]"]').prop('checked', false);
        $('#umultiService').empty(); 
        $( 'input[name= "uservice_value"' ).removeClass('requirefield');
        var serviceallocationId = $(this).data('serviceallocation-id');
        $('#u_saemp').empty();
        $('#u_saservice').empty();
        $('#update_serviceallocation')[0].reset();
        $('#ajax-loader').show();
        var url = '/hr/serviceallocationmodal/' + serviceallocationId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var empName = response.empName;
                var empID = response.empID;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var serviceNames = response.serviceNames;
                $('input[name="uservice_value"]').val(serviceNames);
                var serviceIds = response.serviceId;
                var serviceIDs = serviceIds.split(',');
                $('#u_saemp').val(empName);
                $('.u_sa_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.usa_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                AllocateEmpServices(empID, siteId,'#u_saservice', function(data) {
                    if ($.fn.DataTable.isDataTable('#umultiserviceTable')) {
                        $('#umultiserviceTable').DataTable().clear().destroy(); 
                    }
                    data.forEach(item => {
                        var billingCostCenter = item.BillingCCNames  ? 
                        item.BillingCCNames .split(',').map((billingcc, index, array) => 
                            `${billingcc}${index < array.length - 1 ? '<hr class="mt-1 mb-1">' : ''}`
                        ).join('') : 
                        'No Billing Cost Center Available';  

                        var performingCostCenter = item.PerformingCCNames  ? 
                        item.PerformingCCNames .split(',').map((performingcc, index, array) => 
                            `${performingcc}${index < array.length - 1 ? '<hr class="mt-1 mb-1">' : ''}`
                        ).join('') : 
                        'No Performing Cost Center Available';  
                        
                        var serviceModes = item.ServiceModeNames ? 
                        item.ServiceModeNames.split(',').map((mode, index, array) => 
                            `${mode}${index < array.length - 1 ? '<hr class="mt-1 mb-1">' : ''}`
                        ).join('') : 
                        'No Modes Available';                       
                        var embedData = `
                            <tr style="font-size:14px;cursor:pointer;">
                                <td>
                                    <div class="custom-control custom-checkbox p-1">
                                        <input type="checkbox" name="uselectedServices[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="uas_${item.id}">
                                        <label class="custom-control-label" for="uas_${item.id}"></label>
                                    </div>
                                </td>
                                <td>${item.name}</td>
                                <td>${item.ServiceTypeName}</td>
                                <td>${item.ServiceGroupName}</td>
                                <td>${billingCostCenter}</td>
                                <td>${performingCostCenter}</td>
                                <td>${serviceModes}</td>
                            </tr>`;
                        $('#umultiService').append(embedData);

                        if (serviceIDs.includes(item.id.toString())) {
                            $('#uas_' + item.id).prop('checked', true);
                        }
                        
                    });
                    $('#umultiService').off('click', 'tr').on('click', 'tr', function(e) {
                        let $checkbox = $(this).find('input[type="checkbox"]');
                        $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
                    });
                    $('#umultiserviceTable').DataTable({
                        paging: false,
                        searching: true, 
                        ordering: true, 
                        columnDefs: [
                            { orderable: false, targets: [0] } 
                        ]
                    });

                });

                $('#edit-serviceallocation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Allocated Service Modal

    //Update Allocated Service
    $('#umultiService').on('change', 'input[name="uselectedServices[]"]', function() {
        updateHiddenUpdatedServices();
    });

    $('#update_serviceallocation').on('submit', function (event) {
        event.preventDefault();
        updateHiddenUpdatedServices();
        var formData = SerializeForm(this);
        var Id = $('.u_sa_id').val();
        var uServiceValue = $('input[name="uservice_value"]').val();
        if (!uServiceValue) {
            $( 'input[name= "uservice_value"' ).addClass('requirefield');
            return;
        }
        var url = '/hr/update-allocatedservice/' + Id;
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
                            $( 'input[name= "uservice_value"' ).removeClass('requirefield');
                            $('#edit-serviceallocation').modal('hide');
                            $('#view-allocatedservice').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_serviceallocation')[0].reset();
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
    //Update Allocated Service

});