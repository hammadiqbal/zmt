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
    fetchBrandsForGenerics('0101', false);
    
    var orgId = $('#ir_org').val();
    var siteIds = $('#ir_site').val();
    var genericIds = $('#ir_generic').val();
    var brandIds = $('#ir_brand').val();
    fetchBatchesForReport(orgId, siteIds, genericIds, brandIds, false);
    
    // Initialize locations
    fetchLocationsForReport(siteIds, false);
  
    // Handle Sites multi-select behavior for selectpicker
    $('#ir_site').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allSitesValue = '0101';
        
        if (!selectedValues || selectedValues.length === 0) {
            $(this).selectpicker('val', [allSitesValue]);
            $(this).selectpicker('refresh');
            return;
        }
        
        if (selectedValues.includes(allSitesValue) && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== allSitesValue;
            });
            $(this).selectpicker('val', newValues);
            $(this).selectpicker('refresh');
        }
    });
    
    $('#ir_site').on('hidden.bs.select', function() {
        var selectedValues = $(this).val();
        var selectedGenerics = $('#ir_generic').val();
        var selectedBrands = $('#ir_brand').val();
        
        if (!selectedValues || selectedValues.length === 0) {
            $('#ir_batch').prop('disabled', true);
            $('#inv_report button[type="submit"]').prop('disabled', true);
            return;
        }
        $('#ir_batch').prop('disabled', false);
        
        // Disable submit button while batches are loading
        var $submitBtn = $('#inv_report button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Loading').prop('disabled', true);
        
        // Fetch batches based on all selected values
        var orgId = $('#ir_org').val();
        fetchBatchesForReport(orgId, selectedValues, selectedGenerics, selectedBrands);
        
        // Fetch locations based on selected sites
        fetchLocationsForReport(selectedValues);
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
            $('#ir_batch').prop('disabled', true);
            $('#inv_report button[type="submit"]').prop('disabled', true);
            return;
        }
        $('#ir_brand').prop('disabled', false);
        $('#ir_batch').prop('disabled', false);
        
        var genericIds = selectedValues;
        
        if (genericIds.length > 0) {
            // Disable submit button while brands are loading
            var $submitBtn = $('#inv_report button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Loading').prop('disabled', true);
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
    
    $('#ir_brand').on('hidden.bs.select', function() {
        var selectedValues = $(this).val();
        var selectedGenerics = $('#ir_generic').val();
        
        if (!selectedValues || selectedValues.length === 0 || !selectedGenerics || selectedGenerics.length === 0) {
            $('#ir_batch').prop('disabled', true);
            $('#inv_report button[type="submit"]').prop('disabled', true);
            return;
        }
        $('#ir_batch').prop('disabled', false);
        
        // Disable submit button while batches are loading
        var $submitBtn = $('#inv_report button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Loading').prop('disabled', true);
        
        // Fetch batches based on all selected values
        var orgId = $('#ir_org').val();
        var siteIds = $('#ir_site').val();
        fetchBatchesForReport(orgId, siteIds, selectedGenerics, selectedValues);
    });
    
    // Handle Batches multi-select behavior for selectpicker
    $('#ir_batch').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allBatchesValue = '0101';
        
        // Find the "Select All" option value (comma-separated batch numbers)
        var selectAllValue = null;
        $('#ir_batch option').each(function() {
            if ($(this).text() === 'Select All') {
                selectAllValue = $(this).val();
            }
        });
        
        // If no batches are selected, select "All Batches"
        if (!selectedValues || selectedValues.length === 0) {
            if (selectAllValue) {
                $(this).selectpicker('val', [selectAllValue]);
            } else {
                $(this).selectpicker('val', [allBatchesValue]);
            }
            $(this).selectpicker('refresh');
            return;
        }
        
        // Check if "Select All" (comma-separated) or "0101" is selected along with other batches
        var hasSelectAll = selectedValues.some(function(value) {
            return value === selectAllValue || value === allBatchesValue;
        });
        
        if (hasSelectAll && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== selectAllValue && value !== allBatchesValue;
            });
            $(this).selectpicker('val', newValues);
            $(this).selectpicker('refresh');
        }
    });
    
    // Handle Locations multi-select behavior for selectpicker
    $('#ir_location').on('changed.bs.select', function() {
        var selectedValues = $(this).val();
        var allLocationsValue = '0101';
        
        // Find the "Select All" option value (comma-separated location IDs)
        var selectAllValue = null;
        $('#ir_location option').each(function() {
            if ($(this).text() === 'Select All') {
                selectAllValue = $(this).val();
            }
        });
        
        // If no locations are selected, select "All Locations"
        if (!selectedValues || selectedValues.length === 0) {
            if (selectAllValue) {
                $(this).selectpicker('val', [selectAllValue]);
            } else {
                $(this).selectpicker('val', [allLocationsValue]);
            }
            $(this).selectpicker('refresh');
            return;
        }
        
        // Check if "Select All" (comma-separated) or "0101" is selected along with other locations
        var hasSelectAll = selectedValues.some(function(value) {
            return value === selectAllValue || value === allLocationsValue;
        });
        
        if (hasSelectAll && selectedValues.length > 1) {
            var newValues = selectedValues.filter(function(value) {
                return value !== selectAllValue && value !== allLocationsValue;
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
        
        // Reset sites, transaction types, generics, locations to "All" only
        $('#ir_site,#ir_transactiontype,#ir_generic,#ir_location').selectpicker('val', ['0101']);
        $('#ir_site,#ir_transactiontype,#ir_generic,#ir_location').selectpicker('refresh');
        
        // Reset brand dropdown by fetching all brands with "Select All" option
        fetchBrandsForGenerics('0101', false);
        
        var orgId = $('#ir_org').val();
        var siteIds = $('#ir_site').val();
        var genericIds = $('#ir_generic').val();
        var brandIds = $('#ir_brand').val();
        fetchBatchesForReport(orgId, siteIds, genericIds, brandIds, false);
        
        // Reset locations
        fetchLocationsForReport(siteIds, false);
        
        // Hide report data
        $('#report-results').remove();
        $('.report-results').remove();
        $('#inv_report').nextAll().remove(); // Remove any elements appended after the form
        
        setTimeout(function () {
            $('#ajax-loader').fadeOut(200); // or .hide()
          }, 1000);
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
        
        // Show loading text on button
        var $submitBtn = $('#inv_report button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Loading').prop('disabled', true);
        
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
            $.ajax({
                url: "/inventory-report/get-data",
                method: "GET",
                data: data,
                success: function(response) {
                    // Handle success response
                    if(response.success) {
                        displayReportData(response.data);
                        $('#ajax-loader').hide();
                        $submitBtn.html(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    $('#ajax-loader').hide();
                    $submitBtn.html(originalText).prop('disabled', false);
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
    
    // Check for pending/processing reports for current user
    checkUserPendingReports();
    
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
    html += '            <div>';
        html += '        <button class="btn btn-success btn-sm" onclick="downloadReport()"><i class="fa fa-download"></i> Generate Report</button>';
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
            items: [],
            final_org_balance: 0,
            site_balances: {},
            location_balances: {}
            };
        }
        grouped[key].items.push(it);
        
        // Calculate final balances (use the last transaction's balance)
        grouped[key].final_org_balance = it.org_balance || 0;
        
        // Collect site balances
        if (it.site_name) {
            grouped[key].site_balances[it.site_name] = it.site_balance || 0;
        }
        
        // Collect location balances
        if (it.location_name) {
            grouped[key].location_balances[it.location_name] = it.location_balance || 0;
        }
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
        
        // Balances Summary Line
        html += '  <div class="row mb-2">';
        html += '    <div class="col-md-3"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:9px;font-weight:bold;color:#27ae60;text-transform:uppercase;">Org Balance</div>';
        html += '      <div style="font-size:10px;font-weight:600;color:#495057;">' + g.final_org_balance + '</div>';
        html += '    </div></div>';
        
        html += '    <div class="col-md-6"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:9px;font-weight:bold;color:#f39c12;text-transform:uppercase;">Site - Location Balance</div>';
        
        // Combine site and location balances
        var combinedBalances = {};
        g.items.forEach(function(item) {
            if (item.site_name && item.location_name) {
                var key = item.site_name + ' - ' + item.location_name;
                combinedBalances[key] = item.site_balance || 0;
            } else if (item.site_name) {
                combinedBalances[item.site_name] = item.site_balance || 0;
            } else if (item.location_name) {
                combinedBalances[item.location_name] = item.location_balance || 0;
            }
        });
        
        var combinedKeys = Object.keys(combinedBalances);
        if (combinedKeys.length > 0) {
            var combinedText = combinedKeys.map(function(name) {
                return name + ': ' + combinedBalances[name];
            }).join('<br>');
            html += '      <div style="font-size:10px;font-weight:600;color:#495057;">' + combinedText + '</div>';
        } else {
            html += '      <div style="font-size:10px;font-weight:600;color:#495057;">N/A</div>';
        }
        html += '    </div></div>';
        
        html += '    <div class="col-md-3"><div class="text-center p-2" style="background:#fff;border-radius:6px;border:1px solid #dee2e6;">';
        html += '      <div class="mb-1" style="font-size:9px;font-weight:bold;color:#3498db;text-transform:uppercase;">Total Transactions</div>';
        html += '      <div style="font-size:10px;font-weight:600;color:#495057;">' + g.items.length + '</div>';
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
                    '<strong>' + it.site_label + '</strong> ' + (it.site_name || 'N/A') + '<br>' +
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

// Global variables for report processing
var currentReportId = null;
var statusCheckInterval = null;

// Function to download report (global scope)
function downloadReport() {
    // Get current form data
    var data = SerializeForm(document.getElementById('inv_report'));
    
    // Show progress loader
    showProgressLoader();
    
    // Submit report request
    $.ajax({
        url: '/inventory-report/request-excel',
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                currentReportId = response.report_id;
                showSuccessMessage(response.message);
                startStatusPolling();
            } else {
                hideProgressLoader();
                showErrorMessage('Failed to submit report request');
            }
        },
        error: function(xhr, status, error) {
            hideProgressLoader();
            showErrorMessage('Error submitting report request: ' + error);
        }
    });
}

// Function to show progress loader
function showProgressLoader() {
    var $downloadBtn = $('button[onclick="downloadReport()"]');
    var originalHtml = $downloadBtn.html();
    
    $downloadBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing... 0%')
                .prop('disabled', true)
                .data('original-html', originalHtml);
    
    // Add progress bar
    if (!$('#progress-container').length) {
        $downloadBtn.after('<div id="progress-container" class="mt-2"><div class="progress" style="height: 20px;"><div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div></div></div>');
    }
}

// Function to hide progress loader
function hideProgressLoader() {
    var $downloadBtn = $('button[onclick="downloadReport()"]');
    var originalHtml = $downloadBtn.data('original-html');
    
    if (originalHtml) {
        $downloadBtn.html(originalHtml).prop('disabled', false);
    }
    
    $('#progress-container').remove();
    
    // Clear any existing interval
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
}

// Function to update progress
function updateProgress(percentage, message) {
    var $downloadBtn = $('button[onclick="downloadReport()"]');
    var $progressBar = $('#progress-bar');
    
    $downloadBtn.html('<i class="fa fa-spinner fa-spin"></i> ' + message);
    $progressBar.css('width', percentage + '%')
                .attr('aria-valuenow', percentage)
                .text(percentage + '%');
}

// Function to start status polling
function startStatusPolling() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
    
    statusCheckInterval = setInterval(function() {
        checkReportStatus();
    }, 3000); // Check every 3 seconds
}

// Function to check report status
function checkReportStatus() {
    if (!currentReportId) {
        return;
    }
    
    $.ajax({
        url: '/inventory-report/check-status',
        method: 'GET',
        data: {
            report_id: currentReportId
        },
        success: function(response) {
            if (response.success) {
                updateProgress(response.progress, response.message);
                
                if (response.status === 'completed') {
                    // Report completed - check if email was sent
                    if (response.email_sent) {
                        // Email sent - show success and stop polling
                        clearInterval(statusCheckInterval);
                        statusCheckInterval = null;
                        
                        hideProgressLoader();
                        showSuccessMessage('Excel report completed! Check your email for the report.');
                    } else {
                        // Email not sent yet - continue polling
                        updateProgress(response.progress, 'Report ready! Sending email...');
                    }
                    
                } else if (response.status === 'failed') {
                    // Report failed
                    clearInterval(statusCheckInterval);
                    statusCheckInterval = null;
                    
                    hideProgressLoader();
                    showErrorMessage('Excel report generation failed. Please try again.');
                }
            } else {
                hideProgressLoader();
                showErrorMessage(response.message || 'Failed to check report status');
            }
        },
        error: function(xhr, status, error) {
            hideProgressLoader();
            showErrorMessage('Error checking report status: ' + error);
        }
    });
}


// Function to show success message
function showSuccessMessage(message) {
    // Remove any existing alerts
    $('.alert-success, .alert-danger').remove();
    
    var alertHtml = '<div class="alert alert-success alert-dismissible fade show mt-4 text-center" role="alert">' +
                    '<i class="fa fa-check-circle"></i> ' + message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
    
    $('#report-results').before(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut();
    }, 15000);
}

// Function to show error message
function showErrorMessage(message) {
    // Remove any existing alerts
    $('.alert-success, .alert-danger').remove();
    
    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">' +
                    '<i class="fa fa-exclamation-circle"></i> ' + message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
    
    $('#report-results').before(alertHtml);
    
    // Auto-hide after 8 seconds
    setTimeout(function() {
        $('.alert-danger').fadeOut();
    }, 8000);
}

// Function to check for pending reports for current user
function checkUserPendingReports() {
    $.ajax({
        url: '/inventory-report/check-user-reports',
        method: 'GET',
        success: function(response) {
            console.log(response);
            if (response.success && response.reports && response.reports.length > 0) {
                showPendingReportsStatus(response.reports);
                
                // Hide generate button if there are pending/processing reports
                var hasActiveReports = response.reports.some(function(report) {
                    return report.status === 'pending' || report.status === 'processing';
                });
                
                if (hasActiveReports) {
                    hideGenerateButton();
                } else {
                    // No active reports - show button regardless of completed/failed reports
                    showGenerateButton();
                }
            } else {
                // No reports found, show generate button
                showGenerateButton();
            }
        },
        error: function(xhr, status, error) {
            console.log('Error checking user reports:', error);
            // On error, show generate button
            showGenerateButton();
        }
    });
}

// Function to show pending reports status
function showPendingReportsStatus(reports) {
    // Remove any existing status alerts
    $('.alert-info').remove();
    
    // Find the first active report (pending or processing)
    var activeReport = reports.find(function(report) {
        return report.status === 'pending' || report.status === 'processing';
    });
    
    if (activeReport) {
        // Replace the generate button with status display
        replaceGenerateButtonWithStatus(activeReport);
        
        // Auto-refresh every 10 seconds if there are processing reports
        setTimeout(function() {
            checkUserPendingReports();
        }, 10000);
    } else {
        // If no active reports, show the generate button
        // Completed/failed reports don't prevent new report generation
        showGenerateButton();
        $('.report-status-container').remove(); // Remove any status message
    }
}

// Function to get status badge class
function getStatusBadgeClass(status) {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Function to hide generate button
function hideGenerateButton() {
    var $generateBtn = $('button[onclick="downloadReport()"]');
    if ($generateBtn.length) {
        $generateBtn.hide();
    }
}

// Function to show generate button
function showGenerateButton() {
    var $generateBtn = $('button[onclick="downloadReport()"]');
    if ($generateBtn.length) {
        $generateBtn.show();
        
        // Remove any existing status containers
        $('.report-status-container').remove();
    }
}

// Function to replace generate button with status display
function replaceGenerateButtonWithStatus(report) {
    var $generateBtn = $('button[onclick="downloadReport()"]');
    if (!$generateBtn.length) {
        return;
    }
    
    // Remove any existing status containers
    $('.report-status-container').remove();
    
    // Hide the generate button
    $generateBtn.hide();
    
    // Create status display HTML
    var statusHtml = '<div class="report-status-container" style="display: inline-block; width: 100%;font-size:12px;">';
    
    if (report.status === 'pending') {
        statusHtml += '<div class="alert alert-info mb-0" style="padding: 8px 12px; margin-bottom: 0;">';
        statusHtml += '<i class="fa fa-spinner fa-spin"></i> <strong>Report in process, you will receive an email when it\'s ready</strong>';
        statusHtml += '<div class="mt-2">';
        statusHtml += '<div class="progress" style="height: 20px;">';
        statusHtml += '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 1%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100">1%</div>';
        statusHtml += '</div>';
        statusHtml += '</div>';
        statusHtml += '</div>';
    } else if (report.status === 'processing') {
        statusHtml += '<div class="alert alert-info mb-0" style="padding: 8px 12px; margin-bottom: 0;">';
        statusHtml += '<i class="fa fa-spinner fa-spin"></i> <strong>Report in process, you will receive an email when it\'s ready</strong>';
        statusHtml += '<div class="mt-2">';
        statusHtml += '<div class="progress" style="height: 20px;">';
        statusHtml += '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' + report.progress_percentage + '%" aria-valuenow="' + report.progress_percentage + '" aria-valuemin="0" aria-valuemax="100">' + report.progress_percentage + '%</div>';
        statusHtml += '</div>';
        statusHtml += '</div>';
        statusHtml += '</div>';
    }
    
    statusHtml += '</div>';
    
    // Insert status display after the generate button
    $generateBtn.after(statusHtml);
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
                var brandOptions = '';
                if(genericIds == '0101')
                {
                    brandOptions = '<option selected value="0101">Select All</option>';
                    response.forEach(function(brand) {
                        brandOptions += '<option value="' + brand.id + '">' + brand.name + '</option>';
                    });
                }
                else{
                    if (response.length === 1) {
                        response.forEach(function(brand) {
                            brandOptions += '<option selected value="' + brand.id + '">' + brand.name + '</option>';
                        });
                    } else {
                        var allBrandIds = response.map(function(brand) {
                            return brand.id;
                        }).join(',');
                        brandOptions = '<option selected value="0101">Select All</option>';
                        response.forEach(function(brand) {
                            brandOptions += '<option value="' + brand.id + '">' + brand.name + '</option>';
                        });
                    }
                }
                
                $('#ir_brand').html(brandOptions);
                $('#ir_brand').selectpicker();
                $('#ir_brand').selectpicker('refresh');
                
                // If only one brand is available, disable the dropdown and keep "Select All" selected
                
                // Re-enable submit button after brands are loaded
                var $submitBtn = $('#inv_report button[type="submit"]');
                $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
                
                // Fetch batches after brands are loaded
                var orgId = $('#ir_org').val();
                var siteIds = $('#ir_site').val();
                var genericIds = $('#ir_generic').val();
                var brandIds = $('#ir_brand').val();
                fetchBatchesForReport(orgId, siteIds, genericIds, brandIds);
            }
            else{
                $('#ir_brand').html('<option selected value=" ">No Data Found</option>').prop('disabled', true).selectpicker('refresh');;
                $('#ir_batch').html('<option selected value=" ">No Data Found</option>').prop('disabled', true).selectpicker('refresh');;
                var $submitBtn = $('#inv_report button[type="submit"]');
                $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error fetching brands:', error);
            var $submitBtn = $('#inv_report button[type="submit"]');
            $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
            
            // Still try to fetch batches even if brands failed
            var orgId = $('#ir_org').val();
            var siteIds = $('#ir_site').val();
            var genericIds = $('#ir_generic').val();
            var brandIds = $('#ir_brand').val();
            fetchBatchesForReport(orgId, siteIds, genericIds, brandIds);
        }
    });
}

// Function to fetch batches based on all fields for report
function fetchBatchesForReport(orgId, siteIds, genericIds, brandIds, showLoading = true) {
    // Convert arrays to comma-separated strings
    var siteIdsStr = Array.isArray(siteIds) ? siteIds.join(',') : siteIds;
    var genericIdsStr = Array.isArray(genericIds) ? genericIds.join(',') : genericIds;
    var brandIdsStr = Array.isArray(brandIds) ? brandIds.join(',') : brandIds;
    if (!orgId || !siteIdsStr || !genericIdsStr || !brandIdsStr) {
        $('#ir_batch').html('<option selected value="">No Data Available</option>').prop('disabled', true).selectpicker('refresh');
        return;
    }
    var $submitBtn = $('#inv_report button[type="submit"]');
    $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Loading').prop('disabled', true);

    $.ajax({
        url: '/inventory/getbatchnoreporting',
        method: 'GET',
        data: {
            orgId: orgId,
            siteId: siteIdsStr,
            genericId: genericIdsStr,
            brandId: brandIdsStr
        },
        beforeSend: function() {
            if (showLoading) {
                $('#ir_batch').html('<option>Loading...</option>');
            }
        },
        success: function(response) {
            if (response && response.length > 0) {
                var batchOptions = '';
                if(siteIdsStr == '0101' && genericIdsStr == '0101' && brandIdsStr == '0101')
                {
                    batchOptions = '<option selected value="0101">Select All</option>';
                    response.forEach(function(batch) {
                        batchOptions += '<option value="' + batch.batch_no + '">' + batch.batch_no + '</option>';
                    });
                }
                else{
                    if (response.length === 1) {
                        response.forEach(function(batch) {
                            batchOptions += '<option selected value="' + batch.batch_no + '">' + batch.batch_no + '</option>';
                        });
                    } else {
                        batchOptions = '<option selected value="0101">Select All</option>';
                        response.forEach(function(batch) {
                            batchOptions += '<option value="' + batch.batch_no + '">' + batch.batch_no + '</option>';
                        });
                    }
                }
                $('#ir_batch').html(batchOptions);
                $('#ir_batch').selectpicker();
                $('#ir_batch').selectpicker('refresh');
                $('#ir_batch').prop('disabled', false);
                
                var $submitBtn = $('#inv_report button[type="submit"]');
                $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
            }
            else {
                $('#ir_batch').html('<option selected value="">No Data Found</option>').prop('disabled', true).selectpicker('refresh');
                var $submitBtn = $('#inv_report button[type="submit"]');
                $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error fetching batches:', error);
            $('#ir_batch').html('<option selected value="">No Data Found</option>').prop('disabled', true).selectpicker('refresh');
            var $submitBtn = $('#inv_report button[type="submit"]');
            $submitBtn.html('<i class="mdi mdi-file-document"></i> Get Inventory Report').prop('disabled', false);
        }
    });
}

// Function to fetch locations based on selected sites for inventory report
function fetchLocationsForReport(siteIds, showLoading = true) {
    // Convert arrays to comma-separated strings
    var siteIdsStr = Array.isArray(siteIds) ? siteIds.join(',') : siteIds;
    
    if (!siteIdsStr) {
        $('#ir_location').html('<option selected value="0101">Select All</option>').prop('disabled', false).selectpicker('refresh');
        return;
    }

    $.ajax({
        url: '/services/getinventoryreportlocations',
        method: 'GET',
        data: {
            siteIds: siteIdsStr,
            inventoryStatus: true,
            empCheck: false
        },
        beforeSend: function() {
            if (showLoading) {
                $('#ir_location').html('<option>Loading...</option>');
            }
        },
        success: function(response) {
            if (response && response.length > 0) {

                var locationOptions = '';
                if(siteIdsStr == '0101')
                {
                    locationOptions = '<option selected value="0101">Select All</option>';
                    response.forEach(function(location) {
                        locationOptions += '<option value="' + location.id + '">' + location.name + '</option>';
                    });
                }
                else{
                    if (response.length === 1) {
                        response.forEach(function(location) {
                            locationOptions += '<option selected value="' + location.id + '">' + location.name + '</option>';
                        });
                    } else {
                        locationOptions = '<option selected value="0101">Select All</option>';
                        response.forEach(function(location) {
                            locationOptions += '<option  value="' + location.id + '">' + location.name + '</option>';
                        });
                    }
                }

                $('#ir_location').html(locationOptions);
                $('#ir_location').selectpicker();
                $('#ir_location').selectpicker('refresh');
                $('#ir_location').prop('disabled', false);
            }
            else {
                $('#ir_location').html('<option selected value="0101">Select All</option>').prop('disabled', false).selectpicker('refresh');
            }
        },
        error: function(xhr, status, error) {
            console.log('Error fetching locations:', error);
            $('#ir_location').html('<option selected value="0101">Select All</option>').prop('disabled', false).selectpicker('refresh');
        }
    });
}
