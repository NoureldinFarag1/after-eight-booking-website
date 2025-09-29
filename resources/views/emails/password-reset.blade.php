<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 20px;
        }
        .logo {
            color: #dc2626;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"></div>
            <h1>Password Reset Request</h1>
        </div>

        <p>Hello!</p>

        <p>You are receiving this email because we received a password reset request for your account.</p>

        <div style="text-align: center;">
            <a href="{{ $url }}" class="button">Reset Password</a>
        </div>

        <p>This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.</p>

        <div class="warning">
            <strong>Security Notice:</strong> If you did not request a password reset, no further action is required. Your password will remain unchanged.
        </div>

        <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
        <p style="word-break: break-all; color: #6c757d; font-size: 14px;">{{ $url }}</p>

        <div class="footer">
            <p>This is an automated email from {{ config('app.name') }}.<br>
            © {{ date('Y') }} After Eight Events. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
