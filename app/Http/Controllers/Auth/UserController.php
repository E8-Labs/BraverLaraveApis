<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Auth\UserType;
use App\Models\Auth\AccountStatus;
use Illuminate\Support\Facades\Validator;
use App\Models\NotificationTypes;
use App\Models\User\Notification;
use App\Models\Subscription;

use App\Http\Resources\User\UserProfileFullResource;
use App\Http\Resources\User\UserProfileLiteResource;

class UserController extends Controller
{
    //

    function getUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('userid', $request->userid)->first();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User details',
					'data' => new UserProfileFullResource($user), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null, 
				]);
			}
    }

    function getUserByInviteCode(Request $request){
    	$validator = Validator::make($request->all(), [
			'invitecode' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$user = User::where('myinvitecode', $request->invitecode)->first();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'User details',
					'data' => new UserProfileFullResource($user), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null, 
				]);
			}
    }


    function deleteUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}
			$user = User::where('userid', $request->userid)->first();
            if($user->role == 'ADMIN'){
				return response()->json(['status' => "0",
					'message'=> 'can not delete admin user ' . $request->userid,
					'data' => $user, 
				]);
			}
			$deleted = User::where('userid', $request->userid)->delete();
			
			if($deleted){
				return response()->json(['status' => "1",
					'message'=> 'User deleted',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user' . $request->userid,
					'data' => null, 
				]);
			}
    }


	function createNewSubscriptionOnApproval($user){
		if($user->stripecustomerid !== NULL){
			// we can create subscription
			$stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
			$plan = $user->subscriptionSelected;



				$code = null;
				if($user->codeSelected != NULL && $user->codeSelected != ''){
					$code = $user->codeSelected;
				}
				$params = [
				  'customer' => $user->stripecustomerid,
	  // 			"promotion_code" => "promo_1NqB0NC2y2Wr4BecXhZvEzeA",
				  "trial_from_plan" => true, // change it to true to avail trial
				  // "trial_period_days" => 7,
				  'items' => [
					['price' => $plan],
				  ],
				];
				
				if($code !== null){
					\Log::info("Promo Code is ". $code);
					// $params["promotion_code"] = $code; // old logic
					$params["discounts"] = [
						[ "coupon" =>  $code ]
					];
				}
				// \Log::info("Creating subscription ", $params);
				$sub = $stripe->subscriptions->create($params);
			// 	\Log::info("Created subscription ", $sub);
				if($sub->id === NULL){
				// failed to create charge
					return ['status' => "0",
						'message'=> "Some error occurred creating charge",
						'data' => $sub, 
					];
				}
				else{
				// subscription was created
					$s = new Subscription;
					$s->userid = $user->userid;
					$s->plan = $plan;
					$s->sub_id = $sub->id;
					$s->sub_status = $sub->status;
					$s->start_date = $sub->start_date . "";
					$saved = $s->save();
					if($saved){
						return ['status' => "1",
							'message'=> "Subscription was created",
							'data' => $sub, 
						];
					}

				}
			
		}
		else{
			// add a card
			return ['status' => "0",
					'message'=> "No payment method found",
					'data' => NULL, 
				];
		}
	}

    function approveUser(Request $request){
    	$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
			"role" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			try{
				//ApprovedShowFlag
				$user = User::where('userid', $request->userid)->first();
			    

				$planSelected = $user->subscriptionSelected;

				$planData = NULL;
				if($planSelected != NULL && $planSelected != ""){
					//subscribe user here.
					$planData = $this->createNewSubscriptionOnApproval($user);
					if($planData){
						if($planData["status"] == "1"){
							\Log::info("Plan successfully subscribed");
							$user->accountstatus = AccountStatus::Approved;
							$user->subscriptionSelected = NULL;
							$user->codeSelected = NULL;
			    			$user->role = $request->role;
						}
						else{
							\Log::info("Plan not subscribed " . $planData);
						}
					}
					else{
						\Log::info("Plan not subscribed  2 " . $planData);
					}
				}
				

				

			    $saved = $user->save();
				if($saved){
					$admin = User::where('role', 'ADMIN')->first();
					Notification::add(NotificationTypes::AccountApproved, $user->userid, $admin->userid, $user);
					return response()->json(['status' => "1",
						'message'=> 'User approved',
						'data' => new UserProfileFullResource($user), 
					]);
				}
				else{
					return response()->json(['status' => "0",
						'message'=> 'Error approving user',
						'data' => null, 
					]);
				}
			}
			catch(\Exception $e){
			    \Log::info('---------------- Exception approving  start----------------------');
			    
			    \Log::info($e);
			    \Log::info('---------------- Exception approving End ----------------------');
			    
			    return response()->json(['status' => "0",
					'message'=> $e->getMessage(),
					'data' => null, 
				]);
			}
    }

    function searchPending(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Pending)->where('role', "!=", UserType::TypeAdmin)->where('role', "!=", 'TEAM')
			->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Pending)->where('name', 'LIKE', "%$search%")->where('role', "!=", UserType::TypeAdmin)->where('role', "!=", 'TEAM')->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => UserProfileLiteResource::collection($users), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }


    function searchActive(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Approved)->where('role', "!=", 'ADMIN')->where('role', "!=", 'TEAM')
			->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Approved)->where('name', 'LIKE', "%$search%")->where('role', "!=", 'ADMIN')->where('role', "!=", 'TEAM')->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => UserProfileLiteResource::collection($users), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }

    function adminTeamMembers(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = User::where('accountstatus', AccountStatus::Approved)->where('role', "=", UserType::TypeTeam)->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;
				$users = User::where('accountstatus', AccountStatus::Approved)->where('name', 'LIKE', "%$search%")->where('role', "=", UserType::TypeTeam)->orderBy('name', 'ASC')->take(50)->skip($off_set)->get();
			}
			else{

			}

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'User list',
					'data' => UserProfileLiteResource::collection($users), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error deleting user',
					'data' => null, 
				]);
			}
    }
}
