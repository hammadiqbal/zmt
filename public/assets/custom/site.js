
// SiteSetup
$(document).ready(function() {

    // open SIte
    $(document).on('click', '.add_site', function() {
        // var orgId = $('#emp_org').val();
        // if(orgId)
        // {
        //     $('#emp_cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', false);
        //     fetchEmployeeCadre(orgId, '#emp_cadre', function(data) {
        //         if (data.length > 0) {
        //             $.each(data, function(key, value) {
        //                 $('#emp_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
        //             });
        //         }
        //     }, function(error) {
        //         console.log(error);
        //     });
        //     $('#emp_position').html("<option selected disabled value=''>Select Position</option>").prop('disabled', false);
        //     fetchEmployeePosition(orgId, '#emp_position', function(data) {
        //         if (data.length > 0) {
        //             $.each(data, function(key, value) {
        //                 $('#emp_position').append('<option value="' + value.id + '">' + value.name + '</option>');
        //             });
        //         }
        //     }, function(error) {
        //         console.log(error);
        //     });
        //     $('#emp_site').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', false);
        //     fetchOrganizationSites(orgId, '#emp_site', function(data) {
        //         $('#emp_site').html("<option selected disabled value=''>Select Head Count Site</option>").prop('disabled', false);
        //         $.each(data, function(key, value) {
        //             $('#emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
        //         });
        //     });
        // }
        $('#province_name').trigger('change');
        $('#add-site').modal('show');
    });
    
    ProvinceChangeDivision('#province_name', '#division_name', '#add_site', 'Karachi');

    $('#district_name').html("<option selected value=''>Select District</option>").prop('disabled', true);
    DivisionChangeDistrict('#division_name', '#district_name', '#add_site');
    // open SIte
    //Add Site
    $('#add_site').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_site')[0]);
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#division_name').val();
        var districtValue = $('#district_name').val();
        formData.append('site_province', provinceValue);
        formData.append('site_division', divisionValue);
        formData.append('site_district', districtValue);
        var resp = true;
        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined') && (fieldName != 'site_gps') && (fieldName != 'site_remarks') && (fieldName != 'old_siteCode') && (fieldName != 'site_landline'))
            {
                var FieldName = fieldName;
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
                url: "/site/addsite",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
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
                                $('#add-site').modal('hide');
                                $('#view-site').DataTable().ajax.reload();
                                $('#add_site').find('select').val($('#add_site').find('select option:first').val()).trigger('change');
                                $('#add_site')[0].reset();
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
                                $('#add_site').find('select').val($('#add_site').find('select option:first').val()).trigger('change');
                                $('#add_site')[0].reset();
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
    //Add Site

    // View Site
    var viewSite =  $('#view-site').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/site/sitedata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'address', name: 'address'},
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

    viewSite.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewSite.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewSite.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Site

    //Site Detail Modal
    $(document).on('click', '.site-detail', function() {
        var siteId = $(this).data('site-id');
        var url = '/site/detail/' + siteId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var contact = response.cell_no;
                var logoPath = response.logo;

                $('#sitelogo').attr('src', logoPath);
                $('#sitename').text(response.name);
                $('#siteorg').text(response.orgName);
                $('#oldSiteCode').text(response.oldCode);
                $('#siteemail').text(response.email);
                $('#sitecontact').html(contact);
                $('#sitelandline').html(response.landline_no);
                $('#siteprovince').text(response.province_name);
                $('#sitedivision').text(response.division_name);
                $('#sitedistrict').text(response.district_name);
                $('#sitepersonname').text(response.person_name);
                $('#sitewebsite a').attr('href', response.website).attr('target', '_blank');
                $('#sitewebsite a').text(response.website);
                $('#siteaddress').text(response.address);
                $('#siteremarks').text(response.remarks);
                $('#site-detail').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Site Detail Modal

    // Update Site Status
    $(document).on('click', '.site_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/site/site-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-site').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Site Status

    // Update Site Modal
    $(document).on('click', '.edit-site', function() {
        var siteId = $(this).data('site-id');
        $('#edit_site')[0].reset();
        $('#ajax-loader').show();
        var url = '/site/updatesite/' + siteId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var province_name = response.province_name;
                var provinceId = response.province_id;
                var divisionName = response.division_name;
                var divisionId = response.division_id;
                var districtName = response.district_name;
                var districtId = response.district_id;
                var orgID = response.orgID;
                var orgName = response.orgName;

                $('#u_site_org').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_site_province').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                $('#u_site_division').html("<option selected value='"+divisionId+"'>" + divisionName + "</option>");
                $('#u_site_district').html("<option selected value='"+districtId+"'>" + districtName + "</option>");
                $('.u_site_name').val(response.siteName);
                $('.u_site_org').val(response.org_name);
                $('.u-site-id').val(response.id);


                $('.u_site_remarks').val(response.remarks);
                $('.u_site_address').val(response.address);
                $('.u_site_person_name').val(response.person_name);
                $('.u_site_person_email').val(response.email);
                $('.u_site_website').val(response.website);
                $('.u_site_gps').val(response.gps);
                $('.u_site_cell').val(response.cell_no);
                $('.u_site_landline').val(response.landline_no);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.u_site_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                fetchOrganizations(orgID,orgName,'#u_site_org', function(data) {
                    $('#u_site_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_site_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                if (provinceId) {
                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                        },
                        beforeSend: function() {
                            $('#u_site_province').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_site_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_site_province').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });


                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    $.ajax({
                        url: 'territory/updatedivision',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                            divisionId: divisionId,
                        },
                        beforeSend: function() {
                            $('#u_site_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_site_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_site_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    // $('#u_site_province').change(function() {
                    $('#u_site_province').off('change.uSiteProvince').on('change.uSiteProvince', function(){
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                                divisionId: divisionId,
                            },
                            beforeSend: function() {
                                $('#u_site_division').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#u_site_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_site_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });

                    // $('#u_site_division').change(function() {
                    $('#u_site_division').off('change.uSiteDivision').on('change.uSiteDivision', function(){
                        var divisionId = $(this).val();
                        $.ajax({
                            url: 'territory/updatedistrict',
                            type: 'GET',
                            data: {
                                divisionId: divisionId,
                            },
                            beforeSend: function() {
                                $('#u_site_district').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#u_site_district').html("<option selected disabled value=''>Select District</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_site_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });
                }//

                $('#edit-site').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Site Modal

    //Update Site
    $('#edit_site').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#edit_site')[0]);
        var provinceValue = $('#u_site_province').val();
        var divisionValue = $('#u_site_division').val();
        var districtValue = $('#u_site_district').val();
        var orgId = $('.u-site-id').val();

        formData.append('u_site_province', provinceValue);
        formData.append('u_site_division', divisionValue);
        formData.append('u_site_district', districtValue);

        var url = '/site/update-site/' + orgId;
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
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
                            $('#edit-site').modal('hide');
                            $('#view-site').DataTable().ajax.reload(); // Refresh DataTable
                            $('#edit_site')[0].reset();
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
    //Update Site
});
// SiteSetup