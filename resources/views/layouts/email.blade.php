<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'After Eight')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0b0b0b;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8f9fa;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background: linear-gradient(180deg, #1c000b 0%, #010103 100%);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #29000d;
        }
        .header {
            padding: 32px 40px 24px;
            text-align: center;
            background: rgba(255, 255, 255, 0.04);
        }
        .logo {
            width: 160px;
            margin-bottom: 16px;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(141, 0, 31, 0.2);
            color: #ff204e;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 12px;
        }
        .content {
            padding: 32px 40px;
        }
        h1 {
            font-size: 26px;
            margin-bottom: 16px;
            letter-spacing: 0.02em;
            color: #ffffff;
        }
        p {
            margin: 0 0 16px;
            color: rgba(248, 249, 250, 0.82);
            line-height: 1.6;
        }
        .panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .panel h2 {
            font-size: 18px;
            margin: 0 0 12px;
            color: #ffffff;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: rgba(248, 249, 250, 0.58);
        }
        .value {
            color: #ffffff;
            font-weight: 600;
            text-align: right;
        }
        .cta-wrapper {
            text-align: center;
        }
        .cta {
            display: inline-block;
            background: #ff204e;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.03em;
            margin-top: 20px;
        }
        .footer {
            padding: 24px 40px 32px;
            text-align: center;
            background: rgba(255, 255, 255, 0.02);
            font-size: 12px;
            color: rgba(248, 249, 250, 0.5);
        }
        .timer {
            font-family: 'Roboto Mono', 'SFMono-Regular', monospace;
            font-size: 20px;
            color: #ff6b8a;
        }
        @media (max-width: 600px) {
            .content, .header, .footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ config('app.url') }}/images/Aftereight-logo.png" alt="After Eight" class="logo">
            @hasSection('badge')
                <div class="badge">@yield('badge')</div>
            @endif
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} After Eight Events. All rights reserved.<br>
            @hasSection('footer-link')
                If the button above does not work, copy and paste this link into your browser:<br>
                <span style="color: rgba(248, 249, 250, 0.7);">@yield('footer-link')</span>
            @endif
        </div>
    </div>
</body>
</html>
