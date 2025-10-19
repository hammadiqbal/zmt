<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 8px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            margin: 8px 0 0 0;
            color: #7f8c8d;
            font-size: 12px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 12px;
            padding: 12px;
            background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
            border-radius: 8px;
            border: 1px solid #95a5a6;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            font-size: 11px;
        }
        .info-value {
            color: #34495e;
            font-size: 11px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #bdc3c7;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success {
            background-color: #27ae60;
            color: white;
        }
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        .badge-secondary {
            background-color: #7f8c8d;
            color: white;
        }
        .badge-info {
            background-color: #3498db;
            color: white;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 2px solid #bdc3c7;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <h2>Generated on {{ date('F d, Y \a\t H:i:s') }}</h2>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Date Range:</span>
            <span class="info-value">{{ $startDateInput }} to {{ $endDateInput }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Sites:</span>
            <span class="info-value">
                @php
                    $siteCount = 0;
                    if ($processedData->count() > 0) {
                        $uniqueSiteIds = $processedData->pluck('site_id')->unique()->filter()->count();
                        $siteCount = $uniqueSiteIds;
                    }
                @endphp
                {{ $siteCount }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Records:</span>
            <span class="info-value">{{ $processedData->count() }}</span>
        </div>
    </div>

    @if($processedData->count() > 0)
        @php
            // Helper function to get property from array or object
            function getItemProperty($item, $property, $default = null) {
                return is_array($item) ? ($item[$property] ?? $default) : ($item->$property ?? $default);
            }
            
            // Group data by generic_name + brand_name + batch_no
            $grouped = [];
            foreach($processedData as $item) {
                $genericName = getItemProperty($item, 'generic_name', 'Unknown');
                $brandName = getItemProperty($item, 'brand_name', 'Unknown');
                $batchNo = getItemProperty($item, 'batch_no', 'Unknown');
                
                $key = $genericName . '|' . $brandName . '|' . $batchNo;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'generic' => $genericName,
                        'brand' => $brandName,
                        'batch' => $batchNo,
                        'items' => [],
                        'final_org_balance' => 0,
                        'site_balances' => [],
                        'location_balances' => []
                    ];
                }
                $grouped[$key]['items'][] = $item;
                
                // Calculate final balances (use the last transaction's balance)
                $grouped[$key]['final_org_balance'] = getItemProperty($item, 'org_balance', 0);
                
                // Collect site balances
                $siteName = getItemProperty($item, 'site_name');
                if (!empty($siteName)) {
                    $grouped[$key]['site_balances'][$siteName] = getItemProperty($item, 'site_balance', 0);
                }
                
                // Collect location balances
                $locationName = getItemProperty($item, 'location_name');
                if (!empty($locationName)) {
                    $grouped[$key]['location_balances'][$locationName] = getItemProperty($item, 'location_balance', 0);
                }
            }
        @endphp

        @foreach($grouped as $key => $group)
            <div style="background-color: #f8f9fa; border: 2px solid #2c3e50; margin: 5px 0; padding: 10px; page-break-inside: avoid;">
                <!-- Product Info Boxes -->
                <div style="margin-bottom: 8px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 33.33%; text-align: center; background-color: #ffffff; border: 2px solid #2c3e50; padding: 8px;">
                                <div style="font-size: 12px; font-weight: bolder; color: black; text-transform: uppercase; margin-bottom: 3px;">GENERIC</div>
                                <div style="font-size: 11px; font-weight: bold; color: #34495e;">{{ $group['generic'] }}</div>
                            </td>
                            <td style="width: 33.33%; text-align: center; background-color: #ffffff; border: 2px solid #2c3e50; padding: 8px;">
                                <div style="font-size: 12px; font-weight: bolder; color: black; text-transform: uppercase; margin-bottom: 3px;">BRAND</div>
                                <div style="font-size: 11px; font-weight: bold; color: #34495e;">{{ $group['brand'] }}</div>
                            </td>
                            <td style="width: 33.33%; text-align: center; background-color: #ffffff; border: 2px solid #2c3e50; padding: 8px;">
                                <div style="font-size: 12px; font-weight: bolder; color: black; text-transform: uppercase; margin-bottom: 3px;">BATCH</div>
                                <div style="font-size: 11px; font-weight: bold; color: #34495e;">{{ $group['batch'] }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Balances Summary Line -->
                <div style="margin-bottom: 8px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 25%; text-align: center; background-color: #ffffff; border: 1px solid #2c3e50; padding: 4px;">
                                <div style="font-size: 8px; font-weight: bold; color: #27ae60; text-transform: uppercase;">ORG BALANCE</div>
                                <div style="font-size: 9px; font-weight: bold; color: #2c3e50;">{{ $group['final_org_balance'] }}</div>
                            </td>
                            <td style="width: 50%; text-align: center; background-color: #ffffff; border: 1px solid #2c3e50; padding: 4px;">
                                <div style="font-size: 8px; font-weight: bold; color: #f39c12; text-transform: uppercase;">SITE - LOCATION BALANCE</div>
                                <div style="font-size: 9px; font-weight: bold; color: #2c3e50;">
                                    @php
                                        $combinedBalances = [];
                                        foreach($group['items'] as $item) {
                                            $siteName = getItemProperty($item, 'site_name');
                                            $locationName = getItemProperty($item, 'location_name');
                                            
                                            if (!empty($siteName) && !empty($locationName)) {
                                                $key = $siteName . ' - ' . $locationName;
                                                $combinedBalances[$key] = getItemProperty($item, 'site_balance', 0);
                                            } elseif (!empty($siteName)) {
                                                $combinedBalances[$siteName] = getItemProperty($item, 'site_balance', 0);
                                            } elseif (!empty($locationName)) {
                                                $combinedBalances[$locationName] = getItemProperty($item, 'location_balance', 0);
                                            }
                                        }
                                    @endphp
                                    @if(count($combinedBalances) > 0)
                                        @foreach($combinedBalances as $name => $balance)
                                            {{ $name }}: {{ $balance }}@if(!$loop->last)<br>@endif
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </td>
                            <td style="width: 25%; text-align: center; background-color: #ffffff; border: 1px solid #2c3e50; padding: 4px;">
                                <div style="font-size: 8px; font-weight: bold; color: #3498db; text-transform: uppercase;">TOTAL TRANSACTIONS</div>
                                <div style="font-size: 9px; font-weight: bold; color: #2c3e50;">{{ count($group['items']) }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
                

                <!-- Transactions Table -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
            <thead>
                <tr>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; color: white;">Transaction Info</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; color: white;">Transaction Qty</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; color: white;">Source</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; color: white;">Destination</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; color: white;">MR Code</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; color: white;">Org Balance</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; color: white;">Site Balance</th>
                            <th style="background-color: #34495e; border: 2px solid #2c3e50; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; color: white;">Location Balance</th>
                </tr>
            </thead>
            <tbody>
                        @foreach($group['items'] as $item)
                            <tr style="background-color: {{ $loop->even ? '#f8f9fa' : '#ffffff' }};">
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">
                                    @php
                                        $formattedDate = '';
                                        $timestamp = getItemProperty($item, 'timestamp');
                                        if ($timestamp) {
                                            $formattedDate = \Carbon\Carbon::createFromTimestamp($timestamp)->format('m/d/Y H:i');
                                        }
                                    @endphp
                                    <strong>Type:</strong> {{ getItemProperty($item, 'transaction_type_name', 'N/A') }}<br>
                                    <strong>Ref Doc #:</strong> {{ getItemProperty($item, 'ref_document_no', 'N/A') }}<br>
                                    <strong>Date:</strong> {{ $formattedDate ?: 'N/A' }}<br>
                                    <strong>{{ getItemProperty($item, 'site_label', 'Site:') }}</strong> {{ getItemProperty($item, 'site_name', 'N/A') }}<br>
                                    <strong>Remarks:</strong> {{ getItemProperty($item, 'remarks', 'N/A') }}
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #3498db; color: white; padding: 2px 4px; font-weight: bold;">{{ getItemProperty($item, 'accurate_transaction_qty', '0') }}</span>
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">
                            @php
                                $sourceDisplay = getItemProperty($item, 'source', '');
                                $sourceTypeName = getItemProperty($item, 'source_type_name');
                                $sourceLocationName = getItemProperty($item, 'source_location_name');
                                
                                if ($sourceTypeName && str_contains(strtolower($sourceTypeName), 'location') && $sourceLocationName) {
                                    $sourceDisplay = $sourceLocationName . ' (' . $sourceTypeName . ')';
                                } elseif ($sourceTypeName && str_contains(strtolower($sourceTypeName), 'vendor')) {
                                    $vendorName = getItemProperty($item, 'source_vendor_person_name', '');
                                    $corporateName = getItemProperty($item, 'source_vendor_corporate_name', '');
                                    if ($vendorName && $corporateName) {
                                        $sourceDisplay = $vendorName . ' - ' . $corporateName;
                                    } elseif ($vendorName) {
                                        $sourceDisplay = $vendorName;
                                    } elseif ($corporateName) {
                                        $sourceDisplay = $corporateName;
                                    } else {
                                        $sourceDisplay = 'Vendor ID: ' . getItemProperty($item, 'source', '');
                                    }
                                    $sourceDisplay .= ' (' . $sourceTypeName . ')';
                                } elseif ($sourceTypeName) {
                                    $sourceDisplay = $sourceDisplay . ' (' . $sourceTypeName . ')';
                                }
                            @endphp
                            {{ $sourceDisplay }}
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">
                            @php
                                $destinationDisplay = getItemProperty($item, 'destination', '');
                                $destinationTypeName = getItemProperty($item, 'destination_type_name');
                                $destinationLocationName = getItemProperty($item, 'destination_location_name');
                                
                                if ($destinationTypeName && str_contains(strtolower($destinationTypeName), 'location') && $destinationLocationName) {
                                    $destinationDisplay = $destinationLocationName . ' (' . $destinationTypeName . ')';
                                } elseif ($destinationTypeName && str_contains(strtolower($destinationTypeName), 'vendor')) {
                                    $vendorName = getItemProperty($item, 'destination_vendor_person_name', '');
                                    $corporateName = getItemProperty($item, 'destination_vendor_corporate_name', '');
                                    if ($vendorName && $corporateName) {
                                        $destinationDisplay = $vendorName . ' - ' . $corporateName;
                                    } elseif ($vendorName) {
                                        $destinationDisplay = $vendorName;
                                    } elseif ($corporateName) {
                                        $destinationDisplay = $corporateName;
                                    } else {
                                        $destinationDisplay = 'Vendor ID: ' . getItemProperty($item, 'destination', '');
                                    }
                                    $destinationDisplay .= ' (' . $destinationTypeName . ')';
                                } elseif ($destinationTypeName) {
                                    $destinationDisplay = $destinationDisplay . ' (' . $destinationTypeName . ')';
                                }
                            @endphp
                            {{ $destinationDisplay }}
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">{{ getItemProperty($item, 'mr_code', 'N/A') }}</td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #27ae60; color: white; padding: 2px 4px; font-weight: bold;">{{ getItemProperty($item, 'org_balance', '0') }}</span>
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #f39c12; color: white; padding: 2px 4px; font-weight: bold;">{{ getItemProperty($item, 'site_balance', '0') }}</span>
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #7f8c8d; color: white; padding: 2px 4px; font-weight: bold;">{{ getItemProperty($item, 'location_balance', '0') }}</span>
                                </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>No Data Found</h3>
            <p>No inventory records found for the selected criteria.</p>
        </div>
    @endif

</body>
</html>
