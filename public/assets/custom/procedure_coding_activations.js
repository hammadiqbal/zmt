$(document).ready(function() {
// Procedure Coding Activation Setup
    var ProcedureCoding =  $('#view-activatedprocedures').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/activation/viewprocedurecoding',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_raw', name: 'id_raw', visible: false },
            { data: 'id', name: 'id' },
            { data: 'medical_codes', name: 'medical_codes' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 1,
                width: "300px"
            },
            {
                targets: 2,
                width: "300px"
            },
            {
                targets: 3,
                width: "300px"
            }
        ]
    });
    ProcedureCoding.on('draw.dt', function() {
        $('[data-toggle="popover"]').popover({
            html: true
        });
    });
    ProcedureCoding.on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });
    ProcedureCoding.on('xhr.dt', function() {
        $('#ajax-loader').hide();
    });


    $(document).on('click', '.assign-medicalcodes', function() {
        var serviceId = $(this).data('service-id');
        var orgId = $(this).data('org-id');
        FetchProcedureMedicalCoding(serviceId,orgId);
        $('#pc_service_id').val(serviceId);
        $('#pc_org_id').val(orgId);
        $('#assign-medicalcodes').modal('show');
        
    });

    $('#mapped_medicalcodes').on('submit', function (event) {
        event.preventDefault();
        var selectedMCIds = [];
        $('input[name="selectedMC[]"]:checked').each(function() {
            selectedMCIds.push($(this).data('id'));
        });
        $('input[name="mc_value[]"]').val(selectedMCIds.join(','));
        var formData = SerializeForm(this);
        var url = '/activation/activateprocedurcoding';
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
                            $('#assign-medicalcodes').modal('hide');
                            $('#view-activatedprocedures').DataTable().ajax.reload();
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
// Procedure Coding Activation Setup
});