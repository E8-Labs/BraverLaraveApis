<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Chat\ChatUser;
use App\Models\Auth\User;
use App\Models\Listing;
use App\Models\Listing\Reservation;

use App\Http\Resources\User\UserProfileFullResource;
use App\Http\Resources\User\UserProfileLiteResource;
use App\Http\Resources\Listing\ListingResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $p = Listing::where('yachtid', $this->productid)->first();
        $chatUserIds = ChatUser::where('chatid', $this->chatid)->pluck('userid')->toArray();
        $res = Reservation::where('chatid', $this->chatid)->first();
        $users = User::whereIn('userid', $chatUserIds)->get();


        $unread = ChatUser::where('chatid', $this->chatid)->where('userid', $request->userid)->sum('unreadcount');
        return [
            "chatid" => $this->chatid,
            'reservationid' => $res != null ? $res->reservationid : null,
            'reservation' => $res,
            "productid" => $this->productid,
            "dateadded" => $this->dateadded,
            "updatedat" => $this->updatedat,
            "fromuserid" => $this->fromuserid,
            "lastmessage" => $this->lastmessage,
            "chattype" => $this->chattype,
            "chatforproduct" => $this->chatforproduct,
            "customaddress" => $this->customaddress,
            "product" => new ListingResource($p),
            "users" => UserProfileLiteResource::collection($users),
            'unread' => $unread,
        ];
    }
}
