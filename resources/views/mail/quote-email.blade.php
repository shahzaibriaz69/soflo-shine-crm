<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SoFlo Shine Quote</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #0d6efd;
            margin: 0;
        }
        .content {
            font-size: 16px;
            line-height: 1.6;
        }
        .details-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #0d6efd;
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 1px solid #eeeeee;
            padding-top: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h2>SOFLO SHINE DETAILING</h2>
            <p style="margin: 5px 0 0 0; color: #555;">Official Service Estimate</p>
        </div>

        <div class="content">
            <p>Hi <strong>{{ $customerName }}</strong>,</p>
            <p>Thank you for choosing SoFlo Shine Detailing! Below are the details and estimate for your vehicle service request:</p>

            <div class="details-box">
                <p style="margin: 5px 0;"><strong>Service Package:</strong> {{ $serviceName }}</p>
                <p style="margin: 5px 0;"><strong>Vehicle Size:</strong> {{ ucfirst($size) }}</p>
                <p style="margin: 5px 0;"><strong>Condition:</strong> {{ ucfirst($condition) }}</p>
                <p style="margin: 5px 0; font-size: 18px; color: #0d6efd;"><strong>Total Estimate:</strong> ${{ number_format($totalAmount, 2) }}</p>
            </div>

            <p>If everything looks good to you, click the button below to review and approve your quote so we can get you scheduled:</p>

            <div class="btn-container">
                <a href="{{ $approvalUrl }}" class="btn">Review & Accept Quote</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} SoFlo Shine Detailing. All rights reserved.</p>
            <p>If you have any questions, feel free to reply directly to this email.</p>
        </div>
    </div>
</body>
</html>