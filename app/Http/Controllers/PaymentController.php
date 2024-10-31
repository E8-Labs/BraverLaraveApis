<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Chat\Invoice;
use App\Models\Chat\ChatThread;
use App\Models\Chat\ChatUser;
use App\Models\Listing\Reservation;
use App\Models\Listing\ReservationStatus;
use App\Models\Card;
use App\Models\Subscription;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\NotificationTypes;
use App\Models\User\Notification;
use App\Models\PaymentIntent;

use Carbon\Carbon;
use Kreait\Laravel\Firebase\Facades\Firebase;

class PaymentController extends Controller
{
    //
    private $coinbase_api_key = "b36967ae-8715-44d7-808b-15ee5a29bb60";//"ecbb36a1-1305-4cfb-917a-2a34561df982";

    function addCard(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
			"cardnumber" => 'required',
			"cardholdername" => 'required',
			"expirydate" => 'required',
			"source" => 'required',
			"cvc" => 'required',
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
			$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
			if($user->stripecustomerid == NULL || $user->stripecustomerid == ''){
				//Generate Stripe id
				
            	$customer = $stripe->customers->create([
            		'description' => 'Braver Customer',
            		'email' => $user->email,
            		'name' => $user->name,
            
             	]);
            	$stripeid= $customer['id'];
            	$user->stripecustomerid = $stripeid;
            	$user->save();
			}
			else{

			}

			try{  
                
                    $card = $stripe->customers->createSource(
                    $user->stripecustomerid,
                    ['source' => $request->source]
                    );

                    $stripecardid=$card['id'];
                    $last4=$card['last4'];
                    $expiryyear=$card['exp_year'];
                    $expirymonth=$card['exp_month'];
                    $country=$card['country'];
                    $brand=$card['brand'];
                    $zip=$card['address_zip'];
                  
               

               $c = new Card();
               $c->cardnumber = $last4;
               $c->expirydate = $expirymonth."/".$expiryyear;
               $c->cardholdername = $request->cardholdername;
               $c->cardbrand = $brand;
               $c->stripecardid = $stripecardid;
               $c->userid = $request->userid;
               // $c->cvc = '';
               $c->save();
                 // $save = Card::create([
                 //        'cardnumber'=>$last4,
                 //        'expirydate' => $expirymonth."/".$expiryyear,
                 //        'cardholdername' => $request->cardholdername,
                 //        'cardbrand' => $brand,
                 //        'zip' => $zip,
                 //        'stripecardid' => $stripecardid,
                 //        'userid' => $request->userid,
                       
                 //    ]);
                    // $id= $save->id;
                    // $c = Card::where('cardid', $id)->first();
                    return response()->json(['status' => "1",
						'message'=> 'Card created',
						'data' => $c, 
					]);







			} catch(\Stripe\Exception\CardException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\RateLimitException $e) {
			  $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\InvalidRequestException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\AuthenticationException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\ApiConnectionException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\ApiErrorException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> 'Card not created '. $error,
						'data' => null, 
					]);
			} 



			    
	}


	function deleteCard(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"stripecardid" => 'required',
				]);

				$cardid = $request->stripecardid;
				$customerid = $request->customerid;

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

			
			$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
        	$sub = $stripe->customers->deleteSource(
            	$customerid,
            	$cardid,
            	[]
        	);
        	Card::where('stripecardid', $request->stripecardid)->delete();
        	return response()->json(['status' => "1",
					'message'=> 'Card deleted',
					'data' => null, 
				]);
	}


	function cardList(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
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
			$userid = $request->userid;
			$user = User::where('userid', $userid)->first();
			if($user == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null, 
				]);
			}
			if($user->stripecustomerid == '' || $user->stripecustomerid == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No cards for this user',
					'data' => null, 
				]);

			}
			try{
			    $stripe = new \Stripe\StripeClient( env('Stripe_Secret'));


			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$limit = $page * 50;

			$data = $stripe->customers->allSources(
            $user->stripecustomerid,
            ['object' => 'card', 'limit' => $limit]
            );

			// return $data;
			$cards = $data->data;
			$rows = array();
			for($i = 0; $i < count($cards); $i++){
    		   $c = $cards[$i];
    		   $stid = $c["id"];
    		   $brand = $c["brand"];
    		   $last = $c["last4"];
    		   $name = $c["name"];
    		   $exm = $c["exp_month"];
    		   $exy = $c["exp_year"];
    		   $expiry = "$exm/$exy";
    		   
    		   $row = array();
    		   $row["stripecardid"] = $stid;
    		   $row["expirydate"] = $expiry;
    		   $row["cardbrand"] = $brand;
    		   $row["cardholdername"] = $name;
    		   $row["cardnumber"] = "**** **** **** $last";
    		   $row["userid"] = $userid;
    		   $row["cvc"] = "";
    		   $dbCard = Card::where('stripecardid', $stid)->first();
    		   $row["dateadded"] = "";
    		   $row["environment"] = "";
    		   if($dbCard){
    		   	$row["cardid"] = $dbCard->cardid;
    		   	$rows[] = $row;
    		   }
    		   else{
    		   		// $stripe->customers->deleteSource(
        			//     $user->stripecustomerid,
        			//     $stid,
        			//     []
        			// );
    		   }
    		   
    		   
    		   
    		}  
    		return response()->json(['status' => "1",
						'message'=> 'Card list ',
						'data' => $rows, 
					]);
			}
					
					
					catch(\Stripe\Exception\CardException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=>  $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\RateLimitException $e) {
			  $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\InvalidRequestException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=>  $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\AuthenticationException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=>  $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\ApiConnectionException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> $error,
						'data' => null, 
					]);
			} catch (\Stripe\Exception\ApiErrorException $e) {
			   $error= $e->getError()->message;
			     return response()->json(['status' => "0",
						'message'=> $error,
						'data' => null, 
					]);
			} 

	}

	private function CreateInvoiceOnServer($request, $charge){
		$invoice = Invoice::where('reservation_id', $request->reservation_id)->first();
			 $message = "";
			 
			if($invoice){
        		
			}
			else{
				$invoice = new Invoice();
				$invoice->invoice_id = $request->invoice_id;
			}
			$tip = 0;
			$serviceFee = 0;
			$tax = 0;
			if($request->has('tip')){
				$tip = (double)$request->tip;
			}

			if($request->has('tax')){
				$tax = (double)$request->tax;
			}
			if($request->has('service_fee')){
				$serviceFee = (double)$request->service_fee;
			}

			$amount = $request->amount + $tax + $serviceFee + $tip;

			$invoice->amount = $request->amount;
    		// echo "this is charge";
    		$invoice->stripe_charge_id = $charge["stripe_charge_id"];
			$invoice->crypto_charge_id = $charge["crypto_charge_id"];
    		// $price = $charge["price"];
    		$payment_status = $charge["payments_status"];
    		$timeline_status = $charge["timeline_status"];

    		// $invoice->crypto_charge_code = $code;
    		$invoice->invoice_by = $request->userid;
    		$invoice->reservation_id = $request->reservation_id;
    		// $invoice->crypto_charge_id = $charge_id;
    		// $invoice->crypto_charge_url = $url;
    		$invoice->payment_status = $payment_status;
    		$invoice->timeline_status = $timeline_status;
			$invoice->service_fee = $serviceFee;
			$invoice->tip = $tip;
			$invoice->tax = $tax;
    		$saved = $invoice->save();
			return $saved;
	}


	function makeReservation(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
			// "reservationdate" => 'required',
			// "reservationtime" => 'required',
			"yachtid" => 'required',
			"amount" => 'required',
			"chatid" => 'required',
			"invoice_id" => 'required',

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
				$res = Reservation::where('chatid', $request->chatid)->first();
			if($res == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No reservation found',
					'data' => null, 
				]);
			}

			DB::beginTransaction();
			// $res->reservationid = uniqid();
			$res->chatid = $request->chatid;
			
			$res->reservedfor = $request->userid;
			$res->dateadded = Carbon::now()->toDateTimeString();
			$yachtid = $request->yachtid;
			$date = $request->reservationdate;
			$time = $request->reservationtime;
			if($request->has('invoicedescription')){
				$res->invoicedescription = $request->invoicedescription;
			}
			else{
				$res->invoicedescription = '';
			}

			if($request->has('reservationdescription')){
				$res->reservationdescription = $request->reservationdescription;
			}
			else{
				$res->reservationdescription = '';
			}

			if($request->has('amount')){
				$res->amountpaid = $request->amount * 100;
			}
			else{
				
			}


			$user = User::where('userid', $request->userid)->first();
			if($user->stripecustomerid == NULL || $user->stripecustomerid == ''){
				return response()->json(['status' => "0",
					'message'=> "User haven't added any Payment method",
					'data' => null, 
				]);
			}

			//Charge user
			$source = '';
			if($request->has('paymentmethod')){
				$source = $request->paymentmethod;
			}
			$cost = $res->amountpaid * 100;

			// return $cost;
			$charge = $this->chargeUser($cost, $user->stripecustomerid, $res->reservationdescription, $source);
			if($charge->id == NULL){
				DB::rollBack();
				return response()->json(['status' => "0",
					'message'=> "Error processing payment",
					'data' => null, 
				]);
			}
			else{
				$trid = $charge["id"];
				$res->reservationstatus = ReservationStatus::StatusReserved;
				$res->transactionid = $trid;
				$res->save();
				try{
					$invoiceCreated = $this->CreateInvoiceOnServer($request, ["stripe_charge_id" => $trid, "crypto_charge_id"=> null, "payment_status"=> "Confirmed", "timeline_status" => "Completed"]);
				}
				catch(\Exception $e){
					\Log::info("-----------StripeLog---------------");
					\Log::info("Stripe payment error " . $e->getMessage());
					\Log::info($e);
					\Log::info("-----------StripeLog---------------");
				}
				DB::commit();
				$chat = ChatThread::where('chatid', $request->chatid)->first();
				$this->sendNotToAllUsers($user, $chat);

				//Update the invoice in firebase
				//3 lines below needs to be tested
				$database = Firebase::database();
		        $reference = $database->getReference('Chat/' . $request->chatid . "/" . $request->invoice_id);
                $reference->update(["paid" => true, "payment_status" => "CONFIRMED"]);


				return response()->json(['status' => "1",
					'message'=> "Payment processed & reservation made",
					'data' => null, 
				]);
			}
			}
			catch(\Exception $e){
				\Log::info('Reservation exception start');
				\Log::info($e);
				\Log::info('Reservation exception end');
				DB::rollBack();
				return response()->json(['status' => "0",
					'message'=> $e->getMessage(),
					'data' => null, 
				]);
			}

			

	}

	function getUserActiveSubscriptions($stripecustomerid){
		$stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
        // $haveActiveSubs = NULL;

        $stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
        // $paymentController = new PaymentController;
        $haveActiveSubs = NULL;//$paymentController->getUserActiveSubscriptions($this->stripecustomerid);

        try{
            \Log::info("Checking active");
            $haveActiveSubs = $stripe->subscriptions->all(['limit' => 30, 'customer' => $stripecustomerid, "status" => "active"]);
                // return $haveActiveSubs;
        }
        catch(\Exception $e){
            \Log::info($e);
            \Log::info("No active subs");
        }
        if($haveActiveSubs === NULL || count($haveActiveSubs->data) === 0){
            try{
                \Log::info("Checking trial");
            $haveActiveSubs = $stripe->subscriptions->all(['limit' => 30, 'customer' => $stripecustomerid, "status" => "trialing"]);
                // return $haveActiveSubs;
            }
            catch(\Exception $e){
                \Log::info("No trials subs " . $e->getMessage());
            }
        }

        if($haveActiveSubs){
        	// $data = $haveActiveSubs->data;
        	return $haveActiveSubs->data;
        }
        else{
        	return NULL;
        }
	}





