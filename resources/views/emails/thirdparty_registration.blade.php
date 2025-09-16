<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        /* Add any additional custom styles here */
    </style>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-2">Welcome to {{ config('app.name') }}</h1>
        <p>We're excited to have you on board!</p>
        <h2 class="mt-4">Your Registration Details:</h2>
        <ul>
            <li><strong>Type:</strong> {{ ucwords($RegistrationType) }}</li>
            <li><strong>Category:</strong> {{ ucwords($VendorCat) }}</li>
            <li><strong>Address:</strong> {{ ucwords($Address) }}</li>
            <li><strong>Organization:</strong> {{ ucwords($orgName) }}</li>
            <li><strong>Focal Person Name:</strong> {{ ucwords($PersonName) }}</li>
            <li><strong>Focal Person Email:</strong> {{ $Email }}</li>
            <li><strong>Cell #:</strong> {{ $Cell }}</li>
            <li><strong>Landline #:</strong> {{ $Landline ?? 'N/A' }}</li>
            <li><strong>Remarks:</strong> {{ $Remarks ? ucwords($Remarks) : 'N/A' }}</li>
            <li><strong>Status:</strong> {{ $emailStatus }}</li>
            <li><strong>Effective Date&Time:</strong> {{ $emailEdt }}</li>
            <li><strong>Added On:</strong> {{ $emailTimestamp }}</li>
        </ul>

        <p>Thank you for joining {{ config('app.name') }}!</p>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
