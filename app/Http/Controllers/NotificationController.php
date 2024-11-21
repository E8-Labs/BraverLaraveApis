<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Menu;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Validator;
use App\Models\NotificationTypes;
use App\Http\Resources\NotificationResource;
use App\Models\User\Notification;

class NotificationController extends Controller
{
    //

    function addNotification(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
			"subtitle" => 'required',
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

			// $not =Notification::add(NotificationTypes::NewUser, $user_id, $admin->userid, $user);
			$not = new Notification;
			$not->title = "Notification";
			$not->from_user = $request->userid;
			$not->message = $request->subtitle;
			$not->notification_type = NotificationTypes::AdminBroadcast;
			// $not->notifiable_type = $not;
			$saved = $not->save();
			if($saved){
				$topic = "Braver_Mass_Notification";
				$result = self::sendFirebaseTopicNotification($topic, "Notificaiton", $request->subtitle);
				return response()->json(['status' => "1",
					'message'=> 'Notification Saved',
					'data' => $not, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Error Saving Notification',
					'data' => null, 
				]);
			}
    }


    function notificationsList(Request $request){
    	$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"userid" => 'required',
			// "subtitle" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			// return $key;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$page = 1;
			$limit = 20;
			if($request->has('page')){
				$page = $request->page;
			}
			
			$off_set = $page * $limit - $limit;
// return "Page is " . $off_set;
			$nots = Notification::where('to_user', $request->userid)->orWhereNull('to_user')->skip($off_set)->take($limit)->orderBy('created_at', 'DESC')->get();
			try{
			    return response()->json(['status' => "1",
					'message'=> 'Notifications obtained',
					'data' => NotificationResource::collection($nots), 
				]);
			}
			catch(\Exception $e){
			    return response()->json(['status' => "0",
					'message'=> 'Notifications not obtained ' . $e,
					'data' => null, 
				]);
			}
    }
}
