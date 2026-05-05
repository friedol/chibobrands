<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply from CHIBO BRAND</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #FF0000 0%, #cc0000 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .email-header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #FF0000;
            margin-bottom: 20px;
        }
        .reply-section {
            background-color: #f9f9f9;
            border-left: 4px solid #FF0000;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .reply-section h3 {
            margin-top: 0;
            color: #FF0000;
            font-size: 16px;
        }
        .reply-content {
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .original-message {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .original-message h4 {
            margin-top: 0;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }
        .original-message p {
            margin: 5px 0;
            color: #666;
            font-size: 13px;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #FF0000;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #cc0000;
        }
        .email-footer {
            background-color: #000000;
            color: #ffffff;
            padding: 30px;
            text-align: center;
            font-size: 13px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>CHIBO BRAND</h1>
            <p>Design • Print • Build Brands</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Hello {{ $customerName }},
            </div>

            <p>Thank you for contacting CHIBO BRAND. We have reviewed your message and here is our response:</p>

            <!-- Admin Reply -->
            <div class="reply-section">
                <h3>Our Response:</h3>
                <div class="reply-content">{{ $replyMessage }}</div>
            </div>

            <div class="divider"></div>

            <!-- Original Message Reference -->
            <div class="original-message">
                <h4>Your Original Message:</h4>
                <p><strong>Subject:</strong> {{ $originalSubject }}</p>
                <p><strong>Message:</strong></p>
                <p>{{ $originalMessage }}</p>
            </div>

            <p>If you have any further questions or need additional assistance, please don't hesitate to reach out to us.</p>

            <center>
                <a href="mailto:chibobrandsltd@gmail.com" class="cta-button">Reply to This Email</a>
            </center>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                Best regards,<br>
                <strong>{{ $adminName }}</strong><br>
                CHIBO BRAND Team
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>CHIBO BRAND</strong></p>
            <p>Professional Printing & Branding Solutions</p>
            <p>Dar es Salaam, Tanzania</p>
            
            <div class="social-links">
                <a href="tel:+255655392319">📞 +255 655 392 319</a> | 
                <a href="tel:+255753883382">📞 +255 753 883 382</a>
            </div>
            
            <p>
                <a href="mailto:chibobrandsltd@gmail.com" style="color: #ffffff;">✉️ chibobrandsltd@gmail.com</a>
            </p>
            
            <div class="social-links">
                <a href="https://www.instagram.com/chibobrands">Instagram</a> | 
                <a href="https://wa.me/255753883382">WhatsApp</a> | 
                <a href="https://www.tiktok.com/@chibo_brandsmifuko1">TikTok</a>
            </div>
            
            <p style="margin-top: 15px; font-size: 11px; opacity: 0.7;">
                © {{ date('Y') }} CHIBO BRAND. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
