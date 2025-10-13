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
            // Group data by generic_name + brand_name + batch_no
            $grouped = [];
            foreach($processedData as $item) {
                $key = ($item->generic_name ?? 'Unknown') . '|' . ($item->brand_name ?? 'Unknown') . '|' . ($item->batch_no ?? 'Unknown');
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'generic' => $item->generic_name ?? 'Unknown',
                        'brand' => $item->brand_name ?? 'Unknown',
                        'batch' => $item->batch_no ?? 'Unknown',
                        'items' => [],
                        'final_org_balance' => 0,
                        'site_balances' => [],
                        'location_balances' => []
                    ];
                }
                $grouped[$key]['items'][] = $item;
                
                // Calculate final balances (use the last transaction's balance)
                $grouped[$key]['final_org_balance'] = $item->org_balance ?? 0;
                
                // Collect site balances
                if (!empty($item->site_name)) {
                    $grouped[$key]['site_balances'][$item->site_name] = $item->site_balance ?? 0;
                }
                
                // Collect location balances
                if (!empty($item->location_name)) {
                    $grouped[$key]['location_balances'][$item->location_name] = $item->location_balance ?? 0;
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
                                            if (!empty($item->site_name) && !empty($item->location_name)) {
                                                $key = $item->site_name . ' - ' . $item->location_name;
                                                $combinedBalances[$key] = $item->site_balance ?? 0;
                                            } elseif (!empty($item->site_name)) {
                                                $combinedBalances[$item->site_name] = $item->site_balance ?? 0;
                                            } elseif (!empty($item->location_name)) {
                                                $combinedBalances[$item->location_name] = $item->location_balance ?? 0;
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
                                        if ($item->timestamp) {
                                            $formattedDate = \Carbon\Carbon::createFromTimestamp($item->timestamp)->format('m/d/Y H:i');
                                        }
                                    @endphp
                                    <strong>Type:</strong> {{ $item->transaction_type_name ?? 'N/A' }}<br>
                                    <strong>Ref Doc #:</strong> {{ $item->ref_document_no ?? 'N/A' }}<br>
                                    <strong>Date:</strong> {{ $formattedDate ?: 'N/A' }}<br>
                                    <strong>{{ $item->site_label ?? 'Site:' }}</strong> {{ $item->site_name ?? 'N/A' }}<br>
                                    <strong>Remarks:</strong> {{ $item->remarks ?? 'N/A' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #3498db; color: white; padding: 2px 4px; font-weight: bold;">{{ $item->accurate_transaction_qty ?? '0' }}</span>
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">
                            @php
                                $sourceDisplay = $item->source ?? '';
                                if ($item->source_type_name && str_contains(strtolower($item->source_type_name), 'location') && $item->source_location_name) {
                                    $sourceDisplay = $item->source_location_name . ' (' . $item->source_type_name . ')';
                                        } elseif ($item->source_type_name && str_contains(strtolower($item->source_type_name), 'vendor')) {
                                            $vendorName = $item->source_vendor_person_name ?? '';
                                            $corporateName = $item->source_vendor_corporate_name ?? '';
                                            if ($vendorName && $corporateName) {
                                                $sourceDisplay = $vendorName . ' - ' . $corporateName;
                                            } elseif ($vendorName) {
                                                $sourceDisplay = $vendorName;
                                            } elseif ($corporateName) {
                                                $sourceDisplay = $corporateName;
                                            } else {
                                                $sourceDisplay = 'Vendor ID: ' . ($item->source ?? '');
                                            }
                                            $sourceDisplay .= ' (' . $item->source_type_name . ')';
                                } elseif ($item->source_type_name) {
                                    $sourceDisplay = $sourceDisplay . ' (' . $item->source_type_name . ')';
                                }
                            @endphp
                            {{ $sourceDisplay }}
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">
                            @php
                                $destinationDisplay = $item->destination ?? '';
                                if ($item->destination_type_name && str_contains(strtolower($item->destination_type_name), 'location') && $item->destination_location_name) {
                                    $destinationDisplay = $item->destination_location_name . ' (' . $item->destination_type_name . ')';
                                        } elseif ($item->destination_type_name && str_contains(strtolower($item->destination_type_name), 'vendor')) {
                                            $vendorName = $item->destination_vendor_person_name ?? '';
                                            $corporateName = $item->destination_vendor_corporate_name ?? '';
                                            if ($vendorName && $corporateName) {
                                                $destinationDisplay = $vendorName . ' - ' . $corporateName;
                                            } elseif ($vendorName) {
                                                $destinationDisplay = $vendorName;
                                            } elseif ($corporateName) {
                                                $destinationDisplay = $corporateName;
                                            } else {
                                                $destinationDisplay = 'Vendor ID: ' . ($item->destination ?? '');
                                            }
                                            $destinationDisplay .= ' (' . $item->destination_type_name . ')';
                                } elseif ($item->destination_type_name) {
                                    $destinationDisplay = $destinationDisplay . ' (' . $item->destination_type_name . ')';
                                }
                            @endphp
                            {{ $destinationDisplay }}
                        </td>
                                <td style="border: 1px solid #000; padding: 5px; font-size: 8px;">{{ $item->mr_code ?? 'N/A' }}</td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #27ae60; color: white; padding: 2px 4px; font-weight: bold;">{{ $item->org_balance ?? '0' }}</span>
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #f39c12; color: white; padding: 2px 4px; font-weight: bold;">{{ $item->site_balance ?? '0' }}</span>
                                </td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 8px;">
                                    <span style="background-color: #7f8c8d; color: white; padding: 2px 4px; font-weight: bold;">{{ $item->location_balance ?? '0' }}</span>
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
