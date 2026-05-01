<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Mentara Health</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
            line-height: 1.6;
            font-size: 15px;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 20px 0;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .email-header {
            background-color: #eaeaea;
            padding: 30px;
            text-align: center;
        }
        .email-header img {
            max-width: 200px;
        }
        .email-body {
            padding: 32px 40px;
        }
        .email-body p {
            margin-bottom: 16px;
            color: #1a1a1a;
        }
        .email-body ul {
            margin-bottom: 16px;
            padding-left: 20px;
            color: #1a1a1a;
        }
        .email-body li {
            margin-bottom: 8px;
        }
        .credentials-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin-bottom: 10px;
        }
        .credential-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .credential-value {
            font-family: monospace;
            font-size: 16px;
            color: #1e293b;
            background: #ffffff;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            display: inline-block;
            margin-top: 4px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background-color: #01365C;
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 40px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #012b4a;
        }
        .email-footer {
            background-color: #2b4c6b;
            padding: 30px 40px;
            text-align: center;
            font-size: 13px;
            color: #ffffff;
        }
        .email-footer p {
            margin: 0 0 10px 0;
            color: #ffffff;
        }
        .email-footer a {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Header -->
            <div class="email-header">
                <img src="{{ asset('assets/images/logo-new.png') }}" alt="Mentara Health">
            </div>

            <!-- Body -->
            <div class="email-body">
                <p>Hi {{ $user->first_name }},</p>
                
                <p>I hope you're doing well.</p>
                
                <p>I wanted to personally thank you for being part of Mentara Health—your participation has truly meant a great deal to us.</p>
                
                <p>We are now in the final stages of preparing to launch our online certification exam platform, and we’d love for you to be among the first to experience it.</p>
                
                <p>As a valued member of our early cohort, you are invited to take the certification exam completely free of charge, including <strong>three full attempts</strong>.</p>
                
                <p><strong>What’s included:</strong></p>
                
                <ul>
                    <li>A realistic simulation of the NCMHCE, designed to mirror the timed format and style of actual exam questions</li>
                    <li>Authentic NCMHCE-style questions presented under timed conditions</li>
                    <li>Instant results with a detailed performance report to guide your continued study</li>
                    <li>Three attempts to strengthen your skills and build confidence</li>
                    <li>A strong head start on your certification journey at no additional cost</li>
                </ul>

                <p>In return, we kindly ask that you complete a short feedback survey after your exam. Your insights will help us refine the platform before the official launch.</p>
                
                <p><strong>To get started:</strong></p>
                
                <ul>
                    <li>Use the portal link below to create your login credentials</li>
                    <li>Once logged in, your exam and three attempts will be available</li>
                </ul>
                
                <p>Here are your temporary login credentials:</p>
                
                <div class="credentials-box">
                    <div class="credential-item">
                        <div class="credential-label">Email Address</div>
                        <div class="credential-value">{{ $user->email }}</div>
                    </div>
                    @if($generatedPassword)
                    <div class="credential-item" style="margin-bottom: 0;">
                        <div class="credential-label">Password</div>
                        <div class="credential-value">{{ $generatedPassword }}</div>
                    </div>
                    @endif
                </div>

                <p>Please click the button below to verify your account and set a new password provided you want to change it. We recommend changing your password after your first login.</p>

                <div class="btn-container">
                    <a href="{{ $resetUrl }}" class="btn" style="color: #ffffff; text-decoration: none;">Login & Set Password</a>
                </div>

                <p>If you have any questions or experience any issues, please feel free to reply to this email—we’re here to help.</p>
                
                <p>Thank you again for your support. We’re excited to have you play a key role in shaping this platform.</p>
                
                <p>Warm regards,<br>
                Lynda S-Taylor</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p style="font-style: italic; font-weight: bold;">Copyright &copy; 2026 Mentara Health, All rights reserved.</p>
                
                <p><strong>Our e-mail address is:</strong><br>
                <a href="mailto:support@mentara.health" style="text-decoration: none; color: #ffffff;">support@mentara.health</a></p>
                
                <p style="margin-top: 20px;"><strong>Want to change how you receive these emails?</strong><br>
                You can <a href="#" style="color: #ffffff;">unsubscribe from this list</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
