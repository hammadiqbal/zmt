
// Finance Transaction Type
$(document).ready(function() {
    // Open Finance Transaction Type Modal
    $(document).on('click', '.add-financetransactiontype', function() {
        var orgId = $('#ftt_org').val();
        if(orgId)
        {
            fetchTransactionSourceDestination(orgId, '#ftt_source', function(data) {
                $('#ftt_source').html("<option selected disabled value=''>Select Organization</option>");
                $.each(data, function(key, value) {
                        $('#ftt_source').append('<option data-name="'+value.name+'" value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchTransactionSourceDestination(orgId, '#ftt_destination', function(data) {
                $('#ftt_destination').html("<option selected disabled value=''>Select Organization</option>");
                $.each(data, function(key, value) {
                    $('#ftt_destination').append('<option data-name="'+value.name+'" value="' + value.id + '">' + value.name + '</option>');
                });
            });
            fetchOrganizationLedger(orgId, '#ftt_ledger', function(data) {
                $('#ftt_ledger').html("<option selected disabled value=''>Select Ledger Type</option>");
                $.each(data, function(key, value) {
                        $('#ftt_ledger').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });

            fetchOrganizationAccount(orgId, '#ftt_debit', function(data) {
                $('#ftt_debit').html("<option selected disabled value=''>Select Debit Account</option>");
                $.each(data, function(key, value) {
                    $('#ftt_debit').append('<option value="' + value.id + '">' + value.accountNames + '</option>');
                });
            });

            fetchOrganizationAccount(orgId, '#ftt_credit', function(data) {
                $('#ftt_credit').html("<option selected disabled value=''>Select Credit Account</option>");
                $.each(data, function(key, value) {
                    $('#ftt_credit').append('<option value="' + value.id + '">' + value.accountNames + '</option>');
                });
            });
        }
        else{
            $('#ftt_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#ftt_org', function(data) {
                $('#ftt_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ftt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#ftt_source').html("<option selected disabled value=''>Select Transaction Source</option>").prop('disabled',true);
            OrgChangeFinanceTransactionSourceDestination('#ftt_org', '#ftt_source', '#add_financetransactiontype');
         
            $('#ftt_destination').html("<option selected disabled value=''>Select Transaction Destination</option>").prop('disabled',true);
            OrgChangeFinanceTransactionSourceDestination('#ftt_org', '#ftt_destination', '#add_financetransactiontype');
    
            $('#ftt_ledger').html("<option selected disabled value=''>Select Ledger Type</option>").prop('disabled',true);
            OrgChangeLedger('#ftt_org', '#ftt_ledger', '#add_financetransactiontype');
    
            $('#ftt_debit').html("<option selected disabled value=''>Select Debit Account</option>").prop('disabled',true);
            OrgChangeAccount('#ftt_org', '#ftt_debit', '#add_financetransactiontype');
    
            $('#ftt_credit').html("<option selected disabled value=''>Select Credit Account</option>").prop('disabled',true);
            OrgChangeAccount('#ftt_org', '#ftt_credit', '#add_financetransactiontype');

        }
        $('#ceilingamount').hide();
        $(document).off('change', '#ftt_amounteditable').on('change', '#ftt_amounteditable', function() {
            var selectedval = $(this).val();
            if(selectedval == 'yes')
            {
                $('#ceilingamount').show();
            }
            else{
                $('#ceilingamount').hide();
            }
        });
        $('#add-financetransactiontype').modal('show');

    });
    // Open Finance Transaction Type Modal
    
    //Add Finance Transaction Type
    $('#add_financetransactiontype').submit(function(e) {
        e.preventDefault(); 
        var data = SerializeForm(this);
        var EditingStatus;
        var Destination = null; 
        var Source = null;
        for (var i = 0; i < data.length; i++) {
            if (data[i].name === 'ftt_amounteditable') {
                EditingStatus = data[i].value;
            }
            if (data[i].name === 'ftt_destination') {
                Destination = data[i].value;
            }
            if (data[i].name === 'ftt_source') {
                Source = data[i].value;
            }
        }
        var logic = false; 
        var excludedFields = [];
      
        $('#ceilingamount').hide(); 
        if (EditingStatus !== 'no' && EditingStatus !== null) {
            $('#ceilingamount').show();
        } else {
            excludedFields.push('ftt_amountceiling');
        }
    
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value === '' || field.value === null) && (!logic || !excludedFields.includes(field.name)))
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
                url: "/finance/addtransactiontype",
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
                                $('#add-financetransactiontype').modal('hide');
                                $('#view-financetransactiontype').DataTable().ajax.reload();
                                $('#add_financetransactiontype').find('select').each(function() {
                                    $(this).val('').trigger('change'); 
                                });
                                $('#add_financetransactiontype')[0].reset();
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
                                $('#add_financetransactiontype').find('select').each(function() {
                                    $(this).val('').trigger('change'); 
                                });
                                $('#add_financetransactiontype')[0].reset();
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
    //Add Finance Transaction Type

    // View Finance Transaction Type
    var viewfinancetransactiontype =  $('#view-financetransactiontype').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/financetransactiontypedata',
        order: [[0, 'asc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'details', name: 'details' },
            { data: 'account', name: 'account' },
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

    viewfinancetransactiontype.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewfinancetransactiontype.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewfinancetransactiontype.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Finance Transaction Type

    // Update Finance Transaction Type Status
    $(document).on('click', '.ftt_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/transactiontype-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-financetransactiontype').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Finance Transaction Type Status

    //Update Finance Transaction Type Modal
    $(document).on('click', '.edit-ftt', function() {
        var TransactiontypeID = $(this).data('ftt-id');
        var url = '/finance/updatefinancetransactiontype/' + TransactiontypeID;
        $('#ajax-loader').show();

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_ftt_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_ftt-id').val(response.id);
                $('#u_ftt_desc').val(response.Description);
                $('#u_ftt_org').html("<option selected value='"+response.orgId+"'>" + response.orgName + "</option>");
                fetchOrganizations(response.orgId, '','#u_ftt_org', function(data) {
                    $.each(data, function(key, value) {
                        $('#u_ftt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                const ActivityTypes = ["Inward", "OutWard", "Reverse"];
                let ActivityOptions = '<option selected value="' + response.Activity + '">' + response.dispActivity + '</option>';
                for (let ActivityType of ActivityTypes) {
                    if (response.dispActivity !== ActivityType) {
                        ActivityOptions += '<option value="' + ActivityType + '">' + ActivityType + '</option>';
                    }
                }
                $('#u_ftt_activity').html(ActivityOptions);


                $('#u_ftt_source').html("<option selected value='"+response.TransactionSourceId+"'>" + response.TransactionSourceName + "</option>");
                fetchTransactionSourceDestination(response.orgId, '#u_ftt_source', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.TransactionSourceId) {
                            $('#u_ftt_source').append('<option data-name="'+value.name+'" value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeFinanceTransactionSourceDestination('#u_ftt_org', '#u_ftt_source', '#update_financetransactiontype');

                $('#u_ftt_destination').html("<option selected value='"+response.TransactionDestinationId+"'>" + response.TransactionDestinationName + "</option>");
                fetchTransactionSourceDestination(response.orgId, '#u_ftt_destination', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.TransactionDestinationId) {
                            $('#u_ftt_destination').append('<option data-name="'+value.name+'" value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeFinanceTransactionSourceDestination('#u_ftt_org', '#u_ftt_destination', '#update_financetransactiontype');

                $('#u_ftt_debit').html("<option selected value='"+response.DebitAccountId+"'>" + response.DebitAccount + "</option>");
                fetchOrganizationAccount(response.orgId, '#u_ftt_debit', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.DebitAccountId) {
                            $('#u_ftt_debit').append('<option value="' + value.id + '">' + value.accountNames + '</option>');
                        }
                    });
                });
                OrgChangeAccount('#u_ftt_org', '#u_ftt_debit', '#update_financetransactiontype');

                $('#u_ftt_credit').html("<option selected value='"+response.CreditAccountId+"'>" + response.CreditAccount + "</option>");
                fetchOrganizationAccount(response.orgId, '#u_ftt_credit', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.CreditAccountId) {
                            $('#u_ftt_credit').append('<option value="' + value.id + '">' + value.accountNames + '</option>');
                        }
                    });
                });
                OrgChangeAccount('#u_ftt_org', '#u_ftt_credit', '#update_financetransactiontype');

                $('#u_ftt_ledger').html("<option selected value='"+response.LedgerId+"'>" + response.Ledger + "</option>");
                fetchOrganizationLedger(response.orgId, '#u_ftt_ledger', function(data) {
                    $.each(data, function(key, value) {
                        if (value.id !== response.LedgerId) {
                            $('#u_ftt_ledger').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeLedger('#u_ftt_org', '#u_ftt_ledger', '#update_financetransactiontype');

                const AmountEditings = ["Yes", "No"];
                let AmountEditingOptions = '<option selected value="' + response.AmountEditable + '">' + response.DisplayAmountEditable + '</option>';
                for (let AmountEditing of AmountEditings) {
                    if (response.DisplayAmountEditable !== AmountEditing) {
                        AmountEditingOptions += '<option value="' + AmountEditing + '">' + AmountEditing + '</option>';
                    }
                }
                $('#u_ftt_amounteditable').html(AmountEditingOptions);
                if(response.AmountEditable == 'Yes')
                {
                    $('#u_ceilingamount').show();
                    $('#u_ftt_amountceiling').val(response.AmountCeiling);
                    $('#u_ftt_amountceiling').prop('required', true);
                }
                else{
                    $('#u_ceilingamount').hide();
                    $('#u_ftt_amountceiling').val('');
                    $('#u_ftt_amountceiling').prop('required', false);
                }
                $('#u_ftt_amountceiling').val(response.AmountCeiling);
                $(document).off('change', '#u_ftt_amounteditable').on('change', '#u_ftt_amounteditable', function() {
                    var selectedval = $(this).val();
                    if(selectedval == 'Yes')
                    {
                        $('#u_ceilingamount').show();
                        $('#u_ftt_amountceiling').val(response.AmountCeiling);
                        $('#u_ftt_amountceiling').prop('required', true);
                    }
                    else{
                        $('#u_ceilingamount').hide();
                        $('#u_ftt_amountceiling').val('');
                        $('#u_ftt_amountceiling').prop('required', false);
                    }
                });

                const DiscountAllows = ["Yes", "No"];
                let DiscountAllowOptions = '<option selected value="' + response.DiscountAllowed + '">' + response.DisplayDiscountAllowed + '</option>';
                for (let DiscountAllow of DiscountAllows) {
                    if (response.DisplayDiscountAllowed !== DiscountAllow) {
                        DiscountAllowOptions += '<option value="' + DiscountAllow + '">' + DiscountAllow + '</option>';
                    }
                }
                $('#u_ftt_discountallowed').html(DiscountAllowOptions);
                
                $('#ajax-loader').hide();
                $('#edit-financetransactiontype').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Finance Transaction Type Modal

    //Update Finance Transaction Type
    $('#update_financetransactiontype').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_ftt-id') {
                Id = formData[i].value;
                break;
            }
        }
        var url = '/finance/update-financetransactiontype/' + Id;
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
                            $('#edit-financetransactiontype').modal('hide');
                            $('#view-financetransactiontype').DataTable().ajax.reload();
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
                            $('#edit-financetransactiontype').modal('hide');
                            $('#view-financetransactiontype').DataTable().ajax.reload(); // Refresh DataTable
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
    //Update Finance Transaction Type
});
// Finance Transaction Type