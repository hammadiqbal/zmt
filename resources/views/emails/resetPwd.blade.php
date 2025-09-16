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
        <p>Dear, {{ $userName }}!</p>
        <h1>Reset Password</h1>
        <p>You are receiving this email because you requested a password reset.</p>
        <p>To reset your password, please click on the following link:</p>
        <a href="{{ url('resetPwd', $token) }}">Reset Password</a>
        <p>If you did not request a password reset, please ignore this email.</p>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
