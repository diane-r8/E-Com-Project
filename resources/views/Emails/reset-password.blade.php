<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body>
    <h2>Hello</h2>
    <p>We received a request to reset your password for your account at <strong>Crafts N' Wraps</strong>.</p>
    
    <p>Please click the button below to reset your password:</p>

    <p>
        <a href="{{ $resetUrl }}" 
           style="padding: 10px 20px; background-color:  #5D6E54; color: #fff; text-decoration: none; border-radius: 5px;">
            Reset Password
        </a>
    </p>

    <p>If you’re having trouble clicking the button, copy and paste this URL into your browser:</p>
    <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>

    <p>This link will expire in 10 minutes. If you did not request a password reset, no further action is required.</p>

    <p>Thank you for choosing <strong>Crafts N' Wraps</strong>! 💐</p>
</body>
</html>
