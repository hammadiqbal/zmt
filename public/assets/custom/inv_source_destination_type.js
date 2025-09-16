// Inventory Source Destination Type Setup
$(document).ready(function() {
    // Open Inventory Source Destination Type Setup
    $(document).on('click', '.add-invsdt', function() {
        var orgId = $('#invsdt_org').val();
        if(orgId)
        {
            $('#invsdt_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#invsdt_org', function(data) {
                $('#invsdt_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#invsdt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
        }
        $('#add-invsdt').modal('show');
    });
    //Open Inventory Source Destination Type Setup

    //Add Inventory Source Destination Type
    $('#add_invsdt').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'sm_brand'))
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
                url: "/inventory/addsourcedestinationtype",
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
                                $('#add-invsdt').modal('hide');
                                $('#view-invsdt').DataTable().ajax.reload();
                                $('#add_invsdt')[0].reset();
                                $('#add_invsdt').find('select').each(function(){
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
                                $('#add_invsdt').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_invsdt')[0].reset();
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
    //Add Inventory Source Destination Type

    // View Inventory Source Destination Type
    var viewInventorySourceDestinationType =  $('#view-invsdt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/viewsourcedestinationtype',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type' },
            { data: 'third_party_status', name: 'third_party_status' },
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

    viewInventorySourceDestinationType.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewInventorySourceDestinationType.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewInventorySourceDestinationType .on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // Inventory Source Destination Type

    // Update Inventory Source Destination Type Status
    $(document).on('click', '.invsdt_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invsdt-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invsdt').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Inventory Source Destination Type Status

    //Update Inventory Source Destination Type Modal
    $(document).on('click', '.edit-invsdt', function() {
        var invsdtId = $(this).data('invsdt-id');
        var url = '/inventory/updatesourcedestinationtype/' + invsdtId;
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
                $('.u_invsdt-id').val(response.id);
                $('#u_invsdt_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_invsdt_org', function(data) {
                    $('#u_invsdt_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_invsdt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                $('#u_invsd_type').val(response.type);
                $('#u_invsdt_tps').val(response.third_party).change();
                $('#edit-invsdt').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Source Destination Type Modal

    //Update Inventory Source Destination Type
    $('#u_invsdt').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invsdtId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_invsdt-id') {
                invsdtId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-sourcedestinationtype/' + invsdtId;
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
                            $('#edit-invsdt').modal('hide');
                            $('#view-invsdt').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invsdt')[0].reset();
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
    //Update Inventory Source Destination Type
});
// Inventory Source Destination Type Setup
