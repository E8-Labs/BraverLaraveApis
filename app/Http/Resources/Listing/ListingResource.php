<?php

namespace App\Http\Resources\Listing;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Listing\ListingImage;
use App\Http\Resources\Listing\ListingImageResource;
use App\Models\ListingTypes;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $images = ListingImage::where('productid', $this->yachtid)->get();

        $image = $this->seatingimage;
        $env = env('APP_DEBUG');
        if($env == true){ // debug mode
                $image = \Config::get('constants.item_images_debug') . $image;
            }
            else{
                $image = \Config::get('constants.item_images') . $image;
            }

            $location = $this->yachtaddress;
            if($location === NULL || $location === ''){
                $location = "";
            }
            if($this->type === ListingTypes::TypeCustom){
                $location = "Global";
            }
        return [
            "yachtid"=> $this->yachtid,
            "yachtname"=> $this->yachtname,
            "rooms" => $this->rooms,
            "yachtdescription"=> $this->yachtdescription,
            "yachtprice"=> $this->yachtprice,
            "weekly_price"=> $this->weekly_price,
            "price_full_day"=> $this->price_full_day,
            "yachtaddress"=> $location,
            "yachtweburl"=> $this->yachtweburl,
            "yachtphone"=> $this->yachtphone,
            "addedby"=> $this->addedby,
            "dateadded"=> $this->dateadded,
            "featured"=> $this->featured,
            "deleted"=> $this->deleted,
            "type"=> $this->type,
            "seatingimage"=> $image,
            "instaurl"=> $this->instaurl,
            "eventdate"=> $this->eventdate,
            "eventtime"=> $this->eventtime,
            "eventenddate"=> $this->eventenddate,
            "eventendtime"=> $this->eventendtime,
            "lat"=> $this->lat,
            "lang"=> $this->lang,
            'media' => ListingImageResource::collection($images),
        ];
    }
}
