
$(function () {
    "use strict";
    $(function () {
        $(".preloader").fadeOut();
    });
    jQuery(document).on('click', '.mega-dropdown', function (e) {
        e.stopPropagation()
    });
    // ==============================================================
    // This is for the top header part and sidebar part
    // ==============================================================
    var set = function () {
            var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
            var topOffset = 70;
            if (width < 1170) {
                $("body").addClass("mini-sidebar");
                $('.navbar-brand span').hide();
                $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
                $(".sidebartoggler i").addClass("ti-menu");
            }
            else {
                $("body").removeClass("mini-sidebar");
                $('.navbar-brand span').show();
                //$(".sidebartoggler i").removeClass("ti-menu");
            }

            var height = ((window.innerHeight > 0) ? window.innerHeight : this.screen.height) - 1;
            height = height - topOffset;
            if (height < 1) height = 1;
            if (height > topOffset) {
                $(".page-wrapper").css("min-height", (height) + "px");
            }

    };
    $(window).ready(set);
    $(window).on("resize", set);
    // ==============================================================
    // Theme options
    // ==============================================================
    $(".sidebartoggler").on('click', function () {
        if ($("body").hasClass("mini-sidebar")) {
            $("body").trigger("resize");
            $(".scroll-sidebar, .slimScrollDiv").css("overflow", "hidden").parent().css("overflow", "visible");
            $("body").removeClass("mini-sidebar");
            $('.navbar-brand span').show();
            //$(".sidebartoggler i").addClass("ti-menu");
        }
        else {
            $("body").trigger("resize");
            $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
            $("body").addClass("mini-sidebar");
            $('.navbar-brand span').hide();
            //$(".sidebartoggler i").removeClass("ti-menu");
        }
    });
    // topbar stickey on scroll

    $(".fix-header .topbar").stick_in_parent({});


    // this is for close icon when navigation open in mobile view
    $(".nav-toggler").click(function () {
        $("body").toggleClass("show-sidebar");
        $(".nav-toggler i").toggleClass("mdi mdi-menu");
        $(".nav-toggler i").addClass("mdi mdi-close");
    });

    $(".search-box a, .search-box .app-search .srh-btn").on('click', function () {
        $(".app-search").toggle(200);
    });
    // ==============================================================
    // Right sidebar options
    // ==============================================================
    $(".right-side-toggle").click(function () {
        $(".right-sidebar").slideDown(50);
        $(".right-sidebar").toggleClass("shw-rside");
    });

    $('.floating-labels .form-control').on('focus blur', function (e) {
        $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
    }).trigger('blur');

    // ==============================================================
    // Auto select left navbar
    // ==============================================================
    $(function () {
        var url = window.location;
        var element = $('ul#sidebarnav a').filter(function () {
            return this.href == url;
        }).addClass('active').parent().addClass('active');
        while (true) {
            if (element.is('li')) {
                element = element.parent().addClass('in').parent().addClass('active');
            }
            else {
                break;
            }
        }

    });
    // ==============================================================
    //tooltip
    // ==============================================================
    $(function () {
            $('[data-toggle="tooltip"]').tooltip()
    })
    // ==============================================================
    //Popover
    // ==============================================================
    $(function () {
            $('[data-toggle="popover"]').popover()
        })
    // ==============================================================
    // Sidebarmenu
    // ==============================================================
    $(function () {
        $('#sidebarnav').metisMenu();
    });

    // ==============================================================
    // Slimscrollbars
    // ==============================================================
    $('.scroll-sidebar').slimScroll({
        position: 'left'
        , size: "5px"
        , height: '100%'
        , color: '#dcdcdc'
    });
    $('.message-center').slimScroll({
        position: 'right'
        , size: "5px"

        , color: '#dcdcdc'
    });


    $('.aboutscroll').slimScroll({
        position: 'right'
        , size: "5px"
        , height: '80'
        , color: '#dcdcdc'
    });
    $('.message-scroll').slimScroll({
        position: 'right'
        , size: "5px"
        , height: '570'
        , color: '#dcdcdc'
    });
    $('.chat-box').slimScroll({
        position: 'right'
        , size: "5px"
        , height: '470'
        , color: '#dcdcdc'
    });

    $('.slimscrollright').slimScroll({
        height: '100%'
        , position: 'right'
        , size: "5px"
        , color: '#dcdcdc'
    });

    // ==============================================================
    // Resize all elements
    // ==============================================================
    $("body").trigger("resize");
    // ==============================================================
    // To do list
    // ==============================================================
    $(".list-task li label").click(function () {
        $(this).toggleClass("task-done");
    });

    // ==============================================================
    // Login and Recover Password
    // ==============================================================
    $('#to-recover').on("click", function () {
        $("#loginform").slideUp();
        $("#recoverform").fadeIn();
    });

    // ==============================================================
    // Collapsable cards
    // ==============================================================
        $('a[data-action="collapse"]').on('click',function(e){
            e.preventDefault();
            $(this).closest('.card').find('[data-action="collapse"] i').toggleClass('ti-minus ti-plus');
            $(this).closest('.card').children('.card-body').collapse('toggle');

        });
        // Toggle fullscreen
        $('a[data-action="expand"]').on('click',function(e){
            e.preventDefault();
            $(this).closest('.card').find('[data-action="expand"] i').toggleClass('mdi-arrow-expand mdi-arrow-compress');
            $(this).closest('.card').toggleClass('card-fullscreen');
        });

        // Close Card
        $('a[data-action="close"]').on('click',function(){
            $(this).closest('.card').removeClass().slideUp('fast');
        });

        $('.userOrganization').hide();

    
});

function goBack() {
    window.history.back();
}

document.addEventListener('keydown', function (event) {
    var isEnterKey = event.key === 'Enter' || event.keyCode === 13;
    if (isEnterKey) {
        // Trigger a click on the OK button
        Swal.clickConfirm();
    }
});

$(document).ready(function() {
    $(".password-toggle").click(function() {
      var passwordField = $(".showpwd");
      var passwordFieldType = passwordField.attr("type");

      if (passwordFieldType === "password") {
        passwordField.attr("type", "text");
        $(".password-toggle i").removeClass("fa-eye-slash").addClass("fa-eye");
      } else {
        passwordField.attr("type", "password");
        $(".password-toggle i").removeClass("fa-eye").addClass("fa-eye-slash");
      }
    });

    $(".password-toggle1").click(function() {
        console.log('click');
      var passwordField = $(".showpwd");
      var passwordFieldType = passwordField.attr("type");

      if (passwordFieldType === "password") {
        passwordField.attr("type", "text");
        $(".password-toggle1 i").removeClass("fa-eye-slash").addClass("fa-eye");
      } else {
        passwordField.attr("type", "password");
        $(".password-toggle1 i").removeClass("fa-eye").addClass("fa-eye-slash");
      }
    });
});


