<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserChatProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $image = $this->url;
        if($this->baseUrlType == "Old"){
            $image = \Config::get('constants.profile_images_old') . $image;
        }
        else{
            $image = \Config::get('constants.profile_images_new') . $image;
        }
        return [
            "userid"=> $this->userid,
            "name"=> $this->name,
            "email"=> $this->email,
            "dateadded"=> $this->dateadded,
            "role"=> $this->role,
            "url"=> $image,
            "accountstatus"=> $this->accountstatus,
        ];
    }
}
