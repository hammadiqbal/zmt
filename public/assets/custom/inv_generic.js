$(document).ready(function() {
    //Open Add Inventory Generic Setup
    $(document).on('click', '.add-invgeneric', function() {
        var orgId = $('#ig_org').val();
        $('#ig_pm').hide();
        $('#add-invgeneric').find('form').trigger('reset');
        $('.dt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        if(orgId)
        {
            $('#ig_cat').html("<option selected disabled value=''>Select Item Category</option>");
            fetchInventoryCategory(orgId,'#ig_cat', function(data) {
                $('#ig_cat').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ig_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#ig_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#ig_org', function(data) {
                $('#ig_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ig_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#ig_cat').html("<option selected disabled value=''>Select Category</option>").prop('disabled', true);
            OrgChangeItemCategory('#ig_org', '#ig_cat', '#u_invtype');
        }
        $('#ig_subcat').html("<option selected disabled value=''>Select Item Sub Category</option>").prop('disabled', true);
        CategoryChangeSubCategory('#ig_cat', '#ig_subcat', '#add_invgeneric');

        $('#ig_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        SubCategoryChangeInventoryType('#ig_subcat', '#ig_type', '#add_invgeneric');

        $('#ig_cat').change(function() {
            var itemCat = $(this).val();
            CheckInventoryCategoryConsumption(itemCat, '#ig_patientmandatory', function(bool) {
                if (bool) {
                    $('#ig_pm').show();
                } else {
                    $('#ig_pm').hide();
                    $('#ig_patientmandatory').val('n');
                }
            });
        });
        
        $('#add-invgeneric').modal('show');
    });
    //Open Inventory Generic Setup
    

    //Add Inventory Generic
    $('#add_invgeneric').submit(function(e) {
        e.preventDefault(); 
        var data = SerializeForm(this);

        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '') || (field.value == null) && field.name != 'ig_patientmandatory')
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
                url: "/inventory/addinvgeneric",
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
                                $('#add-invgeneric').modal('hide');
                                $('#view-invgeneric').DataTable().ajax.reload();
                                $('#add_invgeneric')[0].reset();
                                $('#add_invgeneric').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
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
                                $('#add_invgeneric').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_invgeneric')[0].reset();
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
    //Add Inventory Generic

    // View Inventory Generic Data
    var viewinventorygeneric =  $('#view-invgeneric').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/invgeneric',
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
                "data": 'subCatName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'typeName',
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
                targets: 7,
                width: "250px"
            }
        ]
    });

    viewinventorygeneric.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventorygeneric.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventorygeneric.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Generic Data

    // Update Inventory Generic Status
    $(document).on('click', '.invgeneric_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invgeneric-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invgeneric').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Generic Status

    //Update Inventory Generic Modal
    $(document).on('click', '.edit-invgeneric', function() {
        var InnventoryGenericId = $(this).data('invgeneric-id');
        var url = '/inventory/updateinvgeneric/' + InnventoryGenericId;
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
                $('.u_ig-id').val(response.id);
                $('.u_ig_description').val(response.name);
                $('#u_ig_cat').html("<option selected value="+ response.catId +">" + response.catName + "</option>");
                $('#u_ig_subcat').html("<option selected value="+ response.subcatId +">" + response.subcatName + "</option>");
                $('#u_ig_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                $('#u_ig_type').html("<option selected value="+ response.typeId +">" + response.typeName + "</option>");

                 CheckInventoryCategoryConsumption(response.catId, '#u_ig_patientmandatory', function(bool) {
                    if (bool) {
                        $('#u_ig_pm').show();
                    } else {
                        $('#u_ig_pm').hide();
                        $('#u_ig_patientmandatory').val('n');
                    }
                });

                $('#u_ig_cat').change(function() {
                    var itemCat = $(this).val();
                    CheckInventoryCategoryConsumption(itemCat, '#u_ig_patientmandatory', function(bool) {
                        if (bool) {
                            $('#u_ig_pm').show();
                        } else {
                            $('#u_ig_pm').hide();
                            $('#u_ig_patientmandatory').val('n');
                        }
                    });
                });

                fetchInventoryCategory(response.orgId,'#u_ig_cat', function(data) {
                    $('#u_ig_cat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_ig_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeItemCategory('#u_ig_org', '#u_ig_cat', '#u_invgeneric');

                fetchSelectedInventorySubCategory(response.catId,'#u_ig_subcat', function(data) {
                    $('#u_ig_subcat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.subcatId != value.id)
                        {
                            $('#u_ig_subcat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                CategoryChangeSubCategory('#u_ig_cat', '#u_ig_subcat', '#edit-invgeneric');

                fetchOrganizations(response.orgId,response.orgName,'#u_ig_org', function(data) {
                    $('#u_ig_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_ig_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                fetchSelectedInventoryType(response.catId,response.subcatId,response.orgId,'#u_ig_type', function(data) {
                    $('#u_ig_type').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.typeId != value.id)
                        {
                            $('#u_ig_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                SubCategoryChangeInventoryType('#u_ig_subcat', '#u_ig_type', '#u_invgeneric');
                
                $('#u_ig_patientmandatory').val(response.patientMandatory).change();
                $('#edit-invgeneric').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Generic Modal

    //Update Inventory Generic
    $('#u_invgeneric').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invgenericId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_ig-id') {
                invgenericId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invgeneric/' + invgenericId;
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
                            $('#edit-invgeneric').modal('hide');
                            $('#view-invgeneric').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invgeneric')[0].reset();
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
    //Update Inventory Generic
});