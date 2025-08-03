<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
  
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h4 class="mb-4">Account Credentials Updated</h4>
        <p>Dear {{ ucwords($userName) }},</p>
        <p>
            Your account credentials have been updated by <strong>{{ ucwords($sessionName) }}</strong>.
        </p>
        <ul>
            <li><strong>New Email:</strong> {{ $newEmail }}</li>
            <li><strong>New Password:</strong> {{ $pwd }}</li>
        </ul>
        <p>
            You can now log in to your account using your new credentials at:<br>
            <a href="https://pilot.esystematic.com/">https://pilot.esystematic.com/</a>
        </p>
        <p>
            <strong>Important:</strong> For your security, you have been logged out from all devices. Please use your new credentials to log in again.
        </p>
        <p>
            If you did not request this change or believe this is a mistake, please contact our support team immediately.
        </p>
        <hr>
        <p><em>This is an automated email. Please do not reply to this message.</em></p>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
