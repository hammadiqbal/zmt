$(document).ready(function() {
    $('#act_kpi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#act_kpi_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    OrgChangeSites('#act_kpi_org', '#act_kpi_site', '#activate_kpi');
    SiteChangeCostCenter('#act_kpi_site', '#act_kpi_cc', '#activate_kpi');

        //Open Activate KPI Setup
        $(document).on('click', '.kpi_activation', function() {
            var orgId = $('#act_kpi_org').val();
            if(orgId)
            {
                fetchOrganizationSites(orgId, '#act_kpi_site', function(data) {
                    $('#act_kpi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('#act_kpi_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
            $('#kpi_activation').modal('show');
        });
        //Open Activate KPI Setup
        $('#activate_kpi').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = $(this).serializeArray();
    
            var kpiID = $('#act_kpi').val();
            var OrgId = $('#act_kpi_org').val();
            var SiteId = $('#act_kpi_site').val();
            var CCID = $('#act_kpi_cc').val();
    
            data.push({ name: 'act_kpi', value: kpiID });
            data.push({ name: 'act_kpi_org', value: OrgId });
            data.push({ name: 'act_kpi_site', value: SiteId });
            data.push({ name: 'act_kpi_cc', value: CCID });
    
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
                    url: "/kpi/activatekpi",
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
                                    $('#kpi_activation').modal('hide');
                                    $('#view-kpiactivation').DataTable().ajax.reload(); // Refresh DataTable
                                    $('#activate_kpi').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#act_kpi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                    $('#act_kpi_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    
                                    $('#activate_kpi')[0].reset();
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
                                    $('#activate_kpi').find('select').each(function() {
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#activate_kpi')[0].reset();
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
        //Activate KPI
    
        // View Activated KPIData
        var ActivatedKPIData =  $('#view-kpiactivation').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/kpi/getactivatekpidata',
            order: [[0, 'desc']],
            columns: [
                { data: 'id_raw', name: 'id_raw', visible: false },
                { data: 'id', name: 'id' },
                { data: 'kpiName', name: 'kpiName' ,render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
                { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }},
                { data: 'ccName', name: 'ccName' ,render: function(data, type, row) {
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
                    targets: 2,
                    width: "150px"
                },
                {
                    targets: 6,
                    width: "250px"
                }
            ]
        });
    
        ActivatedKPIData.on('draw.dt', function() {
            $('[data-toggle="popover"]').popover({
                html: true
            });
        });
        // Show the loader before an AJAX request is made
        ActivatedKPIData.on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });
        // Hide the loader after the AJAX request is complete
        ActivatedKPIData.on('xhr.dt', function() {
            $('#ajax-loader').hide();
        });
        // View Activated KPIData
    
        // Update Activated KPI Status
        $(document).on('click', '.activatekpi', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};
            $.ajax({
                url: '/kpi/update-activatekpi',
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                    if(status == 200)
                    {
                        $('#view-kpiactivation').DataTable().ajax.reload();
                    }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                }
            });
    
        });
        // Update Activated KPI Status
    
        // Update Activated KPI Modal
        $(document).on('click', '.edit-activatekpi', function() {
            var activatekpiId = $(this).data('activatekpi-id');
            $('#u_kpi').empty();
            $('#u_korg').empty();
            $('#u_ksite').empty();
            $('#uk_edt').empty();
    
            $('#update_kpiactivation')[0].reset();
            $('#ajax-loader').show();
            var url = '/kpi/updateactivatekpi/' + activatekpiId;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#ajax-loader').hide();
                    var kpiName = response.kpiName;
                    var kpiID = response.kpiID;
                    var orgName = response.orgName;
                    var orgID = response.orgID;
                    var siteName = response.siteName;
                    var siteId = response.siteId;
                    var ccName = response.ccName;
                    var ccID = response.ccID;
                    $('#u_kpi').html("<option selected value='"+kpiID+"'>" + kpiName + "</option>");
                    $('#u_korg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                    $('#u_ksite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                    $('#u_kcc').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                    $('.u_kpi_id').val(response.id);
                    var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('.edt').each(function() {
                        var edtElement = $(this);
                        edtElement.val(formattedDateTime);
                    });
    
                    $.ajax({
                        url: 'kpi/getselectedkpi',
                        type: 'GET',
                        data: {
                            kpiID: kpiID,
                        },
                        beforeSend: function() {
                            $('#u_kpi').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_kpi').find('option:contains("Loading...")').remove();
                            $.each(resp, function(key, value) {
                                $('#u_kpi').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
    
                    fetchOrganizations(orgID,orgName,'#u_korg', function(data) {
                        $('#u_korg').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            $('#u_korg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        });
                    });
    
                    if (orgID) {
                        fetchSites(orgID, '#u_ksite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    if(value.id != siteId){
                                        $('#u_ksite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-kpiactivation').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
    
    
                        $('#u_korg').off('change').on('change', function() {
                            $('#u_ksite').empty();
                            var organizationId = $(this).val();
                            fetchSites(organizationId, '#u_ksite', function(data) {
                                if (data.length > 0) {
                                    $.each(data, function(key, value) {
                                        if(value.id != siteId){
                                            $('#u_ksite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                        }
                                    });
                                }
                                else {
                                    Swal.fire({
                                        text: 'Sites are not available for selected Organization',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#edit-kpiactivation').modal('hide');
                                        }
                                    });
                                }
                            }, function(error) {
                                console.log(error);
                            });
                        });
    
    
                        fetchActivatedCostCenters(siteId, '#u_kcc', function(data) {
                            if (data.length > 0) {
                                $('#u_kcc').find('option:contains("Loading...")').remove(); // Remove the loading option
                                $.each(data, function(key, value) {
                                    if(value.id != response.ccID){
                                        $('#u_kcc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                            }
                            else{
                                Swal.fire({
                                    text: 'Cost Centerss are not Activated for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-kpiactivation').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
    
                        $('#u_ksite').off('change').on('change', function() {
                            var siteId = $(this).val();
                            fetchActivatedCostCenters(siteId, '#u_kcc', function(data) {
                                if (data.length > 0) {
                                    $('#u_kcc').empty();
    
                                    $('#u_kcc').find('option:contains("Loading...")').remove(); // Remove the loading option
                                    $.each(data, function(key, value) {
                                        $('#u_kcc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    });
                                    var ccID = $('#u_kcc').val();
                                }
                                else{
                                    Swal.fire({
                                        text: 'Cost Centers are not Activated for selected Site',
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        allowOutsideClick: false
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#edit-kpiactivation').modal('hide');
                                        }
                                    });
                                }
                            }, function(error) {
                                console.log(error);
                            });
    
                        });
    
    
                    }
    
                    $('#edit-kpiactivation').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#ajax-loader').hide();
                    console.log(textStatus, errorThrown);
                }
            });
        });
        // Update Activated KPI Modal
    
        //Update Activated KPI
        $('#update_kpiactivation').on('submit', function (event) {
            event.preventDefault();
            var data = SerializeForm(this);
            var Id = $('.u_kpi_id').val();
            var url = '/update-activatekpi/' + Id;
            $.ajax({
                url: url,
                method: 'POST',
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
                                $('#edit-kpiactivation').modal('hide');
                                $('#view-kpiactivation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#update_kpiactivation')[0].reset();
                                $('.text-danger').hide();
                            }
                            $('.text-danger').hide();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });
        //Update Activated KPI
});