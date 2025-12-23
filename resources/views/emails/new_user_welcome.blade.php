<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Exam Portal</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            color: #51545E;
            line-height: 1.6;
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
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .email-header {
            background-color: #4f46e5;
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 32px;
        }
        .welcome-text {
            font-size: 18px;
            color: #333333;
            margin-bottom: 24px;
        }
        .credentials-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
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
            padding: 4px 8px;
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
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #4338ca;
        }
        .email-footer {
            background-color: #f4f4f7;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Header -->
            <div class="email-header">
                <h1>Welcome to Exam Portal</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <h2 class="welcome-text">Hello, {{ $user->first_name }}!</h2>
                
                <p>Welcome to the Exam Portal. We are excited to have you on board! Your account has been successfully created.</p>
                
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
                    <a href="{{ $resetUrl }}" class="btn">Login & Set Password</a>
                </div>

                <p style="margin-bottom: 0;">If you have any questions, feel free to reply to this email.</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} Exam Portal. All rights reserved.</p>
                <p>If you didn't create this account, you can safely ignore this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
