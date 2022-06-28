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
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class PaymentController extends Controller
{
    //
    private $coinbase_api_key = "ecbb36a1-1305-4cfb-917a-2a34561df982";

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


			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->first();
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
				DB::commit();
				$chat = ChatThread::where('chatid', $request->chatid)->first();
				$this->sendNotToAllUsers($user, $chat);
				return response()->json(['status' => "1",
					'message'=> "Payment processed & reservation made",
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

			if($charge == null){
        		$charge = $this->createCryptoCharge($request->amount, $description, $name);
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
			$reason = "";
			if($request->has('reason')){
				$reason = $request->reason;
			}

			$resid = $request->reservationid;
			$fromid = $request->fromid;



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
                     $token = $user["fcmtoken"];
                $data = array();
                $data["title"] = $fromname;
                $data["body"] = "paid invoice";
                $data["sound"] = "default";
                $data["chatid"] = $chatid;
                
                $pushsent = $this->Push_Notification($token, $data);
                }
               
            //   $push[$i] = $pushsent;
            }
	}

}