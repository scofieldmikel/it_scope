{{-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        .otp-code {
            font-size: 24px;
            color: #007bff;
            margin-top: 10px;
        }
        .contact-info {
            margin-top: 20px;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your One-Time Password (OTP) Verification</h1>
        <p>Thank you for choosing IT Scope! To ensure the security of your account, we have generated a One-Time Password (OTP) for your verification. Please use the OTP provided below to complete your registration/transaction:</p>
        <div class="otp-code">OTP: {{ $totpService->addChecksum(true)->generateCode() }}</div>
        <p>Please note that this OTP is valid for <b>{{$totpService->getExpirationTime()}}</b> seconds and should not be shared with anyone. It is a crucial security measure to protect your account and personal information.</p>
        <p>If you did not initiate this request or have any concerns about your account's security, please contact our customer support team immediately at <a href="mailto:support@itscope.com">support@itscope.com</a> or call us at 08022740630. We are here to assist you 24/7.</p>
        <p>Thank you for choosing IT Scope for your  account registration needs. We value your trust and are committed to providing you with a secure and seamless experience.</p>
        <div class="contact-info">Sincerely,<br>IT Scope</div>
        <a href="itscope.com" class="button">Visit Our Website</a>
    </div>
</body>
</html>