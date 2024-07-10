<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Menu;
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
use Illuminate\Support\Facades\Storage;
use App\Models\NotificationTypes;
use App\Models\User\Notification;
use Illuminate\Support\Facades\Mail;
use App\Models\Listing;



use Carbon\Carbon;

class ChatController extends Controller
{


	// function sendMessageNotification(Request $request){
	// 	$validator = Validator::make($request->all(), [
	// 		"apikey" => 'required',
	// 		"userid" => 'required',
	// 		"chatid" => 'required',
	// 		'message' >
	// 			]);

	// 		if($validator->fails()){
	// 			return response()->json(['status' => "0",
	// 				'message'=> 'validation error',
	// 				'data' => null, 
	// 				'validation_errors'=> $validator->errors()]);
	// 		}

	// 		$key = $request->apikey;
	// 		if($key != $this->APIKEY){ // get value from constants
	// 			return response()->json(['status' => "0",
	// 				'message'=> 'invalid api key',u
	// 				'data' => null, 
	// 			]);
	// 		}


	// 	$not = new Notification;
	// 		$not->title = "Notification";
	// 		$not->from_user = $request->userid;
	// 		$not->message = $request->subtitle;
	// 		$not->notification_type = NotificationTypes::AdminBroadcast;
	// 		// $not->notifiable_type = $not;
	// 		$saved = $not->save();
	// 		if($saved){
	// 			return response()->json(['status' => "1",
	// 				'message'=> 'Notification Saved',
	// 				'data' => $not, 
	// 			]);
	// 		}
	// 		else{
	// 			return response()->json(['status' => "0",
	// 				'message'=> 'Error Saving Notification',
	// 				'data' => null, 
	// 			]);
	// 		}
	// }
    function createChat(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"fromuser" => 'required',
			// "reservationdate" => 'required',
			// "reservationtime" => 'required',
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

			$yachtid = $request->productid;
			if($yachtid == null){
				$yachtid = '';
			}

			DB::beginTransaction();

			$user = User::where('userid', $request->fromuser)->first();

			
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
				if($request->has('customaddress')){
					$res->customaddress = $request->customaddress;
				}
				else{
					$res->customaddress = '';
				}
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
				$res->yachtid = $yachtid;

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
					$res->reservationdescription = $request->reservationdescription;
				}
				else{
					$res->reservationdescription = '';
				}
				

				if($request->has('guests')){
					$res->guests = $request->guests;
				}
				else{
					$res->guests = NULL;
				}

				if($request->has('days')){
					$res->days = $request->days;
				}
				else{
					$res->days = NULL;
				}

				if($request->has('rooms')){
					$res->rooms = $request->rooms;
				}
				else{
					$res->rooms = NULL;
				}
				
				if($request->has('budget')){
					$res->budget = $request->budget;
				}
				else{
					$res->budget = NULL;
				}


