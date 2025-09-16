
// Financial Payroll Deduction
$(document).ready(function() {
    //Add  Payroll Deduction
    $('#add_payrolldeduction').submit(function(e) {
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                
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
                url: "/finance/addpayrolldeduction",
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
                                $('#add-payrolldeduction').modal('hide');
                                $('#view-payrolldeduction').DataTable().ajax.reload();
                                $('#add_payrolldeduction').find('select').val($('#add_payrolldeduction').find('select option:first').val()).trigger('change');
                                $('#add_payrolldeduction')[0].reset();
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
                                $('#add_payrolldeduction').find('select').val($('#add_payrolldeduction').find('select option:first').val()).trigger('change');
                                $('#add_payrolldeduction')[0].reset();
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
    //Add  Payroll Deduction

    // View  Payroll Deduction
    var viewPayrollDeduction =  $('#view-payrolldeduction').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/payrolldeductiondata',
        order: [[0, 'asc']],
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
                width: "300px"
            },
            {
                targets: 4,
                width: "300px"
            }
        ]
    });

    viewPayrollDeduction.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewPayrollDeduction.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewPayrollDeduction.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Payroll Deduction

    // Update Payroll Deduction Status
    $(document).on('click', '.payrolldeduction_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/payrolldeduction-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-payrolldeduction').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Payroll Deduction Status

    //Update Payroll Deduction Modal
    $(document).on('click', '.edit-payrolldeduction', function() {
        var PayrollDeduction = $(this).data('payrolldeduction-id');
        var url = '/finance/updatepayrolldeduction/' + PayrollDeduction;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#date-format1').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_payrolldeduction-id').val(response.id);
                $('.u_payrolldeduction').val(response.name);
                $('.u_pd_org').html("<option selected value='"+response.orgId+"'>" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'.u_pd_org', function(data) {
                    $('.u_pd_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('.u_pd_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });
                $('#edit-payrolldeduction').modal('show');
                $('#ajax-loader').hide();
              
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Payroll Deduction Modal

    //Update Payroll Deduction
    $('#u_payrolldeduction').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_payrolldeduction-id') {
                Id = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-payrolldeduction/' + Id;
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
                else if (fieldName == 'info')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-payrolldeduction').modal('hide');
                            $('#view-payrolldeduction').DataTable().ajax.reload();
                        }
                    });
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
                            $('#edit-payrolldeduction').modal('hide');
                            $('#view-payrolldeduction').DataTable().ajax.reload(); // Refresh DataTable
                            $('.text-danger').hide();
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Payroll Deduction
});
// Financial Payroll Deduction