<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reservation Assignment</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=EB+Garamond:wght@400;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
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
            padding: 20px;
            text-align: center;
            background-color: #D1D4D3;
        }

        .header img {
            width: 90px;
            height: auto;
        }

        .content {
            padding: 30px;
            font-size: 16px;
            color: #333333;
        }

        .content p {
            margin-bottom: 10px;
        }

        .content strong {
            font-weight: 700;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            background-color: #D1D4D3;
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
    </div>

    <div class="content">
        <p>Hey <strong>{{$user_name}}</strong>,</p>
        <p><strong>Braver</strong> has assigned you to a new reservation request from a client. Please check the Braver app to interact with the customer and manage the request.</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Braver. All Rights Reserved.
    </div>
</div>

</body>
</html>
