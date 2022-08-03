<?php

namespace App\Http\Resources\Listing;

use Illuminate\Http\Resources\Json\JsonResource;

class ListingImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $image = $this->mediaurl;
        $env = env('APP_DEBUG');
        
        
        if($this->baseUrl == "Old"){
            $image = \Config::get('constants.item_images_old') . $image;
        }
        else{
            
            if($env == true){
                $image = \Config::get('constants.item_images_debug') . $image;
            }
            else{
                $image = \Config::get('constants.item_images') . $image;
            }
        }

        return [
            "mediaid"=> $this->mediaid,
            "mediatitle"=> $this->mediatitle,
            "mediatype"=> $this->mediatype,
            "mediaurl"=> $image,
            "baseurl" => $this->baseUrl,
            "productid"=> $this->productid,
            "dateadded"=> $this->dateadded,
            "width"=> $this->width,
            "height"=> $this->height,
        ];
    }
}