// Role and Rights
$(document).ready(function() {
    //Add ROle
    $('#add_role').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
            $(data).each(function(i, field){
                if(field.value == '')
                {
                    var FieldName = field.name;
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
                    resp = false;
                }
                else{
                    resp = true;
                }
                // console.log('up');
            });
            if(resp != false)
            {
                $.ajax({
                    url: "/roles/addrole",
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
                                    $('#add-user').modal('hide');
                                    $('#view-roles').DataTable().ajax.reload(); // Refresh DataTable
                                    $('#add_role')[0].reset();
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
                                    $('#add_role')[0].reset();
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
    //Add Role

    // VieW Role
    var viewrole =  $('#view-roles').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/roles/data',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'role', name: 'role', render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'remarks', name: 'remarks' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 3,
                width: "250px"
            },
            {
                targets: 5,
                width: "300px"
            }
        ]
    });

    viewrole.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });


    // Show the loader before an AJAX request is made
    viewrole.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewrole.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Role

    // Update Status
    $(document).on('click', '.role_status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            var data = {id: id,status: status};

            $.ajax({
                url: '/roles/update-status', // Replace with the actual URL for updating the status
                method: 'GET',
                data: data,
                beforeSend: function() {
                    $('#ajax-loader').show();
                },
                success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-roles').DataTable().ajax.reload(); // Refresh DataTable
                }
                },
                error: function(xhr, status, error) {
                // Handle any AJAX errors
                console.log(error);
                }
            });

    });
    // Update Status

    //Rights Setup
    $('#rights_setup').submit(function(e) {
        e.preventDefault(); 
        var groupedValues = {};
        $('.tab-pane').each(function(){
            var tabName = $(this).attr('id');
            var checkboxes = $(this).find('input[type="checkbox"]');
            checkboxes.each(function(){
                var name = $(this).attr('name');
                var value = $(this).is(':checked') ? 1 : 0;
                if (!groupedValues.hasOwnProperty(name)) {
                    groupedValues[name] = [];
                }
                groupedValues[name].push(value);            
            });
        });
        var roleId = $('#role_id').val();

        var formData = {
            role_id: roleId,
        };
        for (var key in groupedValues) {
            formData[key] = groupedValues[key].join(',');
        }

        $.ajax({
            url: "/rights-setup/assignrights",
            method: "POST",
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
            success: function(response) {
            for (var fieldName in response) {
                var fieldErrors = response[fieldName];
                var fieldName = fieldName;
            }

                if (fieldName == 'success')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.text-danger').hide();
                            window.location.href = '/user-roles';
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
        // }
    });
    //Rights Setup

    //Update Rights
    $('#update_rights').submit(function(e) {
        e.preventDefault(); 
        var groupedValues = {};
        $('.tab-pane').each(function(){
            var tabName = $(this).attr('id');
            var checkboxes = $(this).find('input[type="checkbox"]');
            checkboxes.each(function(){
                var name = $(this).attr('name');
                var value = $(this).is(':checked') ? 1 : 0;
                if (!groupedValues.hasOwnProperty(name)) {
                    groupedValues[name] = [];
                }
                groupedValues[name].push(value);            
            });
        });
        var roleId = $('#role_id').val();

        var formData = {
            role_id: roleId,
        };
        for (var key in groupedValues) {
            formData[key] = groupedValues[key].join(',');
        }

        $.ajax({
            url: "/rights/updaterights",
            method: "POST",
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
            success: function(response) {
            for (var fieldName in response) {
                var fieldErrors = response[fieldName];
                var fieldName = fieldName;
            }

                if (fieldName == 'success')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.text-danger').hide();
                            window.location.reload();
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
        // }
    });
    //Update Rights

    //Update Role Modal
    $(document).on('click', '.edit-role', function() {
        var roleId = $(this).data('role-id');
        $('#ajax-loader').show();
        var url = '/roles/' + roleId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.role-id').val(response.id);
                $('.role-name').val(response.role);  // Adjust these based on the actual structure of your response data
                $('.update-remark').val(response.remarks);
                $('#edit-role').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Role Modal

    //Update Role
    $('#update_role').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var roleId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'role-id') {
                roleId = formData[i].value;
                break;
            }
        }
        var url = '/update-role/' + roleId;
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
                            $('#edit-role').modal('hide');
                            $('#view-roles').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_role')[0].reset();
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
    //Update Role

});// Role and Rights


//Logs
$(document).ready(function() {
    $(document).on('click', '.logs-modal', function() {
        const logId = $(this).data('log-id');
        const modal = $('#logs');
        const modalBody = modal.find('.modal-body .row');
        modalBody.empty();
        $('#ajax-loader').show();

        if (!logId) {
            modalBody.append($('<div class="col-12 m-t-10">').text('No logs available'));
            modal.modal('show');
            $('#ajax-loader').hide();
            return;
        }
        const url = '/viewlogs/' + logId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();

                if (response.length > 0) {
                    response.forEach(log => {
                        const moduleTitle = log.module.toUpperCase();
                        modal.find('.modal-title').text(moduleTitle);

                        const card = $('<div class="card">')
                            .append($('<div class="card-header">').text(log.timestamp))
                            .append($('<div class="card-body">').html(`<p class="card-text">${log.content}</p>`));

                        modalBody.append($('<div class="col-12 m-t-10">').append(card));
                    });
                } else {
                    modalBody.append($('<div class="col-12 m-t-10">').text('Logs not found'));
                }
                modal.modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
});
//Logs


$(document).ready(function() {
    // Showing Inventory Type
    OrgChangeSites('#itt_org', '#itt_site', '#add_invmanagement');

    //Open Add Inventory Management Setup
    $(document).on('click', '.add-invmanagement', function() {
        $('#itt_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        $('#itt_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#itt_org', function(data) {
            $('#itt_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-invmanagement').modal('show');
    });
    //Open Add Inventory Management Setup
});


// $(document).ready(function() {
//     //Open Inventory Management Modal
//     $(document).on('click', '.add-manageinventory', function() {
//         var orgId = $('#im_org').val();
//         if(orgId)
//         {
//             $('#im_site').html("<option selected disabled value=''>Select Site</option>");
//             fetchOrganizationSites(orgId, '#im_site', function(data) {
//                 $.each(data, function(key, value) {
//                     $('#im_site').append('<option value="' + value.id + '">' + value.name + '</option>');
//                 });
//             });
//             $('#im_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled',false);
//             fetchOrganizationBrand(orgId,'#im_brand', function(data) {
//                 $.each(data, function(key, value) {
//                     $('#im_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
//                 });
//             });

//         }
//         else{
//             $('#im_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
//             OrgChangeSites('#im_org', '#im_site', '#add_manageinventory');

//             $('#im_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled',true);
//             OrgChangeBrand('#im_org', '#im_brand', '#add_manageinventory');
//         }

//         $('#im_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>");
//         fetchTransactionTypes('null', '#im_transactiontype', false, function(data) {
//             if (data && data.length > 0) {
//                 $.each(data, function(key, value) {
//                     $('#im_transactiontype').append('<option data-type="' + value.transaction_type + '" value="' + value.id + '">' + value.name + '</option>');
//                 });
//             } else {
//                 Swal.fire({
//                     text: 'Transaction Types are not currently available.',
//                     icon: 'error',
//                     confirmButtonText: 'OK'
//                 }).then((result) => {
//                     if (result.isConfirmed) {
//                         $('#add-manageinventory').modal('hide');
//                     }
//                 });
//             }
//         });
        
//         $('#inv-management-section').hide();
//         $(document).off('change', '#im_transactiontype').on('change', '#im_transactiontype', function() {
//             if(!orgId)
//             {
//                 $('#im_org').val($(this).find('option:first').val()).trigger('change');
//                 $('#im_site').val($(this).find('option:first').val()).trigger('change').prop('disabled',true);
//                 $('#im_destination').val($(this).find('option:first').val()).trigger('change').prop('disabled',true);
//             }
//             $('#im_batch_no').val('');
//             $('#expiry-date').val('');
//             $('#im_rate').val('');
//             $('#im_qty').val('');
//             $('#itemQty').show();
//             $('#im_qty').prop('disabled',false);
//             var TransactionTypeID = $(this).val();
//             var dataType = $(this).find('option:selected').data('type'); 
//             $('#inv-management-section').show();
//             $('#itemexpiry,#itemrate').hide();
//             var referenceDocument = '';
//             var From = '';
//             var To = '';
//             if(dataType == 'opening balance')
//             {
//                 $('#itemexpiry,#itemrate').show();
//                 $('#im_rate,#expiry-date').prop('disabled',false);
//                 $('#reference_document_section, #from_section, #to_section').hide();
//                 var referenceDocument = 'null';
//                 var From = 'null';
//                 var To = 'null';
//                 $('#selectoption').hide();
//                 $('#selectoption select').removeAttr('name id');
//                 $('#opentext').show();
//                 $('#opentext input').attr({
//                     'name': 'im_reference_document',
//                     'id': 'im_reference_document'
//                 });
//                 $('#im_reference_document').val('').prop('disabled',false);
//                 $('#select_batch select').select2('destroy');
//                 $('#select_batch').find('label, select').remove();
//                 $('#enter_batch').show();
//                 $('#enter_batch').html('<label for="im_reference_document">Enter Batch #</label><input type="text" placeholder="Enter Batch #" class="form-control input-sm">');
//                 $('#enter_batch input').attr({
//                     'name': 'im_batch_no',
//                     'id': 'im_batch_no'
//                 });

//             }
//             else if(dataType == 'addition')
//             {
//                 $('#itemexpiry,#itemrate').show();
//                 $('#im_rate,#expiry-date').prop('disabled',false);
//                 $('#reference_document_section, #from_section, #to_section').show();
//                 var referenceDocument = 'Open Text';
//                 if(!orgId)
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                     OrgChangeVendor('#im_org', '#im_origin', '#add_manageinventory');
//                     $('#im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_destination', '#add_manageinventory');
//                 }
//                 else{
//                     $('#im_destination').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',false);
//                     fetchOrganizationVendor(orgId, '#im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_origin').append('<option value="' + value.id + '">' + value.person_name + '</option>');
//                         });
//                     });
//                 }
//                 $('#selectoption').hide();
//                 $('#selectoption select').removeAttr('name id');
//                 $('#opentext').show();
//                 $('#opentext input').attr({
//                     'name': 'im_reference_document',
//                     'id': 'im_reference_document'
//                 });
//                 $('#im_reference_document').val('').prop('disabled',false);
//                 $('#select_batch select').select2('destroy');
//                 $('#select_batch').find('label, select').remove();
//                 $('#enter_batch').show();
//                 $('#enter_batch').html('<label for="im_reference_document">Enter Batch #</label><input type="text" placeholder="Enter Batch #" class="form-control input-sm">');
//                 $('#enter_batch input').attr({
//                     'name': 'im_batch_no',
//                     'id': 'im_batch_no'
//                 });
//             }
//             else if(dataType == 'reduction')
//             {
//                 $('#reference_document_section, #from_section, #to_section').show();
//                 $("#im_batch_no").remove();
//                 if(!orgId)
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_origin', '#add_manageinventory');
//                     $('#im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                     OrgChangeVendor('#im_org', '#im_destination', '#add_manageinventory');
//                 }
//                 else
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                     $('#im_destination').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',false);
//                     fetchOrganizationVendor(orgId, '#im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_destination').append('<option value="' + value.id + '">' + value.person_name + '</option>');
//                         });
//                     });
//                 }
//                 $('#opentext input').removeAttr('name id');
//                 $('#opentext').hide();
//                 $('#selectoption').show();
//                 $('#selectoption select').attr({
//                     'id': 'im_reference_document',
//                     'name': 'im_reference_document'
//                 }).select2();
//                 $('#im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>").prop('disabled',true);
//                 BrandChangeInventory('#im_brand', '#im_reference_document', '#add_manageinventory');

//                 $('#select_batch').show();
//                 $('#select_batch').html('<label class="control-label">Select Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                 $('#enter_batch').hide();
//                 $('#enter_batch').find('label, input').remove();
//                 $('#select_batch select').attr({
//                     'id': 'im_batch_no',
//                     'name': 'im_batch_no'
//                 }).select2();
//                 $('#im_batch_no').html("<option selected disabled value=''>Select Batch #</option>").prop('disabled',true);
//                 BrandChangeBatch('#im_brand', '#im_batch_no', '#add_manageinventory');
//                 BatchChangeExpiryRate('add','#im_batch_no', '#itemexpiry', '#itemrate', 'reduction');
//             }
//             else if(dataType == 'transfer')
//             {
//                 $('#reference_document_section').hide();
//                 $('#from_section, #to_section').show();
//                 $('#selectoption').hide();
//                 $('#selectoption select').removeAttr('name id');
//                 $('#opentext').show();
//                 $('#opentext input').attr({
//                     'name': 'im_reference_document',
//                     'id': 'im_reference_document'
//                 });
//                 $('#im_reference_document').val('').prop('disabled',false);

//                 $('#select_batch').show();
//                 $('#select_batch').html('<label class="control-label">Select Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                 $('#enter_batch').hide();
//                 $('#enter_batch').find('label, input').remove();
//                 $('#select_batch select').attr({
//                     'id': 'im_batch_no',
//                     'name': 'im_batch_no'
//                 }).select2();
//                 $('#im_batch_no').html("<option selected disabled value=''>Select Batch #</option>").prop('disabled',true);
//                 BrandChangeBatch('#im_brand', '#im_batch_no', '#add_manageinventory');
//                 BatchChangeExpiryRate('add','#im_batch_no', '#itemexpiry', '#itemrate', 'transfer');
//                 if(!orgId)
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_origin', '#add_manageinventory');
//                     $('#im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_destination', '#add_manageinventory');
//                 }
//                 else
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                     $('#im_destination').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                 }
                

//             }
//             else if(dataType == 'general consumption')
//             {
//                 $('#to_section').hide();
//                 $('#reference_document_section, #from_section').show();
//                 if(!orgId)
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_origin', '#add_manageinventory');
//                 }
//                 else
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                 }
//                 $('#opentext').hide();
//                 $('#opentext input').removeAttr('name id');
//                 $('#selectoption').show();
//                 $('#selectoption select').attr({
//                     'id': 'im_reference_document',
//                     'name': 'im_reference_document'
//                 }).select2();
//                 $('#im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
//                 SiteChangeRequisition('#im_site', '#im_transactiontype', '#im_reference_document', '#add_manageinventory');
                
//                 $('#select_batch').show();
//                 $('#select_batch').html('<label class="control-label">Select Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                 $('#enter_batch').hide();
//                 $('#enter_batch').find('label, input').remove();
//                 $('#select_batch select').attr({
//                     'id': 'im_batch_no',
//                     'name': 'im_batch_no'
//                 }).select2();
//                 $('#im_batch_no').html("<option selected disabled value=''>Select Batch #</option>").prop('disabled',true);
//                 BrandChangeBatch('#im_brand', '#im_batch_no', '#add_manageinventory');
//                 BatchChangeExpiryRate('add','#im_batch_no', '#itemexpiry', '#itemrate', 'general_consumption');
//             }
//             else if(dataType == 'patient consumption')
//             {
//                 $('#reference_document_section, #from_section, #to_section').show();
            
//                 if(!orgId)
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                     OrgChangeSites('#im_org', '#im_origin', '#add_manageinventory');
//                 }
//                 else
//                 {
//                     $('#im_origin').html("<option selected disabled value=''>Select Site</option>");
//                     fetchOrganizationSites(orgId, '#im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             $('#im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     });
//                 }
//                 $('#im_destination').html("<option selected disabled value=''>Select MR#</option>").prop('disabled',true);
//                 SiteChangeMRCode('#im_site', '#im_destination', '#add_manageinventory', null);
//                 $('#opentext').hide();
//                 $('#opentext input').removeAttr('name id');
//                 $('#selectoption').show();
//                 $('#selectoption select').attr({
//                     'id': 'im_reference_document',
//                     'name': 'im_reference_document'
//                 }).select2();
//                 $('#im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
//                 SiteChangeRequisition('#im_site', '#im_transactiontype', '#im_reference_document', '#add_manageinventory');
                
//                 $('#select_batch').show();
//                 $('#select_batch').html('<label class="control-label">Select Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                 $('#enter_batch').hide();
//                 $('#enter_batch').find('label, input').remove();
//                 $('#select_batch select').attr({
//                     'id': 'im_batch_no',
//                     'name': 'im_batch_no'
//                 }).select2();
//                 $('#im_batch_no').html("<option selected disabled value=''>Select Batch #</option>").prop('disabled',true);
//                 BrandChangeBatch('#im_brand', '#im_batch_no', '#add_manageinventory');
//                 BatchChangeExpiryRate('add','#im_batch_no', '#itemexpiry', '#itemrate', 'patient_consumption');
//             }
//             else if(dataType == 'reversal')
//             {
//                 $('#opentext').hide();
//                 $('#opentext input').removeAttr('name id');
//                 $('#itemQty').hide();
//                 $('#selectoption').show();
//                 $('#selectoption select').attr({
//                     'id': 'im_reference_document',
//                     'name': 'im_reference_document'
//                 }).select2();
//                 $('#im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>").prop('disabled',true);
//                 BrandChangeInventory('#im_brand', '#im_reference_document', '#add_manageinventory');

//                 $('#select_batch').show();
//                 $('#select_batch').html('<label class="control-label">Select Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                 $('#enter_batch').hide();
//                 $('#enter_batch').find('label, input').remove();
//                 $('#select_batch select').attr({
//                     'id': 'im_batch_no',
//                     'name': 'im_batch_no'
//                 }).select2();
//                 $('#im_batch_no').html("<option selected disabled value=''>Select Batch #</option>").prop('disabled',true);
//                 BrandChangeBatch('#im_brand', '#im_batch_no', '#add_manageinventory');
//                 BatchChangeExpiryRate('add','#im_batch_no', '#itemexpiry', '#itemrate', 'reversal');

//                 $('#reference_document_section').show();
//                 $('#from_section, #to_section').hide();
//             }

//             if(!orgId)
//             {
//                 $('#im_org').html("<option selected disabled value=''>Select Organization</option>");
//                 fetchTransactionTypeOrganizations(TransactionTypeID, '#im_org', function(data) {
//                     $('#im_org').find('option:contains("Loading...")').remove();
//                     $.each(data, function(key, value) {
//                         $('#im_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
//                     });
//                 });
//             }
            
//         });
//         $('#add-manageinventory').modal('show');
//     });
//     //Open Inventory Management Modal

//     //Add Inventory Management
//     $('#add_manageinventory').submit(function(e) {
//         e.preventDefault();
//         var data = SerializeForm(this);
//         var transactionTypevalue = data.find(function(item) {
//             return item.name === 'im_transactiontype';
//         });
//         var logic = false; 
//         if (transactionTypevalue) {
//             var excludedFields = [];
//             CheckTransactionTypes(transactionTypevalue.value, '#im_transactiontype')
//                 .then(function (transactionTypedata) {
//                     $.each(transactionTypedata, function (key, value) {
//                         if (value.transaction_type == 'opening balance') {
//                             logic = true;
//                             excludedFields = ['im_reference_document', 'im_origin', 'im_destination'];
//                         } else if (value.transaction_type == 'addition') {
//                             logic = false;
//                             excludedFields = [];
//                         } else if (value.transaction_type == 'general consumption') {
//                             logic = true;
//                             excludedFields = ['im_reference_document', 'im_destination']; 
//                         } else if (value.transaction_type == 'reversal') {
//                             logic = true;
//                             excludedFields = ['im_origin', 'im_destination']; 
//                         }
//                         // else if (value.transaction_type == 'reduction') {
//                         //     logic = true;
//                         //     excludedFields = ['im_expiry', 'im_rate']; 
//                         // } 
//                         else if (value.transaction_type == 'patient consumption') {
//                             logic = false;
//                             excludedFields = [];
//                         }
//                         else if (value.transaction_type == 'transfer') {
//                             logic = true;
//                             excludedFields = ['im_reference_document']; 
//                         }
//                     });
//                     var resp = true;
//                     $(data).each(function(i, field){
//                         if ((field.value === '' || field.value === null) && (typeof field.name !== 'undefined') && (!logic || !excludedFields.includes(field.name)))
//                         {
//                             var FieldName = field.name;
//                             console.log(FieldName);
//                             var FieldID = '#'+FieldName + "_error";
//                             $(FieldID).text("This field is required");
//                             $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
//                             $( 'input[name= "' +FieldName +'"' ).focus(function() {
//                                 $(FieldID).text("");
//                                 $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
//                             })
//                             $('select[name= "' +FieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
//                             $('select[name= "' +FieldName +'"' ).on('select2:open', function() {
//                                 $(FieldID).text("");
//                                 $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
//                             });
//                             $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
//                                 $(FieldID).text("");
//                                 $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
//                             })
//                             $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
//                             resp = false;
//                         }
//                     });
//                     if(resp != false)
//                     {
//                         $.ajax({
//                             url: "/inventory/addinvmanagement",
//                             method: "POST",
//                             headers: {
//                                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                             },
//                             data: data,
//                             beforeSend: function() {
//                                 Swal.fire({
//                                     title: "Processing",
//                                     allowOutsideClick: false,
//                                     willOpen: () => {
//                                         Swal.showLoading();
//                                     },
//                                     showConfirmButton: false
//                                 });
//                             },
//                             success: function(response) {
//                                 for (var fieldName in response) {
//                                     var fieldErrors = response[fieldName];
//                                 }
//                                 if (fieldName == 'error')
//                                 {
//                                     Swal.fire({
//                                         text: fieldErrors,
//                                         icon: fieldName,
//                                         confirmButtonText: 'OK'
//                                     })
//                                 }
//                                 else if (fieldName == 'success')
//                                 {
//                                     Swal.fire({
//                                         text: fieldErrors,
//                                         icon: fieldName,
//                                         allowOutsideClick: false,
//                                         confirmButtonText: 'OK'
//                                     }).then((result) => {
//                                         if (result.isConfirmed) {
//                                             $('#add-manageinventory').modal('hide');
//                                             location.reload();
//                                         }
//                                     });
//                                 }
//                                 else if (fieldName == 'info')
//                                 {
//                                     Swal.fire({
//                                         text: fieldErrors,
//                                         icon: fieldName,
//                                         confirmButtonText: 'OK'
//                                     }).then((result) => {
//                                         if (result.isConfirmed) {
//                                             $('#add-manageinventory').modal('hide');
//                                         }
//                                     });
//                                 }
//                             },
//                             error: function(error) {
//                                 if (error.responseJSON && error.responseJSON.errors) {
//                                     $('.text-danger').show();
//                                     var errors = error.responseJSON.errors;
//                                     for (var fieldName in errors) {
//                                         var fieldErrors = errors[fieldName];
//                                         for (var i = 0; i < fieldErrors.length; i++) {
//                                             fieldName = '#'+fieldName + '_error';
//                                             $(fieldName).text(fieldErrors[i]);
//                                         }
//                                     }
//                                     Swal.close();
//                                 }
//                             }
//                         });
//                     }
//                 })
//                 .fail(function (error) {
//                     console.error(error);
//                 });
//         }
//     });
//     //Add Inventory Management

//     // View Inventory Management
//     var viewInventoryManagement =  $('#view-manageinventory').DataTable({
//         processing: true,
//         serverSide: true,
//         ajax: '/inventory/invmanagement',
//         order: [[0, 'desc']],
//         columns: [
//             { data: 'id_raw', name: 'id_raw', visible: false },
//             { data: 'id', name: 'id' },
//             { data: 'brand_details', name: 'brand_details' },
//             { data: 'transaction_details', name: 'transaction_details' },
//             { data: 'status', name: 'status' },
//             { data: 'action', name: 'action', orderable: false, searchable: false }
//         ],
//         columnDefs: [
//             {
//                 targets: 1,
//                 width: "300px"
//             },
//               {
//                 targets: 2,
//                 width: "300px"
//             },
//               {
//                 targets: 3,
//                 width: "350px"
//             },
//             {
//                 targets: 5,
//                 width: "350px"
//             }
//         ]
//     });

//     viewInventoryManagement.on('draw.dt', function() {
//         $('[data-toggle="popover"]').popover({
//             html: true
//         });
//     });
//     viewInventoryManagement.on('preXhr.dt', function() {
//         $('#ajax-loader').show();
//     });
//     viewInventoryManagement.on('xhr.dt', function() {
//         $('#ajax-loader').hide();
//     });
//     // View Inventory Management

//     //Update Inventory Management Modal
//     $(document).on('click', '.edit-manageinventory', function() {
//         var inventoryId = $(this).data('manageinventory-id');
//         var url = '/inventory/updateinvmanagement/' + inventoryId;
//         $('#ajax-loader').show();
//         $.ajax({
//             url: url,
//             type: 'GET',
//             dataType: 'json',
//             success: function(response) {
//                 $('#u_im_brand').empty();
//                 $('#u_im_site').empty();
//                 $('#ajax-loader').hide();
//                 var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
//                 $('.uedt').each(function() {
//                     var edtElement = $(this);
//                     edtElement.val(formattedDateTime);
//                 });
//                 $('.u_im-id').val(response.id);
//                 var transactionType =  response.TransactionType;

//                 $('#u_im_qty').prop('disabled',false);

//                 var orgId = $('#u_im_org').val();
//                 if(!orgId)
//                 {
//                     $('#u_im_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
//                     fetchOrganizations('null', '','#u_im_org', function(data) {
//                         $('#u_im_org').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.orgId)
//                             {
//                                 $('#u_im_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
//                             }
//                         });
//                     });
//                     OrgChangeSites('#u_im_org', '#u_im_site', '#add_manageinventory');
//                     OrgChangeBrand('#u_im_org', '#u_im_brand', '#add_manageinventory');

//                 }
//                 $('#u_im_site').html("<option selected value='"+response.siteId+"'>" + response.siteName + "</option>");
//                 fetchSites(response.orgId, '#u_im_site', function(data) {
//                     if (data.length > 0) {
//                         $.each(data, function(key, value) {
//                             $('#u_im_site').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         });
//                     }
//                     else {
//                         Swal.fire({
//                             text: 'Sites are not available for selected Organization',
//                             icon: 'error',
//                             confirmButtonText: 'OK'
//                         }).then((result) => {
//                             if (result.isConfirmed) {
//                                 $('#edit-materialconsumption').modal('hide');
//                             }
//                         });
//                     }
//                 }, function(error) {
//                     console.log(error);
//                 },response.siteId);

//                 $('#u_im_brand').html("<option selected value='"+response.brandId+"'>" + response.brandName + "</option>");
//                 fetchOrganizationBrand(response.orgId,'#u_im_brand', function(data) {
//                     $('#u_im_brand').find('option:contains("Loading...")').remove();
//                     $.each(data, function(key, value) {
//                         if(value.id != response.brandId)
//                         {
//                             $('#u_im_brand').append('<option value="' + value.id + '">' + value.name + '</option>');
//                         }
//                     });
//                 });
//                 $('#u_im_transactiontype').html("<option selected  data-type="+ transactionType +" value="+response.transactionTypeId+">"+response.TransactionTypeName+"</option>").prop('disabled',true);
                
//                 if(transactionType == 'opening balance')
//                 {
//                     $('#u_reference_document_section, #u_from_section, #u_to_section').hide();
//                     var referenceDocument = 'null';
//                     var From = 'null';
//                     var To = 'null';
//                     $('#u_selectoption').hide();
//                     $('#u_selectoption select').removeAttr('name id');
//                     $('#u_opentext').show();
//                     $('#u_opentext input').attr({
//                         'name': 'u_im_reference_document',
//                         'id': 'u_im_reference_document'
//                     });
//                     $('#u_im_reference_document').val(response.document_no);

//                     $('#u_select_batch select').select2('destroy');
//                     $('#u_select_batch').find('label, select').remove();
//                     $('#u_enter_batch').show();
//                     $('#u_enter_batch').html('<label for="im_reference_document">Update Batch #</label><input type="text" class="form-control input-sm">');
//                     $('#u_enter_batch input').attr({
//                         'name': 'u_im_batch_no',
//                         'id': 'u_im_batch_no'
//                     });
//                     $('#u_im_batch_no').val(response.batchNo);

//                 }
//                 else if(transactionType == 'addition')
//                 {
//                     $('#u_reference_document_section, #u_from_section, #u_to_section').show();
//                     var referenceDocument = 'Open Text';
//                     if(!orgId)
//                     {
//                         OrgChangeVendor('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                         OrgChangeSites('#u_im_org', '#u_im_destination', '#update_manageinventory');
//                     }
//                     $('#u_im_origin').attr({'required': 'required'});
//                     $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
//                     fetchOrganizationVendor(response.orgId, '#u_im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.OriginId)
//                             {
//                                 $('#u_im_origin').append('<option value="' + value.id + '">' + value.person_name + '</option>');
//                             }
//                         });
//                     });

//                     $('#u_im_destination').attr({'required': 'required'});
//                     $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
//                     fetchOrganizationSites(response.orgId, '#u_im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.DestinationId)
//                             {
//                                 $('#u_im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
//                             }
//                         });
//                     });
                    
//                     $('#u_selectoption').hide();
//                     $('#u_selectoption select').removeAttr('name id');
//                     $('#u_opentext').show();
//                     $('#u_opentext input').attr({
//                         'name': 'u_im_reference_document',
//                         'id': 'u_im_reference_document',
//                         'required': 'required'
//                     });
//                     $('#u_im_reference_document').val('');
//                     $('#u_im_reference_document').val(response.document);

//                     $('#u_select_batch select').select2('destroy');
//                     $('#u_select_batch').find('label, select').remove();
//                     $('#u_enter_batch').show();
//                     $('#u_enter_batch').html('<label for="im_reference_document">Update Batch #</label><input type="text" class="form-control input-sm">');
//                     $('#u_enter_batch input').attr({
//                         'name': 'u_im_batch_no',
//                         'id': 'u_im_batch_no'
//                     });
//                     $('#u_im_batch_no').val(response.batchNo);

//                 }
//                 else if(transactionType == 'reduction')
//                 {
//                     $('#u_opentext').hide();
//                     $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
//                     $('#u_opentext input').removeAttr('name id');
//                     $('#u_selectoption').show();
//                     $('#u_selectoption select').attr({
//                         'id': 'u_im_reference_document',
//                         'name': 'u_im_reference_document',
//                         'required': 'required'
//                     });
//                     $('#u_im_reference_document').empty();
//                     $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
//                     if(!orgId)
//                     {
//                         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                         OrgChangeVendor('#u_im_org', '#u_im_destination', '#update_manageinventory');

//                     }
                    
//                     fetchBrandInventory(response.brandId, '#u_im_reference_document', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.documentId)
//                             {
//                                 $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.code+'-00000'+value.id + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_manageinventory');

//                     $('#u_im_origin').attr({'required': 'required'});
//                     $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
//                     fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.DestinationId)
//                             {
//                                 $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                             }
//                         });
//                     });


//                     $('#u_im_destination').attr({'required': 'required'});
//                     $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
//                     fetchOrganizationVendor(response.orgId, '#u_im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.DestinationId)
//                             {
//                                 $('#u_im_destination').append('<option value="' + value.id + '">' + value.person_name + '</option>');
//                             }
//                         });
//                     });
//                     $('#u_reference_document_section, #u_from_section, #u_to_section').show();

//                     $('#u_select_batch').show();
//                     $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                     $('#u_enter_batch').hide();
//                     $('#u_enter_batch').find('label, input').remove();
//                     $('#u_select_batch select').attr({
//                         'id': 'u_im_batch_no',
//                         'name': 'u_im_batch_no'
//                     }).select2();
//                     $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
//                     fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
//                         $('#u_im_batch_no').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.id)
//                             {
//                                 $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_manageinventory');
//                     BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'reduction');
//                 }
//                 else if(transactionType == 'transfer')
//                 {
//                     $('#u_reference_document_section').hide();
//                     $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
//                     $('#u_from_section, #u_to_section').show();
//                     $('#u_selectoption').hide();
//                     $('#u_selectoption select').removeAttr('name id');
//                     $('#u_opentext').show();
//                     $('#u_opentext input').attr({
//                         'name': 'u_im_reference_document',
//                         'id': 'u_im_reference_document',
//                         'required': 'required'
//                     });

//                     if(!orgId)
//                     {
//                         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                         OrgChangeSites('#u_im_org', '#u_im_destination', '#update_manageinventory');
//                     }

//                     $('#u_im_origin').attr({'required': 'required'});
//                     $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
//                     fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.OriginId)
//                             {
//                                 $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                             }
//                         });
//                     });

//                     $('#u_im_destination').attr({'required': 'required'});
//                     $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
//                     fetchOrganizationSites(response.orgId, '#u_im_destination', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.DestinationId)
//                             {
//                                 $('#u_im_destination').append('<option value="' + value.id + '">' + value.name + '</option>');
//                             }
//                         });
//                     });

//                     $('#u_select_batch').show();
//                     $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                     $('#u_enter_batch').hide();
//                     $('#u_enter_batch').find('label, input').remove();
//                     $('#u_select_batch select').attr({
//                         'id': 'u_im_batch_no',
//                         'name': 'u_im_batch_no'
//                     }).select2();
//                     $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
//                     fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
//                         $('#u_im_batch_no').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.id)
//                             {
//                                 $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_manageinventory');
//                     BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'transfer');
//                 }
//                 else if(transactionType == 'general consumption')
//                 {
//                     $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
//                     $('#u_to_section').hide();
//                     $('#u_reference_document_section, #u_from_section').show();

//                     $('#u_im_origin').attr({'required': 'required'});
//                     $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
//                     fetchOrganizationSites(response.orgId, '#u_im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.DestinationId)
//                             {
//                                 $('#u_im_origin').append('<option value="' + value.id + '">' + value.name + '</option>');
//                             }
//                         });
//                     });
//                     if(!orgId)
//                     {
//                         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                     }

//                     $('#u_opentext').hide();
//                     $('#u_opentext input').removeAttr('name id');
//                     $('#u_selectoption').show();
//                     $('#u_selectoption select').attr({
//                         'id': 'u_im_reference_document',
//                         'name': 'u_im_reference_document',
//                         'required': 'required'
//                     });
//                     $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
//                     fetchSiteRequisition(response.siteId, response.transactionTypeId, '#u_im_reference_document', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.documentId)
//                             {
//                                 $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.remarks + '</option>');
//                             }
//                         });
//                     });
//                     SiteChangeRequisition('#u_im_site', '#u_im_transactiontype', '#u_im_reference_document', '#update_manageinventory');
                    
//                     $('#u_select_batch').show();
//                     $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                     $('#u_enter_batch').hide();
//                     $('#u_enter_batch').find('label, input').remove();
//                     $('#u_select_batch select').attr({
//                         'id': 'u_im_batch_no',
//                         'name': 'u_im_batch_no'
//                     }).select2();
//                     $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
//                     fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
//                         $('#u_im_batch_no').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.id)
//                             {
//                                 $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_manageinventory');
//                     BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'general_consumption');
//                 }
//                 else if(transactionType == 'patient consumption')
//                 {
//                     $('#u_im_expirydate,#u_im_rate').prop('disabled',true);
//                     $('#u_reference_document_section, #u_from_section, #u_to_section').show();

//                     $('#u_im_origin').attr({'required': 'required'});
//                     $('#u_im_origin').html("<option selected value="+response.OriginId+">"+response.OriginName+"</option>").prop('disabled', false);
//                     fetchOrganizationVendor(response.orgId, '#u_im_origin', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.OriginId)
//                             {
//                                 $('#u_im_origin').append('<option value="' + value.id + '">' + value.person_name + '</option>');
//                             }
//                         });
//                     });
//                     if(!orgId)
//                     {
//                         OrgChangeVendor('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                     }
//                     $('#u_im_destination').attr({'required': 'required'});
//                     $('#u_im_destination').html("<option selected value="+response.DestinationId+">"+response.DestinationName+"</option>").prop('disabled', false);
//                     fetchPatientMR(response.siteId, '#u_im_destination', null, function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.mr_code != response.DestinationId)
//                             {
//                                 $('#u_im_destination').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
//                             }
//                         });
//                     });
//                     SiteChangeMRCode('#u_im_site', '#u_im_destination', '#update_manageinventory', null);
//                     $('#u_opentext').hide();
//                     $('#u_opentext input').removeAttr('name id');
//                     $('#u_selectoption').show();
//                     $('#u_selectoption select').attr({
//                         'id': 'u_im_reference_document',
//                         'name': 'u_im_reference_document',
//                         'required': 'required'
//                     });
//                     $('#u_im_reference_document').html("<option selected disabled value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
//                     fetchSiteRequisition(response.siteId, response.transactionTypeId, '#u_im_reference_document', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.documentId)
//                             {
//                                 $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.remarks + '</option>');
//                             }
//                         });
//                     });
//                     SiteChangeRequisition('#u_im_site', '#u_im_transactiontype', '#u_im_reference_document', '#update_manageinventory');
                    
//                     $('#u_select_batch').show();
//                     $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                     $('#u_enter_batch').hide();
//                     $('#u_enter_batch').find('label, input').remove();
//                     $('#u_select_batch select').attr({
//                         'id': 'u_im_batch_no',
//                         'name': 'u_im_batch_no'
//                     }).select2();
//                     $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
//                     fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
//                         $('#u_im_batch_no').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.id)
//                             {
//                                 $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_manageinventory');
//                     BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'patient_consumption');
//                 }
//                 else if(transactionType == 'reversal')
//                 {
//                     $('#u_im_expirydate,#u_im_rate,#u_im_qty').prop('disabled',true);
//                     $('#u_opentext').hide();
//                     $('#u_opentext input').removeAttr('name id');
//                     $('#u_selectoption').show();
//                     $('#u_selectoption select').attr({
//                         'id': 'u_im_reference_document',
//                         'name': 'u_im_reference_document',
//                         'required': 'required'
//                     });
//                     $('#u_im_reference_document').empty();
//                     $('#u_im_reference_document').html("<option selected value="+response.documentId+">"+response.document+"</option>").prop('disabled', false);
//                     fetchBrandInventory(response.brandId, '#u_im_reference_document', function(data) {
//                         $.each(data, function(key, value) {
//                             if(value.id != response.documentId)
//                             {
//                                 $('#u_im_reference_document').append('<option value="' + value.id + '">' + value.code+'-00000'+value.id + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_manageinventory');
//                     $('#u_reference_document_section').show();
//                     $('#u_from_section, #u_to_section').hide();

//                     $('#u_select_batch').show();
//                     $('#u_select_batch').html('<label class="control-label">Update Batch  #</label> <select class="form-control selecter p-0" style="color:#222d32"></select>');
//                     $('#u_enter_batch').hide();
//                     $('#u_enter_batch').find('label, input').remove();
//                     $('#u_select_batch select').attr({
//                         'id': 'u_im_batch_no',
//                         'name': 'u_im_batch_no'
//                     }).select2();
//                     $('#u_im_batch_no').html("<option selected value='"+response.id+"'>" + response.batchNo + "</option>");
//                     fetchBrandBatch(response.brandId,'#u_im_batch_no', function(data) {
//                         $('#u_im_batch_no').find('option:contains("Loading...")').remove();
//                         $.each(data, function(key, value) {
//                             if(value.id != response.id)
//                             {
//                                 $('#u_im_batch_no').append('<option data-id= "' + value.id + '" value="' + value.batch_no + '">' + value.batch_no + '</option>');
//                             }
//                         });
//                     });
//                     BrandChangeBatch('#u_im_brand', '#u_im_batch_no', '#add_manageinventory');
//                     BatchChangeExpiryRate('update','#u_im_batch_no', '#u_itemexpiry', '#u_itemrate', 'reversal');
//                 }

               
//                 var formattedExpiryDate = moment(response.expiryDate).format('YYYY-MM-DD');
//                 $('#u_im_expirydate').each(function() {
//                     var expiryDateElement = $(this);
//                     expiryDateElement.val(formattedExpiryDate);
//                 });
//                 $('#u_im_rate').val(response.rate);
//                 $('#u_im_qty').val(response.qty);
//                 var documentType = response.document_type;
                
//                 // fetchTransactionTypeOrganizations(TransactionTypeID,'#u_im_org', function(data) {
//                 //     $('#u_im_org').empty();
//                 //     $('#u_im_org').html("<option selected disabled value=''>Select Organization</option>");
//                 //     $('#u_im_org').find('option:contains("Loading...")').remove();
//                 //     $.each(data, function(key, value) {
//                 //         $('#u_im_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
//                 //     });
//                 // });
//                 // fetchTransactionTypes('null', '#u_im_transactiontype', false, function(data) {
//                 //     if (data && data.length > 0) {
//                 //         $.each(data, function(key, value) {
//                 //             if(value.id != response.transactionTypeId)
//                 //             {
//                 //                 $('#u_im_transactiontype').append('<option data-type="' + value.transaction_type + '" value="' + value.id + '">' + value.name + '</option>');
//                 //             }
//                 //         });
//                 //     } else {
//                 //         Swal.fire({
//                 //             text: 'Transaction Types are not currently available.',
//                 //             icon: 'error',
//                 //             confirmButtonText: 'OK'
//                 //         }).then((result) => {
//                 //             if (result.isConfirmed) {
//                 //                 $('#edit-manageinventory').modal('hide');
//                 //             }
//                 //         });

//                 //     }
//                 // });

                
//                 // $(document).off('change', '#u_im_transactiontype').on('change', '#u_im_transactiontype', function() {
//                 // // $(document).on('change', '#u_im_transactiontype', function() {
//                 //     $('#u_im_org').val($(this).find('option:first').val()).trigger('change');
//                 //     $('#u_im_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
//                 //     $('#u_im_brand').html("<option selected disabled value=''>Select Brand</option>").prop('disabled',true);
//                 //     OrgChangeBrand('#u_im_org', '#u_im_brand', '#add_manageinventory');

//                 //     var TransactionTypeID = $(this).val();
//                 //     var dataType = $(this).find('option:selected').data('type'); 
//                 //     $('#u_inv-management-section').show();
//                 //     var referenceDocument = '';
//                 //     var From = '';
//                 //     var To = '';

//                 //     if(dataType == 'opening balance')
//                 //     {
//                 //         $('#u_reference_document_section, #u_from_section, #u_to_section').hide();
//                 //         var referenceDocument = 'null';
//                 //         var From = 'null';
//                 //         var To = 'null';
//                 //         $('#u_selectoption').hide();
//                 //         $('#u_selectoption select').removeAttr('name id');
//                 //         $('#u_opentext').show();
//                 //         $('#u_opentext input').attr({
//                 //             'name': 'u_im_reference_document',
//                 //             'id': 'u_im_reference_document'
//                 //         });
//                 //     }
//                 //     else if(dataType == 'addition')
//                 //     {
//                 //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
//                 //         var referenceDocument = 'Open Text';
//                 //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                 //         OrgChangeVendor('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                 //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                 //         OrgChangeSites('#u_im_org', '#u_im_destination', '#update_manageinventory');
//                 //         $('#u_selectoption').hide();
//                 //         $('#u_selectoption select').removeAttr('name id');
//                 //         $('#u_opentext').show();
//                 //         $('#u_opentext input').attr({
//                 //             'name': 'u_im_reference_document',
//                 //             'id': 'u_im_reference_document'
//                 //         });
//                 //     }
//                 //     else if(dataType == 'reduction')
//                 //     {
//                 //         $('#u_opentext').hide();
//                 //         $('#u_opentext input').removeAttr('name id');
//                 //         $('#u_selectoption').show();
//                 //         $('#u_selectoption select').attr({
//                 //             'id': 'u_im_reference_document',
//                 //             'name': 'u_im_reference_document'
//                 //         });
//                 //         $('#u_im_reference_document').empty();
//                 //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>");
//                 //         BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_manageinventory');
//                 //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
//                 //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                 //         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                 //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                 //         OrgChangeVendor('#u_im_org', '#u_im_destination', '#update_manageinventory');
                        
//                 //     }
//                 //     else if(dataType == 'transfer')
//                 //     {
//                 //         $('#u_reference_document_section').hide();
//                 //         $('#u_from_section, #u_to_section').show();
//                 //         $('#u_selectoption').hide();
//                 //         $('#u_selectoption select').removeAttr('name id');
//                 //         $('#u_opentext').show();
//                 //         $('#u_opentext input').attr({
//                 //             'name': 'u_im_reference_document',
//                 //             'id': 'u_im_reference_document'
//                 //         });
//                 //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                 //         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                 //         $('#u_im_destination').html("<option selected disabled value=''>Select Destination</option>").prop('disabled',true);
//                 //         OrgChangeSites('#u_im_org', '#u_im_destination', '#update_manageinventory');
//                 //     }
//                 //     else if(dataType == 'general consumption')
//                 //     {
//                 //         $('#u_to_section').hide();
//                 //         $('#u_reference_document_section, #u_from_section').show();
//                 //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                 //         OrgChangeSites('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                 //         $('#u_opentext').hide();
//                 //         $('#u_opentext input').removeAttr('name id');
//                 //         $('#u_selectoption').show();
//                 //         $('#u_selectoption select').attr({
//                 //             'id': 'u_im_reference_document',
//                 //             'name': 'u_im_reference_document'
//                 //         });
//                 //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
//                 //         SiteChangeRequisition('#u_im_site', '#u_im_transactiontype', '#u_im_reference_document', '#update_manageinventory');
//                 //     }
//                 //     else if(dataType == 'patient consumption')
//                 //     {
//                 //         $('#u_reference_document_section, #u_from_section, #u_to_section').show();
//                 //         $('#u_im_origin').html("<option selected disabled value=''>Select Origin</option>").prop('disabled',true);
//                 //         OrgChangeVendor('#u_im_org', '#u_im_origin', '#update_manageinventory');
//                 //         $('#u_im_destination').html("<option selected disabled value=''>Select MR#</option>").prop('disabled',true);
//                 //         SiteChangeMRCode('#u_im_site', '#u_im_destination', '#update_manageinventory', null);
//                 //         $('#u_opentext').hide();
//                 //         $('#u_opentext input').removeAttr('name id');
//                 //         $('#u_selectoption').show();
//                 //         $('#u_selectoption select').attr({
//                 //             'id': 'u_im_reference_document',
//                 //             'name': 'u_im_reference_document'
//                 //         });
//                 //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Requisition</option>").prop('disabled',true);
//                 //         SiteChangeRequisition('#u_im_site', '#u_im_transactiontype', '#u_im_reference_document', '#update_manageinventory');
        
//                 //     }
//                 //     else if(dataType == 'reversal')
//                 //     {
//                 //         $('#u_opentext').hide();
//                 //         $('#u_opentext input').removeAttr('name id');
//                 //         $('#u_selectoption').show();
//                 //         $('#u_selectoption select').attr({
//                 //             'id': 'u_im_reference_document',
//                 //             'name': 'u_im_reference_document'
//                 //         });
//                 //         $('#u_im_reference_document').empty();
//                 //         $('#u_im_reference_document').html("<option selected disabled value=''>Select Previous Inventory Transaction</option>");
                       
//                 //         BrandChangeInventory('#u_im_brand', '#u_im_reference_document', '#update_manageinventory');
//                 //         $('#u_reference_document_section').show();
//                 //         $('#u_from_section, #u_to_section').hide();
//                 //     }
//                 // });
//                 $('#edit-manageinventory').modal('show');
//             },
//             error: function(jqXHR, textStatus, errorThrown) {
//                 $('#ajax-loader').hide();
//                 console.log(textStatus, errorThrown);
//             }
//         });
//     });
//     //Update Inventory Management Modal

//     //Update Inventory Management
//     $('#update_manageinventory').on('submit', function (event) {
//         event.preventDefault();
//         var formData = SerializeForm(this);
//         var inventoryManagementId;
//         for (var i = 0; i < formData.length; i++) {
//             if (formData[i].name === 'u_im-id') {
//                 inventoryManagementId = formData[i].value;
//                 break;
//             }
//         }
//         var url = 'inventory/update-invmanagement/' + inventoryManagementId;
//         $.ajax({
//             url: url,
//             method: 'POST',
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//             },
//             data: formData,
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
//             success: function (response) {
//                 for (var fieldName in response) {
//                     var fieldErrors = response[fieldName];
//                     var fieldName = fieldName;
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
//                             $('#edit-manageinventory').modal('hide');
//                             $('#view-manageinventory').DataTable().ajax.reload(); // Refresh DataTable
//                             $('.text-danger').hide();
//                         }
//                     });
//                 }
//             },
//             error: function (xhr, status, error) {
//                 console.log(xhr.responseText);
//             }
//         });
//     });
//     //Update Inventory Management

// });
//END