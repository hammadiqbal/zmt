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
        <h2 class="mt-2">Congratulations! Your Successful Registration as a Donor in Our System is Confirmed</h2>
        <p>We're excited to have you on board!</p>

        <h2 class="mt-4">Your Details:</h2>
        <ul>
            <li><strong>Organization Name:</strong> {{ $orgName }}</li>
            <li><strong>Donor Type:</strong> {{ $DonorType }}</li>
            @if(!empty($CorporateName))
            <li><strong>Corporate Name:</strong> {{ ucwords($CorporateName) }}</li>
            @endif
            <li><strong>Focal Person Name:</strong> {{ $FocalPersonName }}</li>
            <li><strong>Focal Person Email:</strong> {{ $FocalPersonEmail }}</li>
            <li><strong>Focal Person Cell #:</strong> {{ $FocalPersonCell }}</li>
            @if(!empty($FocalPersonLandline))
            <li><strong>Focal Person Landline #:</strong> {{ ucwords($FocalPersonLandline) }}</li>
            @endif
            <li><strong>Address:</strong> {{ $Address }}</li>
            <li><strong>Remarks:</strong> {{ $Remarks }}</li>
            <li><strong>Effective Date&Time:</strong> {{ $emailEdt }}</li>
        </ul>

        <p>Thank you for joining {{ config('app.name') }}!</p>

        <hr>

        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
