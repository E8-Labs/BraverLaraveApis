<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Chat\ChatUser;
use App\Models\Chat\ChatThread;
use App\Models\Listing\Reservation;
use App\Models\Listing\ReservationStatus;
use App\Models\Card;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\Chat\ChatResource;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class ChatController extends Controller
{
    function createChat(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"fromuser" => 'required',
			"reservationdate" => 'required',
			"reservationtime" => 'required',
			// "productid" => 'required',
			"chattype" => 'required',
			"chatforproduct" => 'required',
			'users' => 'required',

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

			$chat = ChatThread::where('chatforproduct', $request->chatforproduct)
					->where('fromuserid', $request->fromuser)
					->where('chattype', $request->chattype)->first();
					if($chat){
						return response()->json(['status' => "1",
							'message'=> 'Chat already exists',
							'data' => new ChatResource($chat), 
						]);
					}


			DB::beginTransaction();




			$user = User::where('userid', $request->fromuser)->orWhere('id', $request->fromuser)->first();

			$yachtid = $request->productid;

			$chat = new ChatThread();
			$chat->chatid = uniqid();
			// $chat->reservationid = uniqid();
			$chat->fromuserid = $request->fromuser;
			
			if($request->has('lastmessage')){
				$chat->lastmessage = $request->lastmessage;
			}
			else{
				$chat->lastmessage = "";
			}
			$chat->chattype = $request->chattype;
			$chat->chatforproduct = $request->chatforproduct;
			if($request->has('customaddress')){
				$chat->customaddress = $request->customaddress;
			}
			else{
				$chat->customaddress = '';
			}
			$chat->dateadded = Carbon::now()->toDateTimeString();
			$chat->updatedat = Carbon::now()->toDateTimeString();
			$chat->productid = $yachtid;

			$chatSaved = $chat->save();
			if($chatSaved){

				$saved = $this->saveChatUsers($request->users, $chat, $user, $request->chatforproduct, $chat->lastmessage);
				if($saved){

				}
				else{
					DB::rollBack();
					return response()->json(['status' => "0",
						'message'=> 'Error creating chat',
						'data' => null, 
					]);
				}

				$res = new Reservation();
				$res->agenthandler = '';
				$res->transactionid = '';
				$res->amountpaid = '';
				$res->paymentmethod = '';
				$res->refundid = '';
				$res->cancelledby = '';
				$res->invoiceid = '';
				$res->invoicedescription = '';
				$res->reservationid = uniqid();
				$res->chatid = $chat->chatid;
				$res->reservationstatus = ReservationStatus::StatusPendingPayment;
				$res->reservedfor = $request->fromuser;
				$res->dateadded = Carbon::now()->toDateTimeString();
				$res->yachtid = $request->productid;

				if($request->has('reservationdate')){
					$res->reservationdate = $request->reservationdate;
				}
				else{
					$res->reservationdate = '';
				}

				if($request->has('reservationtime')){
					$res->reservationtime = $request->reservationtime;
				}
				else{
					$res->reservationtime = '';
				}

				if($request->has('reservationenddate')){
					$res->reservationenddate = $request->reservationenddate;
				}
				else{
					$res->reservationenddate = '';
				}

				if($request->has('reservationendtime')){
					$res->reservationendtime = $request->reservationendtime;
				}
				else{
					$res->reservationendtime = '';
				}

				if($request->has('reservationdescription')){
					$res->reservationendtime = $request->reservationdescription;
				}
				else{
					$res->reservationdescription = '';
				}
				

				if($request->has('guests')){
					$res->guests = $request->guests;
				}
				else{
					$res->guests = '';
				}

				if($request->has('days')){
					$res->days = $request->days;
				}
				else{
					$res->days = '';
				}

				


				if($res->save()){
					DB::commit();
					return response()->json(['status' => "1",
						'message'=> 'Chat created',
						'data' => new ChatResource($chat), 
					]);
				}
				else{
					DB::rollBack();
					return response()->json(['status' => "0",
						'message'=> 'Error creating chat',
						'data' => null, 
					]);
				}

			}
			else{
				DB::rollBack();
				return response()->json(['status' => "0",
					'message'=> 'Error creating chat',
					'data' => null, 
				]);
			}
	}

	private function saveChatUsers($users, $chat, $fromUser, $chatforproduct, $lastmessage){
		foreach($users as $user){
			$cu = new ChatUser();
			

			$userid = $user["userid"];
			$cu->userid = $userid;
                $role = $user["role"];
                $cu->role = $role;
                $cu->chatid = $chat->chatid;

                $unread = 0;
                if ($role === 'Admin'){
                    $unread = 1;
                    $u = User::where('userid', $userid)->orWhere('id', $userid)->first();
                    $fcm = $u->fcmtoken;
                    
                    $data = array();
                    $data["title"]=$fromUser->name;
                    if ($lastmessage === ''){
                        $lastmessage = "requested to reserve $chatforproduct";
                    }
                        $data["body"]=$lastmessage;
                        $data["sound"]="default";
                        $data["chatid"]=$chat->chatid;
                    //logic to send fcm
                    // NotificationManager::sendFCM($fcm,$data);
                }

                $cu->unreadcount = $unread;
                if($cu->save()){

                }
                else{
                	return false;
                }
		}
		return true;
	}
}
