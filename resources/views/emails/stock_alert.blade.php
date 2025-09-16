<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .info-table th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Stock Alert Notification</h2>
    </div>

    <div class="alert-box">
        <h3>Alert Type: {{ ucfirst(str_replace('_', ' ', $alertType)) }}</h3>
        <p><strong>{{ $alertMessage }}</strong></p>
    </div>

    <table class="info-table">
        <tr>
            <th>Item Generic</th>
            <td>{{ $itemGeneric }}</td>
        </tr>
        <tr>
            <th>Item Brand</th>
            <td>{{ $itemBrand }}</td>
        </tr>
        <tr>
            <th>Organization</th>
            <td>{{ $orgName }}</td>
        </tr>
        <tr>
            <th>Site</th>
            <td>{{ $siteName }}</td>
        </tr>
        <tr>
            <th>Location</th>
            <td>{{ $locationName }}</td>
        </tr>
        <tr>
            <th>Current Balance</th>
            <td>{{ $currentBalance }}</td>
        </tr>
        <tr>
            <th>Threshold Value</th>
            <td>{{ $thresholdValue }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>This alert was generated automatically by ZMT EMR.</p>
        <p>Please take appropriate action to address this stock level issue.</p>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
