
<!DOCTYPE html>
<html>
<head>
  <style>
@import url('https://fonts.googleapis.com/css?family=EB+Garamond&display=swap');
@import url('https://fonts.googleapis.com/css?family=Lato&display=swap');
</style>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

   <div id="div1" style="background: #D1D4D3; height:590px;width: 667px;margin: auto;">
   <div style="padding: 20px;">
    

   </div>
  
<div id="div2" style="width: 479px;margin: auto; border:1px solid #f5f5f5;background: white;padding: 30px">
  <center>
            <a href="http://braverhospitalityapp.com"><img src="http://braverhospitalityapp.com/braver/storage/app/Images/braverlogo.png" alt="Braver Hospitality" style="width:90px;height:90px;"></a>
    </center>

<p style="padding-left: 23px; font-family: 'Lato', sans-serif; font-size:15px"> Hey <strong>{{$user_name}}</strong>'s Birthday is {{$time}}. Here are the user details </p>
 
    <div class="container">
    <div class="row">
      <div class="col">
        <strong>Name:</strong>
      </div>
      <div class="col">
        <p>{{$user_name}}</p>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <strong>Email:</strong>
      </div>
      <div class="col">
        <p>{{$user_email}}</p>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <strong>Phone:</strong>
      </div>
      <div class="col">
        <p>{{$phone}}</p>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <strong>City:</strong>
      </div>
      <div class="col">
        <p>{{$city}}</p>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <strong>State:</strong>
      </div>
      <div class="col">
        <p>{{$state}}</p>
      </div>
    </div>
  </div>
    
    
    
    
    
        
    
  <!--<span>Name: <strong>{{$user_name}}</strong></span>-->
 <!--       <span>Email: <strong>{{$user_email}}</strong></span>-->
 <!--       <span>Phone: <strong>{{$phone}}</strong></span>-->
 <!--       <span>City: <strong>{{$city}}</strong></span>-->
 <!--       <span>State: <strong>{{$state}}</strong></span> -->
    
    </div>
    

    <p style="text-align: center;font-size:13px; color: black;font-family: 'Lato', sans-serif; ">&copy;Copyright Braver. All Rights Reserved</p>

  </div>

</body>
</html>





