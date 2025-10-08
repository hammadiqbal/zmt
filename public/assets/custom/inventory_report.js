// Updated: Removed location from grouping - Cache bust: 2024-01-15
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
    // $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
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
    
    // Handle Transaction Types multi-select behavior for selectpicker
    $('#ir_transactiontype').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allTransactionTypesValue = '0101';
        
        // If no transaction types are selected, select "All Transaction Types"
        if (!selectedValues || selectedValues.length === 0) {
            $(this).selectpicker('val', [allTransactionTypesValue]);
            $(this).selectpicker('refresh');
            return;
        }
        
        // If "All Transaction Types" is selected along with other types, remove "All Transaction Types"
        if (selectedValues.includes(allTransactionTypesValue) && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== allTransactionTypesValue;
            });
            $(this).selectpicker('val', newValues);
            $(this).selectpicker('refresh');
        }
    });
    
    // Handle Generics multi-select behavior for selectpicker
    $('#ir_generic').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allGenericsValue = '0101';
        
        // If no generics are selected, select "All Generics"
        if (!selectedValues || selectedValues.length === 0) {
            $(this).selectpicker('val', [allGenericsValue]);
            $(this).selectpicker('refresh');
            return;
        }
        
        // If "All Generics" is selected along with other generics, remove "All Generics"
        if (selectedValues.includes(allGenericsValue) && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== allGenericsValue;
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
        // $('#ir_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
        // $('#ir_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
        // $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
        // $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
        // $('#ir_transactiontype').html("<option selected disabled value=''>Select Transaction Type</option>").prop('disabled', true);
        
        // Reset sites, transaction types, and generics to "All" only
        $('#ir_site,#ir_transactiontype,#ir_generic').selectpicker('val', ['0101']);
        $('#ir_site,#ir_transactiontype,#ir_generic').selectpicker('refresh');
        
        $('#ajax-loader').hide();

        // $('#ir_type').val('').trigger('change');
        // $('#ir_generic').val('').trigger('change');
        // $('#ir_brand').val('').trigger('change');
    });
  
    // Use existing functions for cascading dropdowns
    // CategoryChangeSubCategory('#ir_cat', '#ir_subcat', '#inv_report');
    // SubCategoryChangeInventoryType('#ir_subcat', '#ir_type', '#inv_report');
    // TypeChangeInventoryGeneric('#ir_type', '#ir_generic', '#inv_report');
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
        
        // Create report results container with attractive header
        var reportHtml = '<div id="report-results" class="mt-4">';
        
        // Add attractive header like the second screenshot
        reportHtml += '<div class="row page-titles" style="background-color: #f2f7f8; border: 1px solid #e9ecef; border-radius: 5px; margin-bottom: 20px;">';
        reportHtml += '<div class="col-md-5 col-8 align-self-center">';
        reportHtml += '<h3 class="text-themecolor m-b-0 m-t-0">Inventory Report</h3>';
        reportHtml += '</div>';
        reportHtml += '<div class="col-md-7 col-4 align-self-center">';
        reportHtml += '<div class="d-flex m-t-10 justify-content-end">';
        reportHtml += '<div class="d-flex m-r-20 m-l-10">';
        reportHtml += '<div class="chart-text m-r-10">';
        reportHtml += '<h6 class="m-b-0"><small>TOTAL RECORDS</small></h6>';
        reportHtml += '<h4 class="m-t-0 text-info">' + data.length + '</h4>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        reportHtml += '<div class="d-flex m-r-20 m-l-10">';
        reportHtml += '<div class="chart-text m-r-10">';
        reportHtml += '<h6 class="m-b-0"><small>SITES</small></h6>';
        reportHtml += '<h4 class="m-t-0 text-primary">' + (data.length > 0 ? new Set(data.map(item => item.site_id)).size : 0) + '</h4>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        reportHtml += '<div>';
        reportHtml += '<button class="btn btn-success btn-sm" onclick="downloadReport()"><i class="fa fa-download"></i> Download PDF</button>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        reportHtml += '</div>';
        
        reportHtml += '<div class="card">';
        reportHtml += '<div class="card-body p-0">';
        
        if(data.length > 0) {
            // Group data directly by generic, brand, batch (no location grouping)
            var groupedData = {};
            data.forEach(function(item) {
                var key = (item.generic_name || 'Unknown') + '|' + 
                         (item.brand_name || 'Unknown') + '|' + 
                         (item.batch_no || 'Unknown');
                
                if (!groupedData[key]) {
                    groupedData[key] = {
                        generic: item.generic_name || 'Unknown',
                        brand: item.brand_name || 'Unknown',
                        batch: item.batch_no || 'Unknown',
                        items: []
                    };
                }
                groupedData[key].items.push(item);
            });
            
            // Create report for each item group
            Object.keys(groupedData).forEach(function(key) {
                var group = groupedData[key];
                    
                    reportHtml += '<div class="item-group border-bottom p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; margin: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
                    reportHtml += '<div class="row mb-3">';
                    reportHtml += '<div class="col-md-4">';
                    reportHtml += '<div class="text-center p-2" style="background: #ffffff; border-radius: 6px; border: 1px solid #dee2e6;">';
                    reportHtml += '<div class="mb-1" style="font-size: 11px; font-weight: bold; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Generic</div>';
                    reportHtml += '<div style="font-size: 12px; font-weight: 600; color: #495057;">' + group.generic + '</div>';
                    reportHtml += '</div>';
                    reportHtml += '</div>';
                    reportHtml += '<div class="col-md-4">';
                    reportHtml += '<div class="text-center p-2" style="background: #ffffff; border-radius: 6px; border: 1px solid #dee2e6;">';
                    reportHtml += '<div class="mb-1" style="font-size: 11px; font-weight: bold; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Brand</div>';
                    reportHtml += '<div style="font-size: 12px; font-weight: 600; color: #495057;">' + group.brand + '</div>';
                    reportHtml += '</div>';
                    reportHtml += '</div>';
                    reportHtml += '<div class="col-md-4">';
                    reportHtml += '<div class="text-center p-2" style="background: #ffffff; border-radius: 6px; border: 1px solid #dee2e6;">';
                    reportHtml += '<div class="mb-1" style="font-size: 11px; font-weight: bold; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Batch</div>';
                    reportHtml += '<div style="font-size: 12px; font-weight: 600; color: #495057;">' + group.batch + '</div>';
                    reportHtml += '</div>';
                    reportHtml += '</div>';
                    reportHtml += '</div>';
                    
                    // Display transactions for this item group
                    reportHtml += '<div class="table-responsive">';
                    reportHtml += '<table class="table table-sm table-hover mb-0">';
                    reportHtml += '<thead class="thead-light">';
                    reportHtml += '<tr>';
                    reportHtml += '<th>Transaction Info</th>';
                    reportHtml += '<th>Transaction Qty</th>';
                    reportHtml += '<th>Source</th>';
                    reportHtml += '<th>Destination</th>';
                    reportHtml += '<th>MR Code</th>';
                    reportHtml += '<th class="text-center">Org Balance</th>';
                    reportHtml += '<th class="text-center">Site Balance</th>';
                    reportHtml += '<th class="text-center">Location Balance</th>';
                    reportHtml += '</tr>';
                    reportHtml += '</thead>';
                    reportHtml += '<tbody>';
                    
                    group.items.forEach(function(item) {
                        // Format source display
                        var sourceDisplay = item.source || '';
                        if (item.source_type_name && item.source_type_name.toLowerCase().includes('location') && item.source_location_name) {
                            var cleanTypeName = item.source_type_name.replace('Inventory Location', 'Location');
                            sourceDisplay = item.source_location_name + ' (' + cleanTypeName + ')';
                        } else if (item.source_type_name && item.source_type_name.toLowerCase().includes('vendor')) {
                            // Handle vendor display
                            var vendorName = '';
                            if (item.source_vendor_person_name && item.source_vendor_corporate_name) {
                                vendorName = item.source_vendor_person_name + ' - ' + item.source_vendor_corporate_name;
                            } else if (item.source_vendor_person_name) {
                                vendorName = item.source_vendor_person_name;
                            } else if (item.source_vendor_corporate_name) {
                                vendorName = item.source_vendor_corporate_name;
                            } else {
                                vendorName = 'Vendor ID: ' + item.source;
                            }
                            sourceDisplay = vendorName + ' (' + item.source_type_name + ')';
                        } else if (item.source_type_name) {
                            sourceDisplay = sourceDisplay + ' (' + item.source_type_name + ')';
                        }
                        
                        // Format destination display
                        var destinationDisplay = item.destination || '';
                        if (item.destination_type_name && item.destination_type_name.toLowerCase().includes('location') && item.destination_location_name) {
                            var cleanTypeName = item.destination_type_name.replace('Inventory Location', 'Location');
                            destinationDisplay = item.destination_location_name + ' (' + cleanTypeName + ')';
                        } else if (item.destination_type_name && item.destination_type_name.toLowerCase().includes('vendor')) {
                            // Handle vendor display
                            var vendorName = '';
                            if (item.destination_vendor_person_name && item.destination_vendor_corporate_name) {
                                vendorName = item.destination_vendor_person_name + ' - ' + item.destination_vendor_corporate_name;
                            } else if (item.destination_vendor_person_name) {
                                vendorName = item.destination_vendor_person_name;
                            } else if (item.destination_vendor_corporate_name) {
                                vendorName = item.destination_vendor_corporate_name;
                            } else {
                                vendorName = 'Vendor ID: ' + item.destination;
                            }
                            destinationDisplay = vendorName;
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
                        
                        // Transaction Info column content
                        var transactionInfo = '<strong>Type:</strong> ' + (item.transaction_type_name || 'N/A') + '<br>';
                        transactionInfo += '<strong>Ref Doc #:</strong> ' + (item.ref_document_no || 'N/A') + '<br>';
                        transactionInfo += '<strong>Date:</strong> ' + formattedDate + '<br>';
                        transactionInfo += '<strong>Site:</strong> ' + (item.site_name || 'N/A') + '<br>';
                        transactionInfo += '<strong>Remarks:</strong> ' + (item.remarks || 'N/A');
                        
                        // Handle accurate transaction_qty (now single value)
                        var transactionQtyDisplay = '<span class="badge badge-info">' + (item.accurate_transaction_qty || '0') + '</span>';
                        
                        reportHtml += '<tr>';
                        reportHtml += '<td><small>' + transactionInfo   + '</small></td>';
                        reportHtml += '<td>' + transactionQtyDisplay + '</td>';
                        reportHtml += '<td><small>' + sourceDisplay + '</small></td>';
                        reportHtml += '<td><small>' + destinationDisplay + '</small></td>';
                        reportHtml += '<td><code>' + (item.mr_code || 'N/A') + '</code></td>';
                        reportHtml += '<td class="text-center"><span class="badge badge-success">' + (item.org_balance || '0') + '</span></td>';
                        reportHtml += '<td class="text-center"><span class="badge badge-warning">' + (item.site_balance || '0') + '</span></td>';
                        reportHtml += '<td class="text-center"><span class="badge badge-secondary">' + (item.location_balance || '0') + '</span></td>';
                        reportHtml += '</tr>';
                    });
                    
                    reportHtml += '</tbody>';
                    reportHtml += '</table>';
                    reportHtml += '</div>';
                    reportHtml += '</div>';
                });
                
        } else {
            reportHtml += '<div class="text-center p-4">';
            reportHtml += '<i class="fa fa-info-circle fa-3x text-muted mb-3"></i>';
            reportHtml += '<h5 class="text-muted">No Data Found</h5>';
            reportHtml += '<p class="text-muted">No inventory records found for the selected criteria.</p>';
            reportHtml += '</div>';
        }
        
        reportHtml += '</div>';
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


