// Outsourced Services
$(document).ready(function() {
    // Show Add Outsourced Service Modal
    $(document).on('click', '.add-outsourcedservice', function() {
        var OrgId = $('#os_org').val();
        if(OrgId)
        {
            fetchOrganizationSites(OrgId, '#os_site', function(data) {
                $('#os_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled', false);
                $.each(data, function(key, value) {
                    $('#os_site').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });  

        }
        else{
            $('#os_site').html("<option selected disabled value=''>Select Site</option>").prop('disabled',true);
            OrgChangeSites('#os_org', '#os_site', null);

            $('#os_referralsite').html("<option selected disabled value=''>Select Referral Site</option>").prop('disabled',true);
            OrgChangeReferralSites('#os_org', '#os_referralsite', '#add_outsourcedservice');
            
        }

        // $('#pio_service').html("<option selected disabled value=''>Select Service</option>").prop('disabled', true);
        // SiteChangeService('#pio_site', '#pio_service', '#add_patientinout');

        $('#add-outsourcedservice').modal('show');
    });

    // Add Outsourced Service Form Submission
    // $('#add_outsourced_service').submit(function(e) {
    //     e.preventDefault();
    //     var data = $(this).serialize();
    //     var resp = true;
    //     $(this).find('input, select, textarea').each(function() {
    //         var name = $(this).attr('name');
    //         var value = $(this).val();
    //         if ((value === '' || value === null) && name !== 'remarks') {
    //             resp = false;
    //             // Optionally, show error messages here
    //         }
    //     });
    //     if (resp) {
    //         $.ajax({
    //             url: '/outsourced-services/add', // Update this URL as needed
    //             method: 'POST',
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             data: data,
    //             beforeSend: function() {
    //                 // Optionally, show a loading indicator
    //             },
    //             success: function(response) {
    //                 // Handle success (show message, reset form, close modal, etc.)
    //                 if (response.success) {
    //                     $('#add-outsourcedservice').modal('hide');
    //                     $('#add_outsourced_service')[0].reset();
    //                     // Optionally, reload table or show a success message
    //                 } else if (response.error) {
    //                     // Optionally, show error message
    //                 }
    //             },
    //             error: function(xhr) {
    //                 // Optionally, handle validation errors
    //             }
    //         });
    //     }
    // });
});
// Outsourced Services 