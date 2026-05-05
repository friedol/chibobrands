<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verified - CHIBO BRAND</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #FF0000;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .button:hover {
            background-color: #cc0000;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            background-color: #e8f5e8;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Welcome to CHIBO BRAND!</h1>
        <p>Your account has been verified</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $customer->name }},</h2>
        
        <p>Great news! Your CHIBO BRAND account has been successfully verified by our team. You can now access all the features and benefits of your account.</p>
        
        <div class="highlight">
            <h3>✅ Account Status: Verified</h3>
            <p>Your account is now active and ready to use!</p>
        </div>
        
        <h3>What's Next?</h3>
        <p>Now that your account is verified, you can:</p>
        <ul>
            <li>Browse our complete product catalog</li>
            <li>Access wholesale pricing (if applicable)</li>
            <li>Place orders and track their status</li>
            <li>Manage your profile and preferences</li>
            <li>Receive exclusive offers and updates</li>
        </ul>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $loginUrl }}" class="button">Sign In to Your Account</a>
            <a href="{{ $dashboardUrl }}" class="button">Go to Dashboard</a>
        </div>
        
        <h3>Need Help?</h3>
        <p>If you have any questions or need assistance, please don't hesitate to contact us:</p>
        <ul>
            <li><strong>Phone:</strong> +255 687 183 330</li>
            <li><strong>WhatsApp:</strong> <a href="https://wa.me/255687183330">+255 687 183 330</a></li>
            <li><strong>Email:</strong> info@chibobrand.com</li>
        </ul>
        
        <p>Thank you for choosing CHIBO BRAND. We look forward to serving your printing and branding needs!</p>
        
        <p>Best regards,<br>
        <strong>The CHIBO BRAND Team</strong></p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} CHIBO BRAND. All rights reserved.</p>
        <p>This email was sent to {{ $customer->email }} because you created an account with CHIBO BRAND.</p>
        <p>If you did not create this account, please contact us immediately.</p>
    </div>
</body>
</html>
