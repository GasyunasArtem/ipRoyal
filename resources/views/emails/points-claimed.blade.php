<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Points Claimed Successfully</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e4e4e4;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c5aa0;
            margin: 0;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 10px;
        }
        .highlight {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
        .details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e4e4e4;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2c5aa0;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Points Claimed Successfully!</h1>
            <p>Hello {{ $user->name }}, your points have been converted to USD</p>
        </div>

        <div class="highlight">
            <h2>Congratulations!</h2>
            <div class="amount">${{ number_format($usdEarned, 2) }}</div>
            <p>has been added to your wallet</p>
        </div>

        <div class="details">
            <h3>Transaction Details</h3>
            <div class="detail-row">
                <span><strong>Points Claimed:</strong></span>
                <span>{{ number_format($pointsClaimed) }} points</span>
            </div>
            <div class="detail-row">
                <span><strong>Conversion Rate:</strong></span>
                <span>1 point = ${{ number_format($conversionRate, 2) }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Transactions Processed:</strong></span>
                <span>{{ $transactionCount }}</span>
            </div>
            <div class="detail-row">
                <span><strong>USD Earned:</strong></span>
                <span><strong>${{ number_format($usdEarned, 2) }}</strong></span>
            </div>
            <div class="detail-row">
                <span><strong>Date:</strong></span>
                <span>{{ now()->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <p>Keep earning points by updating your profile daily!</p>
            <p><em>Remember: You can update your profile once per day to earn +5 points.</em></p>
        </div>

        <div class="footer">
            <p>This is an automated message from {{ config('app.name') }}.</p>
            <p>Thank you for using our service!</p>
        </div>
    </div>
</body>
</html>
