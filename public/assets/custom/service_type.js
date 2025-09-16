$(document).ready(function() {
        //Add Service Type
        $('#add_servicetype').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = $(this).serializeArray();
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
                    resp = false;
                }
            });
    
            if(resp != false)
            {
                $.ajax({
                    url: "/services/addservicetype",
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
                                    $('#add-servicetype').modal('hide');
                                    $('#view-servicetype').DataTable().ajax.reload();
                                    $('#add_servicetype')[0].reset();
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
                                    $('#add_servicetype')[0].reset();
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
        //Add Service Type
    
        // View Service Type Data
        var viewserviceType =  $('#view-servicetype').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/services/servicetype',
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'id', name: 'id' },
                {
                    "data": 'name',
                    "render": function(data, type, row) {
                        return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                    }
                },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 1,
                    width: "200px"
                },
                {
                    targets: 4,
                    width: "300px"
                }
            ]
        });
    
        viewserviceType.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewserviceType.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewserviceType.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Service Type Data
    
        // Update Service Type Status
        $(document).on('click', '.servicetype_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
    
            $.ajax({
                url: '/services/st-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-servicetype').DataTable().ajax.reload();
                    }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                }
            });
    
        });
        // Update Service Type Status
    
        //Update Service Type Modal
        $(document).on('click', '.edit-servicetype', function() {
            var ServiceModeId = $(this).data('servicetype-id');
            var url = '/services/updateservicetype/' + ServiceModeId;
            $('#ajax-loader').show();
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('.edt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
                    $('.st-id').val(response.id);
                    $('.u_st').val(response.name);
                    $('#edit-servicetype').modal('show');
                    $('#ajax-loader').hide();
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update Service Type Modal
    
        //Update Service Type
        $('#u_servicetype').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var stId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'st-id') {
                    stId = formData[i].value;
                    break;
                }
            }
            var url = 'services/update-servicetype/' + stId;
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
                                $('#edit-servicetype').modal('hide');
                                $('#view-servicetype').DataTable().ajax.reload(); // Refresh DataTable
                                $('#u_servicetype')[0].reset();
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
        //Update Service Type
});