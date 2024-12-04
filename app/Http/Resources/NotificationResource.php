<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\NotificationTypes;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $icon = '';
        if($this->notification_type == NotificationTypes::TypeMessage){
            $icon = \Config::get('constants.message_ic');
        }
        else if($this->notification_type == NotificationTypes::TypeReservation){
            $icon = \Config::get('constants.calendar_ic');
        }
        else if($this->notification_type == NotificationTypes::InvoicePaid){
            $icon = \Config::get('constants.invoice_ic');
        }
        else if($this->notification_type == NotificationTypes::NewUser || 
                $this->notification_type == NotificationTypes::AccountApproved){
            $icon = \Config::get('constants.new_user_ic');
        }
        else if($this->notification_type == NotificationTypes::AdminBroadcast){
            $icon = \Config::get('constants.calendar_ic');
        }
        else if($this->notification_type == NotificationTypes::TypeReservationCancelled){
            $icon = \Config::get('constants.calendar_ic');
        }
        else if($this->notification_type == NotificationTypes::TeamMemberReservationInvite){
            $icon = \Config::get('constants.calendar_ic');
        }
        
        return [
            "id"=> $this->id,
            "title"=> $this->title,
            "subtitle"=> $this->subtitle,
            "message"=> $this->message,
            "from_user"=> $this->from_user,
            "to_user"=> $this->to_user,
            "updated_at"=> $this->updated_at,
            "created_at"=> $this->created_at,
            "notification_type"=> $this->notification_type,
            "notifiable_id"=> $this->notifiable_id,
            "notifiable_type"=> $this->notifiable_type,
            "icon"=> $icon,
            // "myinvitecode"=> $this->myinvitecode,
            // "invitedbycode"=> $this->invitedbycode,
            // "stripecustomerid"=> $this->stripecustomerid,,
        ];
    }
}
