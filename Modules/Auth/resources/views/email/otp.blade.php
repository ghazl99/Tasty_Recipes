<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;">
        <div class="card-body">
            <h2 class="card-title text-center text-primary mb-4">Your OTP Code</h2>
            <p class="text-muted">Hello,</p>
            <p class="fs-5">
                Your one-time password is:
                <b>{{ $otp }}</b>
            </p>
            <p class="text-muted">This code will expire in 10 minutes.</p>
            <hr>
            <p class="mb-0 text-center text-secondary">Thank you,<br><strong>Your App Team</strong></p>
        </div>
    </div>
</body>
</html>
