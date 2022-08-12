<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Auth\User;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use App\Models\NotificationTypes;
use App\Models\User\Notification;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\User\UserProfileFullResource;
use App\Http\Resources\User\UserProfileLiteResource;

use App\Http\Controllers\ReportController;

use Carbon\Carbon;
// use Illuminate\Support\Facades\Files;
// use JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use App\Http\Resources\UserProfileResource;

class UserAuthController extends Controller
{
    //

    function getChekrReport(Request $request){
    	$userid = $request->userid;
    	$user = User::where('userid', $userid)->first();
    	$rep = new ReportController();
    	// return $user->chekrcandidateid;
    	$report = $this->getchekrreportFromServer($user);//$rep->getCheckrReport($user->chekrcandidateid);//

    	return response()->json(['status' => "1",
			'message'=> 'Report',
			'data' => $report, 
		]);

    }


    function createChekrReport(Request $request){
    	$userid = $request->userid;
    	$user = User::where('userid', $userid)->first();

    	
    	try{
    	    $rep = new ReportController();
    	    if($user->chekrreportid != NULL){
    // 		return "report id not null";
    		if($user->ssn_trace == 'complete' && $user->national_status == 'complete' && $user->sex_offender_status == 'clear' && $user->chekrstatus == 'clear'){
    			// get no need to get report
    			// User::where('userid', $userid)->update(['accountstatus'=> 'Approved']);

    			$user->accountstatus = AccountStatus::Approved;
    			$user->save();
    			return $user;
    		}
    		// return "getting report details";
    		$report = $this->getchekrreportFromServer($user);
    		return response()->json(['status' => "1",
					'message'=> 'Report created',
					'data' => $report, 
			]);
    	}
    	else{
    	   // return "NULL";
    		$id = $this->createCandidate($user);
    // 		return $id;
    		$report = $rep->getCheckrReport($id);
    		$report_error = null;
    		// return $report;
    		if(!isset($report->error)){
    			$id = $report->id;
    			User::where('userid', $user->userid)->update(['chekrreportid' => $id]);
    			$user = User::where('userid', $user->userid)->first();
    			return response()->json(['status' => "1",
					'message'=> 'Report created',
					'data' => new UserProfileFullResource($user), 
				]);
    		}
    		else{
    			$report_error = $report->error;
    			return response()->json(['status' => "0",
					'message'=> $report_error,
					'data' => null, 
				]);
    		}
    	}
    	}
    	catch(\Exception $e){
    	    \Log::info($e);
    	    return response()->json(['status' => "0",
					'message'=> $e->getMessage(),
					'data' => null, 
			]);
    	}
    	
    }

