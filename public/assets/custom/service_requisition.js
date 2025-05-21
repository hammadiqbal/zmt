$(document).ready(function() {
    
    // Service Requisition Setup
    // $(document).on('click', '.add-serviceRequisitionSetup', function() {
    //     $('.text-danger').show();
    //     var orgId = $('#sr_org').val();
    //     if(!orgId)
    //     {
    //         $('#sr_org').empty();
    //         $('#sr_org').select2();
    //         fetchOrganizations(null,null,'#sr_org', function(data) {
    //             var options = ["<option selected disabled value=''>Select Organization</option>"];
    //             $.each(data, function(key, value) {
    //                 options.push('<option value="' + value.id + '">' + value.organization + '</option>');
    //             });
    //             $('#sr_org').html(options.join('')).trigger('change');
    //         });
    //     }
        
    //     $('#add-serviceRequisitionSetup').modal('show');
    // });

    // $('#add_serviceRequisitionSetup').submit(function(e) {
    //     e.preventDefault();
    //     var data = SerializeForm(this);
    //     var resp = true;

    //     $(data).each(function(i, field){
    //         if ((field.value === '' || field.value === null) && field.name !== 'sr_description') {
    //             var FieldName = field.name;
    //             var FieldID = '#'+FieldName + "_error";
    //             $(FieldID).text("This field is required");
    //             $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
    //             $( 'input[name= "' +FieldName +'"' ).focus(function() {
    //                 $(FieldID).text("");
    //                 $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
    //             })
    //             $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
    //             $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
    //                 $(FieldID).text("");
    //                 $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
    //             });
    //             resp = false;
    //         }
    //     });

    //     if(resp != false)
    //     {
    //         $.ajax({
    //             url: "/services/addservicerequisition",
    //             method: "POST",
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             data: data,
    //             beforeSend: function() {
    //                 Swal.fire({
    //                     title: "Processing",
    //                     allowOutsideClick: false,
    //                     willOpen: () => {
    //                         Swal.showLoading();
    //                     },
    //                     showConfirmButton: false
    //                 });
    //             },
    //             success: function(response) {

    //                 for (var fieldName in response) {
    //                     var fieldErrors = response[fieldName];
    //                 }
    //                 if (fieldName == 'error')
    //                 {
    //                     Swal.fire({
    //                         text: fieldErrors,
    //                         icon: fieldName,
    //                         confirmButtonText: 'OK'
    //                     })
    //                 }
    //                 else if (fieldName == 'success')
    //                 {
    //                     Swal.fire({
    //                         text: fieldErrors,
    //                         icon: fieldName,
    //                         allowOutsideClick: false,
    //                         confirmButtonText: 'OK'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             $('#add-serviceRequisitionSetup').modal('hide');
    //                             $('#view-serviceRequisitionSetup').DataTable().ajax.reload();
    //                             $('#add_serviceRequisitionSetup').find('select').each(function(){
    //                                 $(this).val($(this).find('option:first').val()).trigger('change');
    //                             });
    //                             $('#add_serviceRequisitionSetup')[0].reset();
    //                             $('.text-danger').hide();
    //                         }
    //                     });
    //                 }
    //                 else if (fieldName == 'info')
    //                 {
    //                     Swal.fire({
    //                         text: fieldErrors,
    //                         icon: fieldName,
    //                         confirmButtonText: 'OK'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             $('#add_serviceRequisitionSetup').find('select').each(function(){
    //                                 $(this).val($(this).find('option:first').val()).trigger('change');
    //                             });
    //                             $('#add_serviceRequisitionSetup')[0].reset();
    //                         }
    //                     });
    //                 }

    //             },
    //             error: function(error) {
    //                 if (error.responseJSON && error.responseJSON.errors) {
    //                     $('.text-danger').show();
    //                     var errors = error.responseJSON.errors;
    //                     for (var fieldName in errors) {
    //                         var fieldErrors = errors[fieldName];
    //                         for (var i = 0; i < fieldErrors.length; i++) {
    //                             fieldName = '#'+fieldName + '_error';
    //                             $(fieldName).text(fieldErrors[i]);
    //                         }
    //                     }
    //                     Swal.close();
    //                 }
    //             }
    //         });
    //     }
    // });

    var ServiceRequisition =  $('#view-serviceRequisitionSetup').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/viewservicerequisition',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'mandatory', name: 'mandatory' },
            { data: 'desc', name: 'desc' },
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

    ServiceRequisition.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceRequisition.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ServiceRequisition.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });

    $(document).on('click', '.servicerequisition', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/servicerequisition-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-serviceRequisitionSetup').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });

    $(document).on('click', '.edit-servicerequisition', function() {
        var servicerequisitionId = $(this).data('servicerequisition-id');
        $('#update_serviceRequisitionSetup')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/servicerequisitionmodal/' + servicerequisitionId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                $('.servicerequisition_id').val(response.id);
                var orgName = response.orgName;
                var orgID = response.orgID;
                var location = response.location;
                $('#u_srorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
           
                var ServiceRequestStatus = response.ServiceRequestStatus;
                var ServiceRequestStatusId = response.ServiceRequestStatusId;
                $('#usr_status').html("<option selected value='"+ServiceRequestStatusId+"'>" + ServiceRequestStatus + "</option>");
                var ServiceRequestStatusNext = ServiceRequestStatusId == '1' ? 'No' : 'Yes';
                var ServiceRequestStatusNextid = response.ServiceRequestStatusId == '1' ? 0 : 1;
                $('#usr_status').append('<option value="' + ServiceRequestStatusNextid + '">' + ServiceRequestStatusNext + '</option>');

                var Description = response.Description;
                $('#usr_description').val(Description);

                var serviceId = response.serviceId;
                var serviceName = response.serviceName;
                $('#usr_service').find('option:selected').remove();
                $('#usr_service').prepend(`<option selected value="${serviceId}" selected>${serviceName}</option>`);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                fetchOrganizations(orgID,orgName,'#u_srorg', function(data) {
                    $('#u_srorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_srorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });
                $('#edit-serviceRequisitionSetup').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    
    $('#update_serviceRequisitionSetup').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var Id = $('.servicerequisition_id').val();
        var url = '/services/update-servicerequisition/' + Id;
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
                            $('#edit-serviceRequisitionSetup').modal('hide');
                            $('#view-serviceRequisitionSetup').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_serviceRequisitionSetup')[0].reset();
                            $('.text-danger').hide();
                        }
                        $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    // Service Requisition Setup
});