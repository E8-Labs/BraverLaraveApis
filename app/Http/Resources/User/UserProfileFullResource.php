<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\PaymentController;

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
        $paymentController = new PaymentController;
        $haveActiveSubs = $paymentController->getUserActiveSubscriptions($this->stripecustomerid);

        $plans = $haveActiveSubs;


        $mySubscription = ["status" => "inactive", "plan" => ""]; // Monthly & Yearly
        if($plans === NULL || count($plans) === 0){
            //if no previous subscription, then just subscribe
            // return $this->createSubscription($request);
        }
        else{
            $sub = $plans[0];
            $isTrial = $paymentController->checkIfTrial($sub);
            
            $price = $sub->plan->id;
            $type = "";

            // if($price === env("Test_Yearly_Plan_Id")){
            //     $type = "Yearly";
            // }
            // else if($price === env("Test_Monthly_Plan_Id")){
            //     $type = "Monthly";
            // }

            if($price === env("Live_Yearly_Plan_Id")){
                $type = "Yearly";
            }
            else if($price === env("Live_Monthly_Plan_Id")){
                $type = "Monthly";
            }
            if($isTrial){
                $mySubscription = ["status" => "trialing", "plan" => $type];
            }
            else if ($sub->status === "active"){
                $mySubscription = ["status" => "active", "plan" => $type];
            }
            else{
                $mySubscription = ["status" => "inactive", "plan" => $type];
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
            "plan" => $mySubscription
        ];
    }
}
