<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email Address</title>
</head>
<body>
    <h2>Hello</h2>
    <p>Thank you for registering with <strong>Crafts N' Wraps</strong>! 🎉</p>
    <p>Please verify your email address by clicking the button below:</p>

    <p>
        <a href="{{ $verificationUrl }}" 
           style="padding: 10px 20px; background-color: #5D6E54; color: #fff; text-decoration: none; border-radius: 5px;">
            Verify Email
        </a>
    </p>

    <p>If you’re having trouble clicking the button, copy and paste this URL into your browser:</p>
    <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>

    <p>Thank you for choosing <strong>Crafts N' Wraps</strong>! 💐</p>
</body>
</html>
