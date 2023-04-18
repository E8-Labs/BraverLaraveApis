<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
// use JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Resources\UserProfileResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Resources\User\UserProfileFullResource;
use App\Http\Resources\User\UserProfileLiteResource;

class SocialLoginController extends Controller
{

	function RegisterUserWithSocial(Request $req){
		$validator = Validator::make($req->all(), [
						//'email' => 'required|string|email|max:255|unique:users',
						// 'phone' => 'required|unique:users',
						'provider_id' => 'required|string|unique:user',
					]);

		if($validator->fails()){
			return response()->json(['status' => false,
				'message'=> 'validation error',
				'data' => null, 
				'validation_errors'=> $validator->errors()]);
		}


		DB::beginTransaction();
		$code = rand ( 10000 , 99999 );//Str::random(5);
		$user_id = uniqid();
		$user=new User;
		$user->userid = $user_id;
		$email = $this->getEmailForSocialLogin($req);
		// $user->phone=$req->phone;
		$user->email=$email;
		// $user->role=$req->role;
		$user->password=Hash::make($req->provider_id);//$req->provider_id;//
		$user->provider_id = $req->provider_id;
		$user->provider_name = $req->provider_name;

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
		// $user->email=$req->email;
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
   			// file_put_contents($filePath, $imageData);
   			$user->url = "/". $folder. "/storage/app/Images/". $fileName;

		}
		else{
		    return response()->json(['status' => "0",
					'message'=> 'Image not added',
					'data' => null, 
					'error' => 'error',
				]);
		}
		
		// $user->password=Hash::make($req->password);
		
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


		// $result=$user->save();
		// $token = Auth::login($user);
		
		// $user_id = $user->id;
		if($result)
		    {
		 //    	$reg = new UserAuthController();
			// 	$response = $reg->AddProfile($req, $user);
			// if($response == null){
			// 	DB::rollBack();
			// 	return response()->json([
			// 		'message' => 'User not registered',
			// 		'status' => false,
			// 		'data' => null,
			// 	]);
			// }
			DB::commit();
			$profile = User::where('userid', $user_id)->first();
			$data = ['profile'=> new UserProfileFullResource($profile)];
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


    function isSocialLoginAccountExists(Request $request){

		$validator = Validator::make($request->all(), [
			'provider_id' => 'required|string',
			'provider_name' =>'required|string',
// 			'email' => 'required'
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null, 
					'validation_errors'=> $validator->errors()]);
			}
			$loginid = $request->provider_id;
			$provider_name = $request->provider_name;
			
			

			$user = User::where('provider_id', $loginid)->first();

			
			if ($user == null){
				// return response()->json([
				// 	'message'=> "Account doesn't exist ". $loginid,
				// 	'status' =>  false,
				// 	'data'   => null,
				// 	]);
			}
			{
			    $email = $request->email;
			    if($email == ''){
			        $email = $this->getEmailForSocialLogin($request);
			    }


			    $exists = User::where('email', $email)->first();
			    // echo "Email " . $email;
				if($exists){
		            if( ($exists->provider_name == 'facebook' && $request->provider_name == 'facebook') || ($exists->provider_name == 'google' && $request->provider_name == 'google') || ($exists        ->provider_name == 'apple' && $request->provider_name == 'apple') ){
		        	// the user is trying to login
		            }
		        	else{
		        	    return response()->json(['status' => "0",
		        			'message'=> 'Email already exists',
		        			'socialStatus' => 'EmailAlreadyExistsWithDifferentProvider',
		        			'data' => null, 
		        		]);
		        	}
		        }
		        
		        else{
		        	return response()->json(['status' => "0",
		        			'message'=> 'Email is available',
		        			'socialStatus' => 'EmailAvailable',
		        			'data' => null, 
		        		]);
		        }


				
				$credentials = ["email" => $email, "password" => $loginid];
				// $credentials = $request->only('email', 'password');

        		
				try
				{
					$token = Auth::login($user);
					if(!$token )
					{
						return response()->json([
							'message' =>'Invalid_Credentials',
							'status' =>"0",
							'data' => $credentials,
						]);
					}
				}
				catch (JWTException $e)
				{
					return response()->json([
					'message' => 'Could not create token '. $e->getMessage(),
					'status'=>"0"]);
				}

				$id = $user->id;

				$profile = Profile::where('user_id', $id)->first();
				$data = ["access_token" => $token];
				if ($profile == null){
					$data["profile"] = null; // means user is just regisetered his details are missing
				}
				else{
					$data["profile"] = new UserProfileFullResource($profile);
				}
				
					return response()->json([
					'message'=> 'Account logged in',
					'status' =>  "1",
					'data'   => $data
					]);
			}
	}

	function getEmailForSocialLogin(Request $request){
		$email = '';
			if ($request->has('email')){
				$email = $request->email;
			}
			else{
				
			}
			if($email === '' || $email === NULL){
				$email = $request->provider_id."@".$request->provider_name.".com";
			}
			return $email;
	}
}
