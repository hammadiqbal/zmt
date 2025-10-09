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
    
    // Initialize brand dropdown with "All Brands" option
    fetchBrandsForGenerics('0101', false);
    
    // $('#ir_type').html("<option selected disabled value=''>Select Item Type</option>").prop('disabled', true);
    // $('#ir_subcat').html("<option selected disabled value=''>Select Sub Category</option>").prop('disabled', true);
    // $('#ir_generic').html("<option selected disabled value=''>Select Item Generic</option>").prop('disabled', true);
    // $('#ir_brand').html("<option selected disabled value=''>Select Item Brand</option>").prop('disabled', true);
    
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
    
    $('#ir_generic').on('hidden.bs.select', function() {
        var selectedValues = $(this).val();
        
        // $('#ir_brand').html('<option selected disabled value="">Select Item Brand</option>');
        
        if (!selectedValues || selectedValues.length === 0 ) {
            $('#ir_brand').prop('disabled', true);
            return;
        }
        $('#ir_brand').prop('disabled', false);
        
        var genericIds = selectedValues;
        
        if (genericIds.length > 0) {
            fetchBrandsForGenerics(genericIds.join(','));
        }
    });
    
    // Handle Brands multi-select behavior for selectpicker
    $('#ir_brand').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allBrandsValue = '0101';
        
        // Find the "Select All" option value (comma-separated brand IDs)
        var selectAllValue = null;
        $('#ir_brand option').each(function() {
            if ($(this).text() === 'Select All') {
                selectAllValue = $(this).val();
            }
        });
        
        // If no brands are selected, select "All Brands"
        if (!selectedValues || selectedValues.length === 0) {
            if (selectAllValue) {
                $(this).selectpicker('val', [selectAllValue]);
            } else {
                $(this).selectpicker('val', [allBrandsValue]);
            }
            $(this).selectpicker('refresh');
            return;
        }
        
        // Check if "Select All" (comma-separated) or "0101" is selected along with other brands
        var hasSelectAll = selectedValues.some(function(value) {
            return value === selectAllValue || value === allBrandsValue;
        });
        
        if (hasSelectAll && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== selectAllValue && value !== allBrandsValue;
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
        
        // Reset sites, transaction types, generics to "All" only
        $('#ir_site,#ir_transactiontype,#ir_generic').selectpicker('val', ['0101']);
        $('#ir_site,#ir_transactiontype,#ir_generic').selectpicker('refresh');
        
        // Reset brand dropdown by fetching all brands with "Select All" option
        fetchBrandsForGenerics('0101', false);
        
        // Hide report data
        $('#report-results').remove();
        $('.report-results').remove();
        $('#inv_report').nextAll().remove(); // Remove any elements appended after the form
        
        setTimeout(function () {
            $('#ajax-loader').fadeOut(200); // or .hide()
          }, 1000);
        // $('#ir_type').val('').trigger('change');
        // $('#ir_generic').val('').trigger('change');
        // $('#ir_brand').val('').trigger('change');
    });
  
    // Use existing functions for cascading dropdowns
    // CategoryChangeSubCategory('#ir_cat', '#ir_subcat', '#inv_report');
    // SubCategoryChangeInventoryType('#ir_subcat', '#ir_type', '#inv_report');
    // TypeChangeInventoryGeneric('#ir_type', '#ir_generic', '#inv_report');
    // GenericChangeBrand('#ir_generic', '#ir_brand', '#inv_report');

    //Inventory Report Form Submission
    $('#inv_report').submit(function(e) {
        e.preventDefault();
        $('#ajax-loader').show();
        var data = SerializeForm(this);
        var resp = true;
        
        // Validate required fields
        // $(data).each(function(i, field){
        //     // Skip validation for start and end dates as they are handled separately
        //     if (field.name === 'start' || field.name === 'end') {
        //         return;
        //     }
            
        //     if ((field.value == '') || (field.value == null))
        //     {
        //         var FieldName = field.name;
        //         var FieldID = '#'+FieldName + "_error";
        //         $(FieldID).text("This field is required");
        //         $( 'input[name= "' +FieldName +'"' ).addClass('requirefield');
        //         resp = false;
        //     }
        // });
        
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


});

