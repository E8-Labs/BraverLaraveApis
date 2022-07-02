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

use Carbon\Carbon;
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
		if($req->has('ssn')){
			$user->ssn = $req->ssn;
		}
		if($req->has('last_name')){
			$user->lastname = $req->last_name;
		}
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
			$user = $profile;
			$dob = '';
                        $chekr_error = null;
                        if($user->dob){
                            // return ["date" => "". $user->dob];
                            $dob = Carbon::createFromFormat('m/d/Y', $user->dob)->format('Y-m-d');
                            
                            $data = [
			        	        "first_name" => $user->name,
			        	        "last_name" => $user->name,
			        	        "phone" => $user->phone,
			        	        "email" => $user->email,
			        	        "dob" => $dob,
			        	        "ssn" => $user->ssn,
			        	        // "zipcode"=>$login['zip'],
			        	    ];
			        	    
			        	    $json = $this->createCheckrCandidate($data);
			        	    
			        	    if(array_key_exists('id', $json)){
			        	        
			        	    	User::where('userid', $user->userid)->update(['chekrcandidateid' => $json["id"]]);
			        	    }
			        	    else{
	                            $chekr_error = $json['error'];
			        	    }
                        }
                        else{
                            
                        }
			// $data = ['profile'=> new UserProfileResource($profile), 'access_token'=> compact('token')];chekrcandidateid
// 			$data = [
// 			    "first_name" => $profile->name,
// 			    "last_name" => $profile->name,
// 			    "phone" => $profile->phone,
// 			    "email" => $profile->email,
// 			    "dob" => $profile->dob,
// 			    "ssn" => $profile->ssn,
// 			    // "zipcode"=>$login['zip'],
// 			];
// 			$id = $this->createCheckrCandidate($data);
// 			if($id){
// 				User::where('userid', $user_id)->update(['chekrcandidateid' => $id]);
// 			}
// 			else{

// 			}
			return response()->json([
					'message' => 'User registered',
					'status' => "1",
					'data' => new UserProfileFullResource($profile),
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
// 			return response()->json(['status' => "0",
// 					'message'=> 'custom error',
// 					'user' => $user, 
// 					'data' => null,
// 				]);

            try{
                    if (Hash::check($request->password, $user->password)) {
                        
                        $dob = '';
                        $chekr_error = null;
                        if($user->dob){
                            // return ["date" => "". $user->dob];
                            $dob = Carbon::createFromFormat('m/d/Y', $user->dob)->format('Y-m-d');
                            
                            $data = [
			        	        "first_name" => $user->name,
			        	        "last_name" => $user->name,
			        	        "phone" => $user->phone,
			        	        "email" => $user->email,
			        	        "dob" => $dob,
			        	        "ssn" => $user->ssn,
			        	        // "zipcode"=>$login['zip'],
			        	    ];
			        	    
			        	    $json = $this->createCheckrCandidate($data);
			        	    
			        	    if(array_key_exists('id', $json)){
			        	        
			        	    	User::where('userid', $user->userid)->update(['chekrcandidateid' => $json["id"]]);
			        	    }
			        	    else{
	                            $chekr_error = $json['error'];
			        	    }
                        }
                        else{
                            
                        }
                        
			        	
        
        
			        	return response()->json([
			        			'message' => 'User logged in',
			        			'status' => "1",
			        			'data' => new UserProfileFullResource($user),
			        			'chekr_error' => $chekr_error,
			        			'dob' => $dob,
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
            catch(\Exception $e){
                return response()->json(['status' => "0",
					'message'=> 'custom error ' . $e->getMessage() . $request->password,
					'user' => $user, 
					'data' => null,
				]);
            }
			
		}

		function updateInviteCode(Request $request){
			$validator = Validator::make($request->all(), [
			'userid' => 'required',
			"apikey" => 'required',
			'invitecode' => 'required',
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

			$invitinguser = User::where('myinvitecode', $request->invitecode)->first();
			if($invitinguser == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No such invite code',
					'data' => null, 
				]);
			}
			
			$user = User::where('userid', $request->userid)->orWhere('id', $request->userid)->first();
			$user->invitedbycode = $request->invitecode;
			$done = $user->save();
			if($done){
				return response()->json(['status' => "1",
					'message'=> 'Updated invite code',
					'data' => new UserProfileFullResource($user), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Invite code not updated',
					'data' => null, 
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

        function checkEmailExists(Request $request){
        	$validator = Validator::make($request->all(), [
			'email' => 'required',
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

			$user = User::where('email', $request->email)->first();
			if($user){
				return response()->json(['status' => "0",
					'message'=> 'Email already taken',
					'data' => null, 
				]);
			}
			else{
				return response()->json(['status' => "1",
					'message'=> 'Email available',
					'data' => null, 
				]);
			}
        }
		
}
