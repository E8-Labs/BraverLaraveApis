<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            font-family: 'Lato', sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #000000;
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }

        .header img {
            width: 240px;
            height: auto;
        }

        .header p {
            margin: 10px 0 0;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #14A5BF;
        }

        .content {
            padding: 40px;
            font-size: 16px;
            color: #333333;
            text-align: center;
        }

        .content h2 {
            font-family: 'EB Garamond', serif;
            color: #000000;
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 20px;
            font-size: 15px;
        }

        .btn-reset {
            background-color: #4ca2bc;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
        }

        .btn-reset:hover {
            background-color: #00a590;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            background-color: #f0f0f0;
            color: #666666;
        }

        .footer a {
            color: #333333;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
<div class="header">
        <a href="http://braverhospitalityapp.com">
            <img src="http://braverhospitalityapp.com/braver/storage/app/Images/braverlogo.png" alt="Braver Hospitality">
        </a>
        <p>Elevate every experience</p>
    </div>

    <div class="content">
        <p>PASSWORD RESET</p>
        <h2>Hi, {{ $name }}</h2>
        <p>You recently requested to reset your password for your <strong>Braver Hospitality App</strong> account. Click the button below to reset your password:</p>
        
        <a href="{{ route('reset.password.get', $code) }}" class="btn-reset">RESET PASSWORD</a>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Braver Hospitality App. All rights reserved. <br>
        <a href="http://braverhospitalityapp.com">Visit our website</a>
    </div>
</div>

</body>
</html>
