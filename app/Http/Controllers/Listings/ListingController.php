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

class ListingController extends Controller
{

	function addListing(Request $request){
		$validator = Validator::make($request->all(), [
			'yachtname' => 'required',
// 			'address' => 'required',
			// 'yachtdescription' => 'required',
// 			'userid' => 'required',
			'type' => 'required',
			"apikey" => 'required',
				]);

			if($validator->fails()){
				return response()->json(['status' => "0",
					'message'=> 'validation error',
					'data' => null,
					'params' => $request->all(),
					'validation_errors'=> $validator->errors()]);
			}

			$key = $request->apikey;
			if($key != $this->APIKEY){ // get value from constants
				return response()->json(['status' => "0",
					'message'=> 'invalid api key',
					'data' => null, 
				]);
			}

			$env = env('APP_DEBUG');
			$folder = 'braver';
			if($env == true){
				$folder = 'braver_testing';
			}
				
		try{
		    DB::beginTransaction();

		$listing = new Listing();
// 		$listing->addedby = $request->userid;
		if($request->has('userid')){
			$listing->addedby = $request->userid;
		}
		else{
			$listing->addedby = '';
		}
		$listing->yachtname = $request->yachtname;
		
		$listing->dateadded = Carbon::now()->toDateTimeString();
		$listing->yachtdescription = $request->yachtdescription;
		
		
		
		$listing->type = $request->type;


		if($request->has('address')){
		    
			$listing->yachtaddress = $request->address;
			if($request->address == NULL){
			    $listing->yachtaddress = '';
			}
		}
		else{
			$listing->yachtaddress = '';
		}

		if($request->has('url')){
			$listing->yachtweburl = $request->url;
			if($request->url == NULL){
			    $listing->yachtweburl = '';
			}
		}
		else{
			$listing->yachtweburl = '';
		}



		if($request->has('weekly_price')){
			$listing->weekly_price = $request->weekly_price;
			if($request->weekly_price == NULL){
			    $listing->weekly_price = '';
			}
		}
		else{
			$listing->weekly_price = '';
		}




		if($request->has('price')){
			$listing->yachtprice = $request->price;
			if($request->price == NULL){
			    $listing->yachtprice = '';
			}
		}
		else{
			$listing->yachtprice = '';
		}
		if($request->has('phone')){
			$listing->yachtphone = $request->phone;
			if($request->phone == NULL){
			    $listing->yachtphone = '';
			}
		}
		else{
			$listing->yachtphone = '';
		}
		if($request->has('eventdate')){
			$listing->eventdate = $request->eventdate;
		}
		else{
			$listing->eventdate = '';
		}
		if($request->has('eventtime')){
			$listing->eventtime = $request->eventtime;
		}
		else{
			$listing->eventtime = '';
		}

		if($request->has('eventenddate')){
			$listing->eventenddate = $request->eventenddate;
		}
		else{
			$listing->eventenddate = '';
		}

		if($request->has('eventendtime')){
			$listing->eventendtime = $request->eventendtime;
		}
		else{
			$listing->eventendtime = '';
		}

		if($request->has('instaurl')){
			$listing->instaurl = $request->instaurl;
			if($request->instaurl == NULL){
			    $listing->instaurl = '';
			}
		}
		else{
		    $listing->instaurl = '';
		}
		if($request->has('fulldayprice')){
			$listing->price_full_day = $request->fulldayprice;
			if($request->fulldayprice == NULL){
			    $listing->price_full_day = '';
			}
		}
		else{
		    $listing->price_full_day = '';
		}
		if($request->has('lat')){
			$listing->lat = $request->lat;
		}
		else{
		  //  $listing->lat = 0;
		}
		if($request->has('lang')){
			$listing->lang = $request->lang;
		}
		else{
		  //  $listing->lang = 0;
		}

		if($request->hasFile('seatingimage')){

			$ima = $request->seatingimage;
			$data=$request->file('seatingimage')->store('Images');
   			$listing->seatingimage = $data;
		}
		else if ($request->has('seatingimage')){
			// base64 image
			$url = $this->saveBase64Iamge($request->seatingimage, "/". $folder ."/storage/app/Images/");
			// $result = $this->saveImage($url, $listing_id, "", "Image");
			$listing->seatingimage = $url;
		}
		else{
			$listing->seatingimage = '';
		}

		$images = array();//$decoded['images'];
		$listing->yachtid = uniqid();
		$listing->save();

		if($request->hasFile('file'))
		{
			$data=$request->file('file')->store('Videos');
			$result = $this->saveImage($data, $listing->yachtid, $request->videotitle, "Video");
			$saved[] = $result;
		}

		$response = $this->AddImages($request, $listing->yachtid);
        // return $response;
		if($response==null)
			{
				DB::rollBack();
				return response()->json([
					'message' => 'Listing not added',
					'status' => "0",
					'data' => null,
				]); 
			}
			else
			{
				$$listing["images"] = $response;
				
				DB::commit();
				return response()->json(['status' => true,
					'message'=> 'Listing  added',
					'data'=> new ListingResource($listing)

				]);

			}
		}
		catch(\Exception $e){
			\Log::info("-------------------------------Listing Add Exception Start-------------------------------");
			\Log::info($e);
			\Log::info("-------------------------------Listing Add Exception End---------------------------------");
		    return response()->json(['status' => false,
					'message'=> 'Listing  not added',
					'error' => $e->getMessage(),
					'data'=> null,

				]);
		}
	}


