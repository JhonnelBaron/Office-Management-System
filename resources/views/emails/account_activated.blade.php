<!DOCTYPE html>
<html>
<head>
    <title>Account Activated</title>
</head>
<body>
    <h1>Hello, {{ $user->name }}!</h1>
    <p>Your account has been successfully activated. You can now log in to your account using the link below:</p>

    <a href="{{ url('localhost:3000') }}">Click here to login</a>

    <p>Thank you for being a part of our community.</p>
</body>
</html>