				if($res->save()){
					DB::commit();
					$admin = User::where('role', 'ADMIN')->first();
					$from = User::where('userid', $request->fromuser)->first();
					$token = $admin->fcmtoken;
                	$data = array();
                	$data["title"] = $from->name;
                	$data["body"] = "requested to reserve " . $request->chatforproduct;
                	$data["sound"] = "default";
                	$data["chatid"] = $chat->chatid;
                	// $this->Push_Notification($token, $data);
					Notification::add(NotificationTypes::TypeReservation, $request->fromuser, $admin->userid, $res);

					$yacht = Listing::where('yachtid', $yachtid)->first();
					$yachtname = "";
					if($yacht){
						$yachtname = $yacht->yachtname;
					}
					else{
						$yachtname = $request->chatforproduct;
					}
					$this->sendReservationEmail($from, $yachtname);

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

	function sendReservationEmail(User $user = null, $yacht_name){
		
				// $profile = Profiles::where('user_id', $user->id)->first();
				$data = array('user_name'=> $user->name, "user_email" => "jonathan@braverhospitality.com", "user_message" => "", "yacht_name" => $yacht_name);
        	// $data = array('user_name'=> "Hammad", "user_email" => "admin@braverhospitality.com", "user_message" => "");
				Mail::send('Mail/ReservationRequestMail', $data, function ($message) use ($data, $user) {
					//send to $user->email
                        $message->to("info@braverhospitality.com",'Reservation')->subject('New Reservation Request');
                        // $message->from("info@braverhospitality.com");
                    });

				return true;
	}

	public function getChatById(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
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

			$chat = ChatThread::where('chatid', $request->chatid)->first();
			if($chat){
				return response()->json(['status' => "1",
					'message'=> 'Chat obtained',
					'data' => new ChatResource($chat), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Chat does not exist',
					'data' => null, 
				]);
			}

	}

	function getUserChat(Request $request){
		$ListSize = 20;
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
			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}

			$off_set = $page * $ListSize - $ListSize;

			$chatids = ChatUser::where('userid', $request->userid)->pluck('chatid')->toArray();
			$chats = ChatThread::whereIn("chatid", $chatids)->skip($off_set)->take($ListSize)->orderBy('updatedat', 'DESC')->get();
			if($chats){
				return response()->json(['status' => "1",
					'message'=> 'Chats obtained',
					'data' => ChatResource::collection($chats), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Chat does not exist',
					'data' => null, 
				]);
			}
	}


	public function getTeamChat(Request $request){

		$ListSize = 20;
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

			$off_set = $page * $ListSize - $ListSize;
            $chatforproduct = $request->chatforproduct;
            $chattype = $request->chattype;
            $userid = $request->userid;
            $status = $request->status;
            // return $chatforproduct;
            $chatids = ChatUser::where('userid', $userid)->pluck('chatid')->toArray();
            // return $chatids;
            if(count($chatids) === 0){
                return response()->json(['status' => "0",
					'message'=> 'No chats',
					'data' => null, 
				]);
            }
			$chats = ChatThread::whereIn('chatid', $chatids)
			->when($request->has('chatforproduct'), function($query) use ($chatforproduct){
			 //   return $chatforproduct;
				return $query->where('chatforproduct', $chatforproduct);
			})
			->when($request->has('chattype'), function($query) use ($chattype){
				
				if($chattype == "Proper"){
				    $query->where('chattype', $chattype)->orWhere(function($query) use($chattype){
				        $query->where('chattype', 'ReservationRequest')->where('chatforproduct', 'Custom');
				    });
				}
				else{
				    $query->where('chattype', $chattype);
				}
			})
			->when($request->has('status'), function($query) use ($status){
				// $status = $request->status;
				if($status == "Paid"){
					//chatid in (Select chatid from yachtreservations where reservationstatus = 'Reserved' OR reservationstatus = 'Cancelled')
					$chatids = Reservation::where('reservationstatus', ReservationStatus::StatusReserved)
					->orWhere('reservationstatus', ReservationStatus::StatusCancelled)->pluck('chatid')->toArray();
					$query->whereIn('chatid', $chatids);
				}
				else{
					$chatids = Reservation::where('reservationstatus', ReservationStatus::StatusPendingPayment)->pluck('chatid')->toArray();
					$query->whereIn('chatid', $chatids);
				}
				
			})
			->orderBy('updatedat', 'DESC')
			->skip($off_set)->take($ListSize)
            ->get()
			;
			if($chats){
				return response()->json(['status' => "1",
					'message'=> 'Chats obtained',
					'data' => ChatResource::collection($chats), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Chat does not exist',
					'data' => null, 
				]);
			}

	}

	function uploadChatPdf(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
// 			"userid" => 'required',
			"pdf" => 'required',

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


			if($request->hasFile("pdf")){
				$data=$request->file('pdf')->store('ChatFiles');
				return response()->json(['status' => "1",
					'message'=> 'File uploaded',
					'data' => "http://braverhospitalityapp.com/braver/storage/app/" . $data, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No file provided',
					'data' => null, 
				]);
			}
	}

