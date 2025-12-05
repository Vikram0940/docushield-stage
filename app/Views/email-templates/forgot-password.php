<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot your password?</title>
</head>
<body>
    <h2>Hi <?=esc($name)?>,</h2>
    <p>We received a request to reset your password. Click the button below to choose a new one.</p>
    <p><a href="<?=$link?>" style="padding: 10px;background-color: #015ad7!important;border-radius: 6px !important;color: #f7f8fa !important;">Reset Password</a></p>
    <p style="margin-top: 15px;font-weight: bold;"><i>If you didn’t request this, you can safely ignore this email.</i></p>
</body>
</html>
