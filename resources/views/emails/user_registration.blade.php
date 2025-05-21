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
        <h3>Please click the login URL provided below and use the given email and password to log in:</h3>
        <ul>
            <li><strong>URL:</strong> <a href="https://zmt.esystematic.com/"> https://zmt.esystematic.com/</a></li>
            <li><strong>Email:</strong> {{ $useremail }}</li>
            <li><strong>Password:</strong> {{ $pwd }}</li>
        </ul>
        <h2 class="mt-4">Your Registration Details:</h2>
        <ul>
            <li><strong>User Name:</strong> {{ $username }}</li>
            <li><strong>Email:</strong> {{ $useremail }}</li>
            <li><strong>Role Assigned:</strong> {{ $roleName }}</li>
            <li><strong>Organization:</strong> {{ $orgName }}</li>
            @if(!empty($empName))
            <li><strong>Employee Name:</strong> {{ $empName }}</li>
            @endif
            @if(!empty($siteName))
            <li><strong>Sites:</strong> {{ $siteName }}</li>
            @endif
            <li><strong>Status:</strong> {{ $emailStatus }}</li>
            <li><strong>Effective Date&Time:</strong> {{ $emailEdt }}</li>
            <li><strong>Added On:</strong> {{ $emailTimestamp }}</li>
        </ul>

        <p>Thank you for joining {{ config('app.name') }}!</p>
        <p>Once logged in, we kindly request you to update your password using below link for security reasons.</p>
        <a href="https://zmt.esystematic.com/profile">https://zmt.esystematic.com/profile</a>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
