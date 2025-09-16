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
        <h2 class="mt-2">Congratulations! Your Site has been Successfully Registered in Our System</h2>
        <p>We're excited to have you on board!</p>

        <h2 class="mt-4">Your Site Registration Details:</h2>
        <ul>
            <li><strong>Site Name:</strong> {{ $siteName }}</li>
            <li><strong>Organization Name:</strong> {{ $organizationName }}</li>
            <li><strong>Remarks:</strong> {{ $siteRemarks }}</li>
            <li><strong>Address:</strong> {{ $siteaddress }}</li>
            <li><strong>Province:</strong> {{ $provinceName }}</li>
            <li><strong>Division:</strong> {{ $divisionName }}</li>
            <li><strong>District:</strong> {{ $districtName }}</li>
            <li><strong>Site Admin Name:</strong> {{ $sitepersonname }}</li>
            <li><strong>Admin Email:</strong> {{ $sitepersonemail }}</li>
            <li><strong>Website:</strong> {{ $sitewebsite }}</li>
            <li><strong>GPS:</strong> {{ $sitegps }}</li>
            <li><strong>Mobile #:</strong> {{ $sitecell }}</li>
            <li><strong>Landline #:</strong> {{ $sitelandline }}</li>
            <li><strong>Status:</strong> {{ $status }}</li>
            <li><strong>Added On:</strong> {{ $emailTimestamp }}</li>
            <li><strong>Effective Date&Time:</strong> {{ $emailEdt }}</li>
        </ul>

        <p>You will soon receive another email with your login URL and user credentials. Once logged in, we kindly request you to update your password for security reasons.</p>

        <p>Thank you for joining {{ config('app.name') }}!</p>

        <hr>

        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