    function Register(Request $req)
	{
			
			$validator = Validator::make($req->all(), [
			'email' => 'required|string|email|max:255|unique:user',
			// 'phone' => 'required|unique:user',
			'password' => 'required|string|max:40',
			"apikey" => 'required',
			'ssn' => 'required',
			'last_name' => 'required',

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
			
			$user = User::where('phone', $req->phone)->first();
			if($user){
				return response()->json(['status' => "0",
					'message'=> 'Phone already taken',
					'data' => null, 
				]);
			}
			
				
		try{
		    DB::beginTransaction();
		$code = rand ( 10000 , 99999 );//Str::random(5);
		$user_id = uniqid();

		$user=new User();
		if($req->has('zipcode')){
			$user->zip = $req->zipcode;
		}
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
			$env = env('APP_DEBUG');
			$folder = 'braver';
			if($env == true){
				$folder = 'braver_testing';
			}
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
    		$filePath = $_SERVER['DOCUMENT_ROOT']."/". $folder ."/storage/app/Images/". $fileName;

// return $filePath;
            if(!Storage::exists($_SERVER['DOCUMENT_ROOT']."/" . $folder ."/storage/app/Images/")){
                Storage::makeDirectory($_SERVER['DOCUMENT_ROOT']."/". $folder ."/storage/app/Images/");
            }
   			file_put_contents($filePath, $imageData);
   			$user->url = "/". $folder. "/storage/app/Images/". $fileName;

		}
		
		$user->password=Hash::make($req->password);
		
		try{
		    $result=$user->save();
		}
		catch(\Exception $ex){
		    \Log::info($ex);
		    return response()->json(['status' => "0",
					'message'=> 'user not saved exception',
					'data' => null, 
					'error' => $ex,
				]);
		}
		
		// $token = JWTAuth::fromUser($user);
		
		// $user_id = $user->id;
		if($result)
		    {
				
			DB::commit();
			$admin = User::where('role', 'ADMIN')->first();
			Notification::add(NotificationTypes::NewUser, $user_id, $admin->userid, $user);
			//send push
			$profile = User::where('userid', $user_id)->first();
			$user = $profile;

			
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
		catch(\Exception $e){
		    DB::rollBack();
		    \Log::info('----------------------------------------');
			\Log::info($e);
			\Log::info('----------------------------------------');
			\Log::info('----------------------------------------');
			\Log::info('----------------------------------------');
			return response()->json([
					'message' => 'User not registered: Exception',
					'status' => false,
					'data' => null,
					'exception' => $e,
					'errorString' => $e->getMessage(),
				]);
		}
		 
		}


		function checkCode(Request $request){
			$validator = Validator::make($request->all(), [
				'code' => 'required',
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
			
			$user = User::where('myinvitecode', $request->code)->first();
			if($user){
				return response()->json(['status' => "1",
					'message'=> 'Invite code is valid',
					'data' => $user, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Code is invalid',
					'data' => null, 
				]);
			}
		}

		private function createCandidate($user){
			if($user->chekrcandidateid == NULL){
                        	$dob = '';
                        	
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
			        		        "zipcode"=>$user->zip,
			        		    ];
			        		    
			        		    $json = $this->createCheckrCandidate($data);
			        		   // echo json_encode($json);
			        		    if(array_key_exists('id', $json)){
			        		    	$user->chekrcandidateid = $json['id'];
			        		        
			        		    	User::where('userid', $user->userid)->update(['chekrcandidateid' => $json["id"]]);
									return $json['id'];
	
			        		    }
			        		    else{
	                    	        $chekr_error = $json['error'];
	                    	        return NULL;
			        		    }
                        	}
                        	else{
                        	    
                        	}
            }
            else{
            	return $user->chekrcandidateid;
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
			if($user == NULL){
				return response()->json(['status' => "0",
					'message'=> 'No such user',
					'data' => null,
				]);
			}
			// return response()->json(['status' => "0",
			// 		'message'=> 'custom error',
			// 		'user' => $user, 
			// 		'data' => null,
			// 	]);

            try{
                    if (Hash::check($request->password, $user->password)) {
                        
                        // $chekr_error = null;
                 //        if($user->chekrcandidateid == NULL){
                 //        	$dob = '';
                        	
                 //        	if($user->dob){
                 //        	    // return ["date" => "". $user->dob];
                 //        	    $dob = Carbon::createFromFormat('m/d/Y', $user->dob)->format('Y-m-d');
                        	    
                 //        	    $data = [
			        		    //     "first_name" => $user->name,
			        		    //     "last_name" => $user->name,
			        		    //     "phone" => $user->phone,
			        		    //     "email" => $user->email,
			        		    //     "dob" => $dob,
			        		    //     "ssn" => $user->ssn,
			        		    //     "zipcode"=>$user->zip,
			        		    // ];
			        		    
			        		    // $json = $this->createCheckrCandidate($data);
			        		    
			        		    // if(array_key_exists('id', $json)){
			        		    // 	$user->chekrcandidateid = $json['id'];
			        		        
			        		    // 	User::where('userid', $user->userid)->update(['chekrcandidateid' => $json["id"]]);
	
	
			        		    // }
			        		    // else{
	                //     	        $chekr_error = $json['error'];
			        		    // }
                 //        	}
                 //        	else{
                        	    
                 //        	}
                 //        }
                        // $report_error = null;

          //               if($user->chekrreportid != NULL){
          //               	$report = $this->getchekrreportFromServer($user);
          //               }
          //               else{
          //               	$rep = new ReportController();
    						// $report = $rep->getCheckrReport($user->chekrcandidateid);//
    						
    						// if(!isset($report->error)){
    						// 	$id = $report->id;
    						// 	$user->chekrreportid = $id;
    						// 	$report = $this->getchekrreportFromServer($user);
    						// 	User::where('userid', $user->userid)->update(['chekrreportid' => $id]);
    						// }
    						// else{
    						// 	$report_error = $report->error;
    						// }
          //               }
                        
			        	
        
        
			        	return response()->json([
			        			'message' => 'User logged in',
			        			'status' => "1",
			        			'data' => new UserProfileFullResource($user),
			        			// 'chekr_error' => $chekr_error,
			        			// "report_error" => $report_error,
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
            


        	if($request->has('image'))
        	{
        	    $ima = $request->image;
				$fileName =  rand(). date("h:i:s").'image.png';
				$env = env('APP_DEBUG');
				$folder = 'braver';
				if($env == true){
					$folder = 'braver_testing';
				}
	
    			$ima = trim($ima);
    			$ima = str_replace('data:image/png;base64,', '', $ima);
    			$ima = str_replace('data:image/jpg;base64,', '', $ima);
    			$ima = str_replace('data:image/jpeg;base64,', '', $ima);
    			$ima = str_replace('data:image/gif;base64,', '', $ima);
    			$ima = str_replace(' ', '+', $ima);
			
    			$imageData = base64_decode($ima);
    			//Set image whole path here 
    			// $filePath = $_SERVER['DOCUMENT_ROOT']."/". $fileName;
	
   				// file_put_contents($filePath, $imageData);
   				// $user->url = $filePath;


   				$filePath = $_SERVER['DOCUMENT_ROOT']."/". $folder ."/storage/app/Images/". $fileName;

				// return $filePath;
            	if(!Storage::exists($_SERVER['DOCUMENT_ROOT']."/" . $folder ."/storage/app/Images/")){
                	Storage::makeDirectory($_SERVER['DOCUMENT_ROOT']."/". $folder ."/storage/app/Images/");
            	}
   				file_put_contents($filePath, $imageData);
   				$user->url = "/". $folder. "/storage/app/Images/". $fileName;
   				$user->baseUrlType = 'New';
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
        	if($request->has('ssn')){
        	    $ssn = $request->ssn;
        	    $user->ssn = $ssn;
        	    $params["ssn"] = $ssn;
        	}

        	if($request->has('lat')){
        	    $lat = $request->lat;
        	    $user->lat = $lat;
        	    $params["lat"] = $lat;
        	}

        	if($request->has('lang')){
        	    $lang = $request->lang;
        	    $user->lang = $lang;
        	    $params["lang"] = $lang;
        	}
        	if($request->has('zipcode')){
        	    $zipcode = $request->zipcode;
        	    $user->zip = $zipcode;
        	    $params["zip"] = $zipcode;
        	}
        	if($request->has('name')){
        	    $name = $request->name;
        	    $user->name = $name;
        	    $params["name"] = $name;
        	}
        	if($request->has('last_name')){
        	    $name = $request->last_name;
        	    $user->lastname = $name;
        	    $params["lastname"] = $name;
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
        		return response()->json(['status' => (string)"1",
					'message'=> 'User Updated',
					'data' => new UserProfileFullResource($user), 
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

        function checkPhoneExists(Request $request){
        	$validator = Validator::make($request->all(), [
			'phone' => 'required',
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

			$user = User::where('phone', $request->phone)->first();
			if($user){
				return response()->json(['status' => "0",
					'message'=> 'Phone already taken',
					'data' => null, 
				]);
			}
			else{
				return response()->json(['status' => "1",
					'message'=> 'Phone available',
					'data' => null, 
				]);
			}
        }
		
}
