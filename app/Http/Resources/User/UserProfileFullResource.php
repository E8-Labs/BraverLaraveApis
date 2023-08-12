<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileFullResource extends JsonResource
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
        $stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
        $haveActiveSubs = NULL;

        try{
            $haveActiveSubs = $stripe->subscriptions->all(['limit' => 30, 'customer' => $this->stripecustomerid, "status" => "active"]);
                // return $haveActiveSubs;
        }
        catch(\Exception $e){
            Log::info("No active subs");
        }
        if($haveActiveSubs === NULL){
            try{
            $haveActiveSubs = $stripe->subscriptions->all(['limit' => 30, 'customer' => $this->stripecustomerid, "status" => "trialing"]);
                // return $haveActiveSubs;
            }
            catch(\Exception $e){
                Log::info("No trials subs");
            }
        }


        $isPremium = FALSE;
        if($haveActiveSubs){
            $isPremium = TRUE;
        }
        return [
            "userid"=> $this->userid,
            "name"=> $this->name,
            "email"=> $this->email,
            "phone"=> $this->phone,
            "dob"=> $this->dob,
            "gender"=> $this->gender,
            "dateadded"=> $this->dateadded,
            "role"=> $this->role,
            "fcmtoken"=> $this->fcmtoken,
            "url"=> $image,
            "accountstatus"=> $this->accountstatus,
            "deleted"=> $this->deleted,
            "myinvitecode"=> $this->myinvitecode,
            "invitedbycode"=> $this->invitedbycode,
            "stripecustomerid"=> $this->stripecustomerid,
            'city' => $this->city,
            'state' => $this->state,
            "is_premium" => $isPremium,
        ];
    }
}