	function AddImages(Request $req ,$listing_id)
	{
		$saved = array();
		$env = env('APP_DEBUG');
			$folder = 'braver';
			if($env == true){
				$folder = 'braver_testing';
			}

		if($req->hasFile('image0'))
		{
			$data=$req->file('image0')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image0')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image0, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image1'))
		{
			$data=$req->file('image1')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image1')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image1, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}
		

		if($req->hasFile('image2'))
		{
			$data=$req->file('image2')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image2')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image2, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image3'))

		{
			$data=$req->file('image3')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image3')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image3, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image4'))
		{
			$data=$req->file('image4')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image4')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image4, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image5'))
		{
			$data=$req->file('image5')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image5')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image5, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image6'))
		{
			$data=$req->file('image6')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image6')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image6, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image7'))
		{
			$data=$req->file('image7')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image7')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image7, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image8'))
		{
			$data=$req->file('image8')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}
		else if ($req->has('image8')){
			// base64 image
			$url = $this->saveBase64Iamge($req->image8, "/". $folder ."/storage/app/Images/");
			$result = $this->saveImage($url, $listing_id, "", "Image");
			$saved[] = $result;
		}



		if(count($saved) > 0)
		{
			return $saved;
		}
		else
		{
			return null;
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


	function getListings(Request $request){
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

			$type = "Yacht";
			if($request->has('type')){
				$type = $request->type;
			}

			$featured = Listing::where('deleted', 0)->where('featured', 1)->where('type', $type)->first();

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = Listing::where('deleted', 0)->where('featured', "=", 0)->where('type', $type)
			->when($request->has('lat'), function($query) use($request){
				$lat = $request->lat;
				$lang = $request->lang;
				$query->select(DB::raw('yacht.*, ((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(yacht.lat * PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(lat * PI() / 180) * COS((' . $lang . ' - yacht.lang) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 1.609344) as distance'))
                ->orderBy('distance', 'ASC');

			})
			->when($request->has('search'), function($query) use($request){
				$search = $request->search;
                if($search != ''){
                	$tokens = explode(" ", $search);
                	// return $tokens;
                    
					$query->where(function($query) use($tokens){
						foreach($tokens as $tok){

							$query = $query->where('yachtname', 'LIKE', "%$tok%")->orWhere('yachtaddress', 'LIKE', "%$tok%");
						}
					});
					

                    // $users = $query->take(50)->skip($off_set)->get();
                }

			})
			
			->take(50)->skip($off_set)->get();
			if(count($users) > 0 ){
				if($featured){
					$users->splice(0, 0, [$featured]);
				}
			}
			// if($request->has('search')){
			// 	$search = $request->search;
   //              if($search != ''){
   //              	$tokens = explode(" ", $search);
   //              	// return $tokens;
   //                  $query = Listing::where('deleted', 0)->where('featured', "=", 0)->where('type', $type)
   //                  ->when($request->has('lat'), function($query) use($request){
			// 			$lat = $request->lat;
			// 			$lang = $request->lang;
			// 			return $query->select(DB::raw('yacht.*, ((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(yacht.lat * PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(lat * PI() / 180) * COS((' . $lang . ' - yacht.lang) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 1.609344) as distance'))
   //              		->orderBy('distance', 'ASC');

			// 		});
			// 		$query->where(function($query) use($tokens){
			// 			foreach($tokens as $tok){

			// 				$query = $query->where('yachtname', 'LIKE', "%$tok%")->orWhere('yachtaddress', 'LIKE', "%$tok%");
			// 			}
			// 		});
					

   //                  $users = $query->take(50)->skip($off_set)->get();
   //              }
				
			// }
			// else{

			// }

			
			if($users){
				return response()->json(['status' => "1",
					'message'=> 'Listing obtained',
					'data' => ListingResource::collection($users), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Listing not obtained',
					'data' => null, 
				]);
			}
    }


    function getListingById(Request $request){

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

			$id = $request->yachtid;
			$listing = Listing::where('yachtid', $id)->first();
			if($listing){
				return response()->json(['status' => "1",
					'message'=> 'Listing details',
					'data' => new ListingResource($listing), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'No such listing',
					'data' => null, 
				]);
			}

    }

    function reportListing(Request $request){

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

			try{
				$id = $request->yachtid;
			$lis = new ReportedListing();
			$lis->reportedproduct = $id;
			$lis->reportedby = $request->fromid;
			$lis->reason = $request->reason;
			$done = $lis->save();
			if($done){
				$listing = Listing::where('yachtid', $id)->first();
				return response()->json(['status' => "1",
					'message'=> 'Listing reported',
					'data' => new ListingResource($listing), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Some issue on server',
					'data' => null, 
				]);
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

    function deleteListing(Request $request){

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
			$id = $request->yachtid;
			$done = Listing::where('yachtid', $id)->update(['deleted' => 1]);
			if($done){
				
				return response()->json(['status' => "1",
					'message'=> 'Listing deleted',
					'data' => null, 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Some problem on server',
					'data' => null, 
				]);
			}

    }


    function featuretListing(Request $request){

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
			$id = $request->yachtid;
			$yacht = Listing::where('yachtid', $id)->first();
			if($yacht){
			    try{
			    	DB::beginTransaction();
			    	$removeFeatured = Listing::where('type', $yacht->type)->update(['featured' => 0]);
                    $done = Listing::where('yachtid', $id)->update(['featured' => 1]);
				    if($done){
				    	DB::commit();
				    	$listing = Listing::where('yachtid', $id)->first();

				    	return response()->json(['status' => "1",
				    		'message'=> 'Listing featured',
				    		'data' => new ListingResource($listing), 
				    	]);
				    }
				    else{
				    	DB::rollBack();
				    	return response()->json(['status' => "0",
				    		'message'=> 'Some problem on server',
				    		'data' => null, 
				    	]);
				    }
			    }
			    catch(\Exception $e){
			    	return response()->json(['status' => "0",
			    			'message'=> 'Some error occurred',
			    			'error' => $e->getMessage(),
			    			'data' => null, 
			    		]);
			    }
			}
			else{
			    return response()->json(['status' => "0",
			    			'message'=> 'No such yacht',
			    			'data' => null, 
			    		]);
			}
			

    }

		
}















