$(document).ready(function() {
        //Open Service Location Setup
        $(document).on('click', '.add-servicelocation', function() {
            $('.text-danger').show();
            var orgId = $('#sl_org').val();
            if(!orgId)
            {
                $('#sl_org').empty();
                $('#sl_org').select2();
                fetchOrganizations(null,null,'#sl_org', function(data) {
                    var options = ["<option selected disabled value=''>Select Organization</option>"];
                    $.each(data, function(key, value) {
                        options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                    $('#sl_org').html(options.join('')).trigger('change');
                });
            }
          
            $('#add-servicelocation').modal('show');
        });
        //Open Service Location Setup
    
        //Add Service Location
        $('#add_servicelocation').submit(function(e) {
            e.preventDefault();
            var data = SerializeForm(this);
            var resp = true;
    
            $(data).each(function(i, field){
                if ((field.value == '') || (field.value == null))
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
                    url: "/services/addservicelocation",
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
                                    $('#add-servicelocation').modal('hide');
                                    $('#view-servicelocation').DataTable().ajax.reload();
                                    $('#add_servicelocation').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#add_servicelocation')[0].reset();
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
                                    $('#add_servicelocation').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#add_servicelocation')[0].reset();
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
        //Add Service Location
    
        // View Service Location
        var ServiceLocation =  $('#view-servicelocation').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/services/viewservicelocation',
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' ,render: function(data, type, row) {
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
                    width: "300px"
                }
            ]
        });
    
        ServiceLocation.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        ServiceLocation.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        ServiceLocation.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Service Location
    
        // Update Service Location Status
        $(document).on('click', '.servicelocation', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
            $.ajax({
                url: '/services/servicelocation-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-servicelocation').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
    
        });
        // Update Service Location Status
    
        // Update Service Location Modal
        $(document).on('click', '.edit-servicelocation', function() {
            var servicelocationId = $(this).data('servicelocation-id');
            $('#u_slorg').empty();
            $('#u_invstatus').empty();
            $('#update_servicelocation')[0].reset();
            $('#ajax-loader').show();
            var url = '/services/servicelocationmodal/' + servicelocationId;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#ajax-loader').hide();
                    var location = response.location;
                    var orgName = response.orgName;
                    var orgID = response.orgID;
                    var inventoryStatus = response.inventoryStatus;
                    var inventoryStatusId = response.inventoryStatusId;
                    $('#u_slorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                    $('#u_invstatus').html("<option selected value='"+inventoryStatusId+"'>" + inventoryStatus + "</option>");
                    var inventoryStatusNext = inventoryStatusId == '1' ? 'No' : 'Yes';
                    var inventoryStatusNextid = response.inventoryStatusId == '1' ? 0 : 1;
                    $('#u_invstatus').append('<option value="' + inventoryStatusNextid + '">' + inventoryStatusNext + '</option>');
                    $('#u_sl').val(response.location);
                    $('.servicelocation_id').val(servicelocationId);
    
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('.edt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
    
                    fetchOrganizations(orgID,orgName,'#u_slorg', function(data) {
                        $('#u_slorg').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            $('#u_slorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        });
                    });
                    $('#edit-servicelocation').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        // Update Service Location Modal
    
        //Update Service Location
        $('#update_servicelocation').on('submit', function (event) {
            event.preventDefault();
            var formData = SerializeForm(this);
            var Id = $('.servicelocation_id').val();
            var url = '/services/update-servicelocation/' + Id;
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
                                $('#edit-servicelocation').modal('hide');
                                $('#view-servicelocation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#update_servicelocation')[0].reset();
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
        //Update Service Location
});