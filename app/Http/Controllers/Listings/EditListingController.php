<?php

namespace App\Http\Controllers\Listings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Auth\User;
use App\Models\Listing;
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
				
		DB::beginTransaction();
		$listing = Listing::where('yachtid', $request->yachtid)->first();


		if($request->has('yachtname')){
			$listing->yachtname = $request->yachtname;
		}

		if($request->has('address')){
			$listing->address = $request->address;
		}

		if($request->has('yachtdescription')){
			$listing->yachtdescription = $request->yachtdescription;
		}
		if($request->has('phone')){
			$listing->phone = $request->phone;
		}
		if($request->has('url')){
			$listing->url = $request->url;
		}
		if($request->has('price')){
			$listing->price = $request->price;
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
			$url = $this->saveBase64Iamge($request->seatingimage, "/braver/storage/app/Images/");
			// $result = $this->saveImage($url, $listing_id, "", "Image");
			$listing->seatingimage = $url;
		}

		$images = array();//$decoded['images'];

		for($i = 0; $i < 8; $i++){
		    if($request->has('image'. $i)){
		        if($i == 0){
		        	$images[$i] = $request->image0;
		        }
		        if($i == 1){
		        	$images[$i] = $request->image1;
		        }
		        if($i == 2){
		        	$images[$i] = $request->image2;
		        }
		        if($i == 3){
		        	$images[$i] = $request->image3;
		        }
		        if($i == 4){
		        	$images[$i] = $request->image4;
		        }
		        if($i == 5){
		        	$images[$i] = $request->image5;
		        }
		        if($i == 6){
		        	$images[$i] = $request->image6;
		        }
		        if($i == 7){
		        	$images[$i] = $request->image7;
		        }
		    }
		}
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
    			        $url = $this->saveBase64Iamge($b64image, "/braver/storage/app/Images/");
						ListingImage::where('mediaid', $id)->update(['mediaurl' => $url, 'baseUrl' => 'New']);
    			    }
    		}
    		else{
    			//new image
    			$url = $this->saveBase64Iamge($b64image, "/braver/storage/app/Images/");
    			$this->saveImage($data, $request->yachtid, "", "Image");
    		}
		}

		$saved = $listing->save();
		if($saved){
			DB::commit();
			return response()->json(['status' => "1",
					'message'=> 'Edited listing',
					'data' => new ListingResource($listing), 
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

































