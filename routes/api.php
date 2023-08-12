<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Listings\ListingController;
use App\Http\Controllers\Listings\EditListingController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthForgotPasswordController;

use App\Http\Controllers\Auth\SocialLoginController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('forgot_password', [AuthForgotPasswordController::class, 'submitForgetPasswordForm']);//->name('reset.password.post');

// Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
// Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
Route::get('reset-password/{token}', [AuthForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [AuthForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');


Route::post("reset-password-admin",[AuthForgotPasswordController::class,'resetPasswordAdmin']);


Route::post("sendTestEmail",[UserAuthController::class,'sendWelcomeEmail']);
Route::post("checkemail",[UserAuthController::class,'checkEmailExists']);
Route::post("checkcode",[UserAuthController::class,'checkCode']);
Route::post("checkphone",[UserAuthController::class,'checkPhoneExists']);
Route::post("registernewuser",[UserAuthController::class,'Register']);
Route::post("login",[UserAuthController::class,'login']);

Route::post("check_social_Login_exists",[SocialLoginController::class,'isSocialLoginAccountExists']);
Route::post("register_social", [SocialLoginController::class, 'RegisterUserWithSocial']);




Route::post("deleteuser",[UserController::class,'deleteUser']);
Route::post("updateuser",[UserAuthController::class,'updateUser']);
Route::post("redeem_code",[UserAuthController::class,'redeemOfferCode']);
Route::post("updateinvitecode",[UserAuthController::class,'updateInviteCode']);
Route::post("approveuser",[UserController::class,'approveUser']);

Route::post("addcard",[PaymentController::class,'addCard']);
Route::post("cardlist",[PaymentController::class,'cardList']);
Route::post("create_subscription", [PaymentController::class, "createSubscription"]);
Route::post("upgrade_subscription", [PaymentController::class, "upgradeSubscription"]);

Route::post("addyacht",[ListingController::class,'addListing']);
Route::post("deleteyacht",[ListingController::class,'deleteListing']);
Route::post("edityacht",[EditListingController::class,'editListing']);
Route::get("listyachts",[ListingController::class,'getListings']);
Route::get("getyachtbyid",[ListingController::class,'getListingById']);
Route::post("reportlisting",[ListingController::class,'reportListing']);
Route::post("featurelisting",[ListingController::class,'featuretListing']);

Route::get("getchekrreport",[UserAuthController::class,'getChekrReport']);
Route::get("createchekrreport",[UserAuthController::class,'createChekrReport']);

//Chat
Route::post("createchat",[ChatController::class,'createChat']);
Route::post("uploadchatimage",[ChatController::class,'uploadChatImage']);
Route::post("updatechat",[ChatController::class,'updateChat']);
Route::get("getchatbyid",[ChatController::class,'getChatById']);
Route::get("chatlistteam",[ChatController::class,'getTeamChat']);
Route::get("chatlistadmin",[ChatController::class,'getTeamChat']);
Route::get("chatlistuser",[ChatController::class,'getUserChat']);
Route::get("loaduserrequests",[ChatController::class,'getUserRequests']);
Route::get("deletechat",[ChatController::class,'deleteChat']);
Route::get("unreadnotificationsadmin",[ChatController::class,'getUnreadNotifications']);

Route::post("reserveyacht",[PaymentController::class,'makeReservation']);
Route::post("cancelreservation",[PaymentController::class,'cancelReservation']);
Route::post("create_crypto_charge",[PaymentController::class,'createCryptoChargeLinkOnServer']);
Route::post("deletecard",[PaymentController::class,'deleteCard']);


//Notifications
Route::post("add_notification",[NotificationController::class,'addNotification']);
Route::get("notifications_list",[NotificationController::class,'notificationsList']);

Route::post("searchpending",[UserController::class,'searchPending']);
Route::post("searchactive",[UserController::class,'searchActive']);

Route::post("pendingusers",[UserController::class,'searchPending']);
Route::post("activeusers",[UserController::class,'searchActive']);

Route::post("teammembers",[UserController::class,'adminTeamMembers']);
Route::get("getuserbyid",[UserController::class,'getUser']);
Route::get("getuserbyinvitecode",[UserController::class,'getUserByInviteCode']);
Route::get("loadmenu",[MenuController::class,'loadMenu']);


















