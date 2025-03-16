<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
</head>
<body>
    <p>Hello,</p>
    <p>Your One-Time Password (OTP) for login verification is:</p>
    <h2 style="color: #333;">{{ $otp }}</h2>
    <p>This OTP is valid for <strong>{{ $expires_in }} minutes</strong>.</p>
    <p>If you did not request this, please ignore this email.</p>
    <p>Thank you!</p>
</body>
</html>
