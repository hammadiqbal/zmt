$(document).ready(function() {
        //Employee Inventory Location Allocation
        $('.site_ela,.invSite').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        OrgChangeSites('.org_ela', '.site_ela', '#emp_locationallocation');
        OrgChangeSites('.org_ela', '.invSite', '#emp_locationallocation');
    
        $(document).on('click', '.emp-locationAllocation', function() {
            $('.duplicate').not(':first').remove();
            var orgId = $('.org_ela').val();
            if(orgId)
            {
                $('.emp_location').hide(); 
                fetchOrganizationSites(orgId, '.site_ela, .invSite', function(data) {
                    $('.site_ela, .invSite').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                    $.each(data, function(key, value) {
                        $('.site_ela, .invSite').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
    
                fetchServiceLocations(orgId, '.location_ela_value', function(data) {
                    if (data && data.length > 0) {
                        $('.location_ela_value').prop('disabled', false);
                        $('#multiServicelocation').empty();
    
                        if ($.fn.DataTable.isDataTable('#emplocationallocationtable')) {
                            $('#emplocationallocationtable').DataTable().clear().destroy(); 
                        }
                        data.forEach(item => {
                            var embedData = `
                                <tr style="font-size:14px;cursor:pointer;">
                                    <td>
                                        <div class="custom-control custom-checkbox p-1">
                                            <input type="checkbox" name="selectedServiceLocation[]" data-id="${item.id}" data-name="${item.name}" class="custom-control-input" id="ela_${item.id}">
                                            <label class="custom-control-label" for="ela_${item.id}"></label>
                                        </div>
                                    </td>
                                    <td>${item.name}</td>
                                </tr>`;
    
                            
                            $('#multiServicelocation').append(embedData);
                        });
                        $('#multiServicelocation').off('click', 'tr').on('click', 'tr', function(e) {
                            let $checkbox = $(this).find('input[type="checkbox"]');
                            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
                        });
                        $('#emplocationallocationtable').DataTable({
                            paging: false,
                            searching: true, 
                            ordering: true, 
                            columnDefs: [
                                { orderable: false, targets: [0] } 
                            ]
                        });
                    } 
                   
                });
            }
            else{
                $('.emp_location').show(); 
                fetchOrganizations(null,null,'.org_ela', function(data) {
                    var options = ["<option selected disabled value=''>Select Organization</option>"];
                    $.each(data, function(key, value) {
                        options.push('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                    $('.org_ela').html(options.join('')).trigger('change');
                });
    
                $('input[name="location_ela_value"]').prop('disabled', true);
                OrganizationChangServiceLocation('.org_ela', '.location_ela_value', '#emp_locationallocation');
            }
            $('.emp_ela').empty();
            $('.service_sa').select2();
            $('.emp_ela').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
    
            SiteChangeEmployees('.site_ela', '.emp_ela', '#emp_locationallocation');
            $('input[name="location_ela_value"]').val('');
            $('input[name="location_ela[]"]').val('');
            $('#empLocationAllocation').modal('show');
        });
    
        $('#emp_locationallocation').submit(function(e) {
            e.preventDefault(); // Prevent the form from submitting normally
            var data = SerializeForm(this);
           
            var resp = true;
            $(".duplicate").each(function() {
                var row = $(this);
                row.find('input, textarea, select').each(function() {
                    var elem = $(this);
                    var value = elem.val();
                    var fieldName = elem.attr('name').replace('[]', '');
                    var errorField = row.find('.' + fieldName + '_error');
                    if (!value || value === "" || (elem.is('select') && value === null)) {
                        errorField.text("This field is required");
                        if (elem.is('select')) {
                            elem.next('.select2-container').find('.select2-selection').addClass('requirefield');
                            elem.on('select2:open', function() {
                                errorField.text("");
                                elem.next('.select2-container').find('.select2-selection').removeClass("requirefield");
                            });
                        }
                        else {
                            elem.addClass('requirefield');
                            elem.focus(function() {
                                errorField.text("");
                                elem.removeClass("requirefield");
                            });
                        }
                        resp = false;
                    } else {
                        errorField.text("");
                        if (elem.is('select')) {
                            elem.next('.select2-container').find('.select2-selection').removeClass('requirefield');
                        } else {
                            elem.removeClass('requirefield');
                        }
                    }
                });
            });
            $(data).each(function(i, field){
                if ((field.value == '') || (field.value == null))
                {
                    var FieldName = field.name;
                    var FieldID = '.'+FieldName + "_error";
                  
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
                    url: "/hr/allocateemp-location",
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
    
                        console.log(response);
    
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
                                    $('#empLocationAllocation').modal('hide');
                                    $('#view-locationallocation').DataTable().ajax.reload();
                                    $('#emp_locationallocation').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#emp_locationallocation')[0].reset();
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
                                    $('#emp_locationallocation').find('select').each(function(){
                                        $(this).val($(this).find('option:first').val()).trigger('change');
                                    });
                                    $('#emp_locationallocation')[0].reset();
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
    
        $('#viewempLocation').on('change', function() {
            var EmployeeId = $(this).val();
            LoadEmpAllocatedLocation(EmployeeId);
        });
    
        //Update Employee Location
        $('#updateEmpLocation').submit(function(e) {
            e.preventDefault();
            var data = $(this).serializeArray();
            var empId = null;
            for (var i = 0; i < data.length; i++) {
                if (data[i].name === 'empId') {
                    empId = data[i].value;
                    break; // Once you find the first occurrence of empId, you can break out of the loop.
                }
            }
            $.ajax({
                url: "/hr/updateemplocation",
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
                                $('#updateEmpLocation')[0].reset();
                                $('.profiletimeline').empty();
                                LoadEmpAllocatedLocation(empId);
                            }
                        });
                    }
                    else if (fieldName == 'info')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
    
        });
        //Update Employee Location
});