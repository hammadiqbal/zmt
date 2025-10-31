// View Logs Modal Handler
$(document).ready(function() {
    $(document).on('click', '.logs-modal', function() {
        const logId = $(this).data('log-id');
        const modal = $('#logs');
        const modalBody = modal.find('.modal-body .row');
        const modalContent = modal.find('.modal-body');
        modalBody.empty();
        $('#ajax-loader').show();

        if (!logId) {
            modalBody.append($('<div class="col-12 m-t-10">').text('No logs available'));
            modal.modal('show');
            $('#ajax-loader').hide();
            return;
        }
        
        // Reset pagination on initial load
        modal.data('offset', 0);
        
        const url = '/viewlogs/' + logId;
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data: {
                offset: 0,
                limit: 10
            },
            success: function(response) {
                $('#ajax-loader').hide();

                // Handle both old format (array) and new format (object with data)
                const logs = response.data || response;
                const hasMore = response.has_more || false;
                
                if (logs.length > 0) {
                    modal.find('.modal-title').text('Logs History');
                    
                    // Store pagination info
                    modal.data('offset', (modal.data('offset') || 0) + logs.length);
                    modal.data('has-more', hasMore);
                    
                    logs.forEach(log => {
                        const eventBadge = log.event === 'insert' ? 
                            '<span class="badge badge-success">INSERT</span>' : 
                            log.event === 'update' ? 
                            '<span class="badge badge-info">UPDATE</span>' : 
                            log.event === 'status_change' ? 
                            '<span class="badge badge-warning">STATUS CHANGE</span>' : 
                            '<span class="badge badge-secondary">' + log.event.toUpperCase() + '</span>';
                        
                        // Create card header with summary on right
                        const cardHeader = $('<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">');
                        
                        // Left side: timestamp and event
                        const leftSide = $('<div>')
                            .html(`<strong>${log.timestamp}</strong><br><small>Event: ${eventBadge}</small>`);
                        
                        // Right side: summary message with user info
                        const rightSide = $('<div class="text-right">');
                        if (log.summary && typeof log.summary === 'object') {
                            let summaryText = '';
                            
                            // Get message and user info
                            if (log.summary.message) {
                                summaryText = log.summary.message;
                                // Capitalize first letter of each word
                                summaryText = summaryText.toLowerCase().replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }
                            
                            // Add user info if available
                            let userInfo = '';
                            if (log.summary.updated_by) {
                                userInfo = ` By: ${log.summary.updated_by}`;
                            } else if (log.summary.created_by) {
                                userInfo = ` By: ${log.summary.created_by}`;
                            }
                            
                            rightSide.append($('<small>').html(`<strong>${summaryText}${userInfo}</strong>`));
                        }
                        
                        cardHeader.append(leftSide);
                        cardHeader.append(rightSide);
                        
                        // Create card body
                        const cardBody = $('<div class="card-body">');
                        
                        // Add data comparison for updates
                        if (log.event === 'update' && log.previous_data && log.new_data) {
                            const comparisonDiv = $('<div class="row">');
                            
                            // Previous Data
                            const prevCol = $('<div class="col-md-6">')
                                .html('<h6><strong>Previous Data:</strong></h6>')
                                .append(createDataTable(log.previous_data));
                            
                            // New Data
                            const newCol = $('<div class="col-md-6">')
                                .html('<h6><strong>New Data:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            
                            comparisonDiv.append(prevCol).append(newCol);
                            cardBody.append(comparisonDiv);
                        }
                        // For insert, show new_data
                        else if (log.event === 'insert' && log.new_data) {
                            const newDataDiv = $('<div>')
                                .html('<h6><strong>Inserted Data:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            cardBody.append(newDataDiv);
                        }
                        // For status change
                        else if (log.event === 'status_change' && log.previous_data && log.new_data) {
                            const comparisonDiv = $('<div class="row">');
                            
                            const prevCol = $('<div class="col-md-6">')
                                .html('<h6><strong>Previous Status:</strong></h6>')
                                .append(createDataTable(log.previous_data));
                            
                            const newCol = $('<div class="col-md-6">')
                                .html('<h6><strong>New Status:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            
                            comparisonDiv.append(prevCol).append(newCol);
                            cardBody.append(comparisonDiv);
                        }
                        
                        // Create card
                        const card = $('<div class="card mb-3">')
                            .append(cardHeader)
                            .append(cardBody);
                        
                        modalBody.append($('<div class="col-12">').append(card));
                    });
                } else {
                    modalBody.append($('<div class="col-12 m-t-10">').text('Logs not found'));
                }
                
                // Add scroll handler for infinite scroll on modal content
                modalContent.off('scroll').on('scroll', function() {
                    const $this = $(this);
                    if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 50) {
                        loadMoreLogs(logId, modalBody, modal);
                    }
                });
                
                modal.modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ajax-loader').hide();
                console.log(textStatus, errorThrown);
            }
        });
    });
    
    // Helper function to create data table
    function createDataTable(data) {
        const table = $('<table class="table table-sm table-bordered">');
        const tbody = $('<tbody>');
        
        Object.keys(data).forEach(key => {
            const row = $('<tr>');
            let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            if (label === 'Type Id') {
                label = 'Type';
            } else if (label === 'Group Id') {
                label = 'Group';
            } else if (label === 'Unit Id') {
                label = 'Unit';
            } else if (label === 'Site Id') {
                label = 'Site';
            } else if (label === 'Service Location Id') {
                label = 'Service Location';
            } else if (label === 'Service Id') {
                label = 'Service';
            } else if (label === 'Service Mode Id') {
                label = 'Service Mode';
            } else if (label === 'Schedule Id') {
                label = 'Schedule';
            } else if (label === 'Emp Id') {
                label = 'Employee';
            } else if (label === 'Billing Cc') {
                label = 'Billing Cost Center';
            } else if (label === 'Ordering Cc Ids') {
                label = 'Ordering Cost Centers';
            } else if (label === 'Performing Cc Ids') {
                label = 'Performing Cost Centers';
            } else if (label === 'Servicemode Ids') {
                label = 'Service Modes';
            }else if (label === 'Org Id') {
                label = 'Organization';
            }
            else if (label === 'District Id') {
                label = 'District';
            }
            else if (label === 'Province Id') {
                label = 'Province';
            }
            else if (label === 'Division Id') {
                label = 'Division';
            }
            else if (label === 'Cat Id') {
                label = 'Item Category';
            }
            else if (label === 'Sub Catid') {
                label = 'Item Sub Category';
            }
            else if (label === 'Generic Id') {
                label = 'Item Generic';
            }
            else if (label === 'Brand Id') {
                label = 'Item Brand';
            }else if (label === 'Prefix Id') {   
                label = 'Prefix';
            }else if (label === 'Thirdpartycategory') {  
                label = 'Category';
            }else if (label === 'Thirdparty Type') {        
                label = 'Type';
            }else if (label === 'Vendor Id') {
                label = 'Vendor';
            }
        
            row.append($('<th style="width:40%">').text(label));
            row.append($('<td>').text(data[key] === null || data[key] === '' ? '-' : data[key]));
            tbody.append(row);
        });
        
        table.append(tbody);
        return table;
    }
    
    // Function to load more logs on scroll
    function loadMoreLogs(logId, modalBody, modal) {
        const hasMore = modal.data('has-more');
        if (!hasMore) return;
        
        const offset = modal.data('offset') || 0;
        const loading = modal.data('loading');
        
        if (loading) return;
        modal.data('loading', true);
        
        $.ajax({
            url: '/viewlogs/' + logId,
            type: 'GET',
            dataType: 'json',
            data: {
                offset: offset,
                limit: 10
            },
            success: function(response) {
                const logs = response.data || response;
                const hasMore = response.has_more || false;
                
                if (logs.length > 0) {
                    modal.data('offset', offset + logs.length);
                    modal.data('has-more', hasMore);
                    
                    logs.forEach(log => {
                        const eventBadge = log.event === 'insert' ? 
                            '<span class="badge badge-success">INSERT</span>' : 
                            log.event === 'update' ? 
                            '<span class="badge badge-info">UPDATE</span>' : 
                            log.event === 'status_change' ? 
                            '<span class="badge badge-warning">STATUS CHANGE</span>' : 
                            '<span class="badge badge-secondary">' + log.event.toUpperCase() + '</span>';
                        
                        const cardHeader = $('<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">');
                        const leftSide = $('<div>')
                            .html(`<strong>${log.timestamp}</strong><br><small>Event: ${eventBadge}</small>`);
                        const rightSide = $('<div class="text-right">');
                        
                        if (log.summary && typeof log.summary === 'object') {
                            let summaryText = '';
                            if (log.summary.message) {
                                summaryText = log.summary.message.toLowerCase().replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }
                            let userInfo = '';
                            if (log.summary.updated_by) {
                                userInfo = ` By: ${log.summary.updated_by}`;
                            } else if (log.summary.created_by) {
                                userInfo = ` By: ${log.summary.created_by}`;
                            }
                            rightSide.append($('<small>').html(`<strong>${summaryText}${userInfo}</strong>`));
                        }
                        
                        cardHeader.append(leftSide);
                        cardHeader.append(rightSide);
                        
                        const cardBody = $('<div class="card-body">');
                        
                        if (log.event === 'update' && log.previous_data && log.new_data) {
                            const comparisonDiv = $('<div class="row">');
                            const prevCol = $('<div class="col-md-6">')
                                .html('<h6><strong>Previous Data:</strong></h6>')
                                .append(createDataTable(log.previous_data));
                            const newCol = $('<div class="col-md-6">')
                                .html('<h6><strong>New Data:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            comparisonDiv.append(prevCol).append(newCol);
                            cardBody.append(comparisonDiv);
                        } else if (log.event === 'insert' && log.new_data) {
                            const newDataDiv = $('<div>')
                                .html('<h6><strong>Inserted Data:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            cardBody.append(newDataDiv);
                        } else if (log.event === 'status_change' && log.previous_data && log.new_data) {
                            const comparisonDiv = $('<div class="row">');
                            const prevCol = $('<div class="col-md-6">')
                                .html('<h6><strong>Previous Status:</strong></h6>')
                                .append(createDataTable(log.previous_data));
                            const newCol = $('<div class="col-md-6">')
                                .html('<h6><strong>New Status:</strong></h6>')
                                .append(createDataTable(log.new_data));
                            comparisonDiv.append(prevCol).append(newCol);
                            cardBody.append(comparisonDiv);
                        }
                        
                        const card = $('<div class="card mb-3">')
                            .append(cardHeader)
                            .append(cardBody);
                        
                        modalBody.append($('<div class="col-12">').append(card));
                    });
                }
                modal.data('loading', false);
            },
            error: function() {
                modal.data('loading', false);
            }
        });
    }
});