function displayReportData(data) {
    var rows = Array.isArray(data) ? data : [];

    var $container = $('#report-results');
    if (!$container.length) {
        $('#inv_report').after('<div id="report-results" class="mt-4"></div>');
        $container = $('#report-results');
    }
    
    // Get distinct site names from the response data
    // var siteNames = '';
    // if (rows.length > 0) {
    //     var distinctSiteNames = Array.from(new Set(rows.map(it => it.site_name || ''))).filter(Boolean);
    //     siteNames = distinctSiteNames.join(', ');
    // } else {
    //     siteNames = 'No Sites';
    // }

    var siteCount = rows.length
        ? Array.from(new Set(rows.map(it => it.site_id || ''))).filter(Boolean).length
        : 0;

    var html = '';
    html += '<div class="row page-titles" style="background-color:#f2f7f8;border:1px solid #e9ecef;border-radius:5px;margin-bottom:20px;">';
    html += '  <div class="col-md-5 col-8 align-self-center">';
    html += '    <h3 class="text-themecolor m-b-0 m-t-0">Inventory Report</h3>';
    html += '  </div>';
    html += '  <div class="col-md-7 col-4 align-self-center">';
    html += '    <div class="d-flex m-t-10 justify-content-end">';
    html += '      <div class="d-flex m-r-20 m-l-10">';
    html += '        <div class="chart-text m-r-10">';
    html += '          <h6 class="m-b-0"><small>TOTAL RECORDS</small></h6>';
    html += '          <h4 class="m-t-0 text-info">'+ rows.length +'</h4>';
    html += '        </div>';
    html += '      </div>';
    html += '      <div class="d-flex m-r-20 m-l-10">';
    html += '        <div class="chart-text m-r-10">';
    html += '          <h6 class="m-b-0"><small>SITES</small></h6>';
    html += '          <h4 class="m-t-0 text-primary">'+ siteCount +'</h4>';
    html += '        </div>';
    html += '      </div>';
    html += '      <div>';
    html += '        <button class="btn btn-success btn-sm" onclick="downloadReport()"><i class="fa fa-download"></i> Download PDF</button>';
    html += '      </div>';
    html += '    </div>';
    html += '  </div>';
    html += '</div>';

    html += '<div class="card"><div class="card-body p-0">';

    if (!rows.length) {
        html += '<div class="text-center p-4">';
        html += '  <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>';
        html += '  <h5 class="text-muted">No Data Found</h5>';
        html += '  <p class="text-muted">No inventory records found for the selected criteria.</p>';
        html += '</div>';
    } 
    else {
        var grouped = {};
        rows.forEach(function (it) {
        var key = (it.generic_name || 'Unknown') + '|' + (it.brand_name || 'Unknown') + '|' + (it.batch_no || 'Unknown');
        if (!grouped[key]) {
            grouped[key] = {
            generic: it.generic_name || 'Unknown',
            brand: it.brand_name || 'Unknown',
            batch: it.batch_no || 'Unknown',
            items: []
            };
        }
        grouped[key].items.push(it);
        });

        Object.keys(grouped).forEach(function (key) {
        var g = grouped[key];

        html += '<div class="item-group border-bottom p-3" style="background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);border-radius:8px;margin:10px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">';
        html += '  <div class="row mb-3">';
        html += '    <div class="col-md-4"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:11px;font-weight:bold;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;">Generic</div>';
        html += '      <div style="font-size:12px;font-weight:600;color:#495057;">' + g.generic + '</div>';
        html += '    </div></div>';
        html += '    <div class="col-md-4"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:11px;font-weight:bold;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;">Brand</div>';
        html += '      <div style="font-size:12px;font-weight:600;color:#495057;">' + g.brand + '</div>';
        html += '    </div></div>';
        html += '    <div class="col-md-4"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:11px;font-weight:bold;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;">Batch</div>';
        html += '      <div style="font-size:12px;font-weight:600;color:#495057;">' + g.batch + '</div>';
        html += '    </div></div>';
        html += '  </div>';

        html += '  <div class="table-responsive">';
        html += '    <table class="table table-sm table-hover mb-0">';
        html += '      <thead class="thead-light">';
        html += '        <tr>';
        html += '          <th>Transaction Info</th>';
        html += '          <th>Transaction Qty</th>';
        html += '          <th>Source</th>';
        html += '          <th>Destination</th>';
        html += '          <th>MR Code</th>';
        html += '          <th class="text-center">Org Balance</th>';
        html += '          <th class="text-center">Site Balance</th>';
        html += '          <th class="text-center">Location Balance</th>';
        html += '        </tr>';
        html += '      </thead>';
        html += '      <tbody>';

        g.items.forEach(function (it) {
            // Source
            var sourceDisplay = it.source || '';
            if (it.source_type_name && it.source_type_name.toLowerCase().includes('location') && it.source_location_name) {
            var cleanType = it.source_type_name.replace('Inventory Location', 'Location');
            sourceDisplay = it.source_location_name + ' (' + cleanType + ')';
            } else if (it.source_type_name && it.source_type_name.toLowerCase().includes('vendor')) {
            var vn = it.source_vendor_person_name || '';
            var vc = it.source_vendor_corporate_name || '';
            var v = (vn && vc) ? (vn + ' - ' + vc) : (vn || vc || ('Vendor ID: ' + (it.source || '')));
            sourceDisplay = v + ' (' + it.source_type_name + ')';
            } else if (it.source_type_name) {
            sourceDisplay = (sourceDisplay || '') + ' (' + it.source_type_name + ')';
            }

            // Destination
            var destDisplay = it.destination || '';
            if (it.destination_type_name && it.destination_type_name.toLowerCase().includes('location') && it.destination_location_name) {
            var cleanType2 = it.destination_type_name.replace('Inventory Location', 'Location');
            destDisplay = it.destination_location_name + ' (' + cleanType2 + ')';
            } else if (it.destination_type_name && it.destination_type_name.toLowerCase().includes('vendor')) {
            var dvn = it.destination_vendor_person_name || '';
            var dvc = it.destination_vendor_corporate_name || '';
            var dv = (dvn && dvc) ? (dvn + ' - ' + dvc) : (dvn || dvc || ('Vendor ID: ' + (it.destination || '')));
            destDisplay = dv;
            } else if (it.destination_type_name) {
            destDisplay = (destDisplay || '') + ' (' + it.destination_type_name + ')';
            }

            // Timestamp (accept seconds or ms)
            var formattedDate = '';
            if (it.timestamp) {
            var ts = Number(it.timestamp);
            if (!isNaN(ts)) {
                if (ts < 1e12) ts = ts * 1000; // seconds -> ms
                var d = new Date(ts);
                formattedDate =
                d.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' }) +
                ' ' +
                d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            }
            }

            var info = '<strong>Type:</strong> ' + (it.transaction_type_name || 'N/A') + '<br>' +
                    '<strong>Ref Doc #:</strong> ' + (it.ref_document_no || 'N/A') + '<br>' +
                    '<strong>Date:</strong> ' + (formattedDate || 'N/A') + '<br>' +
                    '<strong>Site:</strong> ' + (it.site_name || 'N/A') + '<br>' +
                    '<strong>Remarks:</strong> ' + (it.remarks || 'N/A');

            var qtyBadge = '<span class="badge badge-info">' + (it.accurate_transaction_qty || '0') + '</span>';

            html += '<tr>';
            html += '  <td><small>' + info + '</small></td>';
            html += '  <td>' + qtyBadge + '</td>';
            html += '  <td><small>' + (sourceDisplay || '') + '</small></td>';
            html += '  <td><small>' + (destDisplay || '') + '</small></td>';
            html += '  <td><code>' + (it.mr_code || 'N/A') + '</code></td>';
            html += '  <td class="text-center"><span class="badge badge-success">' + (it.org_balance || '0') + '</span></td>';
            html += '  <td class="text-center"><span class="badge badge-warning">' + (it.site_balance || '0') + '</span></td>';
            html += '  <td class="text-center"><span class="badge badge-secondary">' + (it.location_balance || '0') + '</span></td>';
            html += '</tr>';
        });

        html += '      </tbody>';
        html += '    </table>';
        html += '  </div>';
        html += '</div>';
        });
    }
    html += '</div></div>'; 
    $container.html(html);
    $('html, body').animate({ scrollTop: $container.offset().top - 100 }, 300);
}

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

// Function to fetch brands based on generic IDs
function fetchBrandsForGenerics(genericIds, showLoading = true) {
    $.ajax({
        url: '/inventory/getgenericbrand',
        method: 'GET',
        data: {
            genericId: genericIds
        },
        beforeSend: function() {
            if (showLoading) {
                $('#ir_brand').html('<option>Loading...</option>');
            }
        },
        success: function(response) {
            if (response && response.length > 0) {
                // Collect all brand IDs for "All Brands" option
                var allBrandIds = response.map(function(brand) {
                    return brand.id;
                }).join(',');
                
                var brandOptions = '<option selected value="' + allBrandIds + '">Select All</option>';
                response.forEach(function(brand) {
                    brandOptions += '<option value="' + brand.id + '">' + brand.name + '</option>';
                });
                $('#ir_brand').html(brandOptions);
                $('#ir_brand').selectpicker();
                $('#ir_brand').selectpicker('refresh');
            }
            else{
                $('#ir_brand').html('<option>Data N/A</option>').prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error fetching brands:', error);
        }
    });
}
