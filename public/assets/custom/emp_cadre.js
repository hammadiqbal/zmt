$(document).ready(function() {
        //Open Employee cadre Setup
        $(document).on('click', '.add-empCadre', function() {
            var orgId = $('#cadre_org').val();
            $('#add-empCadre').modal('show');
        });
        //Open Employee cadre Setup
    
        //Add Employee Cadre
        $('#add_empCadre').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
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
                    resp = false;
                }
            });
    
            if(resp != false)
            {
                $.ajax({
                    url: "/hr/addempcadre",
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
                                    $('#add-empCadre').modal('hide');
                                    $('#view-empCadre').DataTable().ajax.reload();
                                    $('#add_empCadre')[0].reset();
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
                                    $('#add_empCadre')[0].reset();
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
        //Add Employee Cadre
    
        // View EmployeeCadre Data
        var viewempCadre =  $('#view-empCadre').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/hr/empcadredata',
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
    
        viewempCadre.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        viewempCadre.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        viewempCadre.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Employee Cadre Data
    
        // Update Employee Cadre
        $(document).on('click', '.empCadre_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
    
            $.ajax({
                url: '/hr/empcadre-status',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                    var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-empCadre').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
    
        });
        // Update Employee Cadre
    
        //Update Employee Cadre Modal
        $(document).on('click', '.edit-empCadre', function() {
            var empCadreId = $(this).data('empcadre-id');
            $('#u_cadreOrg').empty()
            var url = '/hr/empcadreStatus/' + empCadreId;
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
                    var orgName = response.orgName;
                    var orgID = response.orgId;
                    $('.ec-id').val(response.id);
                    $('.u_ec').val(response.name);
                    $('#u_cadreOrg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
    
                    fetchOrganizations(orgID,orgName,'#u_cadreOrg', function(data) {
                        $('#u_cadreOrg').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            if(value.id != orgID)
                            {
                                $('#u_cadreOrg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                            }
                        });
                    });
    
                    $('#edit-empCadre').modal('show');
                    $('#ajax-loader').hide();
    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        //Update Employee Cadre Modal
    
        //Update Employee Cadre Data
        $('#u_empCadre').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeArray();
            var ecId;
            for (var i = 0; i < formData.length; i++) {
                if (formData[i].name === 'ec-id') {
                    ecId = formData[i].value;
                    break;
                }
            }
            var url = 'hr/update-empcadre/' + ecId;
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
                                $('#edit-empCadre').modal('hide');
                                $('#view-empCadre').DataTable().ajax.reload(); // Refresh DataTable
                                $('#u_empCadre')[0].reset();
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
        //Update Employee Cadre Data
});