function upgradeSubscription(Request $request){
    $userid = $request->userid;
    $plan = $request->plan; // subscribe to new plan
    $user = User::where('userid', $userid)->first();
    $plans = $this->getUserActiveSubscriptions($user->stripecustomerid);
    if($plans === NULL || count($plans) === 0){
        //if no previous subscription, then just subscribe
        return $this->createSubscription($request);
    }
    else{
        $sub = $plans[0];
        $isTrial = $this->checkIfTrial($sub);
        if($isTrial){
            return response()->json([
                'status' => "1",
                'message'=> "Please wait for the trial to expire",
                'data' => NULL,
            ]);
        }

        $id = $sub->id;
        $subItem = $sub->items->data[0];
        $subItemId = $subItem->id;

        $stripe = new \Stripe\StripeClient(env('Stripe_Secret'));

        // Step 1: Remove discount if any before upgrading
        try {
            $stripe->subscriptions->deleteDiscount($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => "0",
                'message'=> "Failed to remove discount: " . $e->getMessage(),
                'data' => NULL,
            ]);
        }

        // Step 2: Update the subscription
        try {
            $updated = $stripe->subscriptions->update(
                $id,
                ["items" => [["id" => $subItemId, "price" => $plan]]]
            );

            if($updated){
                return response()->json([
                    'status' => "1",
                    'message'=> "Plan upgraded",
                    'data' => $updated,
                ]);
            } else {
                return response()->json([
                    'status' => "0",
                    'message'=> "Error upgrading the plan",
                    'data' => NULL,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => "0",
                'message'=> "Error during upgrade: " . $e->getMessage(),
                'data' => NULL,
            ]);
        }
    }
}


	 function checkIfTrial($plan){
	 	// foreach($plans as $plan){
	 		if($plan->status === "trialing"){
	 			return TRUE;
	 		}
	 	// }
	 	return FALSE;
	 }



	 

	function createSubscription(Request $request){
		$userid = $request->userid;
		$plan = $request->plan;
		$card = null;
		if($request->has("payment_method")){
		    $card = $request->payment_method;
		}
		$stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
		$oldSub = Subscription::where('userid', $userid)->where('plan', $plan)->orderBy('id', 'DESC')->first();

		
		$haveSubAlready = NULL;
		if($oldSub){
			// return $oldSub->sub_id;
			try{
				$haveSubAlready = $stripe->subscriptions->retrieve($oldSub->sub_id, []);
				if($haveSubAlready->status === "active"){
					return response()->json(['status' => "1",
								'message'=> "Subscription already exists",
								'data' => $sub, 
					]);
				}
				if($haveSubAlready->status === "trialing" ){
					return response()->json(['status' => "1",
								'message'=> "Subscription in trial mode",
								'data' => $sub, 
					]);
				}
				// return $haveSubAlready;
			}
			catch(\Exception $e){

			}
		}
		

		$user = User::where('userid', $userid)->first();
		if($user){
			if($user->stripecustomerid !== NULL){
				// we can create subscription
				
				if($haveSubAlready && $haveSubAlready->status === "paused"){
					\Log::info($haveSubAlready);
					$sub = $stripe->subscriptions->resume(
						$haveSubAlready->id,
						['billing_cycle_anchor' => 'now']
					);
					$oldSub->status = $sub->status;
					$oldSub->save();
					return response()->json(['status' => "1",
								'message'=> "Subscription already existed & paused for same product so renewed",
								'data' => $sub, 
					]);
				}
				else{
				    $code = null;
				    if($request->has("promo_code")){
				        $code = $request->promo_code;
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
				    if($card !== null){
				        $params = ['default_payment_method' => $card, 
				            'customer' => $user->stripecustomerid,
				            // "promotion_code" => "promo_1NqB0NC2y2Wr4BecXhZvEzeA",
  					        "trial_from_plan" => true, // change it to true to avail trial
  					        // "trial_period_days" => 7,
  					        'items' => [
    					         ['price' => $plan],
  					        ],
  					     ];
				    }
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
						return response()->json(['status' => "0",
							'message'=> "Some error occurred creating charge",
							'data' => $sub, 
						]);
					}
					else{
					// subscription was created
						$s = new Subscription;
						$s->userid = $userid;
						$s->plan = $plan;
						$s->sub_id = $sub->id;
						$s->sub_status = $sub->status;
						$s->start_date = $sub->start_date . "";
						$saved = $s->save();
						if($saved){
							return response()->json(['status' => "1",
								'message'=> "Subscription was created",
								'data' => $sub, 
							]);
						}

					}
				}
				
			}
			else{
				// add a card
				return response()->json(['status' => "0",
						'message'=> "No payment method found",
						'data' => NULL, 
					]);
			}
		}
		else{
			// user does not exist
			return response()->json(['status' => "0",
						'message'=> "No such user",
						'data' => NULL, 
					]);
		}
	}



