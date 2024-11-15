<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
    <h1>Password Reset Request</h1>
    <p>Dear {{ $user->name }},</p>
    <p>We received a request to reset your password. You can reset your password by clicking the link below:</p>
    <a href="{{ url('password/reset/'.$token) }}">Reset Password</a>
    <p>If you did not request this change, please ignore this email.</p>
</body>
</html>
