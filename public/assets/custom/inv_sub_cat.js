$(document).ready(function() {
    //Open Inventory Sub Category
    $(document).on('click', '.add-invsubcategory', function() {
        var orgId = $('#isc_org').val();
        if(orgId)
        {
            $('#add-invsubcategory').find('form').trigger('reset');
            $('#isc_catid').html("<option selected disabled value=''>Select Item Category</option>");
            fetchInventoryCategory(orgId,'#isc_org', function(data) {
                $('#isc_catid').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#isc_catid').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
          
        }
        else{
            $('#isc_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#isc_org', function(data) {
                $('#isc_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#isc_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#isc_catid').html("<option selected disabled value=''>Select Category</option>").prop('disabled', true);
            OrgChangeItemCategory('#isc_org', '#isc_catid', '#add_invsubcategory');
        }
      
       

        $('#add-invsubcategory').modal('show');
    });
    //Open Inventory Sub Category

    $('#add_invsubcategory').submit(function(e) {
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
                url: "/inventory/addinvsubcategory",
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
                                $('#add-invsubcategory').modal('hide');
                                $('#view-invsubcategory').DataTable().ajax.reload();
                                $('#add_invsubcategory').find('select').val($('#add_invsubcategory').find('select option:first').val()).trigger('change');
                                $('#add_invsubcategory')[0].reset();
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
                                $('#add_invsubcategory').find('select').val($('#add_invsubcategory').find('select option:first').val()).trigger('change');
                                $('#add_invsubcategory')[0].reset();
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
    //Add Inventory Sub Category

    // View Inventory Sub Category Data
    var viewinventorysubCat =  $('#view-invsubcategory').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/invsubcategory',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'catName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },  
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
                targets: 5,
                width: "250px"
            }
        ]
    });

    viewinventorysubCat.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventorysubCat.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventorysubCat.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Sub Category Data

    // Update Inventory Sub Category Status
    $(document).on('click', '.invsubcategory_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invsubcat-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invsubcategory').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Sub Category Status

    //Update Inventory Sub Category Modal
    $(document).on('click', '.edit-invsubcategory', function() {
        var InnventorySubCatId = $(this).data('invsubcategory-id');
        var url = '/inventory/updateinvsubcategory/' + InnventorySubCatId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_isc-id').val(response.id);
                $('.u_isc_description').val(response.name);
                $('#u_isc_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                fetchOrganizations('null', '','#u_isc_org', function(data) {
                    $('#u_isc_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_isc_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('#u_isc_catid').html("<option selected value="+ response.catId +">" + response.catName + "</option>");
                fetchInventoryCategory(response.orgId,'#u_ssorg', function(data) {
                    $('#u_isc_catid').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_isc_catid').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeItemCategory('#u_isc_org', '#u_isc_catid', '#u_invtype');

                $('#edit-invsubcategory').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Sub Category Modal

    //Update Inventory Sub Category
    $('#u_invsubcategory').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var catId = $('#u_isc_catid').val();
        var invCatId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_isc-id') {
                invCatId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invsubcategory/' + invCatId;
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
                            $('#edit-invsubcategory').modal('hide');
                            $('#view-invsubcategory').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invsubcategory')[0].reset();
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
    //Update Inventory Sub Category
});
