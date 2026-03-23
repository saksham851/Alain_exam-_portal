<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Invitation – Exam Portal</title>
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
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0 0 6px 0;
            font-size: 26px;
            font-weight: 700;
        }
        .email-header p {
            color: rgba(255,255,255,0.85);
            margin: 0;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: #fff;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .email-body {
            padding: 36px 32px;
        }
        .welcome-text {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .description {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 28px;
        }
        .info-box {
            background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
            border: 1px solid #c7d2fe;
            border-left: 4px solid #4f46e5;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .info-label {
            font-weight: 700;
            color: #4f46e5;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .info-value {
            font-family: 'Courier New', monospace;
            font-size: 15px;
            color: #1e293b;
            background: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-top: 4px;
        }
        .credential-row {
            margin-bottom: 14px;
        }
        .credential-row:last-child {
            margin-bottom: 0;
        }
        .permissions-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }
        .permissions-box h4 {
            color: #15803d;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: 700;
        }
        .permissions-box ul {
            margin: 0;
            padding-left: 18px;
        }
        .permissions-box li {
            color: #166534;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0 20px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 40px;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }
        .note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 20px;
        }
        .note strong {
            color: #78350f;
        }
        .expiry-note {
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            margin-bottom: 0;
        }
        .email-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Header -->
            <div class="email-header">
                <div class="badge">🛡️ Admin Invitation</div>
                <h1>You're now an Admin!</h1>
                <p>Exam Portal – Administrative Access</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p class="welcome-text">Hello! 👋</p>
                <p class="description">You have been invited to join <strong>Exam Portal</strong> as an <strong>Administrator</strong>. You now have full access to manage exams, students, and all portal content.</p>

                <!-- Login Credentials -->
                <div class="info-box">
                    <div class="credential-row">
                        <div class="info-label">Login Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    @if($tempPassword)
                    <div class="credential-row" style="margin-top: 14px;">
                        <div class="info-label">Temporary Password</div>
                        <div class="info-value">{{ $tempPassword }}</div>
                    </div>
                    @endif
                </div>

                <!-- Permissions Info -->
                <div class="permissions-box">
                    <h4>✅ Your Admin Permissions Include:</h4>
                    <ul>
                        <li>Manage Students & Users</li>
                        <li>Create & Manage Exams, Categories</li>
                        <li>Manage Questions & Case Studies</li>
                        <li>View Results & Attempts</li>
                        <li>Import/Export Data</li>
                    </ul>
                </div>

                @if($tempPassword)
                <div class="note">
                    <strong>⚠️ Important:</strong> We recommend setting a new password immediately after your first login. Click the button below to set your own password.
                </div>
                @endif

                <!-- CTA Button -->
                <div class="btn-container">
                    <a href="{{ $resetUrl }}" class="btn">🔐 Set Your Password & Login</a>
                </div>

                <p class="expiry-note">This link will expire in 60 minutes. If expired, contact your Super Admin.</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} Exam Portal. All rights reserved.</p>
                <p>If you didn't expect this invitation, you can safely ignore this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
