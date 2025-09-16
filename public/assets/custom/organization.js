//Organization
$(document).ready(function() {
    //Add Organization
    $('#add_organization').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_organization')[0]);
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#division_name').val();
        var districtValue = $('#district_name').val();
        var logoValue = $('#org_logo')[0].files[0];
        var bannerValue = $('#org_banner')[0].files[0];
        formData.append('org_province', provinceValue);
        formData.append('org_division', divisionValue);
        formData.append('org_district', districtValue);
        formData.append('org_logo', logoValue);
        formData.append('org_banner', bannerValue);
        var resp = true;

        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined') && (fieldName != 'org_gps'))
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

                $('input[name="' + FieldName + '"][type="file"]').parent().addClass('requirefield');
                $( 'input[name="' + FieldName + '"][type="file"]').focus(function() {
                    $(FieldID).text("");

                    $('input[name="' + FieldName + '"][type="file"]').parent().removeClass('requirefield');
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
                url: "/orgSetup/addorganization",
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
                                $('#add-organization').modal('hide');
                                $('#view-organization').DataTable().ajax.reload(); // Refresh DataTable
                                $('#add_organization').find('select').val($('#add_organization').find('select option:first').val()).trigger('change');
                                $('#add_organization')[0].reset();
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
                                var logoReset = $('#org_logo').dropify();
                                var logoBanner = $('#org_banner').dropify();
                                logoReset = logoReset.data('dropify');
                                logoReset.resetPreview();
                                logoReset.clearElement();
                                logoBanner = logoBanner.data('dropify');
                                logoBanner.resetPreview();
                                logoBanner.clearElement();
                                $('#add_organization').find('select').val($('#add_organization').find('select option:first').val()).trigger('change');
                                $('#add_organization')[0].reset();
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
    //Add Organization

    $('#district_name').prop('disabled', true);
    DivisionChangeDistrict('#division_name', '#district_name', '#add_organization');

    // View Organization
    var vieworganization =  $('#view-organization').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/orgSetup/vieworganization',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'remarks', name: 'remarks' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            } },
            { data: 'address', name: 'address'  },
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

    vieworganization.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    vieworganization.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    vieworganization.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Organization

    // Update Organization Status
    $(document).on('click', '.organization_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/orgSetup/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {

                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-organization').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Organization Status

    //Organization Detail Modal
    $(document).on('click', '.organization-detail', function() {
        var organizationId = $(this).data('org-id');
        var url = '/orgSetup/detail/' + organizationId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var contact = response.cell_no + '/' + response.landline_no;
                var logoPath = response.logo;
                var bannerPath = response.banner;
                $('#orglogo').attr('src', logoPath);
                $('#orgBanner').attr('src', bannerPath);
                $('#userOrg').text(response.name);
                $('#orgcode').text(response.code);
                $('#orgemail').text(response.email);
                $('#orgcontact').text(contact);
                $('#orgprovince').text(response.province_name);
                $('#orgdivision').text(response.division_name);
                $('#orgdistrict').text(response.district_name);
                $('#orgpersonname').text(response.person_name);
                $('#orgwebsite a').attr('href', response.website).attr('target', '_blank');
                $('#orgwebsite a').text(response.website);
                $('#orgaddress').text(response.address);
                $('#orgremarks').text(response.remarks);
                $('#organization-detail').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Organization Detail Modal

    // Update Organization Modal
    $(document).on('click', '.edit-organization', function() {
        var organizationId = $(this).data('organization-id');
        $('#edit_org')[0].reset();
        $('#ajax-loader').show();
        var url = '/orgSetup/updatemodal/' + organizationId;
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
                var org_logo = response.org_logo;
                var org_banner = response.org_banner;
                var logoname = org_logo.substring(org_logo.lastIndexOf('/') + 1);
                var bannername = org_banner.substring(org_banner.lastIndexOf('/') + 1);

                var org_logo_input = $('#u_org_logo');
                var org_banner_input = $('#u_org_banner');
                var dropifyRenderLogo = org_logo_input.closest('.dropify-wrapper').find('.dropify-render');
                var dropifyRenderBanner = org_banner_input.closest('.dropify-wrapper').find('.dropify-render');

                dropifyRenderLogo.find('img').attr('src', org_logo);
                dropifyRenderBanner.find('img').attr('src', org_banner);

                var bannerdropifyInfos = org_banner_input.closest('.dropify-wrapper').find('.dropify-infos');
                var bannerfilenameInner = bannerdropifyInfos.find('.dropify-filename-inner');
                bannerfilenameInner.text(logoname); //

                var logodropifyInfos = org_logo_input.closest('.dropify-wrapper').find('.dropify-infos');
                var logofilenameInner = logodropifyInfos.find('.dropify-filename-inner');
                logofilenameInner.text(bannername); //

                org_logo_input.attr('data-default-file', org_logo);
                org_banner_input.attr('data-default-file', org_banner);

                org_logo_input.dropify('destroy');
                org_banner_input.dropify('destroy');

                org_logo_input.dropify();
                org_banner_input.dropify();

                $('#u_org_province').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                $('#u_org_division').html("<option selected value='"+divisionId+"'>" + divisionName + "</option>");
                $('#u_org_district').html("<option selected value='"+districtId+"'>" + districtName + "</option>");
                $('.u_org_name').val(response.org_name);
                $('.u-org-id').val(response.id);

                $('.u_org_remarks').val(response.org_remarks);
                $('.u_org_address').val(response.org_address);
                $('.u_org_person_name').val(response.org_personname);
                $('.u_org_person_email').val(response.org_email);
                $('.u_org_website').val(response.org_website);
                $('.u_org_gps').val(response.org_gps);
                $('.u_org_cell').val(response.org_cell_no);
                $('.u_org_landline').val(response.org_landline_no);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.u_org_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                if (provinceId) {
                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                        },
                        beforeSend: function() {
                            $('#u_org_province').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_org_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_org_province').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            $('#u_org_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_org_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_org_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    // $('#u_org_province').change(function() {
                    $('#u_org_province').off('change.uOrgProvince').on('change.uOrgProvince', function(){
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                                divisionId: divisionId,
                            },
                            beforeSend: function() {
                                $('#u_org_division').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                 $('#u_org_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_org_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });

                    // $('#u_org_division').change(function() {
                    $('#u_org_division').off('change.uOrgDivision').on('change.uOrgDivision', function(){
                        var org_divisionid = $(this).val();
                        $.ajax({
                            url: 'territory/updatedistrict',
                            type: 'GET',
                            data: {
                                divisionid: org_divisionid,
                            },
                            beforeSend: function() {
                                $('#u_org_district').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                 $('#u_org_district').html("<option selected disabled value=''>Select District</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_org_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });
                }//

                $('#edit-organization').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Organization Modal

    //Update Organization
    $('#edit_org').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#edit_org')[0]);
        var provinceValue = $('#u_org_province').val();
        var divisionValue = $('#u_org_division').val();
        var districtValue = $('#u_org_district').val();
        var orgId = $('.u-org-id').val();

        var logoValue = $('#u_org_logo')[0].files[0];
        var bannerValue = $('#u_org_banner')[0].files[0];
        if(logoValue != 'undefined')
        {
            formData.append('u_org_logo', logoValue);
        }

        if(bannerValue != 'undefined')
        {
            formData.append('u_org_banner', bannerValue);
        }


        formData.append('u_org_province', provinceValue);
        formData.append('u_org_division', divisionValue);
        formData.append('u_org_district', districtValue);

        var url = '/edit-org/' + orgId;
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
                            $('#edit-organization').modal('hide');
                            $('#view-organization').DataTable().ajax.reload(); // Refresh DataTable
                            $('#edit_org')[0].reset();
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
    //Update Organization

});
//Organization