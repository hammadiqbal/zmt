$(document).ready(function() {
    
    // Initialize date picker for separate start and end inputs
    $('#date-range input[name="start"]').datepicker({
        format: 'mm/dd/yyyy',
        autoclose: true
    });
    
    $('#date-range input[name="end"]').datepicker({
        format: 'mm/dd/yyyy',
        autoclose: true
    });
    
    $('#ir_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
    $('#ir_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
    $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
    $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
    
    // Handle Sites multi-select behavior for selectpicker
    $('#ir_site').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allSitesValue = '0101';
        
        // If no sites are selected, select "All Sites"
        if (!selectedValues || selectedValues.length === 0) {
            $(this).selectpicker('val', [allSitesValue]);
            $(this).selectpicker('refresh');
            return;
        }
        
        // If "All Sites" is selected along with other sites, remove "All Sites"
        if (selectedValues.includes(allSitesValue) && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== allSitesValue;
            });
            $(this).selectpicker('val', newValues);
            $(this).selectpicker('refresh');
        }
    });
    
    // Clear filter functionality
    $('.clearFilter').click(function() {
        $('#ajax-loader').show();

        $('#ir_cat').val('Select Category').trigger('change');
        // $('#ir_subcat').val('').trigger('change');
        $('#ir_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
        $('#ir_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
        $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
        $('#ir_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled', true);
        
        // Reset sites to "All Sites" only
        $('#ir_site').selectpicker('val', ['0101']);
        $('#ir_site').selectpicker('refresh');
        
        $('#ajax-loader').hide();

        // $('#ir_type').val('').trigger('change');
        // $('#ir_generic').val('').trigger('change');
        // $('#ir_brand').val('').trigger('change');
    });
  
    // Use existing functions for cascading dropdowns
    CategoryChangeSubCategory('#ir_cat', '#ir_subcat', '#inv_report');
    SubCategoryChangeInventoryType('#ir_subcat', '#ir_type', '#inv_report');
    TypeChangeInventoryGeneric('#ir_type', '#ir_generic', '#inv_report');
    GenericChangeBrand('#ir_generic', '#ir_brand', '#inv_report');

    //Inventory Report Form Submission
    $('#inv_report').submit(function(e) {
        e.preventDefault();
        $('#ajax-loader').show();
        var data = SerializeForm(this);
        var resp = true;
        
        // Validate required fields
        $(data).each(function(i, field){
            // Skip validation for start and end dates as they are handled separately
            if (field.name === 'start' || field.name === 'end') {
                return;
            }
            
            if ((field.value == '') || (field.value == null))
            {
                var FieldName = field.name;
                var FieldID = '#'+FieldName + "_error";
                $(FieldID).text("This field is required");
                $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
                resp = false;
            }
        });
        
        // Validate date range
        var startDate = $('input[name="start"]').val();
        var endDate = $('input[name="end"]').val();
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            resp = false;
        }

        if(resp == true)
        {
            // Form is valid, proceed with submission
            $.ajax({
                url: "/inventory-report/get-data",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: data,
                success: function(response) {
                    // Handle success response
                    if(response.success) {
                        displayReportData(response.data);
                        $('#ajax-loader').hide();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.log('Error:', error);
                }
            });
        }
    });

    // Function to display report data
    function displayReportData(data) {
        // Remove existing report results if any
        $('#report-results').remove();
        
        // Create report results container
        var reportHtml = '<div id="report-results" class="card mt-4">';
        reportHtml += '<div class="card-header bg-primary text-white">';
        reportHtml += '<h5 class="card-title mb-0"><i class="fa fa-chart-bar"></i> Inventory Report Results</h5>';
        reportHtml += '</div>';
        reportHtml += '<div class="card-body p-0">';
        
        if(data.length > 0) {
            reportHtml += '<div class="table-responsive">';
            reportHtml += '<table class="table table-striped table-hover mb-0">';
            reportHtml += '<thead class="thead-dark">';
            reportHtml += '<tr>';
            reportHtml += '<th class="text-center">#</th>';
            reportHtml += '<th>Date</th>';
            reportHtml += '<th>Transaction Type</th>';
            reportHtml += '<th>Generic Name</th>';
            reportHtml += '<th>Brand Name</th>';
            reportHtml += '<th>Batch No</th>';
            reportHtml += '<th class="text-center">Org Balance</th>';
            reportHtml += '<th class="text-center">Site Balance</th>';
            reportHtml += '<th class="text-center">Location Balance</th>';
            reportHtml += '<th>Source</th>';
            reportHtml += '<th>Destination</th>';
            reportHtml += '<th>Ref Document</th>';
            reportHtml += '<th>MR Code</th>';
            reportHtml += '<th>Remarks</th>';
            reportHtml += '</tr>';
            reportHtml += '</thead>';
            reportHtml += '<tbody>';
            
            data.forEach(function(item, index) {
                // Format source display
                var sourceDisplay = item.source || '';
                if (item.source_type_name && item.source_type_name.toLowerCase().includes('location') && item.source_location_name) {
                    sourceDisplay = item.source_location_name + ' (' + item.source_type_name + ')';
                } else if (item.source_type_name) {
                    sourceDisplay = sourceDisplay + ' (' + item.source_type_name + ')';
                }
                
                // Format destination display
                var destinationDisplay = item.destination || '';
                if (item.destination_type_name && item.destination_type_name.toLowerCase().includes('location') && item.destination_location_name) {
                    destinationDisplay = item.destination_location_name + ' (' + item.destination_type_name + ')';
                } else if (item.destination_type_name) {
                    destinationDisplay = destinationDisplay + ' (' + item.destination_type_name + ')';
                }
                
                // Format date
                var formattedDate = '';
                if (item.timestamp) {
                    var date = new Date(item.timestamp * 1000);
                    formattedDate = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    }) + ' ' + date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
                
                reportHtml += '<tr>';
                reportHtml += '<td class="text-center">' + (index + 1) + '</td>';
                reportHtml += '<td><small>' + formattedDate + '</small></td>';
                reportHtml += '<td><span class="badge badge-info">' + (item.transaction_type_name || 'N/A') + '</span></td>';
                reportHtml += '<td><strong>' + (item.generic_name || 'N/A') + '</strong></td>';
                reportHtml += '<td>' + (item.brand_name || 'N/A') + '</td>';
                reportHtml += '<td><code>' + (item.batch_no || 'N/A') + '</code></td>';
                reportHtml += '<td class="text-center"><span class="badge badge-success">' + (item.org_balance || '0') + '</span></td>';
                reportHtml += '<td class="text-center"><span class="badge badge-warning">' + (item.site_balance || '0') + '</span></td>';
                reportHtml += '<td class="text-center"><span class="badge badge-secondary">' + (item.location_balance || '0') + '</span></td>';
                reportHtml += '<td><small>' + sourceDisplay + '</small></td>';
                reportHtml += '<td><small>' + destinationDisplay + '</small></td>';
                reportHtml += '<td><code>' + (item.ref_document_no || 'N/A') + '</code></td>';
                reportHtml += '<td><code>' + (item.mr_code || 'N/A') + '</code></td>';
                reportHtml += '<td><small class="text-muted">' + (item.remarks ? item.remarks.substring(0, 50) + (item.remarks.length > 50 ? '...' : '') : 'N/A') + '</small></td>';
                reportHtml += '</tr>';
            });
            
            reportHtml += '</tbody>';
            reportHtml += '</table>';
            reportHtml += '</div>';
            
            // Add summary and download section
            reportHtml += '<div class="card-footer bg-light">';
            reportHtml += '<div class="row align-items-center">';
            reportHtml += '<div class="col-md-6">';
            reportHtml += '<small class="text-muted">';
            reportHtml += '<i class="fa fa-info-circle"></i> Total Records: <strong>' + data.length + '</strong>';
            reportHtml += '</small>';
            reportHtml += '</div>';
            reportHtml += '<div class="col-md-6 text-right">';
            reportHtml += '<button type="button" class="btn btn-success btn-sm" onclick="downloadReport()">';
            reportHtml += '<i class="fa fa-download"></i> Download PDF Report';
            reportHtml += '</button>';
            reportHtml += '</div>';
            reportHtml += '</div>';
            reportHtml += '</div>';
        } else {
            reportHtml += '<div class="text-center p-4">';
            reportHtml += '<i class="fa fa-info-circle fa-3x text-muted mb-3"></i>';
            reportHtml += '<h5 class="text-muted">No Data Found</h5>';
            reportHtml += '<p class="text-muted">No inventory records found for the selected criteria.</p>';
            reportHtml += '</div>';
        }
        
        reportHtml += '</div>';
        reportHtml += '</div>';
        
        // Append to the form
        $('#inv_report').after(reportHtml);
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#report-results').offset().top - 100
        }, 500);
    }

});

// Function to download report (global scope)
function downloadReport() {
    // Get current form data
    var data = SerializeForm(document.getElementById('inv_report'));
    
    // Create form for download
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/inventory-report/download-pdf';
    
    // Add CSRF token
    var csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
    form.appendChild(csrfToken);
    
    // Add form data
    data.forEach(function(field) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = field.name;
        input.value = field.value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}


