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
        $paymentMessage = null;
        $showPaymentMessage = false;
        $mySubscription = ["status" => "inactive", "plan" => ""];

        try {
            // Fetch active subscriptions
            $haveActiveSubs = $paymentController->getUserActiveSubscriptions($this->stripecustomerid);
            $isPremium = !empty($haveActiveSubs);
            $plans = $haveActiveSubs ?? []; // Ensure $plans is an array

            if (count($plans) > 0) {
                $sub = $plans[0];
                $isTrial = $paymentController->checkIfTrial($sub);

                $price = optional($sub->plan)->id;
                $type = "";
                $interval = "";
                $amount = optional($sub->plan)->amount / 100 ?? 0;
                $netTotal = $amount;

                // Handle discount/coupons
                $discount = $sub->discount ?? null;
                if ($discount && isset($discount->coupon)) {
                    $coupon = $discount->coupon;
                    $amount_off = $coupon->amount_off ?? null;
                    $percentage_off = $coupon->percent_off ?? null;
                    $off = $amount_off ? $amount_off / 100 : ($amount * $percentage_off / 100);
                    $netTotal = $amount - $off;
                }

                // Determine plan type
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

                // Check for payment issues
                $latestInvoice = optional($sub)->latest_invoice;
                if ($latestInvoice) {
                    $invoiceDetails = $stripe->invoices->retrieve($latestInvoice, []);
                    $paymentIntent = $invoiceDetails->payment_intent ? $stripe->paymentIntents->retrieve($invoiceDetails->payment_intent, []) : null;

                    if ($paymentIntent && $paymentIntent->status === 'requires_payment_method') {
                        $paymentMessage = "Your payment method failed. Please update your payment method to continue your subscription.";
                        $showPaymentMessage = true;
                    }
                }

                $status = $isTrial ? 'trialing' : $sub->status;

                $mySubscription = [
                    "status" => $status,
                    "plan" => $type,
                    "price_id" => $price,
                    "amount" => $amount,
                    "interval" => $interval,
                    "net_amount" => $netTotal,
                    "show_payment_message" => $showPaymentMessage,
                    "payment_message" => $paymentMessage,
                ];
            } else {
                // Fetch the most recent canceled/expired subscription
                $subscriptions = $stripe->subscriptions->all([
                    'customer' => $this->stripecustomerid,
                    'status' => 'all',
                    'limit' => 5, // Fetch up to 5 recent subscriptions
                ]);

                // Filter subscriptions to include only those that are not active or trialing
                $filteredSubscriptions = array_filter($subscriptions->data, function ($sub) {
                    return !in_array($sub->status, ['active', 'trialing']);
                });

                // Get the most recent canceled subscription if any
                $sub = !empty($filteredSubscriptions) ? reset($filteredSubscriptions) : null;

                if ($sub) {
                    $type = optional($sub->plan)->nickname ?? "Unknown Plan";
                    $amount = optional($sub->plan)->amount / 100 ?? 0;
                    $interval = optional($sub->plan)->interval . "ly" ?? "Unknown Interval";

                    // Check for payment-related cancellation
                    $cancellationReason = optional($sub->cancellation_details)->reason;
                    if ($cancellationReason === 'payment_failed' || $sub->status === "incomplete_expired") {
                        $paymentMessage = "Your payment method failed. Please update your payment method to renew your subscription.";
                        $showPaymentMessage = true;
                    }
                    if ($sub->status === "incomplete") {
                        $paymentMessage = "Your payment method failed. We will try again. If the payment method is not active or has insufficient funds, please update the payment method so that subscription can be processed.";
                        $showPaymentMessage = true;
                    }

                    $mySubscription = [
                        "status" => $sub->status ?? "canceled",
                        "plan" => $type,
                        "price_id" => optional($sub->plan)->id ?? null,
                        "amount" => $amount,
                        "interval" => $interval,
                        "cancellation_reason" => $cancellationReason ?? "Unknown",
                        "cancellation_message" => $paymentMessage ?? "Your subscription was canceled. Contact support if you wish to renew.",
                        "show_payment_message" => $showPaymentMessage,
                        "payment_message" => $paymentMessage,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::info("Exception in UserProfileFullResource");
            \Log::info($e);
        }

        $showPaywall = false;
        try {
            // Fetch customer payment sources
            $data = $stripe->customers->allSources(
                $this->stripecustomerid,
                ['object' => 'card', 'limit' => 2]
            );
            $cards = $data->data ?? [];
            $showPaywall = count($cards) == 0 && $this->subscriptionSelected == null && $this->codeSelected == null;
        } catch (\Exception $e) {
            \Log::info("Exception in UserProfileFullResource Paywall");
            \Log::info($e);
        }

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
        ];
    }
}
