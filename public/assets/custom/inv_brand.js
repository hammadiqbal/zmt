
$(document).ready(function() {
    //Open Add Inventory Brand Setup
    $(document).on('click', '.add-invbrand', function() {
        var orgId = $('#ib_org').val();
        $('#add_invbrand').find('form').trigger('reset');
        if(orgId)
        {
            $('#ib_cat').html("<option selected disabled value=''>Select Item Category</option>");
            fetchInventoryCategory(orgId,'#ib_cat', function(data) {
                $('#ib_cat').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ib_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#ib_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#ib_org', function(data) {
                $('#ib_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#ib_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#ib_cat').html("<option selected disabled value=''>Select Category</option>").prop('disabled', true);
            OrgChangeItemCategory('#ib_org', '#ib_cat', '#u_invtype');
        }
        $('#ib_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        $('#ib_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
        $('#ib_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
        CategoryChangeSubCategory('#ib_cat', '#ib_subcat', '#add_invbrand');
        SubCategoryChangeInventoryType('#ib_subcat', '#ib_type', '#add_invgeneric');
        TypeChangeInventoryGeneric('#ib_type', '#ib_generic', '#add_invgeneric');
        
        $('#add-invbrand').modal('show');
    });

    //Open Inventory Brand Setup

    //Add Inventory Brand
    $('#add_invbrand').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
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
                url: "/inventory/addinvbrand",
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
                                $('#add-invbrand').modal('hide');
                                $('#view-invbrand').DataTable().ajax.reload();
                                $('#add_invbrand')[0].reset();
                                $('#add_invbrand').find('select').each(function(){
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
                                $('#add_invbrand').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_invbrand')[0].reset();
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
    //Add Inventory Brand

    // View Inventory Brand Data
    var viewinventorybrand =  $('#view-invbrand').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/invbrand',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'name',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'genericName',
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
                targets: 4,
                width: "250px"
            }
        ]
    });

    viewinventorybrand.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventorybrand.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventorybrand.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Brand Data

    // Update Inventory Brand Status
    $(document).on('click', '.invbrand_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invbrand-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invbrand').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Brand Status

    //Update Inventory Brand Modal
    $(document).on('click', '.edit-invbrand', function() {
        var InnventoryBrandId = $(this).data('invbrand-id');
        var url = '/inventory/updateinvbrand/' + InnventoryBrandId;
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
                $('.u_ib-id').val(response.id);
                $('.u_ib_description').val(response.name);
                $('#u_ib_cat').html("<option selected value="+ response.catId +">" + response.catName + "</option>");
                $('#u_ib_subcat').html("<option selected value="+ response.subcatId +">" + response.subcatName + "</option>");
                $('#u_ib_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                $('#u_ib_type').html("<option selected value="+ response.typeId +">" + response.typeName + "</option>");
                $('#u_ib_generic').html("<option selected value="+ response.genericId +">" + response.genericName + "</option>");

                fetchInventoryCategory(response.orgId,'#u_ib_cat', function(data) {
                    $('#u_ib_cat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_ib_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                OrgChangeItemCategory('#u_ib_org', '#u_ib_cat', '#u_invgeneric');

                fetchSelectedInventorySubCategory(response.catId,'#u_ib_subcat', function(data) {
                    $('#u_ib_subcat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.subcatId != value.id)
                        {
                            $('#u_ib_subcat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                CategoryChangeSubCategory('#u_ib_cat', '#u_ib_subcat', '#u_invgeneric');

                fetchOrganizations(response.orgId,response.orgName,'#u_ib_org', function(data) {
                    $('#u_ib_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_ib_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                fetchSelectedInventoryType(response.catId,response.subcatId,response.orgId,'#u_ib_type', function(data) {
                    $('#u_ib_type').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.typeId != value.id)
                        {
                            $('#u_ib_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                SubCategoryChangeInventoryType('#u_ib_subcat', '#u_ib_type', '#u_invbrand');

                fetchSelectedInventoryGeneric(response.typeId,'#u_ib_generic', function(data) {
                    $('#u_ib_generic').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.genericId != value.id)
                        {
                            $('#u_ib_generic').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                TypeChangeInventoryGeneric('#u_ib_type', '#u_ib_generic', '#u_invbrand');

                $('#edit-invbrand').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Brand Modal

    //Update Inventory Brand
    $('#u_invbrand').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invbrandId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_ib-id') {
                invbrandId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invbrand/' + invbrandId;
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
                            $('#edit-invbrand').modal('hide');
                            $('#view-invbrand').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invbrand')[0].reset();
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
    //Update Inventory Brand
});