	function uploadChatImage(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
// 			"userid" => 'required',
			"image" => 'required',

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

			if($request->has('image')){
				$ima = $request->image;
				$fileName =  rand(). date("h:i:s").'image.png';
	
    			$ima = trim($ima);
    			$ima = str_replace('data:image/png;base64,', '', $ima);
    			$ima = str_replace('data:image/jpg;base64,', '', $ima);
    			$ima = str_replace('data:image/jpeg;base64,', '', $ima);
    			$ima = str_replace('data:image/gif;base64,', '', $ima);
    			$ima = str_replace(' ', '+', $ima);
			
    			$imageData = base64_decode($ima);
    			//Set image whole path here 
    			$subpath = "/braver/storage/app/Images/Chat/";
    			$filePath = $_SERVER['DOCUMENT_ROOT'].$subpath. $fileName;
	
	
            	if(!Storage::exists($_SERVER['DOCUMENT_ROOT'].$subpath)){
            	    Storage::makeDirectory($_SERVER['DOCUMENT_ROOT'].$subpath);
            	}
   				file_put_contents($filePath, $imageData);
   				$url = $subpath. $fileName;
   				return response()->json(['status' => "1",
					'message'=> 'Image uploaded',
					'data' => ["url" => $url], 
					"url" => $url,
				]);

			}
			else{

			}
	}


	function updateChat(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			// "userid" => 'required',
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

			try{
			    $updateother = "0";
			if($request->has('updateother')){
				$updateother = $request->updateother;
			}
			$fromid = '';
			if($request->has('userid')){
				$fromid = $request->userid;
			}
			else if($request->has('fromid')){
				$fromid = $request->fromid;
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'userid or fromid parameter required',
					'data' => null, 
				]);
			}

			if($fromid == ''){
				return response()->json(['status' => "0",
					'message'=> 'userid or fromid can not be empty',
					'data' => null, 
				]);
			}
			
			$chatid = $request->chatid;
			$chat = ChatThread::where('chatid', $chatid)->first();

			if($request->has('productid')){
				$chat->productid = $request->productid;
			}
			$lastmessage = "";
			if($request->has('lastmessage')){
				$lastmessage = $request->lastmessage;
				$chat->lastmessage = $request->lastmessage;
				$chat->updatedat = Carbon::now()->toDateTimeString();
				// $not->title = "Notification";
				// $not = new Notification;
				// $not->from_user = $fromid;
				// $not->message = $lastmessage;
				// $not->notification_type = NotificationTypes::TypeMessage;
				// $not->notifiable_type = $chat;

				// $saved = $not->save();
			}

			

			$saved = $chat->save();
			if(!$saved){
				return response()->json(['status' => "0",
					'message'=> 'Error saving chat',
					'data' => null, 
				]);
			}

			$chatUsers = $chat->getChatUsers();

			$fromuser = User::where('userid', $fromid)->first();
            $fromname = $fromuser->name;
			foreach($chatUsers as $cu){

				if($cu->userid === $fromid){
                    
                }
                else{
                 //    $token = $cu->fcmtoken;
                	// $data = array();
                	// $data["title"] = $fromname;
                	// $data["body"] = $lastmessage;
                	// $data["sound"] = "default";
                	// $data["chatid"] = $chatid;
                	// $this->Push_Notification($token, $data);
                	$lastmessage = $request->lastmessage;
                	if($lastmessage === NULL || $lastmessage === ''){
                	    //don't sent not
                	}
                	else{
                	    Notification::add(NotificationTypes::TypeMessage, $fromid, $cu->userid, $chat, $request->lastmessage);
                	}
                	

                }
				
			}

