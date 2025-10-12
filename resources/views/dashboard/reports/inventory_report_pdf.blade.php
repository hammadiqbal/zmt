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
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 8px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
            <span class="info-label">Sites:</span>
            <span class="info-value">
                @if(in_array('0101', $sites))
                    All Sites
                @else
                    {{ implode(', ', $sites) }}
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Records:</span>
            <span class="info-value">{{ $processedData->count() }}</span>
        </div>
    </div>

    @if($processedData->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Date & Time</th>
                    <th>Transaction Type</th>
                    <th>Generic Name</th>
                    <th>Brand Name</th>
                    <th>Batch No</th>
                    <th class="text-center">Transaction Qty</th>
                    <th class="text-center">Org Balance</th>
                    <th class="text-center">Site Balance</th>
                    <th class="text-center">Location Balance</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Ref Document</th>
                    <th>MR Code</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($processedData as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            @if($item->timestamp)
                                {{ \Carbon\Carbon::createFromTimestamp($item->timestamp)->format('m/d/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $item->transaction_type_name ?? 'N/A' }}</td>
                        <td><strong>{{ $item->generic_name ?? 'N/A' }}</strong></td>
                        <td>{{ $item->brand_name ?? 'N/A' }}</td>
                        <td>{{ $item->batch_no ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->accurate_transaction_qty ?? '0' }}</td>
                        <td class="text-center">{{ $item->org_balance ?? '0' }}</td>
                        <td class="text-center">{{ $item->site_balance ?? '0' }}</td>
                        <td class="text-center">{{ $item->location_balance ?? '0' }}</td>
                        <td>
                            @php
                                $sourceDisplay = $item->source ?? '';
                                if ($item->source_type_name && str_contains(strtolower($item->source_type_name), 'location') && $item->source_location_name) {
                                    $sourceDisplay = $item->source_location_name . ' (' . $item->source_type_name . ')';
                                } elseif ($item->source_type_name) {
                                    $sourceDisplay = $sourceDisplay . ' (' . $item->source_type_name . ')';
                                }
                            @endphp
                            {{ $sourceDisplay }}
                        </td>
                        <td>
                            @php
                                $destinationDisplay = $item->destination ?? '';
                                if ($item->destination_type_name && str_contains(strtolower($item->destination_type_name), 'location') && $item->destination_location_name) {
                                    $destinationDisplay = $item->destination_location_name . ' (' . $item->destination_type_name . ')';
                                } elseif ($item->destination_type_name) {
                                    $destinationDisplay = $destinationDisplay . ' (' . $item->destination_type_name . ')';
                                }
                            @endphp
                            {{ $destinationDisplay }}
                        </td>
                        <td>{{ $item->ref_document_no ?? 'N/A' }}</td>
                        <td>{{ $item->mr_code ?? 'N/A' }}</td>
                        <td>{{ $item->remarks ? substr($item->remarks, 0, 50) . (strlen($item->remarks) > 50 ? '...' : '') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>No Data Found</h3>
            <p>No inventory records found for the selected criteria.</p>
        </div>
    @endif

</body>
</html>
