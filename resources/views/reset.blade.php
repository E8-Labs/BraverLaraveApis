<!DOCTYPE html>

<!--
Template Name: Metronic - Responsive Admin Dashboard Template build with Twitter Bootstrap 4 & Angular 8
Author: KeenThemes
Website: http://www.keenthemes.com/
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
Purchase: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
Renew Support: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en">

	<!-- begin::Head -->
	<head>

		<!--begin::Base Path (base relative path for assets of this page) -->
		<base href="../">

		<!--end::Base Path -->
		<meta charset="utf-8" />
		<title>WateringCan App</title>
		<meta name="description" content="Updates and statistics">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!--begin::Fonts -->
		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
		<script>
			WebFont.load({
				google: {
					"families": ["Poppins:300,400,500,600,700", "Roboto:300,400,500,600,700"]
				},
				active: function() {
					sessionStorage.fonts = true;
				}
			});
		</script>


		<!--end::Fonts -->

		<!--begin::Page Vendors Styles(used by this page) -->
		<link href="../assets/vendors/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />

		<!--end::Page Vendors Styles -->

		<!--begin::Global Theme Styles(used by all pages) -->
		<link href="../assets/vendors/global/vendors.bundle.css" rel="stylesheet" type="text/css" />
		<link href="../assets/css/demo1/style.bundle.css" rel="stylesheet" type="text/css" />

		<!--end::Global Theme Styles -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.css">

		<!--begin::Layout Skins(used by all pages) -->
		<link href="../assets/css/demo1/skins/header/base/light.css" rel="stylesheet" type="text/css" />
		<link href="../assets/css/demo1/skins/header/menu/light.css" rel="stylesheet" type="text/css" />
		<link href="../assets/css/demo1/skins/brand/dark.css" rel="stylesheet" type="text/css" />
		<link href="../assets/css/demo1/skins/aside/dark.css" rel="stylesheet" type="text/css" />

		<!--end::Layout Skins -->
        <link rel=icon href="./assets/media/bg/favicon.png">

    <style>
.sweet-alert .sa-icon.sa-success {

    margin-bottom: 30px;
}
.btn-lg, .btn-group-lg > .btn {
    padding: 0.8rem 1.65rem;
    }
    .btn-primary {

    border: double;
}
</style>
  </head>

	<!-- end::Head -->

	<!-- begin::Body -->
    <body style=" background-image: url('../assets/media/bg/bg-7.jpg');


    background-position: center center;


    background-repeat: no-repeat;

    background-attachment: fixed;


    background-size: cover;"

   >
      <div >
<div  class="container-fluid">
        <div class="row Designrow" >
        @if ($message = Session::get('success'))
<script>
 sweetAlert({
                title:'Updated!',
                text: 'Your password has been updated.',
                type:'success'
          },function(isConfirm){
            
          });

</script>
                  @endif

              

                <div class="col-md-12">

                        @if ($message = Session::get('error'))
                        <div class="alert alert-dark mx-auto w-75" role="alert" >
                                <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                                <div class="alert-text"><strong>{{ $message }}</strong></div>
                              </div>
                        @endif
                      <div class="  ContainerHeight2 bg-white mx-auto" style=" height: 319px;width:35%;">

                       <h3 class="text-center AdminTxt">Reset Password</h3>
<?php
$current_url = url()->current();

?>
                       <form class="w-75 mx-auto d-block " id="form" method="POST" action="/watering/Watercanapis/Admin/public/admin/resetpassword">
                       {{ csrf_field() }}
                       <!----><div class="form-group mt-5">
                          <label for="email">Enter new password:</label>
                          <input id="password" name="password" type="password" class="form-control form-control-sm" required="" minlength="8">
                          <input id="password" name="code" type="hidden" class="form-control form-control-sm" value="{{$current_url}}" >
                        </div>
                        <div class="form-group mt-3">
                                <label for="confirmpassword">Confirm password:</label>
                                <input id="confirmpassword" name="confirmpassword" type="password" class="form-control form-control-sm" required="" minlength="8">
                              </div>

                        <div class="form-group mt-5 form-check">

                          <button  style="font-size:13px;color:white;" type="submit" class="btn float-right btn-sm LoginBtn">Reset</button>
                        </div>

                      </form>

                      </div>

                </div>


        </div>
        </div>
    </div>

    </body>


</body>
</html>