			if($request->has('deleted')){
				$deleted = $request->deleted;
				foreach($deleted as $del){
					ChatUser::where('chatid', $chatid)->where('userid', $del)->delete();
				}
			}


                
			if($request->has('users')){
				$users = $request->users;
				
				try{
					if(count($users) > 0){
					// ChatUser::where('chatid', $chatid)->where('role', 'Team')->delete();
					    foreach($users as $u){
					        
					    	$cu = ChatUser::where('chatid', $chatid)->where('userid', $u["userid"])->first();
					    	if($cu == NULL){
					    		$cu = new ChatUser();
					    	}
					    	$cu->chatid = $chatid;
					    	$cu->userid = $u["userid"];
					    	$cu->role = 'Team';
					    	$saved = $cu->save();
					   // 	return response()->json(['status' => "0",
					   //         'message'=> 'Foreach loop users ',
					   //         'user' => $u, 
					   //         'saved' => $saved,
				    //         ]);
					    	$team = User::where('userid', $u["userid"])->first();
					    	$not = Notification::add(NotificationTypes::TeamMemberReservationInvite, $fromid, $u["userid"], $chat, '');
					    	$this->sendChatInviteEmail($team);
					    	$chat = ChatThread::where('chatid', $chatid)->first();
					    	return response()->json(['status' => "1",
					            'message'=> 'Users added ',
					            'not' => $not, 
					            'from' => $fromid,
					            "uid" => $u["userid"],
					            "data" => new ChatResource($chat),
				            ]);
    
					    }
				    }
				}
				catch(\Exception $e){
					return response()->json(['status' => "0",
						'message'=> $e->getMessage(),
						'data' => null, 
						'exception' => $e,
					]);
				}
				
				
				
			}
			else{
				// update unread count
				// return response()->json(['status' => "0",
				// 	'message'=> 'No chat users',
				// 	'request' => $request->all(), 
				// ]);
				if($updateother == "0"){

					ChatUser::where('chatid', $chatid)->where('userid', $fromid)->update(['unreadcount' => 0]);

				}
				else{
					ChatUser::where('chatid', $chatid)->where('userid', $fromid)->update(['unreadcount' => 0]);
					ChatUser::where('chatid', $chatid)->where('userid', '!=', $fromid)->increment('unreadcount');
				}
			}


