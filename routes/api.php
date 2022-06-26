<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Listings\ListingController;
use App\Http\Controllers\Chat\ChatController;


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

Route::post("register",[UserAuthController::class,'Register']);
Route::post("login",[UserAuthController::class,'login']);
Route::post("deleteuser",[UserController::class,'deleteUser']);
Route::post("updateuser",[UserAuthController::class,'updateUser']);
Route::post("approveuser",[UserController::class,'approveUser']);

Route::post("addcard",[PaymentController::class,'addCard']);
Route::post("cardlist",[PaymentController::class,'cardList']);

Route::post("addyacht",[ListingController::class,'addListing']);
Route::get("listyachts",[ListingController::class,'getListings']);
Route::get("getyachtbyid",[ListingController::class,'getListingById']);
Route::post("reportlisting",[ListingController::class,'reportListing']);
Route::post("featurelisting",[ListingController::class,'featuretListing']);


//Chat
Route::post("createchat",[ChatController::class,'createChat']);



Route::post("searchpending",[UserController::class,'searchPending']);
Route::post("searchactive",[UserController::class,'searchActive']);

Route::post("pendingusers",[UserController::class,'searchPending']);
Route::post("activeuses",[UserController::class,'searchActive']);

Route::post("teammembers",[UserController::class,'adminTeamMembers']);
Route::get("getuserbyid",[UserController::class,'getUser']);
Route::get("getuserbyinvitecode",[UserController::class,'getUserByInviteCode']);
Route::get("loadmenu",[MenuController::class,'loadMenu']);