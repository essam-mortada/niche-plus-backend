<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #D4AF37;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0;
            color: #ffffff;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
        }
        .success-icon {
            text-align: center;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .message {
            text-align: center;
            font-size: 18px;
            color: #333;
            margin-bottom: 30px;
        }
        .details-box {
            background: #f9f9f9;
            border-left: 4px solid #D4AF37;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            color: #333;
            font-weight: 500;
        }
        .amount {
            font-size: 32px;
            font-weight: 700;
            color: #D4AF37;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            background: #f9f9f9;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #D4AF37;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Niche Plus</h1>
            <p>Luxury Lifestyle & Awards</p>
        </div>

        <div class="content">
            <div class="success-icon">✅</div>
            
            <p class="message">
                <strong>Payment Successful!</strong><br>
                Thank you for your application. Your payment has been processed successfully.
            </p>

            <div class="amount">
                ${{ number_format($application->amount, 2) }}
            </div>

            <div class="details-box">
                <h3 style="margin-top: 0; color: #D4AF37;">Application Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Application ID:</span>
                    <span class="detail-value">#{{ $application->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Package Type:</span>
                    <span class="detail-value">{{ ucfirst($application->package_type) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Award Event:</span>
                    <span class="detail-value">{{ $application->award->city ?? 'N/A' }} Awards</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Applicant:</span>
                    <span class="detail-value">{{ $application->applicant_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Company:</span>
                    <span class="detail-value">{{ $application->company_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $application->paid_at ? $application->paid_at->format('F j, Y g:i A') : 'N/A' }}</span>
                </div>

                @if($application->stripe_payment_intent_id)
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $application->stripe_payment_intent_id }}</span>
                </div>
                @endif
            </div>

            <p style="color: #666; font-size: 14px; line-height: 1.8;">
                <strong>What's Next?</strong><br>
                Our team will review your application and contact you within 2-3 business days. 
                You will receive further instructions about the award ceremony and your package benefits.
            </p>

            <p style="color: #666; font-size: 14px;">
                If you have any questions, please don't hesitate to contact us at 
                <a href="mailto:info@nichemagazine.com" style="color: #D4AF37;">info@nichemagazine.com</a>
            </p>
        </div>

        <div class="footer">
            <p><strong>Niche Plus</strong></p>
            <p>Where Excellence Meets Elegance</p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} Niche Magazine. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
