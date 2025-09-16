//Finance Payments
$(document).ready(function() {
    //Open Payment Setup
    $(document).on('click', '.add-financepayment', function() {
        var orgId = $('#fp_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#fp_site', function(data) {
                $('#fp_site').html("<option selected disabled value=''>Select Site</option>");
                $.each(data, function(key, value) {
                    $('#fp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchFinanceTransactionTypes(orgId,'#fp_transactiontype', function(data) {
                $('#fp_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>");
                $.each(data, function(key, value) {
                    $('#fp_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#fp_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#fp_org', function(data) {
                $('#fp_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#fp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#fp_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#fp_org', '#fp_site', '#add_financepayment');
    
            $('#fp_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
            OrgChangeFinanceTransactionTypes('#fp_org', '#fp_transactiontype', '#add_financepayment');
    
        }
        $('#ft_discount').hide();
        $('#fp_discount').attr('required', false);
        $('#add-financepayment').modal('show');
    });
    $(document).on('change', '#fp_transactiontype', function() {
        var TransactionTypeID = $(this).val();
        console.log(TransactionTypeID);
        CheckFinanceTransactionTypes(TransactionTypeID, '#fp_transactiontype')
            .then(function (data) {
                $.each(data, function (key, value) {
                    if (value.discount_allowed == 'yes') {
                        $('#ft_discount').show();
                        $('#fp_discount').attr('required', true);
                    }
                    else
                    {
                        $('#ft_discount').hide();
                        $('#fp_discount').attr('required', false);
                    }

                    if (value.amount_editable == 'no') {
                        $('#fp_amount').removeAttr('max');
                    }
                    else
                    {   
                        var ceilingAmount = value.amount_ceiling;
                        $('#fp_amount').attr('max', ceilingAmount);
                    }
                });
            })
            .fail(function (error) {
                console.error(error);
            });
    });
    //Open Payment Setup

    //Add Payment Setup
    $('#add_financepayment').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && ((field.name != 'fp_paymentoptiondetails') && (field.name != 'fp_discount')))
            {
                var FieldName = field.name;
                var FieldName = field.name.replace('[]', '');
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
                url: "/finance/addfinancepayment",
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
                                $('#add-financepayment').modal('hide');
                                $('#view-financepayment').DataTable().ajax.reload();
                                $('#add_financepayment')[0].reset();
                                $('#add_financepayment').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('.text-danger').hide();
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
    //Add Payment Setup

    // View Finance Payment
    var viewFinancePayment =  $('#view-financepayment').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/financepayment',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'details', name: 'details' },
            { data: 'debit', name: 'debit' },
            { data: 'account_balance', name: 'account_balance' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });
    viewFinancePayment.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewFinancePayment.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewFinancePayment.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Finance Payment

    // Update Finance Payment Status
    $(document).on('click', '.ft_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/financepayment-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-financepayment').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Finance Payment Status

    //Update Finance Payment Modal
    $(document).on('click', '.edit-ft', function() {
        var poId = $(this).data('ft-id');
        $('#edit-financepayment').modal('show');
        var url = '/finance/updatefinancepayment/' + poId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_fp_edt').val(formattedDateTime);
                $('#ft-id').val(response.id);
                $('#u_fp_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_fp_org', function(data) {
                    $('#u_fp_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_fp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#u_fp_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_fp_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_fp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);

                OrgChangeSites('#u_fp_org', '#u_fp_site', '#update_financepayment');

                $('#u_fp_transactiontype').html("<option selected value="+ response.TransactionTypeId +">" + response.TransactionTypeName + "</option>");
                fetchFinanceTransactionTypes(response.orgId,'#u_fp_transactiontype', function(data) {
                    $('#u_fp_transactiontype').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(value.id != response.TransactionTypeId)
                        {
                            $('#u_fp_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeFinanceTransactionTypes('#u_fp_org', '#u_fp_transactiontype', '#add_financepayment');
                if(response.DiscountAllowed == 'no'){
                    $('#uft_discount').hide();
                }
                else{
                    $('#uft_discount').show();
                    $('#u_fp_discount').val(response.Discount);
                    $('#u_fp_discount').attr('required', true);
                }
                if(response.AmountEditable == 'no'){
                    $('#u_fp_amount').removeAttr('max');
                }
                else{
                    var ceilingAmount = response.CeilingAmount;
                    $('#u_fp_amount').attr('max', ceilingAmount);
                }
                $(document).on('change', '#u_fp_transactiontype', function() {
                    var TransactionTypeID = $(this).val();
                    CheckFinanceTransactionTypes(TransactionTypeID, '#u_fp_transactiontype')
                        .then(function (data) {
                            $.each(data, function (key, value) {
                                if (value.discount_allowed == 'yes') {
                                    $('#ft_duft_discountiscount').show();
                                    $('#u_fp_discount').attr('required', true);
                                }
                                else
                                {
                                    $('#uft_discount').hide();
                                    $('#u_fp_discount').attr('required', false);
                                }
            
                                if (value.amount_editable == 'no') {
                                    $('#u_fp_amount').removeAttr('max');
                                }
                                else
                                {   
                                    var ceilingAmount = value.amount_ceiling;
                                    $('#u_fp_amount').attr('max', ceilingAmount);
                                }
                            });
                        })
                        .fail(function (error) {
                            console.error(error);
                        });
                });

                $('#u_fp_paymentoption option[value="' + response.PaymentOption + '"]').remove();
                $('#u_fp_paymentoption').prepend("<option selected value="+ response.PaymentOption +">" + response.PaymentOption + "</option>");

                $('#u_fp_paymentoptiondetails').val(response.PaymentOptionDetail);
                $('#u_fp_amount').val(response.Amount);
                $('#u_fp_remarks').val(response.Remarks);

                $('#edit-financepayment').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Finance Payment Modal

    //Update Finance Payment
    $('#update_financepayment').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var ftId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ft-id') {
                ftId = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-financepayment/' + ftId;
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
                            $('#edit-financepayment').modal('hide');
                            $('#view-financepayment').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_financepayment')[0].reset();
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
    //Update Finance Payment
});
//Finance Payments