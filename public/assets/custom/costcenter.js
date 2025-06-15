$(document).ready(function() {
        //Add CostCenter
        $('#add_costcenter').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = $(this).serializeArray();
            var cc_type = $('#cc_type').val();
            data.push({ name: 'cc_type', value: cc_type });
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
                    resp = false;
    
                }
            });
    
    
    
            if(resp != false)
            {
                $.ajax({
                    url: "/costcenter/addcostcenter",
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
                                    $('#add-costcenter').modal('hide');
                                    $('#costcenter').DataTable().ajax.reload();
                                    $('#add_costcenter').find('select').val($('#add_costcenter').find('select option:first').val()).trigger('change');
    
                                    $('#add_costcenter')[0].reset();
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
                                    $('#add_costcenter').find('select').val($('#add_costcenter').find('select option:first').val()).trigger('change');
                                    $('#add_costcenter')[0].reset();
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
        //Add CostCenter
    
        // VieW CostCenter
         var viewcostcenter =  $('#costcenter').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/costcenter/ccdata',
                data: function (d) {
                    d.cc_type = $('#fb_ct').val();  
                }
            },
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
                { data: 'type', name: 'type' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 1,
                    width: "200px"
                },
                {
                    targets: 5,
                    width: "300px"
                }
            ]
        });

        $('#fb_ct').on('change', function () {
            viewcostcenter.ajax.reload();  
        });

        $('.clearFilter').on('click', function () {
            $('#fb_ct').val($('#fb_ct option:first').val()).change();
            viewcostcenter.ajax.reload();   
        });
    
        viewcostcenter.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewcostcenter.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewcostcenter.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View CostCenter
    
        // Update CostCenter Status
        $(document).on('click', '.cc_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
            $.ajax({
                url: '/costcenter/update-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                    var status = xhr.status;
                    if(status == 200)
                    {
                        $('#costcenter').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
    
        });
        // Update CostCenter Status
    
        //Update CostCenter Modal
        $(document).on('click', '.edit-costcenter', function() {
            var CostCenterId = $(this).data('costcenter-id');
            var url = '/costcenter/updatecostcenter/' + CostCenterId;
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
                    console.log(response.ccType);
                    $('.cc-id').val(response.id);
                    $('.cc_name').val(response.name);
                    $('.u_cc_type').html("<option selected value='"+response.typeid+"'>" + response.ccType + "</option>");
                    $.ajax({
                        url: 'costcenter/getcctype',
                        type: 'GET',
                        data: {
                            ccTypeId: response.typeid,
                            ccType: response.ccType,
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_cc_type').append('<option value="' + value.id + '">' + value.type + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
    
                    $('#edit-costcenter').modal('show');
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update CostCenter Modal
    
        //Update CostCenter
        $('#update_cc').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var ccId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'cc-id') {
                    ccId = formData[i].value;
                    break;
                }
            }
            var url = '/update-costcenter/' + ccId;
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
                                $('#edit-costcenter').modal('hide');
                                $('#costcenter').DataTable().ajax.reload(); // Refresh DataTable
                                $('#update_cc')[0].reset();
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
        //Update CostCenter
});