			$chat = ChatThread::where('chatid', $chatid)->first();
			return response()->json(['status' => "1",
					'message'=> 'Chat updated',
					'data' => new ChatResource($chat), 
				]);
			}
			catch(\Exception $e){
			    \Log::info('--------------Chat Update Exception------------------');
			    
			    \Log::info($e);
			    \Log::info('--------------------------------');
			    return response()->json(['status' => "0",
					'message'=> $e->getMessage(),
					'data' => new ChatResource($chat), 
				]);
			}

	}


	function sendChatInviteEmail(User $user = null){
		
				// $profile = Profiles::where('user_id', $user->id)->first();
				$data = array('user_name'=> $user->name, "user_email" => "info@braverhospitality.com", "user_message" => "");
        	// $data = array('user_name'=> "Hammad", "user_email" => "admin@braverhospitality.com", "user_message" => "");
				Mail::send('Mail/TeamMemberToChatMail', $data, function ($message) use ($data, $user) {
					//send to $user->email
                        $message->to($user->email,'Welcome')->subject('Manage new reservation');
                        // $message->from("info@braverhospitality.com");
                    });

				return true;
	}

	function sendInvite(Request $request){
		// $user = Auth::user();
		$user = User::where('userid', $request->userid)->first();
		$data = array('user_name'=> $user->name, "user_email" => "info@braverhospitality.com", "user_message" => "");
        	// $data = array('user_name'=> "Hammad", "user_email" => "admin@braverhospitality.com", "user_message" => "");
				Mail::send('Mail/TeamMemberToChatMail', $data, function ($message) use ($data, $user) {
					//send to $user->email
                        $message->to($user->email,'Welcome')->subject('Manage new reservation');
                        // $message->from("info@braverhospitality.com");
                    });
	}

	function getUnreadNotifications(Request $request){
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

			$menuarray = Menu::get();
			$data = array();
			for($i = 0; $i < count($menuarray); $i++){
           		 $men = $menuarray[$i];
            	$title = $men->menutitle;
            	$id = Menu::getIdForMenu($title);
            	
            	
            	$chatidsPaidProduct = ChatThread::where('chatforproduct', $id)
            	->where('chattype', 'ReservationRequest')->pluck('chatid')->toArray();
            	$chatidsPaidReservedOnly = Reservation::where('reservationstatus', ReservationStatus::StatusReserved)->pluck('chatid')->toArray();
            	$chatidsPaidNotReservedOnly = Reservation::where('reservationstatus', '!=', ReservationStatus::StatusReserved)->pluck('chatid')->toArray();

            	$unreadPaid = ChatUser::whereIn('chatid', $chatidsPaidProduct)
            	->whereIn('chatid', $chatidsPaidReservedOnly)
            	->where('userid', $request->userid)->sum('unreadcount');
            	$unreadPending = ChatUser::whereIn('chatid', $chatidsPaidProduct)
            	->whereIn('chatid', $chatidsPaidNotReservedOnly)
            	->where('userid', $request->userid)->sum('unreadcount');
            	
            	$data[$id] = ["Pending" => $unreadPending, "Paid" => $unreadPaid];
        	}

        	$properChats = ChatThread::where('chattype', 'Proper')->pluck('chatid')->toArray();
        	$unreadPending = ChatUser::whereIn('chatid', $properChats)
            	->where('userid', $request->userid)->sum('unreadcount');
            	$data["Proper"] = ["Pending" => $unreadPending];
            	return response()->json(['status' => "1",
					'message'=> 'Unread count',
					'data' => $data, 
				]);
	}

	function deleteChat(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
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

			$deleted = ChatThread::where('chatid', $request->chatid)->delete();
			if($deleted){
				return response()->json(['status' => "1",
					'message'=> 'Chat deleted',
					'data' => null, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Could not delete chat',
					'data' => null, 
				]);
			}

	}

	function getUserRequests(Request $request){
		$Size = 20;
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
			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * $Size - $Size;

			$user = User::where('userid', $request->userid)->first();
// 			return $user;

// $sql = "Select * from chat c where c.fromuserid = '$userid' AND c.chatid in (Select r.chatid from  yachtreservations r where r.reservationstatus = 'Reserved');";//where r.reservationstatus = 'Reserved'
//         $user = loadUser($conn, $userid);
//         $role = $user["role"];
//         if($role === "ADMIN"){
//             $sql = "Select * from chat c where c.chatid in (Select r.chatid from  yachtreservations r);";
//         }
//         else if ($role === "TEAM"){
//             $sql = "Select * from chat c where c.chatid in ( Select cu.chatid from chatusers cu where cu.userid = '$userid')";
//         }
			$reserved = Reservation::where('reservationstatus', ReservationStatus::StatusReserved)->pluck('chatid')->toArray();
			$requests = ChatThread::where('fromuserid', $request->userid)->whereIn('chatid', $reserved)->skip($off_set)->take($Size)->get();
// 			return $requests;
			if($user->role == "ADMIN"){
				$reserved = Reservation::pluck('chatid')->toArray();
				// return $reserved;
				$requests = ChatThread::whereIn('chatid', $reserved)->skip($off_set)->take($Size)->get();
			}
			else if ($user->role == "TEAM"){
				$reserved = ChatUser::where('userid', $request->userid)->pluck('chatid')->toArray();
				$requests = ChatThread::whereIn('chatid', $reserved)->skip($off_set)->take($Size)->get();
			}

			return response()->json(['status' => "1",
					'message'=> 'Requests obtained',
					'data' => ChatResource::collection($requests), 
				]);
			
	}

	private function saveChatUsers($users, $chat, $fromUser, $chatforproduct, $lastmessage){
		foreach($users as $user){
			$cu = new ChatUser();
			\Log::info('-------------------Saving chat users---------------');
			\Log::info($chat);
			\Log::info('-------------------Saving chat users---------------');

			$userid = $user["userid"];
			$cu->userid = $userid;
                $role = $user["role"];
                $cu->role = $role;
                $cu->chatid = $chat->chatid;

                $unread = 0;
                if ($role === 'Admin'){
                    $unread = 1;
                    $u = User::where('userid', $userid)->first();
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