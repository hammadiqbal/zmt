
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
        e.preventDefault(); // Prevent the form from submitting normally
        var formdata = $(this).serializeArray();
        var csrfToken = '';
        var roleid = '';
        for (var i = 0; i < formdata.length; i++) {
            if (formdata[i].name === '_token') {
            csrfToken = formdata[i].value;
            break;
            }
        }

        for (var i = 0; i < formdata.length; i++) {
            if (formdata[i].name === 'role_id') {
                roleid = formdata[i].value;
            break;
            }
        }
        var moduleData = [];
        $('.icheck-list').each(function() {
            var moduleName = $(this).find('input[type="hidden"]').attr('name');
            var addValue = $(this).find('input[name="add"]').is(':checked') ? '1' : '2';
            var viewValue = $(this).find('input[name="view"]').is(':checked') ? '1' : '2';
            var editValue = $(this).find('input[name="edit"]').is(':checked') ? '1' : '2';

            var module = {
                name: moduleName,
                add: addValue,
                view: viewValue,
                edit: editValue
            };
            moduleData.push(module);

        });
        var data = {
            modules: moduleData,
            roleid: roleid,
            _token: csrfToken
        };

        if (moduleData.length > 0)
        {
            $.ajax({
                url: "/rights-setup/assignrights",
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
                else if (fieldName == 'info')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
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
        }
    });
    //Rights Setup

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

    //Update Rights
    $('#update-rights').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var formdata = $(this).serializeArray();
        var csrfToken = '';
        var roleid = '';
        for (var i = 0; i < formdata.length; i++) {
            if (formdata[i].name === '_token') {
            csrfToken = formdata[i].value;
            break;
            }
        }

        for (var i = 0; i < formdata.length; i++) {
            if (formdata[i].name === 'roleid') {
                roleid = formdata[i].value;
            break;
            }
        }
        var moduleData = [];
        $('.icheck-list').each(function() {
            var moduleName = $(this).find('input[type="hidden"]').attr('name');
            var addValue = $(this).find('input[name="u_add"]').is(':checked') ? '1' : '2';
            var viewValue = $(this).find('input[name="u_view"]').is(':checked') ? '1' : '2';
            var editValue = $(this).find('input[name="u_edit"]').is(':checked') ? '1' : '2';

            var module = {
                name: moduleName,
                add: addValue,
                view: viewValue,
                edit: editValue
            };
            moduleData.push(module);

        });
        var data = {
            modules: moduleData,
            roleid: roleid,
            _token: csrfToken
        };

        if (moduleData.length > 0)
        {
            $.ajax({
                url: "/update-rights",
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

                if (fieldName == 'success')
                {
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.text-danger').hide();
                            window.location.href = '/update-rights-setup/'+roleid;
                        }
                    });
                }


                },
                error: function(error) {
                    console.log(error)
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
    //Update Rights

});// Role and Rights

