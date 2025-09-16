
//Finance Receiving Payment
$(document).ready(function() {
    //Open Receiving Setup
    $(document).on('click', '.add-financereceiving', function() {
        var orgId = $('#fr_org').val();
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#fr_site', function(data) {
                $('#fr_site').html("<option selected disabled value=''>Select Site</option>");
                $.each(data, function(key, value) {
                    $('#fr_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchFinanceTransactionTypes(orgId,'#fr_transactiontype', function(data) {
                $('#fr_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>");
                $.each(data, function(key, value) {
                    $('#fr_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#fr_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#fr_org', function(data) {
                $('#fr_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#fr_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });  
            $('#fr_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
            OrgChangeSites('#fr_org', '#fr_site', '#add_financereceiving');
            $('#fr_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled',true);
            OrgChangeFinanceTransactionTypes('#fr_org', '#fr_transactiontype', '#add_financereceiving');
        }

        $('#ft_discount').hide();
        $('#fr_discount').attr('required', false);
        $('#add-financereceiving').modal('show');
    });
    $(document).on('change', '#fr_transactiontype', function() {
        var TransactionTypeID = $(this).val();
        console.log(TransactionTypeID);
        CheckFinanceTransactionTypes(TransactionTypeID, '#fr_transactiontype')
            .then(function (data) {
                $.each(data, function (key, value) {
                    if (value.discount_allowed == 'yes') {
                        $('#ft_discount').show();
                        $('#fr_discount').attr('required', true);
                    }
                    else
                    {
                        $('#ft_discount').hide();
                        $('#fr_discount').attr('required', false);
                    }

                    if (value.amount_editable == 'no') {
                        $('#fr_amount').removeAttr('max');
                    }
                    else
                    {   
                        var ceilingAmount = value.amount_ceiling;
                        $('#fr_amount').attr('max', ceilingAmount);
                    }
                });
            })
            .fail(function (error) {
                console.error(error);
            });
    });
    //Open Receiving Setup

    //Add Receiving Setup
    $('#add_financereceiving').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && ((field.name != 'fr_paymentoptiondetails') && (field.name != 'fr_discount')))
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
                url: "/finance/addfinancereceiving",
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
                                $('#add-financereceiving').modal('hide');
                                $('#view-financereceiving').DataTable().ajax.reload();
                                $('#add_financereceiving')[0].reset();
                                $('#add_financereceiving').find('select').each(function(){
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
    //Add Receiving Setup

    // View Finance Receiving
    var viewFinanceReceiving =  $('#view-financereceiving').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/financereceiving',
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
    viewFinanceReceiving.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewFinanceReceiving.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewFinanceReceiving.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Finance Receiving

    // Update Finance Receiving Status
    $(document).on('click', '.ft_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/financetransaction-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-financereceiving').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Finance Receiving Status

    //Update Finance Receiving Modal
    $(document).on('click', '.edit-ft', function() {
        var poId = $(this).data('ft-id');
        $('#edit-financereceiving').modal('show');
        var url = '/finance/updatefinancetransaction/' + poId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_fr_edt').val(formattedDateTime);
                $('#ft-id').val(response.id);
                $('#u_fr_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations(response.orgId,response.orgName,'#u_fr_org', function(data) {
                    $('#u_fr_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_fr_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('#u_fr_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");
                fetchSites(response.orgId, '#u_fr_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_fr_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }, function(error) {
                    console.log(error);
                },response.siteId);

                OrgChangeSites('#u_fr_org', '#u_fr_site', '#update_financereceiving');

                $('#u_fr_transactiontype').html("<option selected value="+ response.TransactionTypeId +">" + response.TransactionTypeName + "</option>");
                fetchFinanceTransactionTypes(response.orgId,'#u_fr_transactiontype', function(data) {
                    $('#u_fr_transactiontype').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(value.id != response.TransactionTypeId)
                        {
                            $('#u_fr_transactiontype').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeFinanceTransactionTypes('#u_fr_org', '#u_fr_transactiontype', '#add_financereceiving');
                if(response.DiscountAllowed == 'no'){
                    $('#uft_discount').hide();
                }
                else{
                    $('#uft_discount').show();
                    $('#u_fr_discount').val(response.Discount);
                    $('#u_fr_discount').attr('required', true);
                }
                if(response.AmountEditable == 'no'){
                    $('#u_fr_amount').removeAttr('max');
                }
                else{
                    var ceilingAmount = response.CeilingAmount;
                    $('#u_fr_amount').attr('max', ceilingAmount);
                }
                $(document).on('change', '#u_fr_transactiontype', function() {
                    var TransactionTypeID = $(this).val();
                    CheckFinanceTransactionTypes(TransactionTypeID, '#u_fr_transactiontype')
                        .then(function (data) {
                            $.each(data, function (key, value) {
                                if (value.discount_allowed == 'yes') {
                                    $('#ft_duft_discountiscount').show();
                                    $('#u_fr_discount').attr('required', true);
                                }
                                else
                                {
                                    $('#uft_discount').hide();
                                    $('#u_fr_discount').attr('required', false);
                                }
            
                                if (value.amount_editable == 'no') {
                                    $('#u_fr_amount').removeAttr('max');
                                }
                                else
                                {   
                                    var ceilingAmount = value.amount_ceiling;
                                    $('#u_fr_amount').attr('max', ceilingAmount);
                                }
                            });
                        })
                        .fail(function (error) {
                            console.error(error);
                        });
                });

                $('#u_fr_paymentoption option[value="' + response.PaymentOption + '"]').remove();
                $('#u_fr_paymentoption').prepend("<option selected value="+ response.PaymentOption +">" + response.PaymentOption + "</option>");

                $('#u_fr_paymentoptiondetails').val(response.PaymentOptionDetail);
                $('#u_fr_amount').val(response.Amount);
                $('#u_fr_remarks').val(response.Remarks);

                $('#edit-financereceiving').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Finance Receiving Modal

    //Update Finance Receiving
    $('#update_financereceiving').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var ftId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ft-id') {
                ftId = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-financetransaction/' + ftId;
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
                            $('#edit-financereceiving').modal('hide');
                            $('#view-financereceiving').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_financereceiving')[0].reset();
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
    //Update Finance Receiving
});
//Finance Receiving Payment