<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        /* Add any additional custom styles here */
    </style>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <p>Hello, {{ $userName }}!</p>
        <p>Your email has been updated by an administrator.</p>
        <p>If you did not initiate this change, please disregard this email.</p>
        <p>You can now log in with your new email using the following URL:</p>
        <p><a href="https://pilot.esystematic.com/">https://pilot.esystematic.com/</a></p>
        <p>If you believe this email is not associated with your account, please ignore it.</p>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
