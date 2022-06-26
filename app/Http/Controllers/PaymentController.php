<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Listing\Reservation;
use App\Models\Card;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

class PaymentController extends Controller
{
    //

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

			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->first();
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
			$user = User::where('userid', $userid)->orWhere('id', $userid)->first();
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


	function makeReservation(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
			"reservationdate" => 'required',
			"reservationtime" => 'required',
			"yachtid" => 'required',
			"amount" => 'required',
			"chatid" => 'required',

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

			$res = new Reservation();
			$res->reservationid = uniqid();
			$res->reservedfor = $request->userid;
			$res->dateadded = Carbon::now()->toDateTimeString();
			$yachtid = $request->yachtid;
			$date = $request->reservationdate;
			$time = $request->reservationtime;

	}

}
