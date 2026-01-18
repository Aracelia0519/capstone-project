<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CaviteGo Paint</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        
        .logo-container {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 16px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .email-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .email-content {
            padding: 40px;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .welcome-section p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.7;
        }
        
        .account-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .account-details h3 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #718096;
            font-weight: 500;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 600;
        }
        
        .requirements-section {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            color: #7c2d12;
        }
        
        .requirements-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .requirements-section ul {
            list-style: none;
            padding-left: 5px;
        }
        
        .requirements-section li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .requirements-section li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #7c2d12;
            font-weight: bold;
        }
        
        .next-steps {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            color: #1a365d;
        }
        
        .next-steps h3 {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box {
            background: #edf2f7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #4299e1;
        }
        
        .info-box h4 {
            color: #2d3748;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }
        
        .email-footer {
            text-align: center;
            padding: 30px 20px;
            background: #f8f9fa;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        
        .email-footer p {
            margin: 5px 0;
        }
        
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Responsive styles */
        @media (max-width: 600px) {
            .email-content {
                padding: 20px;
            }
            
            .email-header {
                padding: 30px 15px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo-container">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h1>CaviteGo Paint</h1>
            <p>Welcome to Our Colorful Community</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            <!-- Welcome Message -->
            <div class="welcome-section">
                <h2>🎉 Welcome, {{ $user['first_name'] }}!</h2>
                <p>Your account has been successfully created with CaviteGo Paint. We're excited to have you on board!</p>
            </div>

            <!-- Account Details -->
            <div class="account-details">
                <h3>📋 Account Details</h3>
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $user['first_name'] }} {{ $user['last_name'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $user['email'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Account Type:</span>
                    <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $user['role'])) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #f59e0b;">Pending Verification</span>
                </div>
            </div>

            <!-- Role-Specific Information -->
            @if(isset($roleInfo[$user['role']]))
                <div class="info-box">
                    <h4>{{ $roleInfo[$user['role']]['title'] }}</h4>
                    <p>{{ $roleInfo[$user['role']]['next_steps'] }}</p>
                </div>
            @endif

            <!-- Requirements Section -->
            <div class="requirements-section">
                <h3>📋 Account Activation Requirements</h3>
                <p>To utilize all functions of the system and get your account activated, please ensure to:</p>
                <ul>
                    <li>Complete your profile information</li>
                    @if($user['role'] == 'distributor')
                        <li>Submit business registration documents</li>
                        <li>Provide valid government-issued ID</li>
                        <li>Complete distributor agreement form</li>
                    @elseif($user['role'] == 'service_provider')
                        <li>Submit professional credentials</li>
                        <li>Provide portfolio of previous work</li>
                        <li>Complete service provider agreement</li>
                    @else
                        <li>Verify your email address</li>
                        <li>Complete your shipping address</li>
                    @endif
                </ul>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>🚀 What's Next?</h3>
                <p><strong>Important:</strong> Your account is currently in <strong>pending</strong> status. Our team will review your registration and contact you within 1-2 business days for verification.</p>
                <p>Once verified, you'll gain full access to:</p>
                <ul>
                    @if($user['role'] == 'client')
                        <li>Browse and purchase paint products</li>
                        <li>Track your orders in real-time</li>
                        <li>Schedule painting services</li>
                        <li>Access exclusive client discounts</li>
                    @elseif($user['role'] == 'distributor')
                        <li>Manage your product catalog</li>
                        <li>Track sales and commissions</li>
                        <li>Access distributor pricing</li>
                        <li>Order management system</li>
                    @else
                        <li>Receive service requests</li>
                        <li>Manage your service calendar</li>
                        <li>Track completed jobs</li>
                        <li>Receive client payments</li>
                    @endif
                </ul>
            </div>

            <!-- Call to Action -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}" class="cta-button">
                    Go to Your Dashboard
                </a>
                <p style="margin-top: 10px; color: #718096; font-size: 14px;">
                    (Will be available after account verification)
                </p>
            </div>

            <!-- Important Information -->
            <div class="info-box">
                <h4>⏳ Processing Time</h4>
                <p>Account verification typically takes <strong>1-2 business days</strong>. You'll receive another email once your account is activated.</p>
                <p style="margin-top: 10px; color: #e53e3e;">
                    <strong>Note:</strong> You can log in to your account, but some features will be restricted until verification is complete.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>Need help? We're here for you!</p>
            <div class="contact-info">
                <div class="contact-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Cavite, Philippines</span>
                </div>
                <div class="contact-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>support@cavitegopaint.com</span>
                </div>
                <div class="contact-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>(046) 123-4567</span>
                </div>
            </div>
            <p style="margin-top: 20px; color: #a0aec0; font-size: 12px;">
                © 2026 CaviteGo Paint. All rights reserved.<br>
                This email was sent to {{ $user['email'] }} because you registered on CaviteGo Paint.
            </p>
        </div>
    </div>
</body>
</html>