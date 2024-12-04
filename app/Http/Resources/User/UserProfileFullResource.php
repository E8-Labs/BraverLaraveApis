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
        // Image URL construction
        $image = $this->url;
        if ($this->baseUrlType == "Old") {
            $image = \Config::get('constants.profile_images_old') . $image;
        } else {
            $image = \Config::get('constants.profile_images_new') . $image;
        }

        // Initialize variables
        $stripe = new \Stripe\StripeClient(env('Stripe_Secret'));
        $paymentController = new PaymentController();
        
        $sub = null;
        $isPremium = false;
        try{
            $haveActiveSubs = $paymentController->getUserActiveSubscriptions($this->stripecustomerid);
            $isPremium = !empty($haveActiveSubs);
        $plans = $haveActiveSubs ?? []; // Ensure $plans is an array
            $mySubscription = ["status" => "inactive", "plan" => ""];

        if (count($plans) > 0) {
            $sub = $plans[0];
            $isTrial = $paymentController->checkIfTrial($sub);

            $price = optional($sub->plan)->id;
            $type = "";
            $interval = "";
            $amount = 1000; // Default amount
            $netTotal = $amount;

            // Initialize variables for coupon details
            $name = null;
            $percentage_off = null;
            $amount_off = null;
            $duration = null;
            $duration_in_months = null;
            $coupon_id = null;

            if ($sub && $sub->plan) {
                $interval = optional($sub->plan)->interval . "ly";
                $amount = optional($sub->plan)->amount / 100;

                // Check if discount exists
                $discount = $sub->discount ?? null;
                $off = 0;

                if ($discount && isset($discount->coupon)) {
                    $coupon = $discount->coupon;

                    // Get amount_off or percent_off
                    $amount_off = $coupon->amount_off ?? null;
                    $percentage_off = $coupon->percent_off ?? null;

                    if ($amount_off !== null) {
                        $off = $amount_off / 100; // Convert amount_off from cents to dollars
                    } elseif ($percentage_off !== null) {
                        $off = ($amount * $percentage_off) / 100;
                    }

                    // Update net total
                    $netTotal = $amount - $off;

                    // Get coupon details
                    $name = $coupon->name ?? null;
                    $duration = $coupon->duration ?? null;
                    $duration_in_months = $coupon->duration_in_months ?? null;
                    $coupon_id = $coupon->id ?? null;
                } else {
                    // No discount or coupon
                    $netTotal = $amount;
                }
            }

            // Determine plan type based on price ID
            switch ($price) {
                case "price_1PqqchC2y2Wr4BecnrBic37s":
                    $type = "Monthly Private";
                    break;
                case "price_1PqqerC2y2Wr4BecRTvEsD1u":
                    $type = "Monthly Executive";
                    break;
                case "price_1PqqiXC2y2Wr4BecgL2a3LmO":
                    $type = "Yearly Private";
                    break;
                case "price_1Pqqj4C2y2Wr4BecXvK55VpD":
                    $type = "Yearly Executive";
                    break;
                default:
                    $type = "Unknown Plan";
                    break;
            }

            // Set subscription status
            $subscriptionStatus = optional($sub)->status ?? 'inactive';
            $status = $isTrial ? 'trialing' : $subscriptionStatus;

            $mySubscription = [
                "status" => $status,
                "plan" => $type,
                "price_id" => $price,
                "amount" => $amount,
                "interval" => $interval,
                "coupon_name" => $name,
                "coupon_id" => $coupon_id,
                "percent_off" => $percentage_off,
                "amount_off" => $amount_off,
                "duration" => $duration,
                "duration_in_months" => $duration_in_months,
                "net_amount" => $netTotal
            ];
        }
        }
        catch(\Exception $e){
            Log::info("Exception in UserProfileFullRessource");
            Log::info($e);
        }

        $showPaywall = false;
        try{
            $data = $stripe->customers->allSources(
                $this->stripecustomerid,
                ['object' => 'card', 'limit' => 2]
            );
    
            $cards = $data->data ?? [];
            $showPaywall = count($cards) == 0 && $this->subscriptionSelected == null && $this->codeSelected == null;
        }
        catch(\Exception $e){
            Log::info("Exception in UserProfileFullRessource Paywall");
            Log::info($e);
        }
        

        // Retrieve customer cards
        

        return [
            "userid" => $this->userid,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "dob" => $this->dob,
            "gender" => $this->gender,
            "dateadded" => $this->dateadded,
            "role" => $this->role,
            "fcmtoken" => $this->fcmtoken,
            "url" => $image,
            "accountstatus" => $this->accountstatus,
            "deleted" => $this->deleted,
            "myinvitecode" => $this->myinvitecode,
            "invitedbycode" => $this->invitedbycode,
            "stripecustomerid" => $this->stripecustomerid,
            "city" => $this->city,
            "state" => $this->state,
            "is_premium" => $isPremium,
            "plan" => $mySubscription,
            "sub" => $sub,
            "shouldShowPaywall" => $showPaywall,
            // "cards" => $cards
        ];
    }
}
