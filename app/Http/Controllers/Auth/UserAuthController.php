<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Auth\User;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\User\UserProfileFullResource;
use App\Http\Resources\User\UserProfileLiteResource;

// use Illuminate\Support\Facades\Files;
// use JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use App\Http\Resources\UserProfileResource;

class UserAuthController extends Controller
{
    //

    function Register(Request $req)
	{
			
			$validator = Validator::make($req->all(), [
			'email' => 'required|string|email|max:255|unique:user',
			'phone' => 'required|unique:user',
			'password' => 'required|string|max:40',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}

			$key = $req->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}
			
			
				
		DB::beginTransaction();
		$code = rand ( 10000 , 99999 );//Str::random(5);
		$user_id = uniqid();
		$user=new User();
		$user->baseUrlType = "New";
		$user->userid = $user_id;
		$user->phone=$req->phone;
		$user->email=$req->email;
		$role = UserType::TypeUser;
		if($req->has('role')){
			$role = $req->role;
		}
		$user->role=$role;
		$user->fcmtoken = "";
		$user->name = $req->name;
		$user->dob = $req->dob;
		$user->myinvitecode = $code;
		// $user->stripecustomerid = "";
		$user->invitedbycode = $req->invitecode;
		$user->gender = $req->gender;
		if($req->has('image')){
			$ima = $req->image;
			$fileName =  rand(). date("h:i:s").'image.png';

    		$ima = trim($ima);
    		$ima = str_replace('data:image/png;base64,', '', $ima);
    		$ima = str_replace('data:image/jpg;base64,', '', $ima);
    		$ima = str_replace('data:image/jpeg;base64,', '', $ima);
    		$ima = str_replace('data:image/gif;base64,', '', $ima);
    		$ima = str_replace(' ', '+', $ima);
		
    		$imageData = base64_decode($ima);
    		//Set image whole path here 
    		$filePath = $_SERVER['DOCUMENT_ROOT']."/braver/storage/app/Images/". $fileName;

// return $filePath;
            if(!Storage::exists($_SERVER['DOCUMENT_ROOT']."/braver/storage/app/Images/")){
                Storage::makeDirectory($_SERVER['DOCUMENT_ROOT']."/braver/storage/app/Images/");
            }
   			file_put_contents($filePath, $imageData);
   			$user->url = "/braver/storage/app/Images/". $fileName;

		}
		$user->password=Hash::make($req->password);
		$result=$user->save();
		// $token = JWTAuth::fromUser($user);
		
		// $user_id = $user->id;
		if($result)
		    {
				
			DB::commit();
			$profile = User::where('userid', $user_id)->first();
			// $data = ['profile'=> new UserProfileResource($profile), 'access_token'=> compact('token')];
			return response()->json([
					'message' => 'User registered',
					'status' => "1",
					'data' =>$profile,
			]);
		    }
		else
			{
				return response()->json([
					'message' => 'User not registered',
					'status' => "0",
					'data' => null,

					
				]);
			}
		 
		}


		function login(Request $request){
			$validator = Validator::make($request->all(), [
			'email' => 'required|string|email',
			'password' => 'required|string|max:40',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}
// return "Login";
			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}
    

			$user = User::where('email', $request->email)->first();
			if (Hash::check($request->password, $user->password)) {
				return response()->json([
						'message' => 'User logged in',
						'status' => "1",
						'data' =>$user,
				]);
			}
			else{
				return response()->json([
						'message' => 'Invalid credentials',
						'status' => "0",
						'data' =>null,
				]);
			}
		}


		function updateUser(Request $request){

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


			$params = array();
       
       $user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->first();
            


        	if($request->hasFile('image'))
        	{
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
    			$filePath = $_SERVER['DOCUMENT_ROOT']."/". $fileName;
	
   				file_put_contents($filePath, $imageData);
   				$user->url = $filePath;
   				$user->save();
        	    
        	}
	
        	if($request->has('fcm_token')){
        	    $fcm_token = $request->fcm_token;
        	    $user->fcmtoken = $fcm_token;
        	    // User::where('id', $user->id)->update(['fcm_key' => $fcm_key]);
        	    // $params["fcm_key"] = $fcm_key;
        	}
        	if($request->has('email')){
        	    $email = $request->email;

        	    $matchingUser = User::where('email', $email)->first();
        	    if($matchingUser){
        	        return response()->json(['status' => false,
        	            'message'=> 'Email is taken',
        	            'data' => null,
        	        ]);
        	    }
        	    $user->email = $email;
        	    // User::where('id', $user->id)->update(['email' => $email]);
        	}
        	if($request->has('name')){
        	    $name = $request->name;
        	    $user->name = $name;
        	    $params["name"] = $name;
        	}
	
        	if($request->has('phone')){
        	    $phone = $request->phone;
        	    $user->phone = $phone;
        	    $params["phone"] = $phone;
        	}
        	if($request->has('gender')){
        	    $phone = $request->gender;
        	    $user->gender = $phone;
        	    $params["gender"] = $phone;
        	}
        	if($request->has('dob')){
        	    $phone = $request->dob;
        	    $user->dob = $phone;
        	    $params["dob"] = $phone;
        	}



        	if($request->has('welcomeflag')){
        	    
        	    $user->accountstatus = AccountStatus::Approved;
        	}

        	$saved = $user->save();
        	if($saved){
        		return response()->json(['status' => "1",
					'message'=> 'User Updated',
					'data' => $user, 
				]);
        	}
        	else{
        		return response()->json(['status' => "0",
					'message'=> 'Some error occurred',
					'data' => null, 
				]);
        	}
        }
		
}
