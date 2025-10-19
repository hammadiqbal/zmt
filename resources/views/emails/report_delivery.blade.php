<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Delivery - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #495057;
        }
        .main-message {
            font-size: 16px;
            margin-bottom: 25px;
            color: #495057;
        }
        .report-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 25px 0;
            border-left: 4px solid #007bff;
        }
        .report-info h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .attachment-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 25px 0;
            color: #856404;
            font-size: 14px;
        }
        .attachment-note strong {
            color: #6c5ce7;
        }
        .signature {
            margin-top: 30px;
            color: #495057;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 12px;
        }
        .automated-note {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            border-radius: 4px;
            margin-top: 20px;
            color: #6c757d;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <div class="greeting">
                <p>Dear {{ ($user && !empty($user->userName)) ? $user->userName : 'User' }},</p>
                <!-- Debug: Available user fields: {{ $user ? json_encode(array_keys($user->toArray())) : 'No user object' }} -->
            </div>

            <div class="main-message">
                <p>Your requested report has been successfully generated and is attached to this email.</p>
            </div>

            <div class="report-info">
                <h3>Report Details</h3>
                <div class="info-row">
                    <span class="info-label">Report Type:</span> 
                    <span class="info-value">{{ strtoupper($report->report_type) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Generated On:</span> 
                    <span class="info-value">{{ $report->processed_at ? $report->processed_at->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">File:</span> 
                    <span class="info-value">{{ $report->file_name ?? 'N/A' }}</span>
                </div>
            </div>

            @if(isset($filterData))
            <div class="report-info" style="margin-top: 20px;">
                <h3>Filter Criteria Used</h3>
                <div class="info-row">
                    <span class="info-label">Date Range:</span> 
                    <span class="info-value">{{ $filterData['date_range'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sites:</span> 
                    <span class="info-value">{{ $filterData['sites'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Transaction Types:</span> 
                    <span class="info-value">{{ $filterData['transaction_types'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Generics:</span> 
                    <span class="info-value">{{ $filterData['generics'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Brands:</span> 
                    <span class="info-value">{{ $filterData['brands'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Batch Numbers:</span> 
                    <span class="info-value">{{ $filterData['batches'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Locations:</span> 
                    <span class="info-value">{{ $filterData['locations'] ?? 'N/A' }}</span>
                </div>
            </div>
            @endif

            <div class="attachment-note">
                <strong>📎 Report Attachment:</strong> Your report is attached to this email for your convenience.
            </div>

            <div class="signature">
                <p>Best regards,<br>
                <strong>{{ config('app.name') }}</strong></p>
            </div>
        </div>

        <div class="footer">
            <div class="automated-note">
                This is an automated email. Please do not reply to this message.
            </div>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
