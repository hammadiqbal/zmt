$(document).ready(function() {
    //Open Add Employee Documents Modal
    $(document).on('click', '.add-empDocuments', function() {
        $('#emp-info-row').hide();
        // $('.dropify').dropify();
        $('.dropify').each(function () {
            const drEvent = $(this).data('dropify');
            if (drEvent) {
                drEvent.resetPreview();
                drEvent.clearElement();
            }
        });
        $('.file-names').empty();
        var orgId = $('#ed_org').val();
        $('#ed-site').empty();
        $('#ed-site').select2();
        $('#ed-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        if(orgId)
        {
            fetchOrganizationSites(orgId, '#ed-site', function(data) {
                $('#ed-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#ed-site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#ed_org').empty();
            $('#ed_org').select2();
            fetchOrganizations(null,null,'#ed_org', function(data) {
                var options = ["<option selected disabled value=''>Select Organization</option>"];
                $.each(data, function(key, value) {
                    options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                });
                $('#ed_org').html(options.join('')).trigger('change'); // This is for Select2
            });
            
            OrgChangeSites('#ed_org', '#ed-site', '#add_empDocuments');
        }
        $('#empid-document').empty();
        $('#empid-document').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#ed-site', '#empid-document', '#add_empDocuments');
        $('#add-empDocuments').modal('show');

        $('#empid-document').change(function() {
            var empId = $(this).val();
            fetchEmployeeDetails(empId, '#empid-document', function(data) {
                $.each(data, function(key, value) {
                    let infoHtml = `
                        <div class="col-12 mt-1 mb-1 emp-block">
                            <div class="card shadow-sm border mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="row align-items-center text-center">
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Organization:</small><br>
                                            <strong class="text-primary source">${value.orgName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Site:</small><br>
                                            <strong class="text-primary destination">${value.siteName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">HeadCount CC:</small><br>
                                            <strong class="text-primary source">${value.ccName || '-'}</strong>
                                        </div>
                                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                                            <small class="text-muted">Position:</small><br>
                                            <strong class="text-primary destination">${value.positionName || '-'}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;

                    $('#emp-info-row').find('.emp-block').remove();
                    $('#emp-info-row')
                    .append(infoHtml)
                    .show();

                    // $('#userDetails').show();
                    // $('#nameLabel').hide();
                    // $('input[name="username"]').val(value.name).attr('readonly', true);
                    // $('#emailLabel').hide();
                    // $('input[name="useremail"]').val(value.email).attr('readonly', true);
                });
        
            }, function(error) {
                console.log(error);
            });
        });
    });

    $(document).on('click', '.downloadempDocuments', function () {
        var attachmentPaths = $(this).data('path'); // Get the file paths from the button's data attribute
        var id = $(this).data('id'); // Get the ID from the button's data attribute
    
        if (attachmentPaths) {
            // Split the attachmentPaths by commas
            var files = attachmentPaths.split(',');
    
            // Iterate over each file and trigger a download
            files.forEach(function (file) {
                var tempLink = document.createElement('a');
                tempLink.href = '/assets/emp/documents/' + id + '_' + file.trim(); // Combine the ID and file name
                console.log(tempLink.href); // Log the file path for debugging
                tempLink.download = file.trim(); // Set the download file name
                tempLink.target = '_blank'; // Open in a new tab if necessary
                tempLink.click(); // Trigger the download
            });
        } else {
            Swal.fire({
                text: 'No attachments available for download.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
    //Open Add Employee Documents Modal

    //Add Employee Documents
    $('#add_empDocuments').submit(function(e) {
        e.preventDefault(); 
        var data = new FormData(this);
        var orgId = $('#ed_org').val();

        data.append('ed_org', orgId);

        // if ($('.userOrganization').css('display') === 'none') {
        //     data.append('ed_org', $('#ed_org').val());
        // }
        $.ajax({
            url: "/hr/addempdocuments",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: data,
            processData: false,
            contentType: false, 
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
                            $('#add-empDocuments').modal('hide');
                            $('#view-empDocuments').DataTable().ajax.reload();
                            $('#add_empDocuments')[0].reset();
                            $('.dropify').each(function () {
                                const drEvent = $(this).data('dropify');
                                if (drEvent) {
                                    drEvent.resetPreview();
                                    drEvent.clearElement();
                                }
                            });
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
                            $('#add_empDocuments').find('select').val($('#add_empDocuments').find('select option:first').val()).trigger('change');
                            $('#add_empDocuments')[0].reset();
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
    });
    //Add Employee Documents

    // View Employee Documents
    var viewempDocuments =  $('#view-empDocuments').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewempdocument/',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'empDetails', name: 'empDetails' },
            { data: 'desc', name: 'desc' },
            { data: 'documents', name: 'documents' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "250px"
            },
            {
                targets: 2,
                width: "250px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });
    viewempDocuments.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewempDocuments.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewempDocuments.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Documents

    // Update Employee Documents Status
    $(document).on('click', '.ed_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empdocument-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empDocuments').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Employee Documents Status
   

    $(document).on('click', '.edit-empDocument', function() {
        var Id = $(this).data('ed-id');
        $('#ajax-loader').show();
        var url = '/hr/updateempdocuments/' + Id;
    
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Set effective date
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').val(formattedDateTime);
                $('#u_document_desc').val(response.desc);
                // Populate existing documents
                var documentsList = $('#existing-documents');
                documentsList.empty(); // Clear any previous entries
                // response.documents.forEach(function(doc, index) {
                //     var listItem = `
                //         <li style="margin-bottom: 10px;">
                //             <a href="/assets/emp/documents/${response.id}_${doc}" target="_blank">${doc}</a>
                //             <button type="button" class="btn btn-danger btn-sm remove-document" data-doc="${response.id}_${doc}" style="margin-left: 10px;">Remove</button>
                //         </li>`;
                //     documentsList.append(listItem);
                // });

                if (response.documents) {
                    var documents = response.documents.split(',');
                    documents.forEach(function(doc) {
                        var listItem = `
                            <li style="margin-bottom: 10px;">
                                <a href="/assets/emp/documents/${response.id}_${doc}" target="_blank">${doc}</a>
                                <button type="button" class="btn btn-danger btn-sm remove-document" data-doc="${doc}" style="margin-left: 10px;">Remove</button>
                            </li>`;
                        documentsList.append(listItem);
                    });
                }
    
                // Set hidden input for removed documents
                $('#u_empDocuments').append('<input type="hidden" name="removed_documents" id="removed-documents" value="">');
    
                $('#ued-id').val(response.id);
                $('#ajax-loader').hide();
                $('#edit-empDocuments').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    
    $(document).on('click', '.remove-document', function () {
        var doc = $(this).data('doc');
        
        // Check if there are more than one documents left
        if ($('.remove-document').length > 1) {
            // Remove the document element
            $(this).parent().remove();
    
            // Update the removed documents list
            var removedDocuments = $('#removed-documents').val();
            removedDocuments = removedDocuments ? removedDocuments.split(',') : [];
            removedDocuments.push(doc);
            $('#removed-documents').val(removedDocuments.join(','));
        } else {
            // Notify the user that at least one document must remain
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'At least one document must remain.',
                confirmButtonText: 'OK'
            });
        }
    });

    // Handle form submission
    $('#u_empDocuments').submit(function(e) {
        e.preventDefault();
        $('#ajax-loader').show();
        var formData = new FormData(this);
        var removedDocs = $('#removed-documents').val();
        if (removedDocs) {
            formData.append('removed_documents', removedDocs);
        }
        $.ajax({
            url: '/hr/saveempdocuments',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#ajax-loader').hide();

                Swal.fire({
                    text: "Documents updated successfully",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    $('#edit-empDocuments').modal('hide');
                    $('#view-empDocuments').DataTable().ajax.reload();
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Documents Modal
});