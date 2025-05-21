$(document).ready(function() {
        //Add KPI Type
        $('#add_kpitype').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = $(this).serializeArray();
            var KTGroup = $('#kt_group').val();
            var KTDimesnion = $('#kt_dimension').val();
            data.push({ name: 'kt_group', value: KTGroup });
            data.push({ name: 'kt_dimension', value: KTDimesnion });
            var resp = true;
            $(data).each(function(i, field){
                if (field.value == '' || field.value == null)
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
                    url: "/kpi/addkpitype",
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
                                    $('#add-kpitype').modal('hide');
                                    $('#view-kpitype').DataTable().ajax.reload();
                                    $('#add_kpitype').find('select').each(function() {
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#add_kpitype')[0].reset();
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
                                    $('#add_kpitype').find('select').val($('#add_kpitype').find('select option:first').val()).trigger('change');
                                    $('#add_kpitype')[0].reset();
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
        //Add KPI Type
    
        // View KPI Type Data
        var viewkpiMode =  $('#view-kpitype').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/kpi/kpitype',
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
                { data: 'group_name', name: 'group_name',render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
                { data: 'dimension_name', name: 'dimension_name',render: function(data, type, row) {
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
                    targets: 6,
                    width: "250px"
                }
            ]
        });
    
        viewkpiMode.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewkpiMode.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewkpiMode.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View KPI Type Data
    
        // Update KPI Type Status
        $(document).on('click', '.kpitype_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
    
            $.ajax({
                url: '/kpi/kt-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-kpitype').DataTable().ajax.reload();
                    }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                }
            });
    
        });
        // Update KPI Type Status
    
        //Update KPI Types Modal
        $(document).on('click', '.edit-kpitype', function() {
            var KPITypeId = $(this).data('kpitype-id');
            var url = '/kpi/updatekpitype/' + KPITypeId;
            $('#ajax-loader').show();
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#ajax-loader').hide();
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('.edt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
                    $('.kt-id').val(response.id);
                    $('.u_kt').val(response.name);
                    $('.u_kt_group').html("<option selected value='"+response.group_id+"'>" + response.group + "</option>");
                    $('.u_kt_dimension').html("<option selected value='"+response.dimension_id+"'>" + response.dimension + "</option>");
    
                    $.ajax({
                        url: 'kpi/getkpigroup',
                        type: 'GET',
                        data: {
                            groupId: response.group_id,
                            group: response.group,
                        },
                        beforeSend: function() {
                            $('.u_kt_group').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_kt_group').find('option:contains("Loading...")').remove();
                                $('.u_kt_group').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
    
                    $.ajax({
                        url: 'kpi/getkpidimension',
                        type: 'GET',
                        data: {
                            dimensionId: response.dimension_id,
                            dimension: response.dimension,
                        },
                        beforeSend: function() {
                            $('.u_kt_dimension').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_kt_dimension').find('option:contains("Loading...")').remove();
                                $('.u_kt_dimension').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
                    $('#edit-kpitype').modal('show');
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update KPI Types Modal
    
        //Update KPI Types
        $('#u_kpitype').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var ktId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'kt-id') {
                    ktId = formData[i].value;
                    break;
                }
            }
    
            var url = 'kpi/update-kpitype/' + ktId;
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
                                $('#edit-kpitype').modal('hide');
                                $('#view-kpitype').DataTable().ajax.reload();
                                $('#u_kpitype')[0].reset();
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
        //Update KPI Types
});