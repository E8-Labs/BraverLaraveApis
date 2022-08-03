<?php

namespace App\Http\Resources\Listing;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Listing\ListingImage;
use App\Http\Resources\Listing\ListingImageResource;

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

        $seating = $this->seatingimage;
        $env = env('APP_DEBUG');
        if($env == true){ // debug mode
                $image = \Config::get('constants.item_images_debug') . $image;
            }
            else{
                $image = \Config::get('constants.item_images') . $image;
            }
        return [
            "yachtid"=> $this->yachtid,
            "yachtname"=> $this->yachtname,
            "yachtdescription"=> $this->yachtdescription,
            "yachtprice"=> $this->yachtprice,
            "price_full_day"=> $this->price_full_day,
            "yachtaddress"=> $this->yachtaddress,
            "yachtweburl"=> $this->yachtweburl,
            "yachtphone"=> $this->yachtphone,
            "addedby"=> $this->addedby,
            "dateadded"=> $this->dateadded,
            "featured"=> $this->featured,
            "deleted"=> $this->deleted,
            "type"=> $this->type,
            "seatingimage"=> $this->seatingimage,
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
