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
        <p>Dear, {{ $sessionName }}!</p>
        <p>We are writing to inform you that your password update for your account
            at {{ config('app.name') }} was successful. Your account is now secured with a new password.
        </p>
        <p>
            This password update was completed on {{ $emailTimestamp }},
             ensuring that your account remains protected with the updated credentials.
        </p>
        <p>If you have made this password change, there is no further action required on your part.
            Your account is now protected with the updated credentials,
            and you can continue to access our services as usual.
        </p>

        <p>However, if you did not initiate this password update or suspect
            any unauthorized activity, please contact Administrator.
            We take account security seriously and will investigate any potential
            concerns promptly.
        </p>

        <p>If you believe this email is not associated with your account, please ignore it.</p>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
