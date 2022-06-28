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
			'address' => 'required',
			'yachtdescription' => 'required',
			'url' => 'required',
			'phone' => 'required',
			'price' => 'required',
			'userid' => 'required',
			'type' => 'required',
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
				
		DB::beginTransaction();

		$listing = new Listing();
		$listing->addedby = $request->userid;
		$listing->yachtname = $request->yachtname;
		$listing->yachtaddress = $request->address;
		$listing->dateadded = Carbon::now()->toDateTimeString();
		$listing->yachtdescription = $request->yachtdescription;
		$listing->yachtweburl = $request->url;
		$listing->yachtphone = $request->phone;
		$listing->yachtprice = $request->price;
		$listing->type = $request->type;
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
		}
		if($request->has('fulldayprice')){
			$listing->price_full_day = $request->fulldayprice;
		}
		if($request->has('lat')){
			$listing->lat = $request->lat;
		}
		if($request->has('lang')){
			$listing->lang = $request->lang;
		}

		if($request->hasFile('seatingimage')){

			$ima = $req->seatingimage;
			$data=$req->file('seatingimage')->store('Images');
   			$listing->seatingimage = $data;
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
					'data'=> $listing

				]);

			}
	}


	function AddImages(Request $req ,$listing_id)
	{
		$saved = array();
		if($req->hasFile('image1'))
		{
			$data=$req->file('image1')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image2'))
		{
			$data=$req->file('image2')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image3'))

		{
			$data=$req->file('image3')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image4'))
		{
			$data=$req->file('image4')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image5'))
		{
			$data=$req->file('image5')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image6'))
		{
			$data=$req->file('image6')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image7'))
		{
			$data=$req->file('image7')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
			$saved[] = $result;
		}

		if($req->hasFile('image8'))
		{
			$data=$req->file('image8')->store('Images/Listing');
			$result = $this->saveImage($data, $listing_id, "", "Image");
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

			$page = 1;
			if($request->has('page')){
				$page = $request->page;
			}
			$off_set = $page * 50 - 50;
			$users = Listing::where('deleted', 0)->where('featured', "=", 0)->where('type', $type)->take(50)->skip($off_set)->get();
			if($request->has('search')){
				$search = $request->search;

				$users = Listing::where('deleted', 0)->where('featured', "=", 0)->where('type', $type)->where('yachtname', 'LIKE', "%$search%")->take(50)->skip($off_set)->get();
			}
			else{

			}

			
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

			$id = $request->yachtid;
			$lis = new ReportedListing();
			$lis->reportedproduct = $id;
			$lis->reportedby = $request->fromid;
			$list->reason = $request->reason;
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

			$done = Listing::where('yachtid', $id)->update(['featured' => 1]);
			if($done){
				$listing = Listing::where('yachtid', $id)->first();
				return response()->json(['status' => "1",
					'message'=> 'Listing featured',
					'data' => new ListingResource($listing), 
				]);
			}
			else{
				return response()->json(['status' => "0",
					'message'=> 'Some problem on server',
					'data' => null, 
				]);
			}

    }

		
}














