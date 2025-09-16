<!DOCTYPE html>
<html>

<head>
    <title>Purchase Order</title>
    <style>
       h1,table{margin-bottom:20px}th,tr.total-row{font-weight:700;background-color:#f4f4f4}.footer,h1,h3{text-align:center}.footer p,body{margin:0}body{font-family:Arial,sans-serif;padding:0;box-sizing:border-box}h1{font-size:22px}h3{font-size:16px;margin-top:10px;margin-bottom:10px}p{font-size:14px;line-height:1.6}table{width:100%;border-collapse:collapse}table,td,th{border:1px solid #ddd}td,th{padding:10px;text-align:left}td{background-color:#fafafa}.footer{font-size:12px;margin-top:30px}@media print{body{width:100%;height:100%}table{page-break-before:always;page-break-inside:avoid;break-inside:avoid}.footer{position:absolute;bottom:0;left:0;right:0}h1,h3{font-size:14px}p{font-size:12px}table td,table th{font-size:12px;padding:8px}}
    </style>
</head>

<body>
    <h1>PO#: {{ $PONo }}</h1>
    <h3>Date: {{ $effectiveDate }}</h3>
    <p><b>Organization:</b> {{ ucwords($orgName) }}</p>
    <p><b>Site:</b> {{ ucwords($siteName) }}</p>
    <p><b>Vendor Name:</b> {{ ucwords($vendorName) }}</p>

    <h3>Item Details</h3>
    <table>
        <thead>
            <tr>
                <th>Brand Name</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Discount</th>
                <th>Net Payable</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            {!! $formattedData !!}
        </tbody>
    </table>

    <p><b>Approved By:</b> {{ $ApproverName }}</p>
    <div class="footer">
        <p>It’s a computer-generated document, does not require any signature.</p>
    </div>
</body>

</html>
