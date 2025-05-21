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
        <h1 class="mt-2">Welcome to {{ ucwords($orgName) }}</h1>
        <p>We're excited to have you on board!</p>

        <h2 class="mt-4">Your Registration Details:</h2>
        <ul>
            <li><strong>Name:</strong> {{ ucwords($Name) }}</li>
            @if(!empty($oldCode))
            <li><strong>Old Employee Code:</strong> {{ $oldCode }}</li>
            @endif
            <li><strong>Gender:</strong> {{ $genderName }}</li>
            <li><strong>Organization Name:</strong> {{ $orgName }}</li>
            <li><strong>Site Name:</strong> {{ $siteName }}</li>
            <li><strong>Assigned Cost Center:</strong> {{ $ccName }}</li>
            <li><strong>Cadre:</strong> {{ $cadreName }}</li>
            <li><strong>Position:</strong> {{ $positionName }}</li>
            <li><strong>Province:</strong> {{ $provinceName }}</li>
            <li><strong>Division:</strong> {{ $divisionName }}</li>
            <li><strong>District:</strong> {{ $districtName }}</li>
            <li><strong>Address:</strong> {{ $Address }}</li>
            <li><strong>Mobile #:</strong> {{ $mobileNo }}</li>
            <li><strong>CNIC:</strong> {{ $CNIC }}</li>
            @if(!empty($orgName))
            <li><strong>Organization:</strong> {{ ucwords($orgName) }}</li>
            @endif
            @if(!empty($Manager))
            <li><strong>Manager/Supervisor:</strong> {{ ucwords($Manager) }}</li>
            @endif
            <li><strong>Email:</strong> {{ $Email }}</li>
            <li><strong>Date Of Joining:</strong> {{ $emailDOJ }}</li>
            <li><strong>Status:</strong> {{ $emailStatus }}</li>
            <li><strong>Added On:</strong> {{ $emailTimestamp }}</li>
            <li><strong>Effective Date&Time:</strong> {{ $emailEdt }}</li>
        </ul>

        <p>You will soon receive another email with your login URL and user credentials. Once logged in, we kindly request you to update your password for security reasons.</p>

        <p>Thank you for joining {{ ucwords($orgName) }}!</p>

        <hr>

        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