// Territory
$(document).ready(function() {
    //Add Province
    $('#add_province').submit(function(e) {
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
                    url: "/territory/addprovince",
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
                                    $('#add-province').modal('hide');
                                    $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
                                    $('#add_province')[0].reset();
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
                                    $('#add_province')[0].reset();
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
    //Add Province

    //Add Division
    $('#add_division').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var selectedValue = $('#p_name').val();
        data.push({ name: 'province', value: selectedValue });
            $(data).each(function(i, field){
                if (field.value == '' || field.value == null)
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
                else{
                    resp = true;
                }
            });
            if(resp != false)
            {
                $.ajax({
                    url: "/territory/adddivision",
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
                                    $('#add-division').modal('hide');
                                    $('#view-division').DataTable().ajax.reload();
                                    $('#add_division').find('select').val($('#add_division').find('select option:first').val()).trigger('change');
                                    $('#add_division')[0].reset();
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
                                    $('#add_division').find('select').val($('#add_division').find('select option:first').val()).trigger('change');
                                    $('#add_division')[0].reset();
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
    //Add Division

    //Add District
    $('#add_district').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#division_name').val();
        data.push({ name: 'province', value: provinceValue });
        data.push({ name: 'division', value: divisionValue });
            $(data).each(function(i, field){
                if (field.value == '' || field.value == null)
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
                else{
                    resp = true;
                }
            });
            if(resp != false)
            {
                $.ajax({
                    url: "/territory/adddistrict",
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
                                    $('#add-district').modal('hide');
                                    $('#view-district').DataTable().ajax.reload(); // Refresh DataTable
                                    $('#add_district').find('select').val($('#add_district').find('select option:first').val()).trigger('change');
                                    $('#add_district')[0].reset();
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
                                    $('#add_district').find('select').val($('#add_district').find('select option:first').val()).trigger('change');
                                    $('#add_district')[0].reset();
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
    //Add District

    // Show Division according to provinceValue
    $('#division_name').prop('disabled', true);
    ProvinceChangeDivision('#province_name', '#division_name', '#add_district');
    // Show Division according to provinceValue

    // VieW Province
    var viewprovince =  $('#view-province').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/territory/province',
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
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 3,
                width: "200px"
            }
        ]
    });

    viewprovince.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewprovince.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewprovince.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Province


    // VieW Division
    var viewdivision =  $('#view-division').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/territory/division',
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
            { data: 'province_name', name: 'province_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 3,
                width: "200px"
            }
        ]
    });

    viewdivision.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewdivision.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewdivision.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Division

     // View District
    var viewdistrict =  $('#view-district').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/territory/district',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'division_name', name: 'division_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            } },
            { data: 'province_name', name: 'province_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            } },
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
                width: "300px"
            }
        ]
    });

    viewdistrict.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewdistrict.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewdistrict.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View District

    // Update Province Status
    $(document).on('click', '.province_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/province/update-status', // Replace with the actual URL for updating the status
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
            }
            },
            error: function(xhr, status, error) {
            // Handle any AJAX errors
            console.log(error);
            }
        });

    });
    // Update Province Status

    // Update Division Status
    $(document).on('click', '.division_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/division/update-status', // Replace with the actual URL for updating the status
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-division').DataTable().ajax.reload(); // Refresh DataTable
            }
            },
            error: function(xhr, status, error) {
            // Handle any AJAX errors
            console.log(error);
            }
        });

    });
    // Update Division Status

    // Update District Status
    $(document).on('click', '.district_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/territory/district/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
            if(status == 200)
            {
                $('#view-district').DataTable().ajax.reload();
            }
            },
            error: function(xhr, status, error) {
            console.log(error);
            }
        });

    });
    // Update District Status

    //Update Province Modal
    $(document).on('click', '.edit-province', function() {
        var provinceId = $(this).data('province-id');
        $('#ajax-loader').show();
        var url = '/territory/province/' + provinceId;
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
                $('.province-id').val(response.id);
                $('.province-name').val(response.name);  // Adjust these based on the actual structure of your response data
                $('#edit-province').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Province Modal

    //Update Division Modal
    $(document).on('click', '.edit-division', function() {
        var divisionId = $(this).data('division-id');
        $('#ajax-loader').show();
        var url = '/territory/division/' + divisionId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                var province_name = response.province_name;
                var provinceId = response.province_id;
                $('.province_name').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                if (provinceId) {
                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                        },
                        success: function(resp) {

                            $.each(resp, function(key, value) {
                                $('.province_name').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            $('#ajax-loader').hide();
                            console.log(error);
                        }
                    });
                }
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.division-id').val(response.id);
                $('.division-name').val(response.name);
                $('#edit-division').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Division Modal

    //Update District Modal
    $(document).on('click', '.edit-district', function() {

        var districtId = $(this).data('district-id');
        var url = '/territory/district/' + districtId;
        $('#ajax-loader').show();
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

                $('.u_province_name').html("<option selected value='"+provinceId+"'>" + province_name + "</option>");
                $('.u_division_name').html("<option selected value='"+divisionId+"'>" + divisionName + "</option>");

                if (provinceId) {
                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: provinceId,
                        },
                        success: function(resp) {

                            $.each(resp, function(key, value) {
                                $('.u_province_name').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            $('.u_division_name').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_division_name').find('option:contains("Loading...")').remove(); // Remove the loading option
                                $('.u_division_name').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    $('.u_province_name').change(function() {
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                                divisionId: divisionId,
                            },
                            success: function(resp) {
                                 $('.u_division_name').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('.u_division_name').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                // $('#division_name').prop('disabled', false);
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });
                }//

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.district-id').val(response.id);
                $('.district-name').val(response.name);
                $('#edit-district').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update District Modal

    //Update Province
    $('#update_province').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var provinceId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'province-id') {
                provinceId = formData[i].value;
                break;
            }
        }
        var url = '/update-province/' + provinceId;
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
                            $('#edit-province').modal('hide');
                            $('#view-province').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_province')[0].reset();
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
    //Update Province

    //Update Division
    $('#update_division').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var divisionId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'division-id') {
                divisionId = formData[i].value;
                break;
            }
        }
        var url = '/update-division/' + divisionId;
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
                            $('#edit-division').modal('hide');
                            $('#view-division').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_division')[0].reset();
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
    //Update Division

    //Update District
    $('#update_district').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var districtId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'district-id') {
                districtId = formData[i].value;
                break;
            }
        }
        var url = '/update-district/' + districtId;
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
                            $('#edit-district').modal('hide');
                            $('#view-district').DataTable().ajax.reload(); // Refresh DataTable
                            $('#district')[0].reset();
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
    //Update District
});// Territory

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

    // Show District according to Division
    $('#district_name').prop('disabled', true);
    DivisionChangeDistrict('#division_name', '#district_name', '#add_organization');


    // $('#division_name').change(function() {
    //     var divisionId = $(this).val();
    //     $('#district_name').html("<option selected disabled value=''>Select District</option>").prop('disabled', true);
    //     if (divisionId) {
    //         $.ajax({
    //             url: 'orgSetup/getdistrict',
    //             type: 'GET',
    //             data: {
    //                 divisionId: divisionId
    //             },
    //             success: function(response) {
    //                 if (response.length > 0) {
    //                     $.each(response, function(key, value) {
    //                         $('#district_name').append('<option value="' + value.id + '">' + value.name + '</option>');
    //                     });
    //                     $('#district_name').prop('disabled', false);
    //                 }
    //                 else{
    //                     Swal.fire({
    //                         text: 'Division are not available for selected Province',
    //                         icon: 'error',
    //                         confirmButtonText: 'OK'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             $('#add_organization')[0].reset();
    //                         }
    //                     });

    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.log(error);
    //             }
    //         });
    //     }
    // });
    // Show District according to Division

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
                console.log(bannerPath);
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

                    $('#u_org_province').change(function() {
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

                    $('#u_org_division').change(function() {
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

//Users
$('#isEmployee').on('switchChange.bootstrapSwitch', function(event, state) {
    if (state) {
        $('#userEmployee').show();
        $('#userDetails').hide();
        $('#useremail_error').hide();
        $('#username_error').hide();
        $('#userEmp option:first');
    } else {
        $('#userDetails').show();
        $('#userEmployee').hide();
        $('#username_error').show();
        $('#nameLabel').show();
        $('input[name="username"]').val('').attr('readonly', false);
        $('#useremail_error').show();
        $('#emailLabel').show();
        $('input[name="useremail"]').val('').attr('readonly', false);
    }
});
$('#userEmp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', true);

$(document).on('change', '#userOrg', function() {
    var orgId = $(this).val();
    fetchOrganizationEmployees(orgId, '#userEmp', function(data) {
        if (data.length > 0) {
            $('#userEmp').empty();
            $('#userEmp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', false);
            $.each(data, function(key, value) {
                $('#userEmp').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        }
        else {
            Swal.fire({
                text: 'Employees are not available for selected Organization',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#add-user').modal('hide');
                }
            });
        }
    }, function(error) {
        console.log(error);
    });
});

$('#userEmp').change(function() {
    var empId = $(this).val();
    fetchEmployeeDetails(empId, '#userEmp', function(data) {
        $.each(data, function(key, value) {
            $('#userDetails').show();
            $('#nameLabel').hide();
            $('input[name="username"]').val(value.name).attr('readonly', true);
            $('#emailLabel').hide();
            $('input[name="useremail"]').val(value.email).attr('readonly', true);
        });

    }, function(error) {
        console.log(error);
    });
});

$(document).ready(function() {
    //Add User
    $('#add_user').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var roleName = $('#roleName').val();
        var orgId = $('#userOrg').val();
        var empid = $('#userEmp').val();
        data.push({ name: 'userRole', value: roleName });
        data.push({ name: 'userOrg', value: orgId });
        data.push({ name: 'userEmp', value: empid });
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                if((FieldName != 'userEmp') && (FieldName != 'username') && (FieldName != 'useremail'))
                {
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

            }

        });

        if(resp != false)
        {
            $.ajax({
                url: "/user/adduser",
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
                        var errorElemId = '#' + fieldName + '_error';
                        console.log(errorElemId);
                        $(errorElemId).text(fieldErrors);
                        $('select[name= "' +fieldName +'"' ).next('.select2-container').find('.select2-selection').addClass('requirefield');
                        (function(currentFieldName, currentErrorElemId) {
                            $('select[name= "' + currentFieldName +'"' ).on('select2:open', function() {
                                $(currentErrorElemId).text("");
                                $(this).next('.select2-container').find('.select2-selection').removeClass("requirefield");
                            });
                        })(fieldName, errorElemId);
                        Swal.close();
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
                                // $('#add-user').modal('hide');
                                // $('#view-user').DataTable().ajax.reload(); // Refresh DataTable
                                // $('#add_user').find('select').val($('#add_user').find('select option:first').val()).trigger('change');
                                // $('#add_user')[0].reset();
                                // $('.text-danger').hide();
                                location.reload();
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
                                location.reload();
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
    //Add User

    // VieW User
    var viewuser =  $('#view-user').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/user/data',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name', render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'email', name: 'email' },
            { data: 'rolename', name: 'rolename',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'empname', name: 'empname',render: function(data, type, row) {
                if (data === null) {
                    return "N/A";
                } else {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 7,
                width: "300px"
            }
        ]
    });

    viewuser.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });

    // Show the loader before an AJAX request is made
    viewuser.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewuser.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View User

    // Update Status
    $(document).on('click', '.user_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/user/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-user').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Status

    //Update User Modal
    $(document).on('click', '.edit-user', function() {
        $('#ajax-loader').show();
        var userId = $(this).data('user-id');
        var url = '/user/editdata/' + userId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var roleId = response.roleId;
                var empId = response.empId;
                var roleName = response.rolename;
                var empName = response.empName;
                var orgId = response.orgId;
                var orgName = response.orgName;
                $('.u_user_role').html("<option selected value='"+roleId+"'>" + roleName + "</option>");
                $('.u_user_org').html("<option selected value='"+orgId+"'>" + orgName + "</option>");

                if (roleId) {
                    $.ajax({
                        url: 'user/updaterole',
                        type: 'GET',
                        data: {
                            roleId: roleId,
                            roleName: roleName,
                        },
                        success: function(resp) {
                            $.each(resp, function(key, value) {
                                $('.u_user_role').append('<option value="' + value.id + '">' + value.role + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    fetchOrganizations(orgId,orgName,'.u_user_org', function(data) {
                        $('.u_user_org').find('option:contains("Loading...")').remove();
                        $.each(data, function(key, value) {
                            $('.u_user_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        });
                    });

                    if(empId === 0)
                    {
                        $('#u_employee').hide();
                        $('.u_user_emp').append('<option selected value="0"></option>');
                        $('.u_user_emp').prop('required', false);
                        $('.user-name').val(response.name).attr('readonly',false);
                        $('.user-email').val(response.email).attr('readonly',false);
                    }
                    else{
                        $('#u_employee').show();
                        $('.u_user_emp').prop('required', true);
                        $('.u_user_emp').html("<option selected value='"+empId+"'>" + empName + "</option>").prop('disabled',true);
                        $('.user-name').val(response.name).attr('readonly',true);
                        $('.user-email').val(response.email).attr('readonly',true);
                        // fetchEmployees(empId, '.u_user_emp', function(data) {
                        //     $.each(data, function(key, value) {
                        //         if(value.id != empId)
                        //         {
                        //             $('.u_user_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        //         }
                        //     });

                        // }, function(error) {
                        //     console.log(error);
                        // });
                    }
                }

                $(document).on('change', '.u_user_org', function() {
                    var orgId = $(this).val();
                    fetchOrganizationEmployees(orgId, '.u_user_emp', function(data) {
                        if (data.length > 0) {
                            $('.u_user_emp').empty();
                            $('.u_user_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled', false);
                            $.each(data, function(key, value) {
                                $('.u_user_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Employees are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#update_user').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });
                });

                $('.u_user_emp').change(function() {
                    var empId = $(this).val();
                    fetchEmployeeDetails(empId, '.u_user_emp', function(data) {
                        $.each(data, function(key, value) {
                            $('input[name="user_name"]').val(value.name).attr('readonly', true);
                            $('input[name="user_email"]').val(value.email).attr('readonly', true);
                        });

                    }, function(error) {
                        console.log(error);
                    });
                });

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.user-id').val(userId);

                $('#edit-user').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update User Modal

    //Update User
    $('#update_user').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var userId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'user-id') {
                userId = formData[i].value;
                break;
            }
        }
        var url = '/update-user/' + userId;
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
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#edit-user').modal('hide');
                        }
                    });
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
                            $('#edit-user').modal('hide');
                            $('#view-user').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_user')[0].reset();
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
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update User

    // Update Image
    $('#u_img').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        console.log('ok');
        var formData = new FormData($('#u_img')[0]);
        var userId = $('#user-id').val();
        var userImg = $('#userImg')[0].files[0];
        formData.append('userImg', userImg);

        var url = '/userImg/' + userId;
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
                            $('.text-danger').hide();
                            window.location.href = 'profile';
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });

        return;




    });
    // Update Image

});
//Users

// Cost Center
$(document).ready(function() {
    //Add CCType
    $('#add_ccType').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var orderingCC = $('#orderingCC').val();
        var performingCC = $('#performingCC').val();
        data.push({ name: 'ordering_cc', value: orderingCC });
        data.push({ name: 'performing_cc', value: performingCC });
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
                url: "/costcenter/addCCType",
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
                        $('.text-danger').hide();
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
                                $('#add-ccType').modal('hide');
                                $('#view-ccType').DataTable().ajax.reload();
                                $('#add_ccType').find('select').val($('#add_ccType').find('select option:first').val()).trigger('change');

                                $('#add_ccType')[0].reset();
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
                                $('#add_ccType').find('select').val($('#add_ccType').find('select option:first').val()).trigger('change');
                                $('#add_ccType')[0].reset();
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
    //Add CCType

    // VieW CCType
    var viewccType =  $('#view-ccType').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/costcenter/cctype',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'remarks', name: 'remarks' },
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

    viewccType.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewccType.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewccType.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View CCType

    // Update CCType Status
    $(document).on('click', '.ccType_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/costcentertype/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-ccType').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update CCType Status

     //Update CCType Modal
    $(document).on('click', '.edit-cctype', function() {
        var ccTypeId = $(this).data('cctype-id');
        var url = '/costcenter/updateCCtype/' + ccTypeId;
        $('#ajax-loader').show();
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
                $('.ccType-id').val(response.id);
                $('.cctype-name').val(response.type);
                $('.cc-remarks').val(response.remarks);
                $('.u_ordering').html("<option selected value='"+response.orderingid+"'>" + response.ordering + "</option>");
                $('.u_performing').html("<option selected value='"+response.performingid+"'>" + response.performing + "</option>");
                var orderingnext = response.orderingid == '1' ? 'Disabled' : 'Enabled';
                var orderingnextid = response.orderingid == '1' ? 0 : 1;
                var performingnext = response.performingid == '1' ? 'Disabled' : 'Enabled';
                var performingnextid = response.performingid == '1' ? 0 : 1;
                $('.u_ordering').append('<option value="' + orderingnextid + '">' + orderingnext + '</option>');
                $('.u_performing').append('<option value="' + performingnextid + '">' + performingnext + '</option>');
                $('#edit-cctype').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update CCType Modal

    //Update CCType
    $('#update_ccType').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ccTypeId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ccType-id') {
                ccTypeId = formData[i].value;
                break;
            }
        }
        var url = '/update-cctype/' + ccTypeId;
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
                            // console.log('result conformed');
                            $('#edit-cctype').modal('hide');
                            $('#view-ccType').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_ccType')[0].reset();
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
    //Update CCType

    //Add CostCenter
    $('#add_costcenter').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var cc_type = $('#cc_type').val();
        data.push({ name: 'cc_type', value: cc_type });
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
                url: "/costcenter/addcostcenter",
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
                                $('#add-costcenter').modal('hide');
                                $('#costcenter').DataTable().ajax.reload();
                                $('#add_costcenter').find('select').val($('#add_costcenter').find('select option:first').val()).trigger('change');

                                $('#add_costcenter')[0].reset();
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
                                $('#add_costcenter').find('select').val($('#add_costcenter').find('select option:first').val()).trigger('change');
                                $('#add_costcenter')[0].reset();
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
    //Add CostCenter

    // VieW CostCenter
     var viewcostcenter =  $('#costcenter').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/costcenter/ccdata',
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
            { data: 'type', name: 'type' },
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
                width: "300px"
            }
        ]
    });

    viewcostcenter.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewcostcenter.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewcostcenter.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View CostCenter

    // Update CostCenter Status
    $(document).on('click', '.cc_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/costcenter/update-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#costcenter').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update CostCenter Status

    //Update CostCenter Modal
    $(document).on('click', '.edit-costcenter', function() {
        var CostCenterId = $(this).data('costcenter-id');
        var url = '/costcenter/updatecostcenter/' + CostCenterId;
        $('#ajax-loader').show();
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
                console.log(response.ccType);
                $('.cc-id').val(response.id);
                $('.cc_name').val(response.name);
                $('.u_cc_type').html("<option selected value='"+response.typeid+"'>" + response.ccType + "</option>");
                $.ajax({
                    url: 'costcenter/getcctype',
                    type: 'GET',
                    data: {
                        ccTypeId: response.typeid,
                        ccType: response.ccType,
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_cc_type').append('<option value="' + value.id + '">' + value.type + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#edit-costcenter').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update CostCenter Modal

    //Update CostCenter
    $('#update_cc').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ccId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'cc-id') {
                ccId = formData[i].value;
                break;
            }
        }
        var url = '/update-costcenter/' + ccId;
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
                            $('#edit-costcenter').modal('hide');
                            $('#costcenter').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_cc')[0].reset();
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
    //Update CostCenter
});
// Cost Center

//Services Setup
$(document).ready(function() {
    //Add Service Mode
    $('#add_servicemode').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/services/addservicemode",
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
                                $('#add-servicemode').modal('hide');
                                $('#view-servicemode').DataTable().ajax.reload();
                                $('#add_servicemode')[0].reset();
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
                                $('#add_servicemode')[0].reset();
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
    //Add Service Mode

    // View Service Mode Data
    var viewserviceMode =  $('#view-servicemode').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/servicemode',
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

    viewserviceMode.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewserviceMode.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewserviceMode.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Mode Data

    // Update Service Mode Status
    $(document).on('click', '.servicemode_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/services/sm-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicemode').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Service Mode Status

    //Update Service Mode Modal
    $(document).on('click', '.edit-servicemode', function() {
        var ServiceModeId = $(this).data('servicemode-id');
        var url = '/services/updateservicemode/' + ServiceModeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.sm-id').val(response.id);
                $('.u_sm').val(response.name);
                $('#edit-servicemode').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Service Mode Modal

    //Update Service Mode
    $('#u_servicemode').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var smId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'sm-id') {
                smId = formData[i].value;
                break;
            }
        }
        var url = 'services/update-servicemode/' + smId;
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
                            $('#edit-servicemode').modal('hide');
                            $('#view-servicemode').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_servicemode')[0].reset();
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
    //Update Service Mode


    //Add Service Type
    $('#add_servicetype').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/services/addservicetype",
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
                                $('#add-servicetype').modal('hide');
                                $('#view-servicetype').DataTable().ajax.reload();
                                $('#add_servicetype')[0].reset();
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
                                $('#add_servicetype')[0].reset();
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
    //Add Service Type

    // View Service Type Data
    var viewserviceType =  $('#view-servicetype').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/servicetype',
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

    viewserviceType.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewserviceType.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewserviceType.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Type Data

    // Update Service Type Status
    $(document).on('click', '.servicetype_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/services/st-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicetype').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Service Type Status

    //Update Service Mode Modal
    $(document).on('click', '.edit-servicetype', function() {
        var ServiceModeId = $(this).data('servicetype-id');
        var url = '/services/updateservicetype/' + ServiceModeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.st-id').val(response.id);
                $('.u_st').val(response.name);
                $('#edit-servicetype').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Service Mode Modal

    //Update Service Mode
    $('#u_servicetype').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var stId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'st-id') {
                stId = formData[i].value;
                break;
            }
        }
        var url = 'services/update-servicetype/' + stId;
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
                            $('#edit-servicetype').modal('hide');
                            $('#view-servicetype').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_servicetype')[0].reset();
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
    //Update Service Mode

    //Add Service Group
    $('#add_servicegroup').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var sg_type = $('#sg_type').val();
        data.push({ name: 'sg_type', value: sg_type });
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
                // $( 'select[name= "' +FieldName +'"' ).addClass('requirefield');
                // $( 'select[name= "' +FieldName +'"' ).focus(function() {
                //     $(FieldID).text("");
                //     $('select[name= "' +FieldName +'"' ).removeClass("requirefield");
                // });
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/services/addservicegroup",
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
                                $('#add-servicegroup').modal('hide');
                                $('#view-servicegroup').DataTable().ajax.reload();
                                $('#add_servicegroup').find('select').val($('#add_servicegroup').find('select option:first').val()).trigger('change');
                                $('#add_servicegroup')[0].reset();
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
                                $('#add_servicegroup').find('select').val($('#add_servicegroup').find('select option:first').val()).trigger('change');
                                $('#add_servicegroup')[0].reset();
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
    //Add Service Group

    // View Service Group Data
    var viewserviceType =  $('#view-servicegroup').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/servicegroup',
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
            { data: 'type_name', name: 'type_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    viewserviceType.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewserviceType.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewserviceType.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Group Data

    // Update Service Group Status
    $(document).on('click', '.servicegroup_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/sg-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicegroup').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Service Group Status

    //Update Service Group Modal
    $(document).on('click', '.edit-servicegroup', function() {
        var ServiceGroupId = $(this).data('servicegroup-id');
        var url = '/services/updateservicegroup/' + ServiceGroupId;
        $('#ajax-loader').show();
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
                $('.sg-id').val(response.id);
                $('.u_sg').val(response.name);
                $('.u_sg_type').html("<option selected value='"+response.typeid+"'>" + response.serviceType + "</option>");
                $.ajax({
                    url: 'services/getservicetype',
                    type: 'GET',
                    data: {
                        serviceTypeId: response.typeid,
                        serviceType: response.serviceType,
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_sg_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#edit-servicegroup').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Service Group Modal

    //Update Service Group
    $('#u_servicegroup').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var sgId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'sg-id') {
                sgId = formData[i].value;
                break;
            }
        }
        var url = 'services/update-servicegroup/' + sgId;
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
                            $('#edit-servicegroup').modal('hide');
                            $('#view-servicegroup').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_servicegroup')[0].reset();
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
    //Update Service Group

    //Add Services
    $('#add_services').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var s_group = $('#s_group').val();
        var s_charge = $('#s_charge').val();
        data.push({ name: 's_group', value: s_group });
        data.push({ name: 's_charge', value: s_charge });
        var resp = true;
        $(data).each(function(i, field){
            var FieldName = field.name;
            var fieldValue = field.value;
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined') && (FieldName != 's_icd_code'))
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
                url: "/services/addservices",
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
                                $('#add-services').modal('hide');
                                $('#view-services').DataTable().ajax.reload();
                                $('#add_services').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_services')[0].reset();
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
                                $('#add_services').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_services')[0].reset();
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
    //Add Services

    // View Services Data
    var viewservices =  $('#view-services').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/getservices',
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
            { data: 'group_name', name: 'group_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'type_name', name: 'type_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'unit', name: 'unit',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    viewservices.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewservices.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewservices.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Services Data

    // Update Services Status
    $(document).on('click', '.services_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/service-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-services').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // Update Services Status

    //Update Services Modal
    $(document).on('click', '.edit-services', function() {
        var ServiceId = $(this).data('services-id');
        var url = '/services/updateservices/' + ServiceId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                $('#ajax-loader').hide();
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.s-id').val(response.id);
                $('.u_service').val(response.name);
                $('.u_s_icd').val(response.icd);
                $('.u_s_unit').val(response.unit);
                $('.u_s_group').html("<option selected value='"+response.group_id+"'>" + response.group_name + "</option>");

                if(response.charge == 1)
                {
                    $('.u_s_charge').html("<option selected value='1'>Yes</option>");
                    $('.u_s_charge').append('<option value="0">no</option>');
                }
                else {
                    $('.u_s_charge').html("<option selected value='0'>No</option>");
                    $('.u_s_charge').append('<option value="1">Yes</option>');
                }
                $.ajax({
                    url: 'services/getservicegroup',
                    type: 'GET',
                    data: {
                        serviceGroupId: response.group_id,
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_s_group').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#edit-services').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Services Modal

    //Update Services
    $('#u_services').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var sgId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 's-id') {
                sgId = formData[i].value;
                break;
            }
        }
        var url = 'services/update-services/' + sgId;
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
                            $('#edit-services').modal('hide');
                            $('#view-services').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_services')[0].reset();
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
    //Update Services

//Services Setup
});

// Key Performance Indicator
$(document).ready(function() {
    //Add KPI Group
    $('#add_kpigroup').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/kpi/addkpigroup",
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
                                $('#add-kpigroup').modal('hide');
                                $('#view-kpigroup').DataTable().ajax.reload();
                                $('#add_kpigroup')[0].reset();
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
                                $('#add_kpigroup')[0].reset();
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
    //Add KPI Group

    // View KPI Group Data
    var viewkpiMode =  $('#view-kpigroup').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kpi/kpigroup',
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

    viewkpiMode.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewkpiMode.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewkpiMode.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View KPI Group Data

    // Update KPI Group Status
    $(document).on('click', '.kpigroup_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/kpi/kg-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-kpigroup').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update KPI Group Status

    //Update KPI Group Modal
    $(document).on('click', '.edit-kpigroup', function() {
        var KPIGroupId = $(this).data('kpigroup-id');
        var url = '/kpi/updatekpigroup/' + KPIGroupId;
        $('#ajax-loader').show();
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
                $('.kg-id').val(response.id);
                $('.u_kg').val(response.name);
                $('#edit-kpigroup').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update KPI Group Modal

    //Update KPI Group
    $('#u_kpigroup').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var kgId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'kg-id') {
                kgId = formData[i].value;
                break;
            }
        }
        var url = 'kpi/update-kpigroup/' + kgId;
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
                            $('#edit-kpigroup').modal('hide');
                            $('#view-kpigroup').DataTable().ajax.reload();
                            $('#u_kpigroup')[0].reset();
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
    //Update KPI Group


    //Add KPI Dimension
    $('#add_kpidimension').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/kpi/addkpidimension",
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
                                $('#add-kpidimension').modal('hide');
                                $('#view-kpidimension').DataTable().ajax.reload();
                                $('#add_kpidimension')[0].reset();
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
                                $('#add_kpidimension')[0].reset();
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
    //Add KPI Dimension

    // View KPI Dimension Data
    var viewkpiMode =  $('#view-kpidimension').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kpi/kpidimension',
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

    viewkpiMode.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewkpiMode.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewkpiMode.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View KPI Dimension Data

    // Update KPI Dimension Status
    $(document).on('click', '.kpidimension_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/kpi/kd-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-kpidimension').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update KPI Dimension Status

    //Update KPI Dimension Modal
    $(document).on('click', '.edit-kpidimension', function() {
        var KPIDimensionId = $(this).data('kpidimension-id');
        var url = '/kpi/updatekpidimension/' + KPIDimensionId;
        $('#ajax-loader').show();
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
                $('.kd-id').val(response.id);
                $('.u_kd').val(response.name);
                $('#edit-kpidimension').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update KPI Dimension Modal

    //Update KPI Dimension
    $('#u_kpidimension').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var kdId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'kd-id') {
                kdId = formData[i].value;
                break;
            }
        }
        var url = 'kpi/update-kpidimension/' + kdId;
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
                            $('#edit-kpidimension').modal('hide');
                            $('#view-kpidimension').DataTable().ajax.reload();
                            $('#u_kpidimension')[0].reset();
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
    //Update KPI Dimension

    //Add KPI Type
    $('#add_kpitype').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var KTGroup = $('#kt_group').val();
        var KTDimesnion = $('#kt_dimension').val();
        data.push({ name: 'kt_group', value: KTGroup });
        data.push({ name: 'kt_dimension', value: KTDimesnion });
        var resp = true;
        $(data).each(function(i, field){
            if (field.value == '' || field.value == null)
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
                url: "/kpi/addkpitype",
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
                                $('#add-kpitype').modal('hide');
                                $('#view-kpitype').DataTable().ajax.reload();
                                $('#add_kpitype').find('select').val($('#add_kpitype').find('select option:first').val()).trigger('change');
                                $('#add_kpitype')[0].reset();
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
                                $('#add_kpitype').find('select').val($('#add_kpitype').find('select option:first').val()).trigger('change');
                                $('#add_kpitype')[0].reset();
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
    //Add KPI Type

    // View KPI Type Data
    var viewkpiMode =  $('#view-kpitype').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kpi/kpitype',
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
            { data: 'group_name', name: 'group_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'dimension_name', name: 'dimension_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    viewkpiMode.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewkpiMode.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewkpiMode.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View KPI Type Data

    // Update KPI Type Status
    $(document).on('click', '.kpitype_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/kpi/kt-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-kpitype').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update KPI Type Status

    //Update KPI Types Modal
    $(document).on('click', '.edit-kpitype', function() {
        var KPITypeId = $(this).data('kpitype-id');
        var url = '/kpi/updatekpitype/' + KPITypeId;
        $('#ajax-loader').show();
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
                $('.kt-id').val(response.id);
                $('.u_kt').val(response.name);
                $('.u_kt_group').html("<option selected value='"+response.group_id+"'>" + response.group + "</option>");
                $('.u_kt_dimension').html("<option selected value='"+response.dimension_id+"'>" + response.dimension + "</option>");

                $.ajax({
                    url: 'kpi/getkpigroup',
                    type: 'GET',
                    data: {
                        groupId: response.group_id,
                        group: response.group,
                    },
                    beforeSend: function() {
                        $('.u_kt_group').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_kt_group').find('option:contains("Loading...")').remove();
                            $('.u_kt_group').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $.ajax({
                    url: 'kpi/getkpidimension',
                    type: 'GET',
                    data: {
                        dimensionId: response.dimension_id,
                        dimension: response.dimension,
                    },
                    beforeSend: function() {
                        $('.u_kt_dimension').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_kt_dimension').find('option:contains("Loading...")').remove();
                            $('.u_kt_dimension').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
                $('#edit-kpitype').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update KPI Types Modal

    //Update KPI Types
    $('#u_kpitype').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ktId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'kt-id') {
                ktId = formData[i].value;
                break;
            }
        }

        var url = 'kpi/update-kpitype/' + ktId;
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
                            $('#edit-kpitype').modal('hide');
                            $('#view-kpitype').DataTable().ajax.reload();
                            $('#u_kpitype')[0].reset();
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
    //Update KPI Types

    //Add KPI
    $('#add_kpi').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var KPIType = $('#kpi_type').val();
        data.push({ name: 'kpi_type', value: KPIType });
        var resp = true;
        $(data).each(function(i, field){
            if (field.value == '' || field.value == null)
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
                url: "/kpi/addkpi",
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
                                $('#add-kpi').modal('hide');
                                $('#view-kpi').DataTable().ajax.reload();
                                $('#add_kpi').find('select').val($('#add_kpi').find('select option:first').val()).trigger('change');
                                $('#add_kpi')[0].reset();
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
                                $('#add_kpi').find('select').val($('#add_kpi').find('select option:first').val()).trigger('change');
                                $('#add_kpi')[0].reset();
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
    //Add KPI

    // View KPI Data
    var viewkpi =  $('#view-kpi').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kpi/kpi',
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
            { data: 'type_name', name: 'type_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'group_name', name: 'group_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'dimension_name', name: 'dimension_name',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],

        columnDefs: [
            {
                targets: 1,
                width: "150px"
            },
            {
                targets: 7,
                width: "250px"
            }
        ]
    });

    viewkpi.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewkpi.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewkpi.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View KPI Data

    // Update KPI Status
    $(document).on('click', '.kpi_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/kpi/kpi-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-kpi').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update KPI Status

    //Update KPI Modal
    $(document).on('click', '.edit-kpi', function() {
        var KPITypeId = $(this).data('kpi-id');
        var url = '/kpi/updatekpi/' + KPITypeId;
        $('#ajax-loader').show();
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
                $('.kpi-id').val(response.id);
                $('.u_kpi').val(response.name);
                $('.u_kpi_type').html("<option selected value='"+response.type_id+"'>" + response.type_name + "</option>");

                $.ajax({
                    url: 'kpi/getkpitype',
                    type: 'GET',
                    data: {
                        typeId: response.type_id,
                    },
                    beforeSend: function() {
                        $('.u_kpi_type').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $.each(resp, function(key, value) {
                            $('.u_kpi_type').find('option:contains("Loading...")').remove();
                            $('.u_kpi_type').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });


                $('#edit-kpi').modal('show');

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update KPI Modal

    //Update KPI
    $('#u_kpi').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var kpiId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'kpi-id') {
                kpiId = formData[i].value;
                break;
            }
        }

        var url = 'kpi/update-kpi/' + kpiId;
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
                            $('#edit-kpi').modal('hide');
                            $('#view-kpi').DataTable().ajax.reload();
                            $('#u_kpi')[0].reset();
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
                            $('#u_kpi').find('select').val($('#u_kpi').find('select option:first').val()).trigger('change');
                            $('#u_kpi')[0].reset();
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
    //Update KPI
});
// Key Performance Indicator

// SiteSetup
$(document).ready(function() {
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
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined') && (fieldName != 'site_gps') && (fieldName != 'old_siteCode'))
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
            { data: 'org_name', name: 'org_name' ,render: function(data, type, row) {
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
                targets: 6,
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
                var contact = response.cell_no + '/' + response.landline_no;
                var logoPath = response.logo;

                $('#sitelogo').attr('src', logoPath);
                $('#sitename').text(response.name);
                $('#siteorg').text(response.orgName);
                $('#oldSiteCode').text(response.oldCode);
                $('#siteemail').text(response.email);
                $('#sitecontact').html(contact);
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

                    $('#u_site_province').change(function() {
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

                    $('#u_site_division').change(function() {
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


// Activations
$(document).ready(function() {
    $('#cc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#site_sa').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#ss_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#emp_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#act_s_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    $('#act_kpi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#act_kpi_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
    $('#emp_cc').html("<option selected disabled value=''>Select Head Count Cost Center</option>").prop('disabled', true);
    $('#sl_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#ss_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);

    //Show Sites
    OrgChangeSites('#cc_org', '#cc_site', '#activate_cc');
    OrgChangeSites('#sl_org', '#sl_site', '#add_servicelocation');
    OrgChangeSites('#ss_org', '#ss_site', '#add_servicelocationscheduling');
    OrgChangeSites('#sb_org', '#sb_site', '#add_servicebooking');
    OrgChangeSites('#org_sa', '#site_sa', '#emp_serviceallocation');

    $(document).on('change', '.u_empcc_org', function() {
        var orgId = $(this).val();
        var currentRowSiteSelect = $(this).closest('.sl-item').find('.u_empcc_site');
        if (orgId) {
            fetchOrganizationSites(orgId,'.u_empcc_site', function(data) {
                if (data.length > 0) {
                    currentRowSiteSelect.empty();
                    currentRowSiteSelect.append('<option selected disabled value="">Select Site</option>');
                    $.each(data, function(key, value) {
                        currentRowSiteSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowSiteSelect.find('option:contains("Loading...")').remove();
                    currentRowSiteSelect.prop('disabled', false);
                }
                else {
                    Swal.fire({
                        text: 'Sites are not available for selected Organization',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentRowSiteSelect.empty();
                        }
                    });
                }
            });
        }
    });

    $(document).on('change', '.empcc_org', function() {
        var orgId = $(this).val();
        var currentRowSiteSelect = $(this).closest('.duplicate').find('.empcc_site');
        if (orgId) {
            fetchOrganizationSites(orgId,'.empcc_site', function(data) {
                if (data.length > 0) {

                    currentRowSiteSelect.empty();
                    currentRowSiteSelect.append('<option selected disabled value="">Select Site</option>');
                    $.each(data, function(key, value) {
                        currentRowSiteSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    currentRowSiteSelect.find('option:contains("Loading...")').remove();
                    currentRowSiteSelect.prop('disabled', false);
                }
                else {
                    Swal.fire({
                        text: 'Sites are not available for selected Organization',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#add_empCC')[0].reset();
                        }
                    });
                }
            });
        }
    });

    OrgChangeSites('#act_s_org', '#act_s_site', '#activate_service');
    OrgChangeSites('#act_kpi_org', '#act_kpi_site', '#activate_kpi');
    OrgChangeSites('#emp_org', '#emp_site', '#add_employee');
    //Show Sites

    // Show Service Location
    SiteChangeServiceLocation('#ss_site', '#ss_location', '#add_locationscheduling');
    SiteChangeServiceLocation('#sb_site', '#sb_location', '#add_servicebooking');
    // Show Service Location

    //Show Activated CostCenter
    SiteChangeCostCenter('#act_s_site', '#act_s_cc', '#activate_service');
    SiteChangeCostCenter('#act_kpi_site', '#act_kpi_cc', '#activate_kpi');
    SiteChangeCostCenter('#emp_site', '#emp_cc', '#add_employee');

    $(document).on('change', '.u_empcc_site', function() {
        var siteId = $(this).val();
        var currentRowCCSelect = $(this).closest('.sl-item').find('.u_empcc');
        if (siteId) {
            fetchActivatedCostCenters(siteId, '.u_empcc', function(data) {
                    if (data.length > 0) {
                        currentRowCCSelect.empty();
                        currentRowCCSelect.append('<option selected disabled value="">Select Cost Center</option>');
                        $.each(data, function(key, value) {
                            currentRowCCSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        currentRowCCSelect.find('option:contains("Loading...")').remove();
                        currentRowCCSelect.prop('disabled', false);
                    }
                    else {
                        Swal.fire({
                            text: 'Cost Centers are not Activated for selected Site',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                currentRowCCSelect.empty();
                                currentRowSiteSelect.val($('.u_empcc_site').find('option:first').val()).trigger('change');
                                currentRowCCSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);

                            }
                        });
                    }

            }, function(error) {
                console.log(error);
            });
        }
    });

    $(document).on('change', '.empcc_site', function() {
        var siteId = $(this).val();
        // var currentRowCCSelect = $(this).closest('.duplicate').find('.emp_costcenter');
        // var currentRowSiteSelect = $(this).closest('.duplicate').find('.empcc_site');
        var ccSelect = $('.emp_costcenter');
        if (siteId) {
            fetchActivatedCostCenters(siteId, '.emp_costcenter', function(data) {
                    if (data.length > 0) {
                        // currentRowCCSelect.empty();
                        // currentRowCCSelect.append('<option selected disabled value="">Select Cost Center</option>');
                        // $.each(data, function(key, value) {
                        //     currentRowCCSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                        // });
                        // currentRowCCSelect.find('option:contains("Loading...")').remove();
                        // currentRowCCSelect.prop('disabled', false);
                        ccSelect.empty();
                        ccSelect.append('<option selected disabled value="">Select Cost Center</option>');
                        $.each(data, function(key, value) {
                            ccSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        ccSelect.find('option:contains("Loading...")').remove();
                        ccSelect.prop('disabled', false);
                        }
                    else {
                        // Swal.fire({
                        //     text: 'Cost Centers are not Activated for selected Site',
                        //     icon: 'error',
                        //     confirmButtonText: 'OK',
                        //     allowOutsideClick: false
                        // }).then((result) => {
                        //     if (result.isConfirmed) {
                        //         currentRowCCSelect.empty();
                        //         currentRowSiteSelect.val($('.empcc_site').find('option:first').val()).trigger('change');
                        //         currentRowCCSelect.html("<option selected disabled value="">Select Cost Center</option>").prop('disabled', true);

                        //     }
                        // });

                        Swal.fire({
                            text: 'Cost Centers are not Activated for selected Site',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                ccSelect.empty();
                                ccSelect.html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
                            }
                        });
                    }

            }, function(error) {
                console.log(error);
            });
        }
    });
    //Show Activated CostCenter


    //Activate Cost Center
    $('#activate_cc').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var ccOrg = $('#cc_org').val();
        var ccSite = $('#cc_site').val();
        var ccName = $('#cc_name').val();
        var ccText = $('#cc_name').find('option[value="' + ccName + '"]').text();

        data.push({ name: 'cc_org', value: ccOrg });
        data.push({ name: 'cc_site', value: ccSite });
        data.push({ name: 'cc_name', value: ccName });
        data.push({ name: 'ccText', value: ccText });
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
                url: "/costcenter/activatecc",
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
                                $('#cc_activation').modal('hide');
                                $('#view-ccactivation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#activate_cc').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_cc')[0].reset();
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
                                $('#activate_cc').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_cc')[0].reset();
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
    //Activate Cost Center

    // View ActivatedCCData
    var ActivatedCCData =  $('#view-ccactivation').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/costcenter/getactivateccdata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'orgName', name: 'orgName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'ccName', name: 'ccName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    ActivatedCCData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedCCData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedCCData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View ActivatedCCData

    // Update ActivateCC Status
    $(document).on('click', '.activatecc', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/costcenter/update-activatecc',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-ccactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update ActivateCC Status

    // Update ActivatedCC Modal
    $(document).on('click', '.edit-activatecc', function() {
        var activateccId = $(this).data('activatecc-id');
        $('#u_ccsite').empty();
        $('#u_costcenter').empty();
        $('#u_ccorg').empty();
        $('#update_ccactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/costcenter/updateactivatecc/' + activateccId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var ccName = response.ccName;
                var ccID = response.ccID;
                $('#u_ccorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_ccsite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_costcenter').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                $('.u_acc_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_ccorg', function(data) {
                    $('#u_ccorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_ccorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                if (orgID) {
                    $.ajax({
                        url: 'costcenter/getselectedcc',
                        type: 'GET',
                        data: {
                            ccID: ccID,
                        },
                        beforeSend: function() {
                            $('#u_costcenter').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_costcenter').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_costcenter').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });


                    fetchSites(orgID, '#u_ccsite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_ccsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-ccactivation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    },siteId);


                    $('#u_ccorg').off('change').on('change', function() {
                        $('#u_ccsite').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_ccsite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_ccsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-ccactivation').modal('hide');
                                    }
                                });
                            }

                        }, function(error) {
                            console.log(error);
                        });


                    });
                }

                $('#edit-ccactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update ActivateCC Modal

    //Update ActivateCC
    $('#update_ccactivation').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#update_ccactivation')[0]);
        var Org = $('#u_ccorg').val();
        var Site = $('#u_ccsite').val();
        var CostCenter = $('#u_costcenter').val();
        var Id = $('.u_acc_id').val();

        formData.append('u_ccorg', Org);
        formData.append('u_ccsite', Site);
        formData.append('u_costcenter', CostCenter);

        var url = '/update-activatecc/' + Id;
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
                            $('#edit-ccactivation').modal('hide');
                            $('#view-ccactivation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_ccactivation')[0].reset();
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
    //Update ActivateCC


    //Activate Services
    $('#activate_service').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        data = data.filter(function(item) {
            return item.name !== 'act_s_mode[]';
        });
        var OrgId = $('#act_s_org').val();
        var SiteId = $('#act_s_site').val();
        var ServiceId = $('#act_s_service').val();
        var CCID = $('#act_s_cc').val();
        var ServiceModeIds = $('#act_s_mode').val();
        data.push({ name: 'act_s_org', value: OrgId });
        data.push({ name: 'act_s_site', value: SiteId });
        data.push({ name: 'act_s_service', value: ServiceId });
        data.push({ name: 'act_s_cc', value: CCID });
        if(ServiceModeIds == null || ServiceModeIds.length === 0){
            data.push({ name: 'act_s_mode[]', value: null });
        }
        else {
            $.each(ServiceModeIds, function(i, val){
                data.push({ name: 'act_s_mode[]', value: val });
            });
        }
        var resp = true;
        $(data).each(function(i, field){
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                var FieldName = field.name.replace('[]', '');
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

                if(FieldName == 'act_s_mode')
                {
                    FieldName = 'act_s_mode'+'[]';
                    var $select = $('select[name="' + FieldName + '"]');
                    $select.parent().find('button[data-id="act_s_mode"]').addClass('requirefield');
                    $select.on('changed.bs.select', function () {
                        $(FieldID).text("");
                        $(this).parent().find('button[data-id="act_s_mode"]').removeClass('requirefield');
                    });
                }

                resp = false;
            }

        });
        if(resp != false)
        {
            $.ajax({
                url: "/services/activateservice",
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
                                $('#service_activation').modal('hide');
                                $('#view-serviceactivation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#activate_service').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#act_s_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);

                                $('#activate_service')[0].reset();
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
                                $('#act_s_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#activate_service').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_service')[0].reset();
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
    //Activate Services

    // View Activated ServicesData
    var ActivatedServiceData =  $('#view-serviceactivation').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/getactivateservicedata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'serviceName', name: 'serviceName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'ccName', name: 'ccName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'rate', name: 'rate', orderable: false, searchable: false },
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

    ActivatedServiceData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedServiceData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedServiceData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Activated ServicesData

    // Update Activated Services Status
    $(document).on('click', '.activateservice', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/update-activateservice',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-serviceactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Activated Services Status

    // Update Activated Services Modal
    $(document).on('click', '.edit-activateservice', function() {
        var activateserviceId = $(this).data('activateservice-id');
        $('#u_sorg').empty();
        $('#u_ssite').empty();
        $('#u_service').empty();
        $('#u_scc').empty();
        $('#update_serviceactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/updateactivateservice/' + activateserviceId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var ccName = response.ccName;
                var ccID = response.ccID;
                var serviceName = response.serviceName;
                var serviceID = response.serviceID;
                var servicemodeIDs = response.servicemodeIDs;
                var servicemodeIDs = servicemodeIDs.split(',');
                for(var i = 0; i < servicemodeIDs.length; i++) {
                    $('#u_ssm option[value="' + servicemodeIDs[i] + '"]').prop('selected', true);
                }
                $('#u_ssm').selectpicker('refresh');

                $('#u_sorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_ssite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_scc').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                $('#u_service').html("<option selected value='"+serviceID+"'>" + serviceName + "</option>");
                $('.u_service_id').val(response.id);
                $('.u_srate').val(response.rate);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                $.ajax({
                    url: 'services/getselectedservices',
                    type: 'GET',
                    data: {
                        serviceID: serviceID,
                    },
                    beforeSend: function() {
                        $('#u_service').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_service').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('#u_service').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                fetchOrganizations(orgID,orgName,'#u_sorg', function(data) {
                    $('#u_sorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_sorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });


                if (orgID) {
                    fetchSites(orgID, '#u_ssite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_ssite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-serviceactivation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    },siteId);

                    $('#u_sorg').off('change').on('change', function() {
                        $('#u_ssite').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_ssite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_ssite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-serviceactivation').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });

                    fetchActivatedCostCenters(siteId, '#u_scc', function(data) {
                        $('#u_scc').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(data, function(key, value) {
                            $('#u_scc').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }, function(error) {
                        console.log(error);
                    },ccID);

                    $('#u_ssite').off('change').on('change', function() {
                        $('#u_scc').empty();
                        var siteId = $(this).val();
                        fetchActivatedCostCenters(siteId, '#u_scc', function(data) {
                            if (data.length > 0) {
                                $('#u_scc').find('option:contains("Loading...")').remove(); // Remove the loading option
                                $.each(data, function(key, value) {
                                    $('#u_scc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else{
                                Swal.fire({
                                    text: 'Cost Centers are not Activated for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-serviceactivation').modal('hide');
                                    }
                                });

                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });
                }

                $('#edit-serviceactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Activated Services Modal

    //Update Activated Service
    $('#update_serviceactivation').on('submit', function (event) {
        event.preventDefault();
        var data = $(this).serializeArray();
        data = data.filter(function(item) {
            return item.name !== 'u_ssm[]';
        });
        var UOrgId = $('#u_sorg').val();
        var USiteId = $('#u_ssite').val();
        var UServiceId = $('#u_service').val();
        var UCCID = $('#u_scc').val();
        var UServiceModeIds = $('#u_ssm').val();
        data.push({ name: 'u_sorg', value: UOrgId });
        data.push({ name: 'u_ssite', value: USiteId });
        data.push({ name: 'u_service', value: UServiceId });
        data.push({ name: 'u_scc', value: UCCID });
        if(UServiceModeIds == null || UServiceModeIds.length === 0){
            data.push({ name: 'u_ssm[]', value: null });
        }
        else {
            $.each(UServiceModeIds, function(i, val){
                data.push({ name: 'u_ssm[]', value: val });
            });
        }

        var Id = $('.u_service_id').val();
        var url = '/update-activateservice/' + Id;
        $.ajax({
            url: url,
            method: 'POST',
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
                            $('#edit-serviceactivation').modal('hide');
                            $('#view-serviceactivation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_serviceactivation')[0].reset();
                            $('.text-danger').hide();
                        }
                        $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Activated Services


    //Activate KPI
    $('#activate_kpi').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();

        var kpiID = $('#act_kpi').val();
        var OrgId = $('#act_kpi_org').val();
        var SiteId = $('#act_kpi_site').val();
        var CCID = $('#act_kpi_cc').val();

        data.push({ name: 'act_kpi', value: kpiID });
        data.push({ name: 'act_kpi_org', value: OrgId });
        data.push({ name: 'act_kpi_site', value: SiteId });
        data.push({ name: 'act_kpi_cc', value: CCID });

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
                url: "/kpi/activatekpi",
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
                                $('#kpi_activation').modal('hide');
                                $('#view-kpiactivation').DataTable().ajax.reload(); // Refresh DataTable
                                $('#activate_kpi').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#act_kpi_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#act_kpi_cc').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);

                                $('#activate_kpi')[0].reset();
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
                                $('#activate_kpi').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#activate_kpi')[0].reset();
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
    //Activate KPI

    // View Activated KPIData
    var ActivatedKPIData =  $('#view-kpiactivation').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kpi/getactivatekpidata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'orgName', name: 'orgName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'ccName', name: 'ccName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "200px"
            },
            {
                targets: 2,
                width: "150px"
            },
            {
                targets: 6,
                width: "250px"
            }
        ]
    });

    ActivatedKPIData.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ActivatedKPIData.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ActivatedKPIData.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Activated KPIData

    // Update Activated KPI Status
    $(document).on('click', '.activatekpi', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/kpi/update-activatekpi',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-kpiactivation').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Activated KPI Status

    // Update Activated KPI Modal
    $(document).on('click', '.edit-activatekpi', function() {
        var activatekpiId = $(this).data('activatekpi-id');
        $('#u_kpi').empty();
        $('#u_korg').empty();
        $('#u_ksite').empty();
        $('#uk_edt').empty();

        $('#update_kpiactivation')[0].reset();
        $('#ajax-loader').show();
        var url = '/kpi/updateactivatekpi/' + activatekpiId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var kpiName = response.kpiName;
                var kpiID = response.kpiID;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var ccName = response.ccName;
                var ccID = response.ccID;


                $('#u_kpi').html("<option selected value='"+kpiID+"'>" + kpiName + "</option>");
                $('#u_korg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_ksite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_kcc').html("<option selected value='"+ccID+"'>" + ccName + "</option>");
                $('.u_kpi_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                $.ajax({
                    url: 'kpi/getselectedkpi',
                    type: 'GET',
                    data: {
                        kpiID: kpiID,
                    },
                    beforeSend: function() {
                        $('#u_kpi').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_kpi').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('#u_kpi').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                fetchOrganizations(orgID,orgName,'#u_korg', function(data) {
                    $('#u_korg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_korg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                if (orgID) {
                    fetchSites(orgID, '#u_ksite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                if(value.id != siteId){
                                    $('#u_ksite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-kpiactivation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });


                    $('#u_korg').off('change').on('change', function() {
                        $('#u_ksite').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_ksite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    if(value.id != siteId){
                                        $('#u_ksite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    }
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-kpiactivation').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });


                    fetchActivatedCostCenters(siteId, '#u_kcc', function(data) {
                        if (data.length > 0) {
                            $('#u_kcc').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(data, function(key, value) {
                                if(value.id != response.ccID){
                                    $('#u_kcc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                        else{
                            Swal.fire({
                                text: 'Cost Centerss are not Activated for selected Site',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-kpiactivation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });

                    $('#u_ksite').off('change').on('change', function() {
                        var siteId = $(this).val();
                        fetchActivatedCostCenters(siteId, '#u_kcc', function(data) {
                            if (data.length > 0) {
                                $('#u_kcc').empty();

                                $('#u_kcc').find('option:contains("Loading...")').remove(); // Remove the loading option
                                $.each(data, function(key, value) {
                                    $('#u_kcc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                var ccID = $('#u_kcc').val();
                            }
                            else{
                                Swal.fire({
                                    text: 'Cost Centers are not Activated for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-kpiactivation').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });

                    });


                }

                $('#edit-kpiactivation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Activated KPI Modal

    //Update Activated KPI
    $('#update_kpiactivation').on('submit', function (event) {
        event.preventDefault();
        var data = $(this).serializeArray();
        data = data.filter(function(item) {
            return item.name !== 'u_ssm[]';
        });
        var UKPI = $('#u_kpi').val();
        var UOrgId = $('#u_korg').val();
        var USiteId = $('#u_ksite').val();
        var UCCID = $('#u_kcc').val();

        data.push({ name: 'u_kpi', value: UKPI });
        data.push({ name: 'u_korg', value: UOrgId });
        data.push({ name: 'u_ksite', value: USiteId });
        data.push({ name: 'u_kcc', value: UCCID });

        var Id = $('.u_kpi_id').val();
        var url = '/update-activatekpi/' + Id;
        $.ajax({
            url: url,
            method: 'POST',
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
                            $('#edit-kpiactivation').modal('hide');
                            $('#view-kpiactivation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_kpiactivation')[0].reset();
                            $('.text-danger').hide();
                        }
                        $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    //Update Activated KPI
});
// Activations

//HR Setup
$(document).ready(function() {
    //Add Gender
    $('#add_gender').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }

        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addgender",
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
                                $('#add-gender').modal('hide');
                                $('#view-gender').DataTable().ajax.reload();
                                $('#add_gender')[0].reset();
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
                                $('#add_gender')[0].reset();
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
    //Add Gender

    // View Gender Data
    var viewGender =  $('#view-gender').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/genderdata',
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

    viewGender.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewGender.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewGender.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Gender Data

    // Update Gender Status
    $(document).on('click', '.gender_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/gender-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-gender').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Gender Status

    //Update Gender Modal
    $(document).on('click', '.edit-gender', function() {
        var ServiceModeId = $(this).data('gender-id');
        var url = '/hr/updategender/' + ServiceModeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.eg-id').val(response.id);
                $('.u_eg').val(response.name);
                $('#edit-gender').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Gender Modal

    //Update Gender
    $('#u_gender').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var egId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'eg-id') {
                egId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-gender/' + egId;
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
                            $('#edit-gender').modal('hide');
                            $('#view-gender').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_gender')[0].reset();
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
    //Update Gender


    //Add Employee Status
    $('#add_empStatus').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addempStatus",
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
                                $('#add-empStatus').modal('hide');
                                $('#view-empStatus').DataTable().ajax.reload();
                                $('#add_empStatus')[0].reset();
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
                                $('#add_empStatus')[0].reset();
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
    //Add Employee Status

    // View Employee Status Data
    var viewempStatus =  $('#view-empStatus').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/empStatusdata',
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

    viewempStatus.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempStatus.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempStatus.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Status Data

    // Update Employee Status
    $(document).on('click', '.emp_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/emp-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empStatus').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Employee Status

    //Update Employee Status Modal
    $(document).on('click', '.edit-empStatus', function() {
        var empStatusId = $(this).data('empstatus-id');
        console.log(empStatusId);
        var url = '/hr/updateempStatus/' + empStatusId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.es-id').val(response.id);
                $('.u_es').val(response.name);
                $('#edit-empStatus').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Status Modal

    //Update Employee Status Data
    $('#u_empStatus').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var esId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'es-id') {
                esId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empStatus/' + esId;
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
                            $('#edit-empStatus').modal('hide');
                            $('#view-empStatus').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empStatus')[0].reset();
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
    //Update Employee Status Data



    //Add Employee Working Status
    $('#add_workingStatus').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addworkingStatus",
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
                                $('#add-workingStatus').modal('hide');
                                $('#view-workingStatus').DataTable().ajax.reload();
                                $('#add_workingStatus')[0].reset();
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
                                $('#add_workingStatus')[0].reset();
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
    //Add Employee Working Status

    // View Employee Working Status Data
    var viewworkingStatus =  $('#view-workingStatus').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/workingStatusdata',
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
            { data: 'jobContinuation', name: 'jobContinuation' },
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

    viewworkingStatus.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewworkingStatus.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewworkingStatus.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Working Status Data

    // Update Employee Working Status
    $(document).on('click', '.working_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/working-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-workingStatus').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Working Status

    //Update Employee Working Status Modal
    $(document).on('click', '.edit-workingStatus', function() {
        var workingStatusId = $(this).data('workingstatus-id');
        var url = '/hr/updateworkingStatus/' + workingStatusId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                if(response.jobContinue == 0) {
                    $('#u_jobcontinue').bootstrapSwitch('state', false);
                } else {
                    $('#u_jobcontinue').bootstrapSwitch('state', true);
                }
                $('.ews-id').val(response.id);
                $('.u_ews').val(response.name);
                $('#edit-workingStatus').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Working Status Modal

    //Update Employee Working Status Data
    $('#u_workingStatus').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ewsId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ews-id') {
                ewsId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-workingStatus/' + ewsId;
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
                            $('#edit-workingStatus').modal('hide');
                            $('#view-workingStatus').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_workingStatus')[0].reset();
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
    //Update Employee Working Status Data



    //Add Employee Qualification Level
    $('#add_empQualification').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addempqualificationlevel",
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
                                $('#add-empQualification').modal('hide');
                                $('#view-empQualification').DataTable().ajax.reload();
                                $('#add_empQualification')[0].reset();
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
                                $('#add_empQualification')[0].reset();
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
    //Add Employee Qualification Level

    // View Employee Qualification Level Data
    var viewempQualification =  $('#view-empQualification').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/empqualificationleveldata',
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

    viewempQualification.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempQualification.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempQualification.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Qualification Level Data

    // Update Employee Qualification Level
    $(document).on('click', '.empQualification_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empqualificationlevel-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empQualification').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Qualification Level

    //Update Employee Qualification Level Modal
    $(document).on('click', '.edit-empQualification', function() {
        var empQualificationId = $(this).data('empqualification-id');
        var url = '/hr/empqualificationlevelmodal/' + empQualificationId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.eql-id').val(response.id);
                $('.u_eql').val(response.name);
                $('#edit-empQualification').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Qualification Level Modal

    //Update Employee Qualification Level Data
    $('#u_empQualification').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var eqlId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'eql-id') {
                eqlId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empqualificationlevel/' + eqlId;
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
                            $('#edit-empQualification').modal('hide');
                            $('#view-empQualification').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empQualification')[0].reset();
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
    //Update Employee Qualification Level Data


    //Add Employee Cadre
    $('#cadre_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#cadre_org', '#cadre_site', '#add_empCadre');

    $('#add_empCadre').submit(function(e) {
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
                url: "/hr/addempcadre",
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
                                $('#add-empCadre').modal('hide');
                                $('#view-empCadre').DataTable().ajax.reload();
                                $('#add_empCadre')[0].reset();
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
                                $('#add_empCadre')[0].reset();
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
    //Add Employee Cadre

    // View EmployeeCadre Data
    var viewempCadre =  $('#view-empCadre').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/empcadredata',
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
                "data": 'orgName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'siteName',
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

    viewempCadre.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempCadre.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempCadre.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Cadre Data

    // Update Employee Cadre
    $(document).on('click', '.empCadre_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empcadre-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empCadre').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Cadre

    //Update Employee Cadre Modal
    $(document).on('click', '.edit-empCadre', function() {
        var empCadreId = $(this).data('empcadre-id');
        $('#u_cadreSite').empty()
        $('#u_cadreOrg').empty()
        var url = '/hr/empcadreStatus/' + empCadreId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                var orgName = response.orgName;
                var orgID = response.orgId;
                var siteName = response.siteName;
                var siteId = response.siteId;
                $('.ec-id').val(response.id);
                $('.u_ec').val(response.name);
                $('#u_cadreOrg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_cadreSite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");

                fetchOrganizations(orgID,orgName,'#u_cadreOrg', function(data) {
                    $('#u_cadreOrg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(value.id != orgID)
                        {
                            $('#u_cadreOrg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                if (orgID) {
                    fetchSites(orgID, '#u_cadreSite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                if(value.id != siteId){
                                    $('#u_cadreSite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-empCadre').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });


                    $('#u_cadreOrg').off('change').on('change', function() {
                        $('#u_cadreSite').empty();
                        var organizationId = $(this).val();
                        console.log(organizationId);
                        fetchSites(organizationId, '#u_cadreSite', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    console.log(data);
                                    $('#u_cadreSite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-empCadre').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });
                }

                $('#edit-empCadre').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Cadre Modal

    //Update Employee Cadre Data
    $('#u_empCadre').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var ecId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ec-id') {
                ecId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empcadre/' + ecId;
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
                            $('#edit-empCadre').modal('hide');
                            $('#view-empCadre').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empCadre')[0].reset();
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
    //Update Employee Cadre Data

    //Add Employee Position
    $('#positionsite').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    $('#emp-cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', true);
    OrgChangeSites('#positionOrg', '#positionsite', '#add_empPosition');
    SiteChangeCadre('#positionsite', '#emp-cadre', '#add_empPosition');

    $('#add_empPosition').submit(function(e) {
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
                url: "/hr/addempposition",
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
                                $('#add-empPosition').modal('hide');
                                $('#view-empPosition').DataTable().ajax.reload();
                                $('#add_empPosition').find('select').val($('#add_empPosition').find('select option:first').val()).trigger('change');
                                $('#add_empPosition')[0].reset();
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
                                $('#add_empPosition').find('select').val($('#add_empPosition').find('select option:first').val()).trigger('change');
                                $('#add_empPosition')[0].reset();
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
    //Add Employee Position

    // View Employee Position Data
    var viewempPosition =  $('#view-empPosition').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/emppositiondata',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'orgName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'siteName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'empcadre', name: 'empcadre',render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    viewempPosition.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempPosition.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempPosition.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Position Data

    // Update Employee Position Status
    $(document).on('click', '.empPosition_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empposition-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empPosition').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Position Status

    //Update Employee Position Modal
    $(document).on('click', '.edit-empPosition', function() {
        var empPositionId = $(this).data('empposition-id');
        $('#u_positionOrg').empty();
        $('#u_positionSite').empty();
        var url = '/hr/emppositionStatus/' + empPositionId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.ep-id').val(response.id);
                $('.u_ep').val(response.name);
                var orgName = response.orgName;
                var orgID = response.orgId;
                var siteName = response.siteName;
                var siteId = response.siteId;
                $('#u_positionOrg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_positionSite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('.u_cadre').html("<option selected value='"+response.cadreid+"'>" + response.cadre + "</option>");

                fetchOrganizations(orgID,orgName,'#u_positionOrg', function(data) {
                    $('#u_positionOrg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(value.id != orgID)
                        {
                            $('#u_positionOrg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                if (orgID) {
                    fetchSites(orgID, '#u_positionSite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                if(value.id != siteId){
                                    $('#u_positionSite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-empPosition').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });

                    $('#u_positionOrg').off('change').on('change', function() {
                        $('#u_positionSite').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_positionSite', function(data) {
                            if (data.length > 0) {
                                $('#u_positionSite').append('<option selected disabled value="">Select Site</option>');
                                $.each(data, function(key, value) {
                                    $('#u_positionSite').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-empPosition').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });

                    fetchEmployeeCadre(siteId, '#u_cadre', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                if(value.id != response.cadreid )
                                {
                                    $('.u_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Cadre are not available for selected Organization & Sites',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-empPosition').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });

                    $('#u_positionSite').off('change').on('change', function() {
                        $('.u_cadre').empty();
                        var siteId = $(this).val();
                        fetchEmployeeCadre(siteId, '.u_cadre', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('.u_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Cadre are not available for selected Organization & Sites',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-empPosition').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });
                    });
                }


                $('#edit-empPosition').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Position Modal

    //Update Employee Position
    $('#u_empPosition').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var epId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ep-id') {
                epId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empposition/' + epId;
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
                            $('#edit-empPosition').modal('hide');
                            $('#view-empPosition').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empPosition')[0].reset();
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
    //Update Employee Position

    //Add Employee
    $('#emp_cadre').html("<option selected disabled value=''>Select Cadre</option>").prop('disabled', true);
    $('#emp_position').html("<option selected disabled value=''>Select Position</option>").prop('disabled', true);

    SiteChangeCadre('#emp_site', '#emp_cadre', '#add_employee');
    SiteChangePosition('#emp_site', '#emp_position', '#add_employee');

    $('#add_employee').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_employee')[0]);
        var provinceValue = $('#province_name').val();
        var divisionValue = $('#division_name').val();
        var districtValue = $('#district_name').val();
        var GenderValue = $('#emp_gender').val();

        var Org = $('#emp_org').val();
        var Site = $('#emp_site').val();
        var CostCenter = $('#emp_cc').val();
        var Cadre = $('#emp_cadre').val();
        var reportTo = $('#emp_reportto').val();
        var qualificationLevel = $('#emp_qual_lvl').val();
        var EmpStatus = $('#emp_status').val();
        var WorkingStatus = $('#emp_working_status').val();
        var EmpPosition = $('#emp_position').val();
        var EmpGuardianRelation = $('#emp_guardian_relation').val();
        var EmpNextOfKinRelation = $('#emp_nextofkin_relation').val();
        var EmpReligion = $('#emp_religion').val();
        var EmpmaritalStatus = $('#emp_marital_status').val();

        var imgValue = $('#emp_img')[0].files[0];
        formData.append('emp_guardian_relation', EmpGuardianRelation);
        formData.append('emp_nextofkin_relation', EmpNextOfKinRelation);
        formData.append('emp_religion', EmpReligion);
        formData.append('emp_marital_status', EmpmaritalStatus);
        formData.append('emp_province', provinceValue);
        formData.append('emp_division', divisionValue);
        formData.append('emp_district', districtValue);
        formData.append('emp_gender', GenderValue);
        formData.append('emp_org', Org);
        formData.append('emp_site', Site);
        formData.append('emp_cc', CostCenter);
        formData.append('emp_cadre', Cadre);
        formData.append('emp_reportto', reportTo);
        formData.append('emp_qual_lvl', qualificationLevel);
        formData.append('emp_status', EmpStatus);
        formData.append('emp_working_status', WorkingStatus);
        formData.append('emp_position', EmpPosition);
        formData.append('emp_img', imgValue);
        var resp = true;

        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if ((fieldValue == '' || fieldValue == 'null' || fieldValue === 'undefined') && ((fieldName != 'emp_oldcode') && (fieldName != 'emp_landline') && (fieldName != 'emp_additionalcell')))
            {
                var FieldName = fieldName;
                if((FieldName == 'start_time') || (FieldName == 'end_time'))
                {
                    if(fieldValue == '')
                    {
                        $('#week_hrs_error').text("Please Select Employee Week Hours");
                        $( 'input[name= "' +FieldName +'"' ).focus(function() {
                            $('#week_hrs_error').text("");
                            $('input[name= "' +FieldName +'"' ).removeClass("requirefield");
                        })
                    }

                }
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
                url: "/hr/addemployee",
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
                                $('#add-employee').modal('hide');
                                $('#view-employee').DataTable().ajax.reload(); // Refresh DataTable
                                $('#add_employee').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_employee')[0].reset();
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
                                var imgReset = $('#emp_img').dropify();
                                imgReset = imgReset.data('dropify');
                                imgReset.resetPreview();
                                imgReset.clearElement();
                                $('#add_employee').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_employee')[0].reset();
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
    //Add Employee

    // View Employee
    var viewemployee =  $('#view-employee').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewemployee',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'name', name: 'name' },
            { data: 'org_name', name: 'org_name' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'mobile_no', name: 'mobile_no'},
            { data: 'email', name: 'email'},
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
                width: "300px"
            }
        ]
    });

    viewemployee.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewemployee.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewemployee.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee

    // Update Employee Status
    $(document).on('click', '.employee_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/employee-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {

                var status = xhr.status;
            if(status == 200)
            {
                $('#view-employee').DataTable().ajax.reload();
            }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Status

    //Employee Detail Modal
    $(document).on('click', '.employee-detail', function() {
        var empId = $(this).data('emp-id');
        var url = '/hr/employeedetail/' + empId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var imgPath = response.Image;
                $('#empImg').attr('src', imgPath);
                $('#empName').text(response.empName);
                $('#empAddress').text(response.Address);
                $('#empOldcode').text(response.OldCode);
                $('#empEmail').text(response.Email);
                $('#empContact').text(response.contact);
                $('#empAdditionalCell').text(response.Additionalcontact);
                $('#empProvince').text(response.Province);
                $('#empGuardian').text(response.GuardianName);
                $('#empRelation').text(response.GuardianRelation);
                $('#empNextOfKin').text(response.NextOfKin);
                $('#empNextOfKinRelation').text(response.NextOfKinRelation);
                $('#empLanguage').text(response.Language);
                $('#empReligion').text(response.Religion);
                $('#empMaritalStatus').text(response.MaritalStatus);
                $('#empDivision').text(response.Division);
                $('#empDistrict').text(response.District);
                $('#empOrg').text(response.Organization);
                $('#empSite').text(response.Site);
                $('#empGender').text(response.Gender);
                $('#empCC').text(response.CostCenter);
                $('#empCadre').text(response.Cadre);
                $('#empPosition').text(response.Position);
                $('#empQualification').text(response.Qualification);
                $('#empStatus').text(response.EmpStatus);
                $('#workingStatus').text(response.WorkingStatus);

                $('#weekHrs').text(response.WeekHrs);
                $('#empManager').text(response.Manager);
                $('#empJD').text(response.JoiningDate);
                $('#empCnic').text(response.cnic);
                $('#empDOB').text(response.DateOfBirth);

                $('#employee-detail').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Employee Detail Modal

    // Update Employee Modal
    $(document).on('click', '.edit-employee', function() {
        var employeeId = $(this).data('employee-id');
        $('#edit_employee')[0].reset();
        $('#ajax-loader').show();
        var url = '/hr/updatemodal/' + employeeId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var empname = response.empName;
                var GuardianName = response.GuardianName;
                var GuardianRelation = response.GuardianRelation;
                var NextOfKin = response.empNextOfKin;
                var NextOfKinRelation = response.empNextOfKinRelation;
                var empCode = response.OldCode;
                var gender = response.Gender;
                var language = response.language;
                var Religion = response.Religion;
                var MaritalStatus = response.MaritalStatus;
                var dob = response.empDOB;
                var org = response.Organization;
                var site = response.Site;
                var cc = response.CostCenter;
                var cadre = response.Cadre;
                var position = response.Position;
                var Manager = response.Manager;
                var Qualification = response.Qualification;
                var empStatus = response.EmpStatus;
                var workingStatus = response.WorkingStatus;
                var doj = response.empDOJ;
                var dol = response.empDOL;
                var province = response.Province;
                var division = response.Division;
                var district = response.District;
                var cnic = response.cnic;
                var cell = response.cell;
                var AdditionalCell = response.AdditionalCell;
                var landline = response.landline;
                var email = response.Email;
                var address = response.Address;
                var image = response.Image;

                var imgName = image.trim().substring(image.lastIndexOf('/') + 1);

                var emp_img_input = $('#u_empImg');
                var dropifyRenderImg = emp_img_input.closest('.dropify-wrapper').find('.dropify-render');

                dropifyRenderImg.find('img').attr('src', image);

                var imgdropifyInfos = emp_img_input.closest('.dropify-wrapper').find('.dropify-infos');
                var imgfilenameInner = imgdropifyInfos.find('.dropify-filename-inner');
                imgfilenameInner.text(imgName); //

                emp_img_input.attr('data-default-file', image);
                emp_img_input.dropify('destroy');
                emp_img_input.dropify();

                $('.u_emp_code').val(empCode);
                $('.u_emp_cnic').val(cnic);
                $('.u_emp_cell').val(cell);
                $('.u_emp_additional_cell').val(AdditionalCell);
                $('.u_emp_landline').val(landline);
                $('.u_emp_email').val(email);
                $('.u_emp_address').val(address);
                $('.u_emp_name').val(empname);
                $('.u_guardian_name').val(GuardianName);
                $('.u_emp_nextofkin').val(NextOfKin);
                $('.u_emp_language').val(language);
                $('#u_emp_weekHrs').val(response.WeekHrs);
                $('.u-emp-id').val(response.id);

                const guardianRelations = ["Father", "Husband"];
                let guardianOptions = '<option selected>' + GuardianRelation + '</option>';
                for (let guardianRelation of guardianRelations) {
                    if (guardianRelation !== GuardianRelation) {
                        guardianOptions += '<option>' + guardianRelation + '</option>';
                    }
                }
                $('#u_guardian_relation').html(guardianOptions);

                const nextofkinRelations = ["Father", "Mother", "Brother", "Sister", "Spouse", "Child", "Grandparent", "Grandchild", "Uncle", "Aunt", "Niece", "Nephew", "Cousin", "Legal Guardian", "Friend", "Partner"];
                let nextofkinOptions = '<option selected>' + NextOfKinRelation + '</option>';
                for (let nextofkinRelation of nextofkinRelations) {
                    if (nextofkinRelation !== NextOfKinRelation) {
                        nextofkinOptions += '<option>' + nextofkinRelation + '</option>';
                    }
                }
                $('#u_nextofkin_relation').html(nextofkinOptions);

                const Religions = ["Islam", "Hindu", "Chiristian", "Sikh"];
                let religionOptions = '<option selected>' + Religion + '</option>';
                for (let religion of Religions) {
                    if (religion !== Religion) {
                        religionOptions += '<option>' + religion + '</option>';
                    }
                }
                $('#u_emp_religion').html(religionOptions);

                // $('#u_emp_marital_status').html("<option selected>" + MaritalStatus + "</option>");

                const MaritalStatuses = ["Single", "Married", "Divorced", "Widowed"];
                let maritalStatusOptions = '<option selected>' + MaritalStatus + '</option>';
                for (let maritalStatus of MaritalStatuses) {
                    if (maritalStatus !== MaritalStatus) {
                        maritalStatusOptions += '<option>' + maritalStatus + '</option>';
                    }
                }
                $('#u_emp_marital_status').html(maritalStatusOptions);

                $('#u_emp_gender').html("<option selected value='"+response.GenderID+"'>" + gender + "</option>");
                $('#u_emp_org').html("<option selected value='"+response.OrganizationID+"'>" + org + "</option>");
                $('#u_emp_site').html("<option selected value='"+response.SiteID+"'>" + site + "</option>");
                $('#u_emp_cc').html("<option selected value='"+response.CostCenterID+"'>" + cc + "</option>");
                $('#u_emp_cadre').html("<option selected value='"+response.CadreID+"'>" + cadre + "</option>");
                $('#u_emp_position').html("<option selected value='"+response.PositionID+"'>" + position + "</option>");
                $('#u_emp_reportto').html("<option selected value='"+response.GenderID+"'>" + gender + "</option>");
                $('#u_qualification').html("<option selected value='"+response.QualificationID+"'>" + Qualification + "</option>");
                $('#u_emp_status').html("<option selected value='"+response.EmpStatusID+"'>" + empStatus + "</option>");
                $('#u_working_status').html("<option selected data-jobstatus='"+response.jobContinue+"' value='"+response.WorkingStatusID+"'>" + workingStatus + "</option>");
                $('#u_emp_province').html("<option selected value='"+response.ProvinceID+"'>" + province + "</option>");
                $('#u_emp_division').html("<option selected value='"+response.DivisionID+"'>" + division + "</option>");
                $('#u_emp_district').html("<option selected value='"+response.DistrictID+"'>" + district + "</option>");
                $('#u_emp_reportto').html("<option selected value='"+response.ManagerID+"'>" + Manager + "</option>");
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.u_emp_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                if(response.jobContinue == 0)
                {
                    $('#u_emp_dol').attr('required', true);
                    var formattedDOL = moment(dol).format('YYYY-MM-DD');
                    $('#u_emp_dol').each(function() {
                        var dolElement = $(this);
                        dolElement.val(formattedDOL);
                    });
                    $('#date_of_leaving').show();
                }
                else{
                    $('#u_emp_dol').attr('required', false);
                    $('#date_of_leaving').hide();
                }

                var formattedDOJ = moment(doj).format('YYYY-MM-DD');
                $('#u_emp_doj').each(function() {
                    var dojElement = $(this);
                    dojElement.val(formattedDOJ);
                });

                var formattedDOB = moment(dob).format('YYYY-MM-DD');
                $('#u_emp_dob').each(function() {
                    var dobElement = $(this);
                    dobElement.val(formattedDOB);
                });

                fetchOrganizations(response.OrganizationID,org,'#u_emp_org', function(data) {
                    $('#u_emp_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_emp_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                $.ajax({
                    url: 'hr/getgender',
                    type: 'GET',
                    data: {
                        genderID: response.GenderID,
                    },
                    beforeSend: function() {
                        $('#u_emp_gender').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('#u_emp_gender').find('option:contains("Loading...")').remove(); // Remove the loading option
                        $.each(resp, function(key, value) {
                            $('#u_emp_gender').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                fetchEmployeeCadre(response.SiteID, '.u_emp_cadre', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(value.id != response.CadreID )
                            {
                                $('.u_emp_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Cadre are not available for selected Organization & Sites',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-empPosition').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                });

                fetchEmployeePosition(response.SiteID, '.u_emp_position', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(value.id != response.PositionID )
                            {
                                $('.u_emp_position').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Positions are not available for selected Organization & Sites',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-empPosition').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                });

                fetchQualificationLevel(response.QualificationID,'.u_qualification', function(data) {
                   $.each(data, function(key, value) {
                       $('.u_qualification').append('<option value="' + value.id + '">' + value.name + '</option>');
                   });
                });

                $.ajax({
                    url: 'hr/getempstatus',
                    type: 'GET',
                    data: {
                        empstatusid: response.EmpStatusID,
                    },
                    beforeSend: function() {
                        $('.u_emp_status').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('.u_emp_status').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('.u_emp_status').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $.ajax({
                    url: 'hr/getworkingstatus',
                    type: 'GET',
                    data: {
                        workingstatusid: response.WorkingStatusID,
                    },
                    beforeSend: function() {
                        $('.u_working_status').append('<option>Loading...</option>');
                    },
                    success: function(resp) {
                        $('.u_working_status').find('option:contains("Loading...")').remove();
                        $.each(resp, function(key, value) {
                            $('.u_working_status').append('<option data-jobstatus="' + value.job_continue + '" value="' + value.id + '">' + value.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });

                $('#u_working_status').off('change').on('change', function() {
                    var jobStatus = $(this).find('option:selected').data('jobstatus');
                    if(jobStatus == 0)
                    {
                        $('#u_emp_dol').attr('required', true);
                        $('#date_of_leaving').show();
                        var currentDate = new Date();
                        $('#u_emp_dol').bootstrapMaterialDatePicker('setDate', currentDate);
                    }
                    else{
                        $('#u_emp_dol').attr('required', false);
                        $('#u_emp_dol').val('');
                        $('#date_of_leaving').hide();
                    }
                });

                fetchEmployees(response.ManagerID, '#u_emp_reportto', function(data) {
                    $.each(data, function(key, value) {
                        $('#u_emp_reportto').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });

                    if(Manager != 'N/A')
                    {
                        $('#u_emp_reportto').append('<option value="0">N/A</option>');
                    }

                }, function(error) {
                    console.log(error);
                });

                if (response.OrganizationID) {
                    fetchSites(response.OrganizationID, '#u_emp_site', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-employee').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    },response.SiteID);

                    $('#u_emp_org').off('change').on('change', function() {
                        $('#u_emp_site').empty();
                        var organizationId = $(this).val();
                        fetchSites(organizationId, '#u_emp_site', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_emp_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Sites are not available for selected Organization',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');
                                    }
                                });
                            }

                        }, function(error) {
                            console.log(error);
                        });
                    });

                    fetchActivatedCostCenters(response.SiteID, '#u_emp_cc', function(data) {
                        $.each(data, function(key, value) {
                            $('#u_emp_cc').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }, function(error) {
                        console.log(error);
                    },response.CostCenterID);

                    $('#u_emp_site').off('change').on('change', function() {
                        $('#u_emp_cc').empty();
                        $('.u_emp_cadre').empty();
                        $('.u_emp_position').empty();
                        var siteId = $(this).val();

                        fetchActivatedCostCenters(siteId, '#u_emp_cc', function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    $('#u_emp_cc').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else{
                                Swal.fire({
                                    text: 'Cost Centers are not Activated for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');

                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });

                        fetchEmployeeCadre(siteId, '#u_cadre', function(data) {
                            if (data.length > 0) {
                                $('.u_emp_cadre').append('<option selected disabled value="">Select Cadre</option>');
                                $.each(data, function(key, value) {
                                    $('.u_emp_cadre').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Cadre are not available for selected Organization & Sites',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });

                        fetchEmployeePosition(siteId, '.u_emp_position', function(data) {
                            if (data.length > 0) {
                                $('.u_emp_position').append('<option selected disabled value="">Select Position</option>');
                                $.each(data, function(key, value) {
                                    $('.u_emp_position').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            }
                            else {
                                Swal.fire({
                                    text: 'Positions are not available for selected Organization & Sites',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-employee').modal('hide');
                                    }
                                });
                            }
                        }, function(error) {
                            console.log(error);
                        });

                    });

                    $.ajax({
                        url: 'territory/updateprovince',
                        type: 'GET',
                        data: {
                            provinceId: response.ProvinceID,
                        },
                        beforeSend: function() {
                            $('#u_emp_province').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_province').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_province').append('<option value="' + value.id + '">' + value.name + '</option>');
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
                            provinceId: response.ProvinceID,
                            divisionId: response.DivisionID,
                        },
                        beforeSend: function() {
                            $('#u_emp_division').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_division').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    $.ajax({
                        url: 'territory/updatedistrict',
                        type: 'GET',
                        data: {
                            divisionId: response.DivisionID,
                        },
                        beforeSend: function() {
                            $('#u_emp_district').append('<option>Loading...</option>');
                        },
                        success: function(resp) {
                            $('#u_emp_district').find('option:contains("Loading...")').remove(); // Remove the loading option
                            $.each(resp, function(key, value) {
                                $('#u_emp_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });

                    $('#u_emp_province').change(function() {
                        var province_id = $(this).val();
                        $.ajax({
                            url: 'territory/updatedivision',
                            type: 'GET',
                            data: {
                                provinceId: province_id,
                            },
                            beforeSend: function() {
                                $('#u_emp_division').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#u_emp_division').html("<option selected disabled value=''>Select Division</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_emp_division').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });

                    $('#u_emp_division').change(function() {
                        var divisionid = $(this).val();
                        $.ajax({
                            url: 'territory/updatedistrict',
                            type: 'GET',
                            data: {
                                divisionId: divisionid,
                            },
                            beforeSend: function() {
                                $('#u_emp_district').append('<option>Loading...</option>');
                            },
                            success: function(resp) {
                                    $('#u_emp_district').html("<option selected disabled value=''>Select District</option>");
                                $.each(resp, function(key, value) {
                                    $('#u_emp_district').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });


                }//

                $('#edit-employee').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Employee Modal

    //Update Employee
    $('#edit_employee').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#edit_employee')[0]);
        var gender = $('#u_emp_gender').val();
        var org = $('#u_emp_org').val();
        var site = $('#u_emp_site').val();
        var costcenter = $('#u_emp_cc').val();
        var cadre = $('#u_emp_cadre').val();
        var position = $('#u_emp_position').val();
        var manager = $('#u_emp_reportto').val();
        var qualificationLevel = $('#u_qualification').val();
        var empStatus = $('#u_emp_status').val();
        var workingStatus = $('#u_working_status').val();
        var province = $('#u_emp_province').val();
        var division = $('#u_emp_division').val();
        var district = $('#u_emp_district').val();
        var empId = $('.u-emp-id').val();

        formData.append('u_emp_gender', gender);
        formData.append('u_emp_org', org);
        formData.append('u_emp_site', site);
        formData.append('u_emp_cc', costcenter);
        formData.append('u_emp_cadre', cadre);
        formData.append('u_emp_position', position);
        formData.append('u_emp_reportto', manager);
        formData.append('u_qualification', qualificationLevel);
        formData.append('u_emp_status', empStatus);
        formData.append('u_working_status', workingStatus);
        formData.append('u_emp_province', province);
        formData.append('u_emp_division', division);
        formData.append('u_emp_district', district);

        var empImg = $('#u_empImg')[0].files[0];
        if(empImg != 'undefined')
        {
            formData.append('u_empImg', empImg);
        }
        var url = '/edit-employee/' + empId;
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
                            $('#edit-employee').modal('hide');
                            $('#view-employee').DataTable().ajax.reload(); // Refresh DataTable
                            $('#edit_employee')[0].reset();
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
    //Update Employee

    //Open Add Employee Modal
    $(document).on('click', '.add_empSalary', function() {
        $('#show_emp').empty();
        $('#es-site').empty();
        $('#es-site').select2();
        $('#es-org').empty();
        $('#es-org').select2();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>");
        fetchOrganizations(null,null,'.es-org', function(data) {
            var options = ["<option selected disabled value=''>Select Organization</option>"];
            $.each(data, function(key, value) {
                options.push('<option value="' + value.id + '">' + value.organization + '</option>');
            });
            $('.es-org').html(options.join('')).trigger('change'); // This is for Select2
        });
        $('#es-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#es-org', '#es-site', '#add_qualificationSetup');
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#es-site', '#show_emp', '#add_empSalary');
        $('#add-empSalary').modal('show');
    });
    //Open Add Employee Modal

    //Add Employee Salary
    $('#add_empSalary').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'salary_remarks'))
            {
                var FieldName = field.name;
                FieldName = field.name.replace('[]', '');
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addempsalary",
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
                                $('#add-empSalary').modal('hide');
                                $('#view-empSalary').DataTable().ajax.reload();
                                $('#add_empSalary').find('select').val($('#add_empSalary').find('select option:first').val()).trigger('change');
                                $('#add_empSalary')[0].reset();
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
                                $('#add_empSalary').find('select').val($('#add_empSalary').find('select option:first').val()).trigger('change');
                                $('#add_empSalary')[0].reset();
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
    //Add Employee Salary

    // View Employee Salary Data
    var viewempSalary =  $('#view-empSalary').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewemployeesalary',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'empSalary', name: 'empSalary' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    viewempSalary.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewempSalary.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewempSalary.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Employee Salary Data

    // Update Employee Salary Status
    $(document).on('click', '.empsalary_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/empsalary-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
                var status = xhr.status;
                if(status == 200)
                {
                    $('#view-empSalary').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Employee Salary Status

    //Update Employee Salary Modal
    $(document).on('click', '.edit-empSalary', function() {
        var empSalaryId = $(this).data('empsalary-id');
        var url = '/hr/updatesalarymodal/' + empSalaryId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.usalary-id').val(response.id);
                $('.uempSalary').val(response.salary).trigger('input');
                $('#edit-empSalary').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Employee Salary Modal

    //Update Employee Salary
    $('#u_empSalary').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var salaryId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'usalary-id') {
                salaryId = formData[i].value;
                break;
            }
        }
        var url = 'hr/update-empsalary/' + salaryId;
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
                            $('#edit-empSalary').modal('hide');
                            $('#view-empSalary').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_empSalary')[0].reset();
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
    //Update Employee Salary

    //Open Add Employee Qualification Setup
    $(document).on('click', '.addqualificationSetup', function() {
        $('#show_emp').empty();
        $('#eq-site').empty();
        $('#eq-site').select2();
        $('#eq-org').empty();
        $('#eq-org').select2();
        fetchOrganizations(null,null,'.eq-org', function(data) {
            var options = ["<option selected disabled value=''>Select Organization</option>"];
            $.each(data, function(key, value) {
                options.push('<option value="' + value.id + '">' + value.organization + '</option>');
            });
            $('.eq-org').html(options.join('')).trigger('change'); // This is for Select2
        });
        $('#eq-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#eq-org', '#eq-site', '#add_qualificationSetup');
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#eq-site', '#show_emp', '#add_qualificationSetup');

        $('#add-qualificationSetup').modal('show');

    });
    //Open Add Employee Qualification Setup

    //Add Qualification Setup
    $('#add_qualificationSetup').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
            {
                var FieldName = field.name;
                FieldName = field.name.replace('[]', '');
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
            }
        });
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

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addqualification-setup",
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
                                $('#add-qualificationSetup').modal('hide');
                                $('#add_qualificationSetup').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_qualificationSetup')[0].reset();
                                location.reload();
                                //refresh here
                                // $('.text-danger').hide();
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
                                $('#add_qualificationSetup').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_qualificationSetup')[0].reset();
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
    //Add Qualification Setup

    //View Qualification Setup
    $('#viewempQualification').on('change', function() {
        var EmployeeId = $(this).val();
        LoadEmployeeQualification(EmployeeId);

    });
    //View Qualification Setup

    //Update Qualification Setup
    $('#updateQualification').submit(function(e) {
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
            url: "/hr/updatequalification-setup",
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
                            $('#updateQualification')[0].reset();
                            $('.profiletimeline').empty();
                            LoadEmployeeQualification(empId);
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
                            $('#updateQualification')[0].reset();
                        }
                    });
                }
            },
            error: function(error) {
                console.log(error);
            }
        });

    });
    //Update Qualification Setup

    //Open Add Employee Medical License Setup
    $(document).on('click', '.addmedicalLicense', function() {
        $('#show_emp').empty();
        $('#em-site').empty();
        $('#em-site').select2();
        $('#em-org').empty();
        $('#em-org').select2();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>");
        fetchOrganizations(null,null,'.em-org', function(data) {
            var options = ["<option selected disabled value=''>Select Organization</option>"];
            $.each(data, function(key, value) {
                options.push('<option value="' + value.id + '">' + value.organization + '</option>');
            });
            $('.em-org').html(options.join('')).trigger('change'); // This is for Select2
        });
        $('#em-site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#em-org', '#em-site', '#add_qualificationSetup');
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#em-site', '#show_emp', '#add_medicalLicense');
        $('#add-medicalLicense').modal('show');
    });
    //Open Add Employee Medical License Setup

    //Add Employee Medical License
    $('#add_medicalLicense').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
            {
                var FieldName = field.name;
                FieldName = field.name.replace('[]', '');
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
            }
        });

        $(".duplicate").each(function() {
            var row = $(this);
            row.find('input, textarea, select').each(function() {
                var elem = $(this);
                var value = elem.val();
                var fieldName = elem.attr('name').replace('[]', '');
                var errorField = row.find('.' + fieldName + '_error');
                if (!value || value === "" || (elem.is('select') && value === null)) {
                    errorField.text("This field is required");
                    elem.addClass('requirefield');
                    elem.focus(function() {
                        errorField.text("");
                        elem.removeClass("requirefield");
                    });
                    resp = false;
                }
                else {
                    errorField.text("");
                    if (elem.is('select')) {
                        elem.next('.select2-container').find('.select2-selection').removeClass('requirefield');
                    } else {
                        elem.removeClass('requirefield');
                    }
                }
            });
        });

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addmedical-license",
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
                                $('#add-medicalLicense').modal('hide');
                                $('#add_medicalLicense').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_medicalLicense')[0].reset();
                                location.reload();
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
                                $('#add_medicalLicense').find('select').each(function() {
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_medicalLicense')[0].reset();
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
    //Add Employee Medical License


    //View Medical License Setup
    $('#viewempMedicalLicense').on('change', function() {
        var EmployeeId = $(this).val();
        LoadEmployeeMedicalLicense(EmployeeId);

    });
    //View Medical License Setup

    //Update Medical License Setup
    $('#updateMedicalLicense').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var empId = null;
        for (var i = 0; i < data.length; i++) {
            if (data[i].name === 'empId') {
                empId = data[i].value;
                break;
            }
        }
        $.ajax({
            url: "/hr/updatemedical-license",
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
                            $('#updateMedicalLicense')[0].reset();
                            $('.profiletimeline').empty();
                            LoadEmployeeMedicalLicense(empId);
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
                            $('#updateMedicalLicense')[0].reset();
                        }
                    });
                }
            },
            error: function(error) {
                console.log(error);
            }
        });

    });
    //Update Medical License Setup

    //Open Add Employee CC Setup
    $(document).on('click', '.addEmpCC', function() {
        $('#show_emp').empty();
        $('.empcc_site').empty();
        $('.empcc_site').select2();
        $('.empcc_org').empty();
        $('.empcc_org').select2();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>");
        fetchOrganizations(null,null,'.empcc_org', function(data) {
            var options = ["<option selected disabled value=''>Select Organization</option>"];
            $.each(data, function(key, value) {
                options.push('<option value="' + value.id + '">' + value.organization + '</option>');
            });
            $('.empcc_org').html(options.join('')).trigger('change');
        });
        $('.empcc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('.empcc_org', '.empcc_site', '#emp_serviceallocation');
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('.empcc_site', '#show_emp', '#add_empCC');
        $('.emp_costcenter').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
        $('#add-empcc').modal('show');


        // $('.empcc_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        // $('.emp_costcenter').html("<option selected disabled value=''>Select Cost Center</option>").prop('disabled', true);
        // $('#show_emp').empty();
        // $('#show_emp').html("<option selected disabled value=''>Select Employee</option>");
        // fetchCCEmployees('empCC', '#show_emp', function(data) {
        //     if (data.length > 0 && data[0] !== "null")
        //     {
        //         $.each(data, function(key, value) {
        //             $('#show_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
        //         })

        //     }
        //     else {
        //         Swal.fire({
        //             text: 'Cost Center already allocated to all Employees',
        //             icon: 'error',
        //             confirmButtonText: 'OK'
        //         }).then((result) => {
        //             if (result.isConfirmed) {
        //                 $('#add-empcc').modal('hide');
        //             }
        //         });
        //     }
        // }, function(error) {
        //     console.log(error);
        // });
        // $('#add-empcc').modal('show');
    });
    //Open Add Employee CC Setup

    //Add Employee CC
    $('#add_empCC').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var Employee = $('#show_emp');
        var empErrorID = $('#emp-id_error');
        var resp = true;

        if (!Employee.val() || Employee.val() === "" || Employee.val() === null) {
            empErrorID.text("This field is required");
            Employee.next('.select2-container').find('.select2-selection').addClass('requirefield');

            Employee.on('select2:open', function() {
                empErrorID.text("");
                Employee.next('.select2-container').find('.select2-selection').removeClass("requirefield");
            });

            resp = false;
        } else {
            empErrorID.text("");
            Employee.next('.select2-container').find('.select2-selection').removeClass('requirefield');
        }

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

        if(resp != false)
        {
            $.ajax({
                url: "/hr/addempcc",
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
                                location.reload();
                            }
                        });
                    }
                    else if (fieldName == 'info')
                    {
                        Swal.fire({
                            text: fieldErrors,
                            icon: fieldName,
                            confirmButtonText: 'OK'
                        // }).then((result) => {
                        //     if (result.isConfirmed) {
                        //         $('#add_empCC').find('select').each(function() {
                        //             $(this).val($(this).find('option:first').val()).trigger('change');
                        //         });
                        //         $('#add_empCC')[0].reset();
                        //     }
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
    //Add Employee CC

    //View Employee CC
    $('#viewempCC').on('change', function() {
        var EmployeeId = $(this).val();
        LoadEmployeeCostCenter(EmployeeId);
    });
    //View Employee CC

    //Update Employee CC
    $('#updateEmpCC').submit(function(e) {
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
            url: "/hr/updateempcc",
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
                            $('#updateEmpCC')[0].reset();
                            $('.profiletimeline').empty();
                            LoadEmployeeCostCenter(empId);
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
    //Update Employee CC

    //Open Add Allocate Service Setup
    $(document).on('click', '.emp-serviceallocation', function() {
        $('#show_emp').empty();
        $('#site_sa').empty();
        $('#site_sa').select2();
        $('#org_sa').empty();
        $('#org_sa').select2();
        $('#service_sa').empty();
        $('#service_sa').select2();
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>");
        fetchOrganizations(null,null,'#org_sa', function(data) {
            var options = ["<option selected disabled value=''>Select Organization</option>"];
            $.each(data, function(key, value) {
                options.push('<option value="' + value.id + '">' + value.organization + '</option>');
            });
            $('#org_sa').html(options.join('')).trigger('change');
        });
        $('#site_sa').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#org_sa', '#site_sa', '#emp_serviceallocation');
        $('#show_emp').html("<option selected disabled value=''>Select Employee</option>").prop('disabled',true);
        SiteChangeEmployees('#site_sa', '#show_emp', '#emp_serviceallocation');
        $('#service_sa').html("<option selected disabled value=''>Select Service</option>").prop('disabled',true);
        SiteChangeService('#site_sa', '#service_sa', '#emp_serviceallocation');
        $('#empserviceallocation').modal('show');
    });
    //Open Add Allocate Service Setup

    //Allocate Service
    $('#emp_serviceallocation').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            var FieldName = field.name;
            var fieldValue = field.value;
            if ((fieldValue == '' || fieldValue == null || fieldValue === 'undefined'))
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
                url: "/hr/allocateemp-service",
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
                                $('#empserviceallocation').modal('hide');
                                $('#view-allocatedservice').DataTable().ajax.reload();
                                $('#emp_serviceallocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#emp_serviceallocation')[0].reset();
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
                                $('#emp_serviceallocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#emp_serviceallocation')[0].reset();
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
    //Allocate Service

    // View Allocated Service
    var AllocateService =  $('#view-allocatedservice').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/hr/viewallocatedservice',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'orgName', name: 'orgName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'serviceName', name: 'serviceName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'serviceGroupName', name: 'serviceGroupName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'serviceTypeName', name: 'serviceTypeName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "150px"
            },
            {
                targets: 2,
                width: "150px"
            },
            {
                targets: 3,
                width: "150px"
            },
            {
                targets: 8,
                width: "250px"
            }
        ]
    });

    AllocateService.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    AllocateService.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    AllocateService.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Allocated Service

    // Update Allocated Service Status
    $(document).on('click', '.serviceallocation_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/hr/sa-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-allocatedservice').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });

    });
    // Update Allocated Service Status

    // Update Allocated Service Modal
    $(document).on('click', '.edit-serviceallocation', function() {
        var serviceallocationId = $(this).data('serviceallocation-id');
        $('#u_saemp').empty();
        $('#u_saservice').empty();
        $('#update_serviceallocation')[0].reset();
        $('#ajax-loader').show();
        var url = '/hr/serviceallocationmodal/' + serviceallocationId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var empName = response.empName;
                var empID = response.empID;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var serviceName = response.serviceName;
                var serviceId = response.serviceId;
                $('#u_saemp').val(empName);
                $('#u_saservice').html("<option selected value='"+serviceId+"'>" + serviceName + "</option>");
                $('.u_sa_id').val(response.id);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.usa_edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });

                fetchSiteServices(siteId, '#u_saservice', function(data) {
                    if (data && data.length > 0) {
                        $('#u_saservice').empty()
                            .append('<option selected disabled value="">Select Service</option>')
                            .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                            .prop('disabled', false)
                            .find('option:contains("Loading...")').remove();
                            $('#u_saservice').trigger('change');
                    } else {
                            Swal.fire({
                                text: 'Services are not Activated for selected Site',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-serviceallocation').modal('hide');
                                }
                            });
                    }
                });

                // $.ajax({
                //     url: 'services/getselectedservices',
                //     type: 'GET',
                //     data: {
                //         serviceID: serviceId,
                //     },
                //     beforeSend: function() {
                //         $('#u_saservice').append('<option>Loading...</option>');
                //     },
                //     success: function(resp) {
                //         $('#u_saservice').find('option:contains("Loading...")').remove();
                //         $.each(resp, function(key, value) {
                //             $('#u_saservice').append('<option value="' + value.id + '">' + value.name + '</option>');
                //         });
                //     },
                //     error: function(xhr, status, error) {
                //         console.log(error);
                //     }
                // });

                $('#edit-serviceallocation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Allocated Service Modal

    //Update Allocated Service
    $('#update_serviceallocation').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#update_serviceallocation')[0]);
        var Emp = $('#u_saemp').val();
        var Org = $('#u_saorg').val();
        var Site = $('#u_sasite').val();
        var Service = $('#u_saservice').val();
        var Id = $('.u_sa_id').val();

        formData.append('u_saemp', Emp);
        formData.append('u_saorg', Org);
        formData.append('u_sasite', Site);
        formData.append('u_saservice', Service);

        var url = '/hr/update-allocatedservice/' + Id;
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
                            $('#edit-serviceallocation').modal('hide');
                            $('#view-allocatedservice').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_serviceallocation')[0].reset();
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
    //Update Allocated Service

    //Add Service Location
    $('#add_servicelocation').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var org = $('#sl_org').val();
        var site = $('#sl_site').val();
        var status = $('#inv_status').val();
        data.push({ name: 'sl_org', value: org });
        data.push({ name: 'sl_site', value: site });
        data.push({ name: 'inv_status', value: status });
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
                url: "/services/addservicelocation",
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
                                $('#add-servicelocation').modal('hide');
                                $('#view-servicelocation').DataTable().ajax.reload();
                                $('#add_servicelocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_servicelocation')[0].reset();
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
                                $('#add_servicelocation').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_servicelocation')[0].reset();
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
    //Add Service Location

    // View Service Location
    var ServiceLocation =  $('#view-servicelocation').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/viewservicelocation',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'orgName', name: 'orgName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'siteName', name: 'siteName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
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

    ServiceLocation.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceLocation.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ServiceLocation.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Location

    // Update Service Location Status
    $(document).on('click', '.servicelocation', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/servicelocation-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicelocation').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Service Location Status

    // Update Service Location Modal
    $(document).on('click', '.edit-servicelocation', function() {
        var servicelocationId = $(this).data('servicelocation-id');
        $('#u_slorg').empty();
        $('#u_slsite').empty();
        $('#u_invstatus').empty();
        $('#update_servicelocation')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/servicelocationmodal/' + servicelocationId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var location = response.location;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var inventoryStatus = response.inventoryStatus;
                var inventoryStatusId = response.inventoryStatusId;
                $('#u_slorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_slsite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_invstatus').html("<option selected value='"+inventoryStatusId+"'>" + inventoryStatus + "</option>");
                var inventoryStatusNext = inventoryStatusId == '1' ? 'Disabled' : 'Enabled';
                var inventoryStatusNextid = response.inventoryStatusId == '1' ? 0 : 1;
                $('#u_invstatus').append('<option value="' + inventoryStatusNextid + '">' + inventoryStatusNext + '</option>');
                $('#u_sl').val(response.location);
                $('.servicelocation_id').val(servicelocationId);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.edt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_slorg', function(data) {
                    $('#u_slorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_slorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                fetchSites(orgID, '#u_slsite', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_slsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Sites are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-servicelocation').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },siteId);

                $('#u_slorg').off('change').on('change', function() {
                    $('#u_slsite').empty();
                    var organizationId = $(this).val();
                    fetchSites(organizationId, '#u_slsite', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_slsite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-servicelocation').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });
                });

                $('#edit-servicelocation').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Service Location Modal

    //Update Service Location
    $('#update_servicelocation').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#update_servicelocation')[0]);
        var Org = $('#u_slorg').val();
        var Site = $('#u_slsite').val();
        var InvStatus = $('#u_invstatus').val();
        var Id = $('.servicelocation_id').val();

        formData.append('u_slorg', Org);
        formData.append('u_slsite', Site);
        formData.append('u_invstatus', InvStatus);

        var url = '/services/update-servicelocation/' + Id;
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
                            $('#edit-servicelocation').modal('hide');
                            $('#view-servicelocation').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_servicelocation')[0].reset();
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
    //Update Service Location


    //Add Service Location Scheduling
    $('#add_locationscheduling').submit(function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var org = $('#ss_org').val();
        var site = $('#ss_site').val();
        var location = $('#ss_location').val();
        var schedulePattern = $('#ss_pattern').val();
        var emp = $('#ss_emp').val();
        data.push({ name: 'ss_org', value: org });
        data.push({ name: 'ss_site', value: site });
        data.push({ name: 'ss_location', value: location });
        data.push({ name: 'ss_pattern', value: schedulePattern });
        data.push({ name: 'ss_emp', value: emp });
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && ((field.name != 'total_patient') && (field.name != 'new_patient') && (field.name != 'followup_patient') && (field.name != 'routine_patient') && (field.name != 'urgent_patient') && (field.name != 'ss_emp')))
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
                url: "/services/addlocationscheduling",
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
                                $('#add-locationscheduling').modal('hide');
                                $('#view-locationscheduling').DataTable().ajax.reload();
                                $('#add_locationscheduling').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_locationscheduling')[0].reset();
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
                                $('#add_locationscheduling').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_locationscheduling')[0].reset();
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
    //Add Service Location Scheduling

    // View Service Location Scheduling
    var ServiceLocationScheduling =  $('#view-locationscheduling').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/viewlocationscheduling',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'locationName', name: 'locationName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'otherdetails', name: 'otherdetails' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "350px"
            },
            {
                targets: 3,
                width: "250px"
            },
            {
                targets: 5,
                width: "250px"
            }
        ]
    });

    ServiceLocationScheduling.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceLocationScheduling.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ServiceLocationScheduling.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Location Scheduling

    // Update Service Location Scheduling Status
    $(document).on('click', '.locationscheduling', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/locationscheduling-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-locationscheduling').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Service Location Scheduling Status

    // Update Service Location Scheduling Modal
    $(document).on('click', '.edit-locationscheduling', function() {
        var locationschedulingId = $(this).data('locationscheduling-id');
        $('#u_ssorg').empty();
        $('#u_sssite').empty();
        $('#u_sspattern').empty();
        $('#u_sslocation').empty();
        $('#u_ssemp').empty();
        $('#update_locationscheduling')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/locationschedulingmodal/' + locationschedulingId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                var name = response.name;
                var empName = response.empName;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var locationName = response.locationName;
                var locationId = response.locationId;
                var schedulePattern = response.schedulePattern;

                var startFormatted = moment.unix(response.startdateTime).format('MM/DD/YYYY h:mm A');
                var endFormatted = moment.unix(response.enddateTime).format('MM/DD/YYYY h:mm A');
                $('#day_time').data('daterangepicker').setStartDate(startFormatted);
                $('#day_time').data('daterangepicker').setEndDate(endFormatted);
                $('#u_ssorg').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_sssite').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_sslocation').html("<option selected value='"+locationId+"'>" + locationName + "</option>");
                var Pattern = schedulePattern.replace(/\b\w/g, function(match) {
                    return match.toUpperCase();
                });
                $('#u_sspattern').html("<option selected>" + Pattern + "</option>");

                if(empName != null)
                {
                    $('#u_ssemp').html("<option selected value='"+response.emp+"'>" + empName + "</option>");
                }
                $('.u_service_schedule').val(name);
                $('.u_total_patient').val(response.TotalPatientLimit);
                $('.u_new_patient').val(response.NewPatientLimit);
                $('.u_followup_patient').val(response.FollowUpPatientLimit);
                $('.u_routine_patient').val(response.RoutinePatientLimit);
                $('.u_urgent_patient').val(response.UrgentPatientLimit);
                $('.u_slocation_id').val(locationschedulingId);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_ssorg', function(data) {
                    $('#u_ssorg').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_ssorg').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                fetchSites(orgID, '#u_sssite', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_sssite').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Sites are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-locationscheduling').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },siteId);

                $('#u_ssorg').off('change').on('change', function() {
                    $('#u_sssite').empty();
                    var organizationId = $(this).val();
                    fetchSites(organizationId, '#u_sssite', function(data) {
                        if (data.length > 0) {
                            $('#u_sssite').html("<option selected disabled value=''>Select Site</option>");
                            $.each(data, function(key, value) {
                                $('#u_sssite').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-locationscheduling').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });
                });

                fetchServiceLocations(siteId, '#u_sslocation', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(locationId != value.id)
                            {
                                $('#u_sslocation').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                        $('#u_sslocation').find('option:contains("Loading...")').remove();
                        $('#u_sslocation').prop('disabled', false);
                    }
                    else{
                        Swal.fire({
                            text: 'Service Locations are not available for selected Site',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-locationscheduling').modal('hide');
                            }
                        });

                    }
                }, function(error) {
                    console.log(error);
                });

                $('#u_sssite').off('change').on('change', function() {
                    var siteId = $(this).val();
                    if (siteId) {
                        fetchServiceLocations(siteId, '#u_sslocation', function(data) {
                            if (data.length > 0) {
                                $('#u_sslocation').empty();
                                $('#u_sslocation').append('<option selected disabled value="">Select Service Location</option>');
                                $.each(data, function(key, value) {
                                    $('#u_sslocation').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                $('#u_sslocation').find('option:contains("Loading...")').remove();
                                $('#u_sslocation').prop('disabled', false);
                            }
                            else{
                                Swal.fire({
                                    text: 'Service Locations are not available for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-locationscheduling').modal('hide');
                                    }
                                });

                            }
                        }, function(error) {
                            console.log(error);
                        });
                    }
                });

                var SchedulePatternOptions = [
                    {value: "none", label: "None"},
                    {value: "daily", label: "Daily"},
                    {value: "weekly", label: "Weekly"},
                    {value: "monday to saturday", label: "Monday To Saturday"}
                ];

                var filteredOptions = SchedulePatternOptions.filter(function(option) {
                    return option.value !== schedulePattern;
                });

                filteredOptions.forEach(function(option) {
                    $("#u_sspattern").append(new Option(option.label, option.value));
                });

                if(response.emp != null)
                {
                    $('#u_ssemp').append('<option value="">N/A</option>');
                    fetchEmployees(response.emp, '#u_ssemp', function(data) {
                        $.each(data, function(key, value) {
                            $('#u_ssemp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }, function(error) {
                        console.log(error);
                    });
                }
                else{
                    $('#u_ssemp').append('<option value="">N/A</option>');
                    fetchEmployees(0, '#u_ssemp', function(data) {
                        $.each(data, function(key, value) {
                            $('#u_ssemp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });

                    }, function(error) {
                        console.log(error);
                    });
                }



                $('#edit-locationscheduling').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Service Location Scheduling Modal

    //Update Service Location Scheduling
    $('#update_locationscheduling').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#update_locationscheduling')[0]);
        var Org = $('#u_ssorg').val();
        var Site = $('#u_sssite').val();
        var Location = $('#u_sslocation').val();
        var SchedulePattern = $('#u_sspattern').val();
        var Emp = $('#u_ssemp').val();
        var Id = $('.u_slocation_id').val();

        formData.append('u_ssorg', Org);
        formData.append('u_sssite', Site);
        formData.append('u_sslocation', Location);
        formData.append('u_sspattern', SchedulePattern);
        formData.append('u_ssemp', Emp);

        var url = '/services/update-locationschedule/' + Id;
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
                        confirmButtonText: 'OK',
                        allowOutsideClick:false
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
                            $('#edit-locationscheduling').modal('hide');
                            $('#view-locationscheduling').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_locationscheduling')[0].reset();
                            // $('.text-danger').hide();
                        }
                        // $('.text-danger').hide();
                    });
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            }
        });
    });
    //Update Service Location Scheduling

    // Open Service Booking
    $(document).on('click', '.add-servicebooking', function() {
        $('#sb_emp').html("<option selected disabled value=''>Select Physician</option>").prop('disabled', true);
        $('#sb_schedule').html("<option selected disabled value=''>Select Service Location Schedule</option>").prop('disabled', true);
        $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
        $('#sb_mr').html("<option selected disabled value=''>Select MR #</option>").prop('disabled', true);
        $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        $('#sb_org').val('Select Organization').trigger('change');
        // Show Service Scheduling
        LocationChangeServiceScheduling('#sb_location', '#sb_site', '#sb_schedule', '#add_servicebooking');
        // Show Service Scheduling
        SiteChangeEmployees('#sb_site', '#sb_emp', '#add_servicebooking');
        // Show MR Code
        SiteChangeMRCode('#sb_site', '#sb_mr', '#add_servicebooking');
        // Show MR Code
        $('#add-servicebooking').modal('show');
    });
    // Open Service Booking

    //Add Service Booking
    $('#add_servicebooking').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });
        if(resp != false)
        {
            $.ajax({
                url: "/services/addservicebooking",
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
                                // $('#add-servicebooking').modal('hide');
                                // $('#sb_location').empty();
                                // $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                // $('#sb_site').empty();
                                // $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                // $('#view-servicebooking').DataTable().ajax.reload();
                                // $('#add_servicebooking').find('select').each(function(){
                                //     $(this).val($(this).find('option:first').val()).trigger('change');
                                // });
                                // $('#add_servicebooking')[0].reset();
                                // $('.text-danger').hide();
                                location.reload();
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
                                $('#sb_location').empty();
                                $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                $('#sb_site').empty();
                                $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#add_servicebooking').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_servicebooking')[0].reset();
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
    //Add Service Booking

    // View Service Booking
    var ServiceBooking =  $('#view-servicebooking').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/services/viewservicebooking',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'empName', name: 'empName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'locationName', name: 'locationName' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'mr_code', name: 'mr_code' ,render: function(data, type, row) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "400px"
            },
            {
                targets: 2,
                width: "150px"
            },
            {
                targets: 6,
                width: "250px"
            }
        ]
    });

    ServiceBooking.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    ServiceBooking.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    ServiceBooking.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Service Booking

    // Update Service Booking Status
    $(document).on('click', '.servicebooking', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/services/servicebooking-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-servicebooking').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Service Booking Status

    // Update Service Booking Modal
    $(document).on('click', '.edit-servicebooking', function() {
        var servicebookingId = $(this).data('servicebooking-id');
        $('#u_sb_org').empty();
        $('#u_sb_site').empty();
        $('#u_sb_location').empty();
        $('#u_sb_schedule').empty();
        $('#u_sb_emp').empty();
        $('#u_sbp_status').empty();
        $('#u_sbp_priority').empty();
        $('#update_servicebooking')[0].reset();
        $('#ajax-loader').show();
        var url = '/services/servicebookingmodal/' + servicebookingId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#ajax-loader').hide();
                console.log(response);
                var remarks = response.remarks;
                var orgName = response.orgName;
                var orgID = response.orgID;
                var siteName = response.siteName;
                var siteId = response.siteId;
                var locationName = response.locationName;
                var locationId = response.locationId;
                var locationScheduleId = response.locationScheduleId;
                var locationScheduleName = response.locationScheduleName;
                var MR = response.MRno;
                var PatientStatus = response.PatientStatus;
                var PatientPriority = response.PatientPriority;
                var empName = response.empName;
                var empId = response.empId;

                var startFormatted = moment.unix(response.startdateTime).format('MM/DD/YYYY h:mm A');
                var endFormatted = moment.unix(response.enddateTime).format('MM/DD/YYYY h:mm A');

                $('#u_sb_org').html("<option selected value='"+orgID+"'>" + orgName + "</option>");
                $('#u_sb_site').html("<option selected value='"+siteId+"'>" + siteName + "</option>");
                $('#u_sb_location').html("<option selected value='"+locationId+"'>" + locationName + "</option>");
                $('#u_sb_schedule').html("<option selected value='"+locationScheduleId+"'>" + locationScheduleName + " (StartTime: " + startFormatted + " EndTime: " + endFormatted + ")</option>");
                $('#u_sbp_status').html("<option selected value='"+PatientStatus+"'>" + capitalizeFirstLetterOfEachWord(PatientStatus) + "</option>");
                $('#u_sbp_priority').html("<option selected value='"+PatientPriority+"'>" + capitalizeFirstLetterOfEachWord(PatientPriority) + "</option>");
                $('#u_sb_emp').html("<option selected value='"+empId+"'>" + capitalizeFirstLetterOfEachWord(empName) + "</option>");
                $('#u_sb_mr').html("<option selected value='"+MR+"'>" + capitalizeFirstLetterOfEachWord(MR) + "</option>");

                $('.u_sb_remarks').val(remarks);
                $('.u_sbooking_id').val(servicebookingId);

                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });


                fetchOrganizations(orgID,orgName,'#u_sb_org', function(data) {
                    $('#u_sb_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        $('#u_sb_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                    });
                });

                fetchSites(orgID, '#u_sb_site', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#u_sb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'Sites are not available for selected Organization',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-servicebooking').modal('hide');
                            }
                        });
                    }
                }, function(error) {
                    console.log(error);
                },siteId);

                $('#u_sb_org').off('change').on('change', function() {
                    $('#u_sb_site').empty();
                    $('#u_sb_site').append('<option selected disabled value="">Select Site</option>');
                    var organizationId = $(this).val();
                    fetchSites(organizationId, '#u_sb_site', function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#u_sb_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Sites are not available for selected Organization',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#edit-servicebooking').modal('hide');
                                }
                            });
                        }
                    }, function(error) {
                        console.log(error);
                    });
                });

                fetchServiceLocations(siteId, '#u_sb_location', function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            if(locationId != value.id)
                            {
                                $('#u_sb_location').append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                        $('#u_sb_location').find('option:contains("Loading...")').remove();
                        $('#u_sb_location').prop('disabled', false);
                    }
                    else{
                        Swal.fire({
                            text: 'Service Locations are not available for selected Site',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#edit-servicebooking').modal('hide');
                            }
                        });

                    }
                }, function(error) {
                    console.log(error);
                });

                fetchServiceScheduling(locationId, siteId, '#u_sb_schedule', function(data) {
                    $.each(data, function(key, value) {
                        if(locationScheduleId != value.id)
                        {
                            const startDate = new Date(value.start_timestamp * 1000); // Convert to milliseconds
                            const endDate = new Date(value.end_timestamp * 1000); // Convert to milliseconds

                            const formattedStartDate = `${startDate.getMonth() + 1}-${startDate.getDate()}-${startDate.getFullYear()} ${startDate.getHours()}:${(startDate.getMinutes() < 10 ? '0' : '') + startDate.getMinutes()} ${startDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                            const formattedEndDate = `${endDate.getMonth() + 1}-${endDate.getDate()}-${endDate.getFullYear()} ${endDate.getHours()}:${(endDate.getMinutes() < 10 ? '0' : '') + endDate.getMinutes()} ${endDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                            $('#u_sb_schedule').append(
                                `<option value="${value.id}">${value.name} (StartTime: ${formattedStartDate} - EndTime: ${formattedEndDate})</option>`
                            );
                            // $('#u_sb_schedule').append('<option value="' + value.id + '">' + value.name + ' </option>');
                        }
                    });
                    $('#u_sb_schedule').find('option:contains("Loading...")').remove();
                    $('#u_sb_schedule').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });

                $('#u_sb_site').off('change').on('change', function() {
                    var siteId = $(this).val();
                    if (siteId) {
                        fetchServiceLocations(siteId, '#u_sb_location', function(data) {
                            if (data.length > 0) {
                                $('#u_sb_location').empty();
                                $('#u_sb_location').append('<option selected disabled value="">Select Service Location</option>');
                                $.each(data, function(key, value) {
                                    $('#u_sb_location').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                $('#u_sb_location').find('option:contains("Loading...")').remove();
                                $('#u_sb_location').prop('disabled', false);
                            }
                            else{
                                Swal.fire({
                                    text: 'Service Locations are not available for selected Site',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-servicebooking').modal('hide');
                                    }
                                });

                            }
                        }, function(error) {
                            console.log(error);
                        });
                    }
                });

                $('#u_sb_location').off('change').on('change', function() {
                    var locationId = $(this).val();
                    if (locationId) {
                        var siteId = $('#u_sb_site').val();
                        fetchServiceScheduling(locationId, siteId, '#u_sb_schedule', function(data) {
                            if (data.length > 0) {
                                $('#u_sb_schedule').empty();
                                $('#u_sb_schedule').append('<option selected disabled value="">Select Schedule</option>');
                                $.each(data, function(key, value) {
                                    const startDate = new Date(value.start_timestamp * 1000); // Convert to milliseconds
                                    const endDate = new Date(value.end_timestamp * 1000); // Convert to milliseconds

                                    const formattedStartDate = `${startDate.getMonth() + 1}-${startDate.getDate()}-${startDate.getFullYear()} ${startDate.getHours()}:${(startDate.getMinutes() < 10 ? '0' : '') + startDate.getMinutes()} ${startDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                                    const formattedEndDate = `${endDate.getMonth() + 1}-${endDate.getDate()}-${endDate.getFullYear()} ${endDate.getHours()}:${(endDate.getMinutes() < 10 ? '0' : '') + endDate.getMinutes()} ${endDate.getHours() >= 12 ? 'PM' : 'AM'}`;
                                    $('#u_sb_schedule').append(
                                        `<option value="${value.id}">${value.name} (StartTime: ${formattedStartDate} - EndTime: ${formattedEndDate})</option>`
                                    );
                                });
                                $('#u_sb_schedule').find('option:contains("Loading...")').remove();
                                $('#u_sb_schedule').prop('disabled', false);
                            }
                            else {
                                Swal.fire({
                                    text: 'Service Locations Schedules are not available for selected Service Location',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#edit-servicebooking').modal('hide');
                                    }
                                });
                            }

                        }, function(error) {
                            console.log(error);
                        });
                    }
                });

                fetchPhysicians(siteId, '#u_sb_emp', function(data) {
                    $.each(data, function(key, value) {
                        if(empId != value.id)
                        {
                            $('#u_sb_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                    $('#u_sb_emp').find('option:contains("Loading...")').remove();
                    $('#u_sb_emp').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });
                SiteChangeEmployees('#u_sb_site', '#u_sb_emp', '#update_servicebooking');

                fetchPatientMR(siteId, '#u_sb_mr', function(data) {
                    $.each(data, function(key, value) {
                        if(MR != value.mr_code)
                        {
                            $('#u_sb_mr').append('<option value="' + value.mr_code + '">' + value.mr_code + '</option>');
                        }
                    });
                    $('#u_sb_mr').find('option:contains("Loading...")').remove();
                    $('#u_sb_mr').prop('disabled', false);

                }, function(error) {
                    console.log(error);
                });
                SiteChangeMRCode('#u_sb_site', '#u_sb_mr', '#update_servicebooking');

                var patientStatusOptions = [
                    {value: "new", label: "New"},
                    {value:     "follow up", label: "Follow Up"}
                ];
                var patientStatus = patientStatusOptions.filter(function(option) {
                    return option.value !== PatientStatus;
                });
                patientStatus.forEach(function(option) {
                    $("#u_sbp_status").append(new Option(option.label, option.value));
                });

                var patientPriorityOptions = [
                    {value: "routine", label: "Routine"},
                    {value: "urgent", label: "Urgent"},
                    {value: "emergency", label: "Emergency"}

                ];
                var patientPriority = patientPriorityOptions.filter(function(option) {
                    return option.value !== PatientPriority;
                });
                patientPriority.forEach(function(option) {
                    $("#u_sbp_priority").append(new Option(option.label, option.value));
                });

                $('#edit-servicebooking').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Update Service Booking Modal

    //Update Service Booking
    $('#update_servicebooking').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData($('#update_servicebooking')[0]);
        var Org = $('#u_sb_org').val();
        var Site = $('#u_sb_site').val();
        var Location = $('#u_sb_location').val();
        var Schedule = $('#u_sb_schedule').val();
        var Physician = $('#u_sb_emp').val();
        var PatientStatus = $('#u_sbp_status').val();
        var PatientPriority = $('#u_sbp_priority').val();
        var Id = $('.u_sbooking_id').val();


        formData.append('u_sb_org', Org);
        formData.append('u_sb_site', Site);
        formData.append('u_sb_location', Location);
        formData.append('u_sb_schedule', Schedule);
        formData.append('u_sbp_status', PatientStatus);
        formData.append('u_sbp_priority', PatientPriority);
        formData.append('u_sb_emp', Physician);

        var url = '/services/update-servicebooking/' + Id;
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
                            $('#edit-servicebooking').modal('hide');
                            $('#view-servicebooking').DataTable().ajax.reload(); // Refresh DataTable
                            $('#update_servicebooking')[0].reset();
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
    //Update Service Booking
});
//HR Setup

//Inventory Setup
$(document).ready(function() {
    //Add Inventory Category
    $('#add_inventorycategory').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
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
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addinvcategory",
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
                                $('#add-inventorycategory').modal('hide');
                                $('#view-inventorycategory').DataTable().ajax.reload();
                                $('#add_inventorycategory')[0].reset();
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
                                $('#add_inventorycategory')[0].reset();
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
    //Add Inventory Category

    // View Inventory Category Data
    var viewinventoryCat =  $('#view-inventorycategory').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/inventorycategory',
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

    viewinventoryCat.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    // Show the loader before an AJAX request is made
    viewinventoryCat.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    // Hide the loader after the AJAX request is complete
    viewinventoryCat.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Category Data

    // Update Inventory Category Status
    $(document).on('click', '.inventorycategory_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invcat-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-inventorycategory').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Category Status

    //Update Inventory Category Modal
    $(document).on('click', '.edit-inventorycategory', function() {
        var InnventoryCatId = $(this).data('inventorycategory-id');
        var url = '/inventory/updateinventorycategory/' + InnventoryCatId;
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
                $('.ic-id').val(response.id);
                $('.u_invcat').val(response.name);
                $('#edit-inventorycategory').modal('show');
                $('#ajax-loader').hide();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Category Modal

    //Update Inventory Category
    $('#u_inventorycategory').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        var invCatId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'ic-id') {
                invCatId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-inventorycategory/' + invCatId;
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
                            $('#edit-inventorycategory').modal('hide');
                            $('#view-inventorycategory').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_inventorycategory')[0].reset();
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
    //Update Inventory Category
});
//Inventory Setup

$(document).ready(function() {
    //Add Inventory Sub Category
    $('#add_invsubcategory').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var SubCategory = $('#isc_catid').val();
        data.push({ name: 'isc_catid', value: SubCategory });
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
                "data": 'name',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'catName',
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
                $('#u_isc_catid').html("<option selected value="+ response.catId +">" + response.catName + "</option>");
                fetchInventoryCategory('inv_cat','#u_ssorg', function(data) {
                    $('#u_isc_catid').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_isc_catid').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
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
        var formData = $(this).serializeArray();
        var catId = $('#u_isc_catid').val();
        formData.push({ name: 'u_isc_catid', value: catId });
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

$(document).ready(function() {
    $('#it_subcat').html("<option selected disabled value=''>Select Item Sub Category</option>").prop('disabled', true);
    CategoryChangeSubCategory('#it_cat', '#it_subcat', '#add_invtype');

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
        ajax: '/inventory/invtype',
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
                "data": 'orgName',
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
                fetchInventoryCategory('inv_cat','#u_it_catid', function(data) {
                    $('#u_it_catid').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_it_catid').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
                fetchSelectedInventorySubCategory(response.catId,'#u_it_subcat', function(data) {
                    $('#u_it_subcat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        console.log(value.id);
                        console.log(response.subcatId);

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
        var formData = $(this).serializeArray();
        var cat = $('#u_it_catid').val();
        var subCat = $('#u_it_subcat').val();
        var org = $('#u_it_org').val();
        formData.push({ name: 'u_it_catid', value: cat });
        formData.push({ name: 'u_it_subcat', value: subCat });
        formData.push({ name: 'u_it_org', value: org });
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

$(document).ready(function() {
    $('#ig_subcat').html("<option selected disabled value=''>Select Item Sub Category</option>").prop('disabled', true);
    CategoryChangeSubCategory('#ig_cat', '#ig_subcat', '#add_invgeneric');
    // Showing Inventory Type
    SubCategoryChangeInventoryType('#ig_subcat', '#ig_type', '#add_invgeneric');
    // Showing Inventory Type

     //Open Add Inventory Generic Setup
    $(document).on('click', '.add-invgeneric', function() {
        $('#add-invgeneric').find('form').trigger('reset');
        $('#ig_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        $('#ig_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#ig_org', function(data) {
            $('#ig_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#ig_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-invgeneric').modal('show');
    });
    $(document).on('change', '#ig_org', function() {
        if($(this).val()) {
            $('.fields-container').slideDown();
        } else {
            $('.fields-container').slideUp();
        }
    });
    //Open Inventory Generic Setup



    //Add Inventory Generic
    $('#add_invgeneric').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        var data = $(this).serializeArray();
        var Category = $('#ig_cat').val();
        var SubCategory = $('#ig_subcat').val();
        var type = $('#ig_type').val();
        var Org = $('#ig_org').val();
        var consumptionType = $('#ig_consumptiontype').val();
        data.push({ name: 'ig_cat', value: Category });
        data.push({ name: 'ig_subcat', value: SubCategory });
        data.push({ name: 'ig_org', value: Org });
        data.push({ name: 'ig_type', value: type });
        data.push({ name: 'ig_consumptiontype', value: consumptionType });
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
                "data": 'orgName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'consumptionType', name: 'consumptionType' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "200px"
            },
            {
                targets: 8,
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
                fetchInventoryCategory('inv_cat','#u_ig_cat', function(data) {
                    $('#u_ig_cat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_ig_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
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


                let optionsHtml = '';
                if (response.consumptionType === 'g') {
                    optionsHtml += '<option selected value="g">General</option>';
                    optionsHtml += '<option value="p">Patient Only</option>';
                    optionsHtml += '<option value="b">Both</option>';
                } else if (response.consumptionType === 'p') {
                    optionsHtml += '<option selected value="p">Patient Only</option>';
                    optionsHtml += '<option value="g">General</option>';
                    optionsHtml += '<option value="b">Both</option>';
                } else if (response.consumptionType === 'b') {
                    optionsHtml += '<option selected value="b">Both</option>';
                    optionsHtml += '<option value="p">Patient Only</option>';
                    optionsHtml += '<option value="g">General</option>';
                }

                $('#u_ig_consumptiontype').html(optionsHtml);
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
        var formData = $(this).serializeArray();
        var cat = $('#u_ig_cat').val();
        var subCat = $('#u_ig_subcat').val();
        var org = $('#u_ig_org').val();
        var type = $('#u_ig_type').val();
        var consumptionType = $('#u_ig_consumptiontype').val();
        formData.push({ name: 'u_ig_cat', value: cat });
        formData.push({ name: 'u_ig_subcat', value: subCat });
        formData.push({ name: 'u_ig_org', value: org });
        formData.push({ name: 'u_ig_type', value: type });
        formData.push({ name: 'u_ig_consumptiontype', value: consumptionType });
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

$(document).ready(function() {
    $('#ib_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
    $('#ib_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
    CategoryChangeSubCategory('#ib_cat', '#ib_subcat', '#add_invbrand');
    SubCategoryChangeInventoryType('#ib_subcat', '#ib_type', '#add_invgeneric');
    TypeChangeInventoryGeneric('#ib_type', '#ib_generic', '#add_invgeneric');

    //Open Add Inventory Brand Setup
    $(document).on('click', '.add-invbrand', function() {
        $('#ib_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        $('#ib_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#ib_org', function(data) {
            $('#ib_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#ib_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-invbrand').modal('show');
    });
    $(document).on('change', '#ib_org', function() {
        if($(this).val()) {
            $('.fields-container-brand').slideDown();
        } else {
            $('.fields-container-brand').slideUp();
        }
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
                "data": 'genericName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'orgName',
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
                targets: 8,
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

                fetchInventoryCategory('inv_cat','#u_ib_cat', function(data) {
                    $('#u_ib_cat').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.catId != value.id)
                        {
                            $('#u_ib_cat').append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                });
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

$(document).ready(function() {
    // Showing Inventory Type
     OrgChangeSites('#itt_org', '#itt_site', '#add_invtransactiontype');

    //Open Add Inventory Transaction Type Setup
    $(document).on('click', '.add-invtransactiontype', function() {
        $('#itt_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
        $('#itt_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#itt_org', function(data) {
            $('#itt_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-invtransactiontype').modal('show');
    });
    //Open Inventory Transaction Type Setup

    //Add Inventory Transaction Type
    $('#add_invtransactiontype').submit(function(e) {
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
                url: "/inventory/addinvtransactiontype",
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
                                $('#add-invtransactiontype').modal('hide');
                                $('#view-invtransactiontype').DataTable().ajax.reload();
                                $('#add_invtransactiontype')[0].reset();
                                $('#add_invtransactiontype').find('select').each(function(){
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
                                $('#add_invtransactiontype').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_invtransactiontype')[0].reset();
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
    //Add Inventory Transaction Type

    // View Inventory Transaction Type Data
    var viewinvtransactiontype =  $('#view-invtransactiontype').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/invtransactiontype',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'transactionType',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'SoucrceTransactionAction',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'DestinationTransactionAction',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'orgName',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'siteName',
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
                width: "150px"
            },
            {
                targets: 6,
                width: "150px"
            },
            {
                targets: 8,
                width: "250px"
            }
        ]
    });

    viewinvtransactiontype.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewinvtransactiontype.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewinvtransactiontype.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Inventory Transaction Type Data

    // Update Inventory Transaction Type Status
    $(document).on('click', '.invtransactiontype_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/invtransactiontype-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-invtransactiontype').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Inventory Transaction Type Status

    //Update Inventory Transaction Type Modal
    $(document).on('click', '.edit-invtransactiontype', function() {
        var InnventoryTransactionTypeId = $(this).data('invtransactiontype-id');
        var url = '/inventory/updateinvtransactiontype/' + InnventoryTransactionTypeId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_itt-id').val(response.id);
                $('.u_description').val(response.name);
                $('#u_itt_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");
                $('#u_itt_site').html("<option selected value="+ response.siteId +">" + response.siteName + "</option>");

                fetchOrganizations('null', '','#u_itt_org', function(data) {
                    $('#u_itt_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_itt_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });

                fetchSites(response.orgId, '#u_itt_site', function(data) {
                    $.each(data, function(key, value) {
                        $('#u_itt_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }, function(error) {
                    console.log(error);
                },response.siteId);

                OrgChangeSites('#u_itt_org', '#u_itt_site', '#u_invtransactiontype');

                let transactiontypes = '';
                if (response.transactiontype === 'e') {
                    transactiontypes += '<option selected value="e">External</option>';
                    transactiontypes += '<option value="i">Internal</option>';
                    transactiontypes += '<option value="c">Consumption</option>';
                    transactiontypes += '<option value="r">Reversal</option>';
                } else if (response.transactiontype === 'i') {
                    transactiontypes += '<option selected value="i">Internal</option>';
                    transactiontypes += '<option value="e">External</option>';
                    transactiontypes += '<option value="c">Consumption</option>';
                    transactiontypes += '<option value="r">Reversal</option>';
                } else if (response.transactiontype === 'c') {
                    transactiontypes += '<option selected value="c">Consumption</option>';
                    transactiontypes += '<option value="i">Internal</option>';
                    transactiontypes += '<option value="e">External</option>';
                    transactiontypes += '<option value="r">Reversal</option>';
                } else if (response.transactiontype === 'r') {
                    transactiontypes += '<option selected value="r">Reversal</option>';
                    transactiontypes += '<option value="i">Internal</option>';
                    transactiontypes += '<option value="e">External</option>';
                    transactiontypes += '<option value="c">Consumption</option>';
                }
                $('#u_transaction_type').html(transactiontypes);

                let sourcetransactionActions = '';
                if (response.sourcetransactionAction === 'a') {
                    sourcetransactionActions += '<option selected value="a">Add</option>';
                    sourcetransactionActions += '<option value="s">Subtract</option>';
                    sourcetransactionActions += '<option value="r">Reversal</option>';
                    sourcetransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.sourcetransactionAction === 's') {
                    sourcetransactionActions += '<option selected value="s">Subtract</option>';
                    sourcetransactionActions += '<option value="a">Add</option>';
                    sourcetransactionActions += '<option value="r">Reversal</option>';
                    sourcetransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.sourcetransactionAction === 'r') {
                    sourcetransactionActions += '<option selected value="r">Reversal</option>';
                    sourcetransactionActions += '<option value="a">Add</option>';
                    sourcetransactionActions += '<option value="s">Subtract</option>';
                    sourcetransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.sourcetransactionAction === 'n') {
                    sourcetransactionActions += '<option selected value="n">Not Applicable</option>';
                    sourcetransactionActions += '<option value="a">Add</option>';
                    sourcetransactionActions += '<option value="s">Subtract</option>';
                    sourcetransactionActions += '<option value="r">Reversal</option>';
                }
                $('#u_sourcetransaction').html(sourcetransactionActions);

                let destinationtransactionActions = '';
                if (response.destinationtransactionAction === 'a') {
                    destinationtransactionActions += '<option selected value="a">Add</option>';
                    destinationtransactionActions += '<option value="s">Subtract</option>';
                    destinationtransactionActions += '<option value="r">Reversal</option>';
                    destinationtransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.destinationtransactionAction === 's') {
                    destinationtransactionActions += '<option selected value="s">Subtract</option>';
                    destinationtransactionActions += '<option value="a">Add</option>';
                    destinationtransactionActions += '<option value="r">Reversal</option>';
                    destinationtransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.destinationtransactionAction === 'r') {
                    destinationtransactionActions += '<option selected value="r">Reversal</option>';
                    destinationtransactionActions += '<option value="a">Add</option>';
                    destinationtransactionActions += '<option value="s">Subtract</option>';
                    destinationtransactionActions += '<option value="n">Not Applicable</option>';
                } else if (response.destinationtransactionAction === 'n') {
                    destinationtransactionActions += '<option selected value="n">Not Applicable</option>';
                    destinationtransactionActions += '<option value="a">Add</option>';
                    destinationtransactionActions += '<option value="s">Subtract</option>';
                    destinationtransactionActions += '<option value="r">Reversal</option>';
                }
                $('#u_destinationtransaction').html(destinationtransactionActions);

                $('#edit-invtransactiontype').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Inventory Transaction Type Modal

    //Update Inventory Transaction Type
    $('#u_invtransactiontype').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var invtransactiontypeId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_itt-id') {
                invtransactiontypeId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-invtransactiontype/' + invtransactiontypeId;
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
                            $('#edit-invtransactiontype').modal('hide');
                            $('#view-invtransactiontype').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_invtransactiontype')[0].reset();
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
    //Update Inventory Transaction Type
});

$(document).ready(function() {
    //Open Vendor Registration Setup
    $(document).on('click', '.add-vendorregistration', function() {
        $('#vendor_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#vendor_org', function(data) {
            $('#vendor_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#vendor_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-vendorregistration').modal('show');
    });
    //Open Vendor Registration Setup

    // //Add Vendor Registration
    $('#add_vendorregistration').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)) && (field.name != 'vendor_landline'))
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });

        if(resp != false)
        {
            $.ajax({
                url: "/inventory/addvendorregistration",
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
                                $('#add-vendorregistration').modal('hide');
                                $('#view-vendorregistration').DataTable().ajax.reload();
                                $('#add_vendorregistration')[0].reset();
                                $('#add_vendorregistration').find('select').each(function(){
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
                                $('#add_vendorregistration').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_vendorregistration')[0].reset();
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
    // //Add Vendor Registration

    // View Vendor Data
    var viewVendor =  $('#view-vendorregistration').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/vendorregistration',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'address',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            {
                "data": 'cell_no',
                "render": function(data, type, row) {
                    return data.replace(/\b\w/g, function(char) { return char.toUpperCase(); });
                }
            },
            { data: 'landline', name: 'landline' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "350px"
            },
            {
                targets: 2,
                width: "200px"
            },
            {
                targets: 6,
                width: "250px"
            }
        ]
    });

    viewVendor.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewVendor.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewVendor.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Vendor Data

    // Update Vendor Status
    $(document).on('click', '.vendor_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/vendor-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-vendorregistration').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Vendor Status

    //Update Vendor Registration Modal
    $(document).on('click', '.edit-vendor', function() {
        var vendorId = $(this).data('vendor-id');
        var url = '/inventory/updatevendorregistration/' + vendorId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var formattedDateTime = moment(response.effective_timestamp, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('.uedt').each(function() {
                    var edtElement = $(this);
                    edtElement.val(formattedDateTime);
                });
                $('.u_vendor-id').val(response.id);
                $('.u_vendor_desc').val(response.name);
                $('#u_vendor_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_vendor_org', function(data) {
                    $('#u_vendor_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_vendor_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('.u_vendor_address').val(response.address);
                $('.u_vendor_name').val(response.personName);
                $('.u_vendor_email').val(response.personEmail);
                $('.u_vendor_cell').val(response.cellNo);
                $('.u_vendor_landline').val(response.landlineNo);
                $('.u_vendor_remarks').val(response.remarks);

                $('#edit-vendorregistration').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Vendor Registration Modal

    //Update Vendor Registration
    $('#update_vendorregistration').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var vendorId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_vendor-id') {
                vendorId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-vendorregistration/' + vendorId;
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
                            $('#edit-vendorregistration').modal('hide');
                            $('#view-vendorregistration').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_vendorregistration')[0].reset();
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
    //Update Vendor Registration
});

$(document).ready(function() {
    //Open Medication Routes Setup
    $(document).on('click', '.add-medicationroutes', function() {
        $('#medicationroute_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#medicationroute_org', function(data) {
            $('#medicationroute_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#medicationroute_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-medicationroutes').modal('show');
    });
    //Open Medication Routes Setup

    //Add Medication Routes
    $('#add_medicationroutes').submit(function(e) {
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
                url: "/inventory/addmedicationroutes",
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
                                $('#add-medicationroutes').modal('hide');
                                $('#view-medicationroutes').DataTable().ajax.reload();
                                $('#add_medicationroutes')[0].reset();
                                $('#add_medicationroutes').find('select').each(function(){
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
                                $('#add_medicationroutes').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_medicationroutes')[0].reset();
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
    //Add Medication Routes

    // View Medication Routes Data
    var viewmedicationRoutes =  $('#view-medicationroutes').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/medicationroutes',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'orgName',
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
                width: "300px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    viewmedicationRoutes.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewmedicationRoutes.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewmedicationRoutes.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Medication Routes Data

    // Update Medication Routes Status
    $(document).on('click', '.medicationRoute_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/medicationroute-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-medicationroutes').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Medication Routes Status

    //Update Medication Routes Modal
    $(document).on('click', '.edit-medicationRoute', function() {
        var medicationRouteId = $(this).data('medicationroute-id');
        console.log(medicationRouteId);
        var url = '/inventory/updatemedicationroutes/' + medicationRouteId;
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
                $('.u_medicationroute-id').val(response.id);
                $('.u_medicationroute').val(response.name);
                $('#u_medicationroute_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_medicationroute_org', function(data) {
                    $('#u_medicationroute_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_medicationroute_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('#edit-medicationroutes').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Medication Routes Modal

    //Update Medication Routes
    $('#update_medicationroutes').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var medicationRouteId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_medicationroute-id') {
                medicationRouteId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-medicationRoute/' + medicationRouteId;
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
                            $('#edit-medicationroutes').modal('hide');
                            $('#view-medicationroutes').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_medicationroutes')[0].reset();
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
    //Update Medication Routes
});

$(document).ready(function() {
    //Open Medication Frequency Setup
    $(document).on('click', '.add-medicationfrequency', function() {
        $('#medicationfrequency_org').html("<option selected disabled value=''>Select Organization</option>");
        fetchOrganizations('null', '','#medicationfrequency_org', function(data) {
            $('#medicationfrequency_org').find('option:contains("Loading...")').remove();
            $.each(data, function(key, value) {
                $('#medicationfrequency_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
            });
        });
        $('#add-medicationfrequency').modal('show');
    });
    //Open Medication Frequency Setup

    //Add Medication Frequency
    $('#add_medicationfrequency').submit(function(e) {
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
                url: "/inventory/addmedicationfrequency",
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
                                $('#add-medicationfrequency').modal('hide');
                                $('#view-medicationfrequency').DataTable().ajax.reload();
                                $('#add_medicationfrequency')[0].reset();
                                $('#add_medicationfrequency').find('select').each(function(){
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
                                $('#add_medicationfrequency').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_medicationfrequency')[0].reset();
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
    //Add Medication Frequency

    // View Medication Frequency Data
    var viewmedicationFrequency =  $('#view-medicationfrequency').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory/medicationfrequency',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            {
                "data": 'orgName',
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
                width: "300px"
            },
            {
                targets: 4,
                width: "250px"
            }
        ]
    });

    viewmedicationFrequency.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewmedicationFrequency.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewmedicationFrequency.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Medication Frequency Data

    // Update Medication Frequency Status
    $(document).on('click', '.medicationfrequency_status ', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};

        $.ajax({
            url: '/inventory/medicationfrequency-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-medicationfrequency').DataTable().ajax.reload();
                }
                },
                error: function(xhr, status, error) {
                    console.log(error);
            }
        });
    });
    // Update Medication Frequency Status

    //Update Medication Frequency Modal
    $(document).on('click', '.edit-medicationfrequency', function() {
        var medicationFrequencyId = $(this).data('medicationfrequency-id');
        var url = '/inventory/updatemedicationfrequency/' + medicationFrequencyId;
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
                $('.u_medicationfrequency-id').val(response.id);
                $('.u_medicationfrequency').val(response.name);
                $('#u_medicationfrequency_org').html("<option selected value="+ response.orgId +">" + response.orgName + "</option>");

                fetchOrganizations('null', '','#u_medicationfrequency_org', function(data) {
                    $('#u_medicationfrequency_org').find('option:contains("Loading...")').remove();
                    $.each(data, function(key, value) {
                        if(response.orgId != value.id)
                        {
                            $('#u_medicationfrequency_org').append('<option value="' + value.id + '">' + value.organization + '</option>');
                        }
                    });
                });
                $('#edit-medicationfrequency').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Medication Frequency Modal

    //Update Medication Frequency
    $('#update_medicationfrequency').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var medicationFrequencyId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'u_medicationfrequency-id') {
                medicationFrequencyId = formData[i].value;
                break;
            }
        }
        var url = 'inventory/update-medicationfrequency/' + medicationFrequencyId;
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
                            $('#edit-medicationfrequency').modal('hide');
                            $('#view-medicationfrequency').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_medicationfrequency')[0].reset();
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
    //Update Medication Frequency


});

$(document).ready(function() {
    //Patient Registration
    $('#patient_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
    OrgChangeSites('#patient_org', '#patient_site', '#add_patient');
    $('#patient_division').html("<option selected disabled value=''>Select Division</option>").prop('disabled', true);
    ProvinceChangeDivision('#patient_province', '#patient_division', '#add_patient');
    $('#patient_district').html("<option selected disabled value=''>Select District</option>").prop('disabled', true);
    DivisionChangeDistrict('#patient_division', '#patient_district', '#add_patient');
    // Add Patient
    $('#add_patient').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($('#add_patient')[0]);
        var relation = $('#relation').val();
        var GuardianRelation = $('#guardian_relation').val();
        var religion = $('#religion').val();
        var maritalStatus = $('#marital_status').val();
        var Gender = $('#patient_gender').val();
        var Org = $('#patient_org').val();
        var Site = $('#patient_site').val();
        var Province = $('#patient_province').val();
        var Division = $('#patient_division').val();
        var District = $('#patient_district').val();

        var imgValue = $('#patient_img')[0].files[0];
        formData.append('relation', relation);
        formData.append('guardian_relation', GuardianRelation);
        formData.append('religion', religion);
        formData.append('marital_status', maritalStatus);
        formData.append('patient_gender', Gender);
        formData.append('patient_org', Org);
        formData.append('patient_site', Site);
        formData.append('patient_province', Province);
        formData.append('patient_division', Division);
        formData.append('patient_district', District);
        formData.append('patient_img', imgValue);

        var resp = true;
        const excludedFields = ['old_mrcode', 'patient_additionalcell', 'patient_landline', 'patient_email', 'patient_img'];
        var firstErrorElement = null;

        formData.forEach(function(value, key) {
            var fieldName = key;
            var fieldValue = value;
            if (((fieldValue == '') || (fieldValue == 'null') || (fieldValue === 'undefined')) && !excludedFields.includes(fieldName))
            {
                var FieldName = fieldName;
                var FieldID = '#'+FieldName + "_error";

                if (!firstErrorElement) {
                    firstErrorElement = FieldName;
                }
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
        // If we found an error element, focus on it
        if (firstErrorElement) {
            $('input[name="' + firstErrorElement + '"], textarea[name="' + firstErrorElement + '"], select[name="' + firstErrorElement + '"]').focus();
        }
        if(resp != false)
        {
            $.ajax({
                url: "/patient/addpatient",
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
                    // if (fieldName == 'error')
                    if (response.error)
                    {
                        Swal.fire({
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        })
                    }
                    if (response.success)
                    {
                        Swal.fire({
                            text: response.success,
                            icon: 'success',
                            allowOutsideClick: false,
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Register Only',
                            denyButtonText: 'Register & Confirm Arrival',
                            cancelButtonText: 'Register & Book Appointment'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#add-patient').modal('hide');
                                $('#view-patient').DataTable().ajax.reload(); // Refresh DataTable
                                $('#add_patient').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patient')[0].reset();
                                $('.text-danger').hide();
                            }
                            else if (result.isDenied) {
                                $('#add-patient').modal('hide');
                                $('#enterMR').val(response.mr_code).prop('disabled',true).keyup();
                                $('#add-patientinout').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                }).modal('show');
                            }
                            else {
                                $('#add-patient').modal('hide');
                                $('#pOrg').text(response.orgName);
                                $('#pSite').text(response.siteName);
                                $('#pMrno').text(response.mr_code);
                                $('.pb_org').val(response.org_id);
                                $('.pb_site').val(response.site_id);
                                $('.pb_mr').val(response.mr_code);
                                $('#sb_schedule').html("<option selected disabled value=''>Select Service Location Schedule</option>").prop('disabled', true);

                                fetchServiceLocations(response.site_id, '#sb_location', function(data) {
                                    const $serviceLocation = $('#sb_location');
                                    if (data && data.length > 0) {
                                        $serviceLocation.empty()
                                            .append('<option selected disabled value="">Select Service Location</option>')
                                            .append(data.map(({id, name}) => `<option value="${id}">${name}</option>`).join(''))
                                            .prop('disabled', false)
                                            .find('option:contains("Loading...")').remove();
                                    } else {
                                        Swal.fire({
                                            text: 'Service Locations are not available for selected Site',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $('#add-servicebooking').modal('hide');
                                                $('#view-patient').DataTable().ajax.reload(); // Refresh DataTable
                                                $('#add_patient').find('select').each(function(){
                                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                                });
                                                $('#add_patient')[0].reset();
                                                $('.text-danger').hide();
                                            }
                                        });
                                    }
                                });
                                // Show Service Scheduling
                                LocationChangeServiceScheduling('#sb_location', '#sb_site', '#sb_schedule', '#add_servicebooking');
                                // Show Service Scheduling
                                fetchPhysicians(response.site_id, '#sb_emp', function(data) {
                                    $('#sb_emp').html("<option selected disabled value=''>Select Designated Physician</option>");
                                    $.each(data, function(key, value) {
                                        $('#sb_emp').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    });
                                    $('#sb_emp').find('option:contains("Loading...")').remove();
                                    $('#sb_emp').prop('disabled', false);
                                }, function(error) {
                                    console.log(error);
                                });
                                $('#add-servicebooking').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                }).modal('show');

                            }
                        });
                    }
                    if (response.info)
                    {
                        Swal.fire({
                            text: response.info,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var imgReset = $('#patient_img').dropify();
                                imgReset = imgReset.data('dropify');
                                imgReset.resetPreview();
                                imgReset.clearElement();
                                $('#add_patient').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patient')[0].reset();
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
    // Add Patient

    // View Patient
    var viewpatient =  $('#view-patient').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/patient/patientdata',
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
            { data: 'cnic', name: 'cnic' },
            { data: 'cell_no', name: 'cell_no' },
            { data: 'address', name: 'address' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "250px"
            },
            {
                targets: 7,
                width: "350px"
            }
        ]
    });

    viewpatient.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewpatient.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewpatient.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Patient

    // Patient Details
    $(document).on('click', '.patient-detail', function() {
        var patientId = $(this).data('patient-id');
        var url = '/patient/patientdetail/' + patientId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                $('#patient-detail').modal('show');
                $('#ajax-loader').hide();
                var imgPath = response.Image;
                $('#patientImg').attr('src', imgPath);
                $('#patientName').text(response.patientName);
                $('#patientAddress').text(response.patientAddress);
                $('#mr_no').text(response.MR);
                $('#guardianName').text(response.patientGuardianName);
                $('#guardianRelation').text(response.patientGuardianRelation);
                $('#nextofKin').text(response.patientNextOfKin);
                $('#nextofkinRelation').text(response.patientNextOfKinRelation);
                $('#patientLanguage').text(response.Language);
                $('#patientReligion').text(response.Religion);
                $('#patientMaritalStatus').text(response.MaritalStatus);
                $('#patientoldMR').text(response.oldMR);
                $('#patientGender').text(response.Gender);
                $('#patientDOB').text(response.DateOfBirth);
                $('#patientOrg').text(response.Org);
                $('#patientSite').text(response.Site);
                $('#patientProvince').text(response.Province);
                $('#patientDivision').text(response.Division);
                $('#patientDistrict').text(response.District);
                $('#patientCNIC').text(response.CNIC);
                $('#patientFamilyNo').text(response.familyNo);
                $('#patientCell').text(response.CellNo);
                $('#patientAdditionalCell').text(response.AdditionalCellNo);
                $('#patientLandline').text(response.Landline);
                $('#patientEmail').text(response.Email);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    // Patient Details
});
//Patient Registration

//Patient Arrival & Departure
$(document).ready(function() {
    //Open Patient Arrival & Departure Setup
    $(document).on('click', '.add-patientinout', function() {
        $('#booking-status').hide();
        $('#patientArrivalDetails').hide();
        $("#pio_remarks").hide();
        $('#enterMR').val('');
        $('#pio_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
        OrgChangeSites('#pio_org', '#pio_site', '#add_patientinout');
        $('#pio_service').html("<option selected disabled value=''>Select Service</option>").prop('disabled',true);
        SiteChangeService('#pio_site', '#pio_service', '#add_patientinout');
        $('#pio_serviceMode').html("<option selected disabled value=''>Select Service Mode</option>").prop('disabled',true);
        ServiceChangeServiceModes('#pio_site', '#pio_service', '#pio_serviceMode', '#add_patientinout');
        $('#pio_billingCC').html("<option selected disabled value=''>Select Billing Cost Center</option>").prop('disabled',true);
        ServiceChangeCostCenter('#pio_site', '#pio_service', '#pio_billingCC', '#add_patientinout');
        SiteChangeServiceLocation('#pio_site', '#pio_serviceLocation', '#add_patientinout');
        LocationChangeServiceScheduling('#pio_serviceLocation', '#pio_site', '#pio_serviceSchedule', '#add_patientinout');
        $('#pio_emp').html("<option selected disabled value=''>Select Designated Physician</option>").prop('disabled',true);
        SiteChangeEmployees('#pio_site', '#pio_emp', '#add_patientinout');
        $('#add-patientinout').modal('show');
    });
    var typingTimer;
    var doneTypingInterval = 200;
    var minLength = 9;
    $('#enterMR').keyup(function() {
        clearTimeout(typingTimer);
        if ($('#enterMR').val().length >= minLength) {
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        }
        else{
            $("#pio_mr_error").text("At least 9 characters are required for the MR #");
            $("#booking-status").hide();
            $("#patientArrivalDetails").hide();
            $('#ajax-loader').hide();
        }
    });
    $('#enterMR').keydown(function() {
        clearTimeout(typingTimer);
    });
    //Open Patient Arrival & Departure Setup

    //Add Patient Arrival & Departure
    $('#add_patientinout').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        var resp = true;
        $(data).each(function(i, field){
            if (((field.value == '') || (field.value == null)))
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
                $( 'textarea[name= "' +FieldName +'"' ).focus(function() {
                    $(FieldID).text("");
                    $('textarea[name= "' +FieldName +'"' ).removeClass("requirefield");
                })
                $( 'textarea[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });
        if(resp != false)
        {
            $.ajax({
                url: "/patient/addpatientarrival",
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
                                $('#add-patientinout').modal('hide');
                                // $('#sb_location').empty();
                                // $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                // $('#sb_site').empty();
                                // $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#view-patientinout').DataTable().ajax.reload();
                                $('#add_patientinout').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_patientinout')[0].reset();
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
                                $('#sb_location').empty();
                                $('#sb_location').html("<option selected disabled value=''>Select Service Location</option>").prop('disabled', true);
                                $('#sb_site').empty();
                                $('#sb_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', true);
                                $('#add_servicebooking').find('select').each(function(){
                                    $(this).val($(this).find('option:first').val()).trigger('change');
                                });
                                $('#add_servicebooking')[0].reset();
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
    //Add Patient Arrival & Departure

    // View Patient Arrival & Departure
    var viewpatient =  $('#view-patientinout').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/patient/patientarrivaldeparture',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'bookingdetails', name: 'bookingdetails' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "350px"
            },
            {
                targets: 2,
                width: "350px"
            }
        ]
    });

    viewpatient.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    viewpatient.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    viewpatient.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });
    // View Patient Arrival & Departure

    // Update Patient Arrival & Departure Status
    $(document).on('click', '.pio_status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var data = {id: id,status: status};
        $.ajax({
            url: '/patient/patientinout-status',
            method: 'GET',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {
            var status = xhr.status;
                if(status == 200)
                {
                    $('#view-patientinout').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });

    });
    // Update Patient Arrival & Departure Status

    // End Patient Arrival & Departure
    $(document).on('click', '#endService', function() {
        $('#endServiceModal').modal('show');
        $("#date-format1").val("");
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm A' });
        var id = $(this).data('id');
        $('#pio_id').val(id);
    });

    $('#end_service').submit(function(e) {
        e.preventDefault();
        var data = SerializeForm(this);
        $.ajax({
            url: '/patient/serviceend',
            method: 'POST',
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
            },
            success: function(response,textStatus, xhr) {


                for (var fieldName in response) {
                    var fieldErrors = response[fieldName];
                }
                if (fieldName == 'success')
                {
                    $('#ajax-loader').hide();
                    Swal.fire({
                        text: fieldErrors,
                        icon: fieldName,
                        allowOutsideClick: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#add-patientinout').modal('hide');
                            $('#view-patientinout').DataTable().ajax.reload();
                            $('.text-danger').hide();
                            $('#endServiceModal').modal('hide');
                        }
                    });
                }
                else if (fieldName == 'error')
                {
                    $( 'input[name= "pio_serviceEnd"' ).addClass('requirefield');
                    $( 'input[name= "pio_serviceEnd"' ).focus(function() {
                        $('#pio_serviceEnd_error').text("");
                        $('input[name= "pio_serviceEnd"' ).removeClass("requirefield");
                    })
                    $('#pio_serviceEnd_error').text(fieldErrors);
                    $('#ajax-loader').hide();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // End Patient Arrival & Departure

    //Update Patient Arrival & Departure Modal
    $(document).on('click', '.edit-patientinout', function() {
        var patientinoutId = $(this).data('patientinout-id');
        var url = '/patient/updatepatientinout/' + patientinoutId;
        $('#ajax-loader').show();
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#patientinout_id').val(patientinoutId);
                $('#u_pio_org').html("<option selected value="+response.orgId+">" + response.orgName + "</option>").prop('disabled',true);
                $('#u_pio_site').html("<option selected value="+response.siteId+">" + response.siteName + "</option>").prop('disabled',true);
                $('#u_pio_status').html("<option selected>" + response.patientStatus + "</option>").prop('disabled',true);
                $('#u_pio_priority').html("<option selected>" + response.patientPriority + "</option>").prop('disabled',true);
                $('#u_pio_location').html("<option selected>" + response.locationName + "</option>").prop('disabled',true);
                $('#u_pio_schedule').html("<option selected>" + response.locationSchedule + "</option>").prop('disabled',true);
                $('#u_pio_emp').html("<option selected>" + response.empName + "</option>").prop('disabled',true);
                var startFormatted = moment.unix(response.start_timestamp).format('MM/DD/YYYY h:mm A');
                var endFormatted = moment.unix(response.end_timestamp).format('MM/DD/YYYY h:mm A');
                $('#u_pio_scheduleDatetime').data('daterangepicker').setStartDate(startFormatted);
                $('#u_pio_scheduleDatetime').data('daterangepicker').setEndDate(endFormatted);
                $('#u_pio_scheduleDatetime').prop('disabled',true);
                $('#u_pio_mr').val(response.mrNo).prop('disabled',true);

                var formattedstartTime = moment(response.StartTime, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                $('#u_pio_serviceStart').val(formattedstartTime);

                if(response.EndTime != null)
                {
                    var formattedendTime = moment(response.EndTime, 'dddd DD MMMM YYYY - hh:mm A').format('dddd DD MMMM YYYY - hh:mm A');
                    $('#u_pio_serviceEnd').val(formattedendTime);
                }
                else{
                    $('#u_pio_serviceEnd').attr('placeholder', 'Select Service End Time');
                }

                var serviceSelector = '#u_pio_service';
                fetchSiteServices(response.siteId, serviceSelector, function(data) {
                    const $Service = $(serviceSelector);
                    $Service.empty()
                    .append('<option selected value='+ response.serviceID +'>'+ response.serviceName +'</option>')
                    .append(
                        data.map(({ id, name }) => {
                            if (id != response.serviceID) {
                                return `<option value="${id}">${name}</option>`;
                            }
                        }).join(''))
                    .prop('disabled', false)
                    .find('option:contains("Loading...")').remove();
                    $Service.trigger('change');
                });
                var serviceModeSelector = '#u_pio_serviceMode';
                fetchSiteServiceMode(response.siteId, response.serviceID, serviceModeSelector, function(data) {
                const $ServiceMode = $(serviceModeSelector);
                    $ServiceMode.empty()
                        .append('<option selected value='+ response.serviceModeID +'>'+ response.service_modeName +'</option>')
                        .append(
                        data.map(({ id, name }) => {
                            if (id != response.serviceModeID) {
                                return `<option value="${id}">${name}</option>`;
                            }
                        }).join(''))
                        .prop('disabled', false)
                        .find('option:contains("Loading...")').remove();
                        $ServiceMode.trigger('change');
                });
                var CCSelector = '#u_pio_billingCC';
                fetchServiceCostCenter(response.siteId, response.serviceID, CCSelector, function(data) {
                    const $CostCenter = $(CCSelector);
                    if (data && data.length > 0) {
                        $CostCenter.empty()
                             .append('<option selected value='+ response.CCID +'>'+ response.CCName +'</option>')
                             .append(
                                data.map(({ id, name }) => {
                                    if (id != response.CCID) {
                                        return `<option value="${id}">${name}</option>`;
                                    }
                                }).join(''))
                             .prop('disabled', false)
                             .find('option:contains("Loading...")').remove();
                             $CostCenter.trigger('change');
                    } else {
                            Swal.fire({
                                text: 'Billing Cost Centers are not Activated for selected Site & Service',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $CostCenter.prop('disabled', true);
                                }
                            });
                    }
                });
                $('#u_pio_payMode').html("<option selected>" + response.paymentMode + "</option>");
                var PaymentMode = [
                    {value: "Cash", label: "Cash"},
                    {value: "Card", label: "Card"}
                ];
                var PaymentMode = PaymentMode.filter(function(option) {
                    return option.value !== response.paymentMode;
                });
                PaymentMode.forEach(function(option) {
                    $("#u_pio_payMode").append(new Option(option.label, option.value));
                });

                $('#u_pio_amount').val(response.Amount);

                $('#edit-patientinout').modal('show');
                $('#ajax-loader').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    //Update Patient Arrival & Departure Modal


    //Update Patient Arrival & Departure
    $('#update_patientinout').on('submit', function (event) {
        event.preventDefault();
        var formData = SerializeForm(this);
        var patientInOutId;
        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'patientinout_id') {
                patientInOutId = formData[i].value;
                break;
            }
        }
        var url = 'patient/update-patientinout/' + patientInOutId;
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
                            $('#edit-patientinout').modal('hide');
                            $('#view-patientinout').DataTable().ajax.reload(); // Refresh DataTable
                            $('#u_patientinout')[0].reset();
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
    //Update Patient Arrival & Departure

});
//Patient Arrival & Departure

//END
