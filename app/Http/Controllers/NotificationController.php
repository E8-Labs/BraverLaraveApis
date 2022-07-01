<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Menu;
use App\Models\User\Notification;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Validator;

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

			$not = new Notification();
			$not->title = "Notification";
			$not->subtitle = $request->subtitle;
			$saved = $not->save();
			if($saved){
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
			// "userid" => 'required',
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

			$nots = Notification::skip($off_set)->take($limit)->orderBy('created_at', 'DESC')->get();
			return response()->json(['status' => "1",
					'message'=> 'Notifications obtained',
					'data' => $nots, 
				]);
    }
}
