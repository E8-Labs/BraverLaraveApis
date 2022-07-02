
<!DOCTYPE html>
<html>
<head>
	<style>
@import url('https://fonts.googleapis.com/css?family=EB+Garamond&display=swap');
@import url('https://fonts.googleapis.com/css?family=Lato&display=swap');
</style>
</head>
<body>

   <div id="div1" style="background: #D1D4D3; height:590px;width: 667px;margin: auto;">
   <div style="padding: 20px;"></div>
	<div id="div2" style="width: 479px;margin: auto; border:1px solid #f5f5f5;background: white;padding: 30px">

        <p style="text-align: center;font-family: 'Lato', sans-serif;font-size: 21px;">PASSWORD RESET</p>
		<h2 style="text-align:center;color: black;">Hi, {{ $name }}</h2>
		<p style="padding-left: 23px; font-family: 'Lato', sans-serif; font-size:15px"> You recently requested to reset your password for your  <strong>Braver Hospitality App</strong> account. Use the button below to reset it</p>
		<br><br>
        <a  href="{{ route('reset.password.get', $code) }}"><button style="margin-left:38%;border:none; border-radius: 4px; height:40px; width: 153px; background:#00C7B2;color: white">RESET PASSWORD</button></a>

		<br><br><br>
		<hr style="width: 92%;">
		<br>
		
    </div>

    

    <p style="text-align: center;font-size:13px; color: black;font-family: 'Lato', sans-serif; ">&copy;Copyright WateringCan App. All Rights Reserved</p>

	</div>

</body>
</html>





