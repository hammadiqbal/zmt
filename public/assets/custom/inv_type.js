$(document).ready(function() {
    $('#fb_subcat').html("<option selected disabled value=''>Select Item Sub Category</option>").prop('disabled', true);
    CategoryChangeSubCategory('#fb_cat', '#fb_subcat', 'null');

    //Open Inventory Type
    $(document).on('click', '.add-invtype', function() {
        var orgId = $('#it_org').val();
        $('#add-invtype').find('form').trigger('reset');
        $('.dt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        if(orgId)
        {
            $('#it_cat').html("<option selected disabled value=''>Select Item Category</option>");
            fetchInventoryCategory(orgId,'#it_cat', function(data) {
                $('#it_cat').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#it_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        }
        else{
            $('#it_org').html("<option selected disabled value=''>Select Organization</option>");
            fetchOrganizations('null', '','#it_org', function(data) {
                $('#it_org').find('option:contains("Loading...")').remove();
                $.each(data, function(key, value) {
                    $('#it_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                });
            });
            $('#it_cat').html("<option selected disabled value=''>Select Category</option>").prop('disabled', true);
            OrgChangeItemCategory('#it_org', '#it_cat', '#add_invtype');
        }
        $('#it_subcat').html("<option selected disabled value=''>Select Item Sub Category</option>").prop('disabled', true);
        CategoryChangeSubCategory('#it_cat', '#it_subcat', '#add_invtype');
        $('#add-invtype').modal('show');
    });
    //Open Inventory Type

    //Add Inventory Type
    $('#add_invtype').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var Category = $('#it_cat').val();
        var SubCategory = $('#it_subcat').val();
        var Org = $('#it_org').val();
        data.push({ name: 'it_cat', value: Category });
        data.push({ name: 'it_subcat', value: SubCategory });
        data.push({ name: 'it_org', value: Org });
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
                url: "/inventory/addinvtype",
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
                                $('#add-invtype').modal('hide');
                                $('#view-invtype').DataTable().ajax.reload();
                                $('#add_invtype')[0].reset();
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
                                $('#add_invtype')[0].reset();
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
    //Add Inventory Type

    // View Inventory Type Data
    var viewinventorytype =  $('#view-invtype').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/inventory/invtype',
            data: function (d) {
                d.cat = $('#fb_cat').val();  
                d.subcat = $('#fb_subcat').val();  
            }
        },
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
                targets: 6,
                width: "250px"
            }
        ]
    });

    $('#fb_cat,#fb_subcat').on('change', function () {
        viewinventorytype.ajax.reload();  
    });

    $('.clearFilter').on('click', function () {
        $('#fb_cat').val($('#fb_cat option:first').val()).change();
        $('#fb_subcat').val($('#fb_subcat option:first').val()).change().prop('disabled', true);
        viewinventorytype.ajax.reload();   
    });

    viewinventorytype.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventorytype.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventorytype.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Type Data

    // Update Inventory Type Status
    $(document).on('click', '.invtype_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invtype-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invtype').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Type Status

    //Update Inventory Type Modal
    $(document).on('click', '.edit-invtype', function() {
        var InnventoryTypeId = $(this).data('invtype-id');
        var url = '/inventory/updateinvtype/' + InnventoryTypeId;
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
                $('.u_it-id').val(response.id);
                $('.u_it_description').val(response.name);
                $('#u_it_catid').html("<option selected value="+ response.catId +">" + response.catName + "</option>");
                $('#u_it_subcat').html("<option selected value="+ response.subcatId +">" + response.subcatName + "</option>").prop('disabled', false);
                $('#u_it_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                
                fetchInventoryCategory(response.orgId,'#u_it_catid', function(data) {
                    $('#u_it_catid').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        console.log(value.id);
                        console.log(response.catId);
                        if(response.catId != value.id)
                        {
                            $('#u_it_catid').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });

                OrgChangeItemCategory('#u_it_org', '#u_it_catid', '#u_invtype');

                fetchSelectedInventorySubCategory(response.catId,'#u_it_subcat', function(data) {
                    $('#u_it_subcat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.subcatId != value.id)
                        {
                            $('#u_it_subcat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                CategoryChangeSubCategory('#u_it_catid', '#u_it_subcat', '#u_invtype');

                fetchOrganizations(response.orgId,response.orgName,'#u_it_org', function(data) {
                    $('#u_it_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_it_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });


                $('#edit-invtype').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Type Modal

    //Update Inventory Type
    $('#u_invtype').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invtypeId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_it-id') {
                invtypeId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invtype/' + invtypeId;
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
                            $('#edit-invtype').modal('hide');
                            $('#view-invtype').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invtype')[0].reset();
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
    //Update Inventory Type
});