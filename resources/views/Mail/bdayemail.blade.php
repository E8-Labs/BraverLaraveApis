<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Notification</title>
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
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
        }

        .header img {
            width: 80px;
            height: auto;
        }

        .content {
            padding: 30px;
            font-size: 16px;
            color: #333333;
        }

        .content p {
            margin: 0 0 15px;
        }

        .content strong {
            font-weight: 700;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
        }

        .info-table td:first-child {
            font-weight: 700;
            color: #666666;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            padding: 15px;
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
    </div>

    <div class="content">
        <p>Hey <strong>{{$user_name}}</strong>,</p>
        <p>Just a quick reminder that your birthday is coming up on <strong>{{$time}}</strong>! Here are your details:</p>

        <table class="info-table">
            <tr>
                <td>Name:</td>
                <td>{{$user_name}}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{$user_email}}</td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td>{{$phone}}</td>
            </tr>
            <tr>
                <td>City:</td>
                <td>{{$city}}</td>
            </tr>
            <tr>
                <td>State:</td>
                <td>{{$state}}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Braver. All rights reserved.
        <br>
        <a href="http://braverhospitalityapp.com">Visit our website</a>
    </div>
</div>

</body>
</html>