public function validateCoupon(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'coupon_code' => 'required|string',
        ]);
        //  dd($request->all());

        // Get the environment (Sandbox or Production)
        $stripe = new \Stripe\StripeClient(env('Stripe_Secret'));

        try {
            // Retrieve the coupon from Stripe using the provided code
            $coupon = $stripe->coupons->retrieve($request->input('coupon_code'));

            // Check if the coupon is valid (not expired or applied)
            if ($coupon && !$coupon->valid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon is not valid.',
                ]);
            }

            return response()->json([
                'status' => "1",
                'message' => 'Coupon is valid.',
                'coupon' => $coupon,
            ]);
        } catch (\Exception $e) {
            // Handle error if the coupon is not found or any other issue occurs
            return response()->json([
                'status' => "0",
                'message' => 'Error retrieving coupon: ' . $e->getMessage(),
            ]);
        }
    }
	

	function cancelSubscription(Request $request){
		$userid = $request->userid;
		

		$stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
		$oldSub = Subscription::where('userid', $userid)->orderBy('id', 'DESC')->first();

		
		$haveSubAlready = NULL;
		$user = User::where("userid", $userid)->first();
			//check directly on stripe, if the user has a subscription
			$haveActiveSubs = $this->getUserActiveSubscriptions($user->stripecustomerid);
			if($haveActiveSubs){
				$sub = $haveActiveSubs[0];
				$sub->id;
			}
		if($oldSub || $haveActiveSubs){
			// return $oldSub->sub_id;
// 			return "Here";
// 			if(!$oldSub && $haveActiveSubs){
// 			    $oldSub = $haveActiveSubs[0];
// 			 //   return "Here ". $oldSub->id;
// 			}
			try{
			    $haveSubAlready = NULL;
				if($oldSub){
				    $haveSubAlready = $stripe->subscriptions->retrieve($oldSub->sub_id, []);
				}
				else if ($haveActiveSubs){
				    $haveSubAlready = $haveActiveSubs[0];
				}
				if($haveSubAlready->status === "active" || $haveSubAlready->status === "trialing"){
					// cancel here
					// $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
					$cancelled = $stripe->subscriptions->cancel($haveSubAlready->id, []);
					if($cancelled){
					    // $oldSub->delete();
					}
					return response()->json(['status' => "1",
						'message'=> "Subscription cancelled",
						'data' => $cancelled, 
						
					]);
				}
			}
			catch(\Exception $e){
				return response()->json(['status' => "0",
					'message'=> $e->getMessage(),
					'data' => null, 
					"old" => $oldSub,
						"already" => $haveSubAlready,
						"active" => $haveActiveSubs
				]);
			}
		}
		else{
			
			return response()->json(['status' => "0",
					'message'=> "User is not subscribed " ,
					'data' => null, 
				]);
		}
	}


	private function chargeUser($amount, $customerstripeid, $description, $source){
		$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
        $array = array();
        if($source === ''){
            $array = [
          'amount' => $amount,
          'currency' => 'usd',
          'customer' => $customerstripeid,
          'description' => $description,
        ];
        }
        else{
            $array = [
          'amount' => $amount,
          'currency' => 'usd',
          'customer' => $customerstripeid,
          'source' => $source,
          'description' => $description,
        ];
        }
        
        
        $charge = $stripe->charges->create($array);

        return $charge;
	}

	public function createCryptoChargeLinkOnServer(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"invoice_id" => 'required',
			"reservation_id" => 'required',
			"userid" => 'required',
			"amount" => 'required',


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

			$description = '';
			if($request->has('description')){
				$description = $request->description;
			}
			$name = 'Crypto';
			if($request->has('name')){
				$name = $request->name;
			}
			$tip = 0;
			$serviceFee = 0;
			$tax = 0;
			if($request->has('tip')){
				$tip = (double)$request->tip;
			}

			if($request->has('tax')){
				$tax = (double)$request->tax;
			}
			if($request->has('service_fee')){
				$serviceFee = (double)$request->service_fee;
			}

			$invoice = Invoice::where('invoice_id', $request->invoice_id)->first();
			 $message = "";
			 $charge = array();
			if($invoice){
        		$chargeid = $invoice->crypto_charge_code;
        		$charge = $this->getCryptoCharge($chargeid);
        		$charge = $this->getRequiredDataFromCharge($charge);
        		$timeline_status = $charge["timeline_status"];
        		$payment_status = $charge["payments_status"];
        		if($timeline_status == "EXPIRED" || $payment_status == "CANCELLED"){
        		    $charge = null;
        		    $message = "Charge expired or cancelled. Generating new";
        		}
			}
			else{
				$invoice = new Invoice();
				$invoice->invoice_id = $request->invoice_id;
			}

			$amount = $request->amount + $tax + $serviceFee + $tip;

			if($charge == null){
        		$charge = $this->createCryptoCharge($amount, $description, $name);
    		}
    		else{
    		    $message = "Charge already exists and not expired and not cancelled";
    		}
    		// echo "this is charge";
    		$code = $charge["code"];
    		$url = $charge["payment_url"];
    		$charge_id = $charge["charge_id"];
    		$price = $charge["price"];
    		$payment_status = $charge["payments_status"];
    		$timeline_status = $charge["timeline_status"];

    		$invoice->crypto_charge_code = $code;
    		$invoice->invoice_by = $request->userid;
    		$invoice->reservation_id = $request->reservation_id;
    		$invoice->crypto_charge_id = $charge_id;
    		$invoice->crypto_charge_url = $url;
    		$invoice->payment_status = $payment_status;
    		$invoice->timeline_status = $timeline_status;
			$invoice->service_fee = $serviceFee;
			$invoice->tip = $tip;
			$invoice->tax = $tax;
    		$saved = $invoice->save();
    		if($saved){
    			return response()->json(['status' => "1",
					'message'=> 'Crypto Charge Created',
					'data' => $charge, 
				]);
    		}
    		else{
    			return response()->json(['status' => "0",
					'message'=> 'Error creating charge',
					'data' => null, 
				]);
    		}
	}

	// function createNewInvoice(Request $request){
	// 	$key = $request->apikey;
	// 		if($key != $this->APIKEY){ // get value from constants
	// 			return response()->json(['status' => "0",
	// 				'message'=> 'invalid api key',
	// 				'data' => null, 
	// 			]);
	// 		}
	// 		$description = '';
	// 		if($request->has('description')){
	// 			$description = $request->description;
	// 		}
	// 		$name = 'Charge';
	// 		if($request->has('name')){
	// 			$name = $request->name;
	// 		}
	// 	$invoice = new Invoice();
	// 	$invoice->invoice_id = $request->invoice_id;

	// 	$charge = $this->createCryptoCharge($request->amount + $request->tax + $request->service_fee, $description, $name);
	// 	$invoice->crypto_charge_code = $code;
    // 		$invoice->invoice_by = $request->userid;
    // 		$invoice->reservation_id = $request->reservation_id;
    // 		$invoice->crypto_charge_id = $charge_id;
    // 		$invoice->crypto_charge_url = $url;
    // 		$invoice->payment_status = $payment_status;
    // 		$invoice->timeline_status = $timeline_status;
    // 		$saved = $invoice->save();
	// }
		//transactionId = $paymentIntentId in case of ACH Payments
		function updateReservation($chatid, $transactionId, $invoice_id, $tip, $tax, $serviceFee, $totalAmount, $payment_status){
		    \Log::info("Updating reservation");
			$res = Reservation::where('chatid', $chatid)->first();
			if($res){
			    \Log::info("Updating reservation found");
				// DB::beginTransaction();
				$res->dateadded = Carbon::now()->toDateTimeString();
				$res->reservationstatus = ReservationStatus::StatusReserved;
				$res->transactionid = $transactionId;
				$res->save();
				try{
					$invoice = Invoice::where('reservation_id', $res->reservationid)->first();
					 $message = "";

					if($invoice){
					
					}
					else{
						$invoice = new Invoice();
						$invoice->invoice_id = $invoice_id;
					}
					
				
					$amount = $totalAmount;
				
					$invoice->amount = $amount;
					$invoice->stripe_charge_id = $transactionId;
					$invoice->reservation_id = $res->reservationid;
					$invoice->payment_status = $payment_status;
					$invoice->invoice_by = $res->reserved_for;
					$saved = $invoice->save();
				// 	DB::commit();
					\Log::info("Updating reservation returning with invoice " . $payment_status);
					return $saved;
				}
				catch(\Exception $e){
					return null;
					\Log::info("-----------StripeLog---------------");
					\Log::info("Stripe payment error " . $e->getMessage());
					\Log::info($e);
					\Log::info("-----------StripeLog---------------");
				}
				
			}
			else{
				return null;
			}
		}

	function stripeWebhook(Request $request){
		$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
		$payload = json_decode($request->getContent(), true);
		\Log::info("------------------------------------------------------");
		\Log::info("Webhook stripe called");
		
		$event_id = $payload["id"];
		$event_type = $payload["type"]; // customer.subscription.updated etc
		\Log::info("Event is " . $event_type);
		\Log::info("Event Data");
		\Log::info($payload);
		
		
		if($event_type === "customer.subscription.updated" || $event_type === "customer.subscription.deleted"){
			$subData = $payload["data"]["object"];
		
			$subid = $subData["id"];
			$status = $subData["status"];
			$cancel_at_period_end = $subData["cancel_at_period_end"];
			
			$dbSub = Subscription::where("sub_id", $subid)->first();
			
			
			$items = $subData["items"]["data"];
			$firstPlan = $items[0]["plan"];
// 			\Log::info($subData);
			$interval = $firstPlan["interval"];
			$plan_id = $firstPlan["id"];
			$amount = $firstPlan["amount"];
			if($dbSub){
			    $dbSub->sub_status = $status;
			    $dbSub->cancel_at_period_end = $cancel_at_period_end;
			    $dbSub->sub_interval = $interval;
			    $dbSub->plan = $plan_id;
			    $dbSub->price = $amount;
			    $dbSub->save();
			}
			if($status == "canceled"){
			    $dbSub->delete();
				\Log::info("Subscription cancelled Sending email");
				$user = User::where("userid", $dbSub->userid)->first();
				$data = array( 'user_name'=> $user->name, "user_email" => $user->email, "phone"=> $user->phone, "city"=> $user->city, "state"=> $user->state, "user_message" => "");
				//send email to the user about subscription cancellation
				Mail::send('Mail/subscriptioncancel', $data, function ($message) use ($data, $user) {
					//send to $user->email
					//"salmanmajid14@gmail.com"
					//$user->email
					$message->to([$user->email, "salman@e8-labs.com"]/*$user->email*/,'Subscription Cancelled')->subject('Subscription Cancelled');
				});
			}
		}
		else if($event_type === "payment_intent.processing" || $event_type === "payment_intent.succeeded" || $event_type === "payment_intent.payment_failed"){
			\Log::info("Payment Intent Event");
			$subData = $payload["data"]["object"];
			$paymentIntentId = $subData["id"];
			$isLive = $subData["livemode"];
			$next_action = $subData["next_action"];
			$payment_method = $subData["payment_method"];
			
			$intent = PaymentIntent::where("payment_intent_id", $paymentIntentId)->first();
			if($intent){
			    \Log::info("Intent Inside");
				$intent->mode = $isLive ? "Live" : "Test";
				$intent->next_action = $next_action;
				$intent->payment_method = $payment_method;
				$intent->webhook_action = $event_type;
				$payment_status = "NEW";
				if($event_type === "payment_intent.processing"){
					//payment New
					$database = Firebase::database();
		                $reference = $database->getReference('Chat/' . $intent->chatid . "/" . $intent->invoiceid);
		              //  $snapshot = $reference->getSnapshot();
                        $reference->update(["paid" => false, "payment_status" => "PENDING"]);
				}
				else if($event_type === "payment_intent.succeeded"){
					// make the reservation here
					//also update invoice in firebase
                    \Log::info("Event Succeded");
					//#1 Reserve
					$payment_status = "CONFIRMED";
					$updated = $this->updateReservation($intent->chatid, $paymentIntentId, $intent->invoiceid, $intent->tip, $intent->tax, $intent->service_fee, $intent->mount, $payment_status);
					if($updated){
                        \Log::info("Reservation updated");
					}
					else{
                        \Log::info("Reservation Not updated");
					}

					//#2 Make Invoice Paid Here
                    	$database = Firebase::database();
		                $reference = $database->getReference('Chat/' . $intent->chatid . "/" . $intent->invoiceid);
		              //  $snapshot = $reference->getSnapshot();
                        $reference->update(["paid" => true, "payment_status" => "CONFIRMED"]);
		                
		                $invRef = $database->getReference('Chat/' . $intent->chatid)->push([
		                    "chatId" => $intent->chatid,
		                    "date" => Carbon::now()->format("d/m/Y"),
		                    "invoiceAmount" => "",
		                    "invoiceId" => $intent->invoiceid,
		                    "msg" => "Your invoice had been successfully paid",
		                    "type" => "InvoicePaid",
		                    "senderId" => $intent->userid,
		                    "msgId" => "",
		                    "timestamp" => Carbon::now()->timestamp
		                    ]);
		                    
		                    $key = $invRef->getKey();
		                    
		                    $invRef->update(["msgId" => $key]);
		                    
		                    //send notification to all users
		                    $user = User::where('userid', $intent->userid)->first();
		                    $chat = ChatThread::where('chatid', $intent->chatid)->first();
				            $this->sendNotToAllUsers($user, $chat);
		                    
					
				}
				else if($event_type === "payment_intent.payment_failed"){
					// do not 
					$payment_status = "FAILED";
					$database = Firebase::database();
		            $reference = $database->getReference('Chat/' . $intent->chatid . "/" . $intent->invoiceid);
                    $reference->update(["paid" => false, "payment_status" => "FAILED"]);
				}
				\Log::info("ReservationSaving Intent");
				$intent->save();

			}
			else{
				\Log::info("This payment intent does not exists ". $paymentIntentId);
			}
			
			
		}
		\Log::info("------------------------------------------------------");
		
		
		return response()->json(['status' => "1",
					'message'=> 'Handled',
					'data' => null, 
				]);
	}


	public function cancelReservation(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"reservationid" => 'required',
			"fromid" => 'required',

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


			$resid = $request->reservationid;
			$fromid = $request->fromid;

			$fromUser = User::where("userid", $fromid)->first();

			$res = Reservation::where('reservationid', $resid)->first();
			$reason = "";
			if($request->has('reason')){
				$reason = $request->reason;
				$res->reason = $reason;
			}
			if($res == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No such reservation',
					'data' => null, 
				]);
			}
			$chargeid = $res->transactionid;

			$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
			try {
                    
                $ref = $stripe->refunds->create([
                'charge' => $chargeid,
                ]);
                if($ref->id == NULL){
                	$er = $ref->ErrorCode;
                	if($er == "charge_already_refunded"){
                		return response()->json(['status' => "1",
							'message'=> 'Already cancelled and refunded ' ,
							'data' => null, 
						]);
						$res->reservationstatus = ReservationStatus::StatusCancelled;
                		$res->cancelledby = $fromid;
                		$res->refunddate = Carbon::now()->toDateTimeString();
                		$res->save();
                	}
                	
                	return response()->json(['status' => "0",
							'message'=> 'Error cancelling reservation ' ,
							'data' => null, 
					]);
                	
                }
                else{
                	$res->reservationstatus = ReservationStatus::StatusCancelled;
                	$res->cancelledby = $fromid;
                	$res->refunddate = Carbon::now()->toDateTimeString();
                	$res->refundid = $ref->id;
                	$res->save();
                	return response()->json(['status' => "1",
							'message'=> 'Cancelled reservation and refunded ' ,
							'data' => null, 
					]);
                }
            }
            catch(\Stripe\Exception\CardException $e) {
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (\Stripe\Exception\RateLimitException $e) {
                  // Too many requests made to the API too quickly
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                  // Invalid parameters were supplied to Stripe's API
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (\Stripe\Exception\AuthenticationException $e) {
                  // Authentication with Stripe's API failed
                  // (maybe you changed API keys recently)
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                  // Network communication with Stripe failed
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                  // Display a very generic error to the user, and maybe send
                  // yourself an email
                  	return response()->json(['status' => "1",
							'message'=> $e->getError()->message ,
							'data' => null,
							"refund"=> "charge not refunded", "ErrorCode" => $e->getError()->code, "type"=> $e->getError()->type 
						]);
                } catch (Exception $e) {
                  // Something else happened, completely unrelated to Stripe
                  	return response()->json(['status' => "1",
							'message'=> $e->getMessage() ,
							'data' => null,
							"refund"=> "charge not refunded",
						]);
            	}
        	

	}




	public function getCryptoCharge($chargeid){
        $url = "https://api.commerce.coinbase.com/charges/".$chargeid;
        
        
        $headers = ['X-CC-Api-Key: '.$this->coinbase_api_key, 'X-CC-Version: 2018-03-22'];
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$url);
        // curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // $payload = json_encode($chargeData);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        // curl_setopt($ch, CURLOPT_POSTFIELDS,
                    // $payload);
              
        $response = curl_exec($ch);
        
        curl_close ($ch);
        return json_decode($response);
    }



    public function getRequiredDataFromCharge($charge){
        $data = $charge->data;
        // return $data;
        $code = $data->code;
        $payment_url = $data->hosted_url;
        $chargeid = $data->id;
        
        $price = $data->pricing;
        $local_price = $price->local;
        $amount = $local_price->amount;
        
        $payments = $data->payments;
        
        $timelines = $data->timeline;
        $timeline_status = "";
        if(count($timelines) == 0){
            $timeline_status = "NEW"; //PENDING
        }
        else{
            foreach($timelines as $time){
                
                if($timeline_status == "CANCELED" && $timeline_status == "EXPIRED"){
                    //don't assign values here
                    // $timeline_status = $time->status;
                }
                else {
                    $timeline_status = $time->status;
                }
            }
        }
        
        
        $payment_status = "";
        if(count($payments) == 0){
            $payment_status = "NEW"; //PENDING
        }
        else{
            foreach($payments as $time){
                
                if($payment_status == "CANCELED" || $payment_status == "EXPIRED"){
                    //delete that charge from db
                }
                else if($payment_status == "CONFIRMED"){
                    //payment made, update firebase node
                    
                }
                else{
                    $payment_status = $time->status;
                }
            }
        }
        return ["code" => $code, "payment_url" => $payment_url, "charge_id" => $chargeid, "payments" => $payments, "price" => "$".$amount, "payments_status" => $payment_status, "timeline" => $timelines, "timeline_status" => $timeline_status];//$charge;
    }

    public function createCryptoCharge($amount, $description, $name){
        
        $chargeData = [
                'name' => $name,
                'description' => $description,
                'local_price' => [
                    'amount' => $amount,
                    'currency' => 'USD'
                    ],
                'pricing_type' => 'fixed_price'
        ];
        
        $headers = ['X-CC-Api-Key: '. $this->coinbase_api_key, 'X-CC-Version: 2018-03-22', 'Content-Type: application/json'];
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.commerce.coinbase.com/charges/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $payload = json_encode($chargeData);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    $payload);
              
        $response = curl_exec($ch);
        
        curl_close ($ch);
        $charge = json_decode($response);
        $data = $this->getRequiredDataFromCharge($charge);
        return $data;
    }


	function sendNotToAllUsers($user, $chat){
    		$fromuser = $user;
            $fromname = $fromuser->name;
            
            $cusers = ChatUser::where('chatid', $chat->chatid)->get();
            // echo json_encode(["message" => 'Chat Push sent', 'users' => $updateunread, 'status'=>'1', "chat" => $chat], JSON_PRETTY_PRINT);
            // die();
            
            for($i = 0; $i < count($cusers); $i ++){
                // $ud = $cusers[$i];
                
                $user = $cusers[$i];
                if($user["userid"] === $user->userid){
                    
                }
                else{
                //      $token = $user["fcmtoken"];
                // $data = array();
                // $data["title"] = $fromname;
                // $data["body"] = "paid invoice";
                // $data["sound"] = "default";
                // $data["chatid"] = $chatid;
                $admin = User::where('role', 'ADMIN')->first();
				Notification::add(NotificationTypes::InvoicePaid, $fromuser->userid, $user->userid, $chat);
                // $pushsent = $this->Push_Notification($token, $data);
                }
               
            //   $push[$i] = $pushsent;
            }
	}



	//ACH Payments
	function createPaymentIntent(Request $request){

		$stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
		$userid = $request->userid;
		$user = User::where("userid", $userid)->first();
		if($user->stripecustomerid !== NULL && $user->stripecustomerid !== ""){

		}
		else{
			//create stripe customer id
			
			$customer = $stripe->customers->create(["name"=> $user->name, "email" => $user->email, 'description' => 'Braver Customer From ACH',]);
			$stripeid= $customer['id'];
            	$user->stripecustomerid = $stripeid;
            	$user->save();
		}


		$tip = 0;
			$serviceFee = 0;
			$tax = 0;
			if($request->has('tip')){
				$tip = (double)$request->tip;
			}

			if($request->has('tax')){
				$tax = (double)$request->tax;
			}
			if($request->has('service_fee')){
				$serviceFee = (double)$request->service_fee;
			}

			$amount = $request->amount + $tax + $serviceFee + $tip;
		$amount = $amount * 100; // convert to cents

		

		try{
			$intent = $stripe->paymentIntents->create([
				'amount' => $amount,
				'currency' => 'usd',
				'setup_future_usage' => 'off_session',
				'customer' => $user->stripecustomerid,
				'payment_method_types' => ['us_bank_account'],
				'payment_method_options' => [
				  'us_bank_account' => [
					'financial_connections' => ['permissions' => ['payment_method', 'balances']],
				  ],
				],
			  ]);
	  
			  $clientSecret = $intent->client_secret;
			  $id = $intent->id; // payment intent id
			  
			  $dbintent = new PaymentIntent;
			  $dbintent->payment_intent_id = $id;
			  $dbintent->userid = $userid;
			  $dbintent->invoiceid = $request->invoiceid;
			  $dbintent->chatid = $request->chatid;
			  $dbintent->reservation_id = $request->reservationid;
			  $dbintent->amount = $amount;
			  $dbintent->tax = $request->tax;
			  $dbintent->tip = $request->tip;
			  $dbintent->service_fee = $request->service_fee;
			  $paymentIntentSaved = $dbintent->save();

			  return response()->json([
				  "message" => "Payment intent created",
				  "status" => "1",
				  "data" => ["client_secret" => $clientSecret, "intent"=> $intent, "dbIntent" => $dbintent]
			  ]);
		}
		catch(\Exception $e){
			return response()->json([
				"message" => "Payment intent not created",
				"status" => "0",
				"data" => $e->getMessage()
			]);
		}
	}


	function connectFirebaseDb(Request $request){
		$database = Firebase::database();
		$reference = $database->getReference('Settings');
		$snapshot = $reference->getSnapshot();

		$value = $snapshot->getValue();
		return response()->json(["data" => "Hello there!", "settings" => $value]);
	}

}
