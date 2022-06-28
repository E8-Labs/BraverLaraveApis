<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "menuid"=> $this->menuid,
            "menutitle"=> $this->menutitle,
            "menuimage"=> \Config::get('constants.item_images_old') . $this->menuimage,
            "dateadded" => $this->dateadded,
            "menuheader"=> \Config::get('constants.item_images_old') . $this->productid,
        ];
    }
}
