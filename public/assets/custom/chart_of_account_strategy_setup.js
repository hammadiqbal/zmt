// Chart Of Accounts Strategy Setup
$(document).ready(function() {
    //Add Chart Of Accounts Strategy Setup
    $('#add_accountStrategySetup').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        // var data = $(this).serializeArray();
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
                url: "/finance/addaccountstrategysetup",
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
                                // $('#add-accountStrategySetup').modal('hide');
                                // $('#view-accountStrategySetup').DataTable().ajax.reload();
                                // $('#add_accountStrategySetup')[0].reset();
                                // $('#add_accountStrategySetup').find('select').val($('#add_accountStrategySetup').find('select option:first').val()).trigger('change');
                                // $('.text-danger').hide();
                                location.reload();
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
                                $('#add_accountStrategySetup').find('select').val($('#add_accountStrategySetup').find('select option:first').val()).trigger('change');
                                $('#add_accountStrategySetup')[0].reset();
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
    //Add Chart Of Accounts Strategy Setup

    // View Chart Of Accounts Strategy Setup Data
    var viewAccountStrategySetup =  $('#view-accountStrategySetup').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/finance/accountstrategysetupdata',
        order: [[0, 'asc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'account_level_setup', name: 'account_level_setup' },
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

    viewAccountStrategySetup.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewAccountStrategySetup.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewAccountStrategySetup.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Chart Of Accounts Strategy Setup Data

    // Update Chart Of Accounts Strategy Setup Status
    $(document).on('click', '.accountstrategysetup_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/finance/accountstrategysetup-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#ajax-loader').hide();
                    Swal.fire({
                        text: 'Status Updated Successfully',
                        icon: 'success',
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                }
            },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Chart Of Accounts Strategy Setup Status

    //Update Chart Of Accounts Strategy Setup Modal
    $(document).on('click', '.edit-accountStrategySetup', function() {
        var accountStrategySetupId = $(this).data('accountstrategysetup-id');
        var url = '/finance/updateaccountstrategysetup/' + accountStrategySetupId;
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
                $('.ass-id').val(response.id);
                $('.u_ass_org').html("<option selected value="+response.OrgId+">" + response.Org + "</option>");
                fetchAccountStrategyOrganizations(response.OrgId,'.u_ass_org', function(data) {
                    $('.u_ass_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('.u_ass_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $('.u_ass_level').html("<option selected value="+response.accountLevelId+">" + response.accountLevel + "</option>");
                fetchAccountStrategy(response.accountLevelId,'.u_asu_ass_levels_org', function(data) {
                    $('.u_ass_level').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('.u_ass_level').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
                $('#edit-accountStrategySetup').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Chart Of Accounts Strategy Setup Modal

    //Update Chart Of Accounts Setup Strategy
    $('#u_accountStrategySetup').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var Id;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ass-id') {
                Id = formData[i].value;
                break;
            }
        }
        var url = 'finance/update-accountstrategysetup/' + Id;
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
                            // $('#edit-accountStrategySetup').modal('hide');
                            // $('#view-accountStrategySetup').DataTable().ajax.reload(); // Refresh DataTable
                            // $('#u_accountStrategySetup')[0].reset();
                            // $('.text-danger').hide();
                            location.reload();
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
    //Update Chart Of Accounts Setup Strategy

    //View Account Level Setup Modal
    $(document).on('click', '.setup_al', function() {
        var level = $(this).data('level');
        var accountStrategySetupId = $(this).data('accountstrategysetup-id');
        $('#setup-accountLevel').find('.close').show();
        showLevel(1,level,accountStrategySetupId);
        $('#setup-accountLevel').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });
    //View Account Level Setup Modal

    //Exit Account Level Setup Modal
    $(document).on('click', '.exit_al', function() {
        $('#view-accountStrategySetup').DataTable().ajax.reload(); 
    });
    //Exit Account Level Setup Modal

    //View Account Levels Modal
    $(document).on('click', '.view_al', function() {
        var level = $(this).data('level');
        var accountStrategySetupId = $(this).data('accountstrategysetup-id');
        var url = '/finance/viewaccountlevels/';
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                accountStrategySetupId: accountStrategySetupId,
                maxLevel: level,
            },
            dataType: 'json',
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(data) {
                // Call a function to render the data using Nestable
                updateNestableLoop(data,level);
                $('#view-accountLevel').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(error) {
                console.log(error);
            }
        });
        // $('#view-accountLevel').modal('show');
    });
    //View Account Levels Modal

});
// Chart Of Accounts Strategy Setup