<?php

namespace App\Http\Controllers\Listings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Auth\User;
use App\Models\Listing;
use App\Models\ListingTypes;
use App\Models\Listing\ListingImage;
use App\Models\Listing\ReportedListing;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\Listing\ListingResource;

use Carbon\Carbon;

class EditListingController extends Controller
{
    function editListing(Request $request){
		$validator = Validator::make($request->all(), [
			"apikey" => 'required',
			"yachtid" => 'required',
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

		$env = env('APP_DEBUG');
			$folder = 'braver';
			if($env == true){
				$folder = 'braver_testing';
			}	
		DB::beginTransaction();
		$listing = Listing::where('yachtid', $request->yachtid)->first();


		if($request->has('yachtname')){
			$listing->yachtname = $request->yachtname;
		}

		if($request->has('address')){
			$listing->yachtaddress = $request->address;
		}

		if($request->has('yachtdescription')){
			$des = $request->yachtdescription;
			if(($des == null || $des == '') && $listing->type !== ListingTypes::TypeTrip){
				return response()->json(['status' => "0",
					'message'=> 'Listing description can not be empty',
					'data' => null, 
				]);
			}
// 			if($des == ''){
// 				return response()->json(['status' => "0",
// 					'message'=> 'Listing description is empty',
// 					'data' => null, 
// 				]);
// 			}
			$listing->yachtdescription = $request->yachtdescription;
		}
		
		if($request->has('phone')){
			$listing->yachtphone = $request->phone;
		}
		if($request->has('url')){
			$listing->yachtweburl = $request->url;
		}
		if($request->has('price')){
			$listing->yachtprice = $request->price;
		}
		if($request->has('eventdate')){
			$listing->eventdate = $request->eventdate;
		}
		if($request->has('eventtime')){
			$listing->eventtime = $request->eventtime;
		}
		if($request->has('instaurl')){
			$listing->instaurl = $request->instaurl;
		}
		if($request->hasFile('file'))
		{
			$data=$request->file('file')->store('Videos');
			$result = $this->saveImage($data, $listing->yachtid, $request->videotitle, "Video");
			$saved[] = $result;
		}

		if($request->hasFile('seatingimage')){

			// $ima = $request->seatingimage;
			$data=$request->file('seatingimage')->store('Images');
   			$listing->seatingimage = $data;
		}
		else if ($request->has('seatingimage')){
			// base64 image
			$url = $this->saveBase64Iamge($request->seatingimage, "/".$folder."/storage/app/Images/");
			// $result = $this->saveImage($url, $listing_id, "", "Image");
			$listing->seatingimage = $url;
		}

		$images = array();//$decoded['images'];

    $index = 0;
		for($i = 0; $i < 8; $i++){
		    if($request->has('image'. $i)){
		        if($i == 0){
		        	$images[$index] = $request->image0;
		        	$index += 1;
		        }
		        if($i == 1){
		        	$images[$index] = $request->image1;
		        	$index += 1;
		        }
		        if($i == 2){
		        	$images[$index] = $request->image2;
		        	$index += 1;
		        }
		        if($i == 3){
		        	$images[$index] = $request->image3;
		        	$index += 1;
		        }
		        if($i == 4){
		        	$images[$index] = $request->image4;
		        	$index += 1;
		        }
		        if($i == 5){
		        	$images[$index] = $request->image5;
		        	$index += 1;
		        }
		        if($i == 6){
		        	$images[$index] = $request->image6;
		        	$index += 1;
		        }
		        if($i == 7){
		        	$images[$index] = $request->image7;
		        	$index += 1;
		        }
		    }
		}
// 		return $images;
		for($i = 0; $i < count($images); $i++){
		    $b64image = $images[$i];
		    if(strpos($b64image, "Delete_") !== false){
    		    $tok = explode("_",$b64image);//strtok($string, "_");
    		    $id = $tok[1];
    		    ListingImage::where('mediaid', $id)->delete();
    		    $sql = "Delete from productmedia where mediaid = $id";
    		}
    		else if(strpos($b64image, "Media_") !== false){ // update image
    			    $tok = explode("_",$b64image);//strtok($b64image, "_");
			
    			    if(count($tok) >= 3){
    			        $first = $tok[0];
    			        $id = $tok[1];
    			        $newimage = str_replace($first."_".$id."_","",$b64image);
    			        $b64image = $newimage;
    			        $url = $this->saveBase64Iamge($b64image, "/".$folder."/storage/app/Images/");
						ListingImage::where('mediaid', $id)->update(['mediaurl' => $url, 'baseUrl' => 'New']);
    			    }
    		}
    		else{
    			//new image
    			$url = $this->saveBase64Iamge($b64image, "/".$folder."/storage/app/Images/");
    			$this->saveImage($url, $request->yachtid, "", "Image");
    		}
		}

		$saved = $listing->save();
		if($saved){
			DB::commit();
			$yacht = Listing::where('yachtid', $request->yachtid)->first();
			return response()->json(['status' => "1",
					'message'=> 'Edited listing',
					'data' => new ListingResource($yacht), 
				]);
		}
		else{
			DB::rollBack();
			return response()->json(['status' => "0",
					'message'=> 'Could not edit listing',
					'data' => null, 
				]);
		}
		}
		catch(\Exception $e){
		    DB::rollBack();
		    \Log::info('----------------Exception editing start------------------');
		    \Log::info($e);
		    \Log::info('----------------Exception editing end------------------');
			return response()->json(['status' => "0",
					'message'=> 'Error editing : Exception',
					'data' => null, 
				]);
		}




	}





	private function saveImage($url, $listing_id = -1, $media_title, $media_type){
		$listing_image= new ListingImage();
		// $listing_image->mediaid = uniqid();
		$listing_image->mediatitle = $media_title;
		$listing_image->mediatype = $media_type;
		$listing_image->baseUrl = 'New';
		// $listing_image->image_width  = 0;
		// $listing_image->image_height = 0;
		if($listing_id != -1){
		    $listing_image->productid   = $listing_id;
		}
		$listing_image->mediaurl = $url;
		$result =$listing_image->save();
		return $listing_image;
	}
}

































