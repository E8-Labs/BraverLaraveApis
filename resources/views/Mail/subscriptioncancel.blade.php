<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Notification</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=EB+Garamond&display=swap');
        @import url('https://fonts.googleapis.com/css?family=Lato&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'Lato', sans-serif;
        }

        .container {
            background-color: #D1D4D3;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .content {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
            height: 100px;
        }

        .message {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .message strong {
            color: #000;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 30px;
        }

        .footer a {
            color: #888;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="logo">
                <a href="http://braverhospitalityapp.com">
                    <img src="http://braverhospitalityapp.com/braver/storage/app/Images/bravernew.png" alt="Braver Hospitality">
                </a>
            </div>

            <p class="message">Hey <strong>{{$user_name}}</strong>,</p>
            <p class="message">We wanted to let you know that your subscription plan has expired or been canceled. We hope you've enjoyed our services so far, and we'd love to continue serving you.</p>
            <p class="message">Please log in to the app and subscribe to a plan to continue enjoying our services.</p>

            <p class="message">If you have any questions or need assistance, feel free to contact our support team.</p>

            <div class="button-container">
                <a href="https://main.d5iqt2n70gsih.amplifyapp.com/" class="button" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">Renew Subscription</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Braver. All Rights Reserved. | 
            <a href="http://braverhospitalityapp.com/privacy-policy">Privacy Policy</a>
        </div>
    </div>
</body>
</html>
