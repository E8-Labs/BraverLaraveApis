<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends  Authenticatable
{
    use HasFactory;
    protected $table = 'user';
    public $timestamps = false;


    function createStripeCustomer(){
        $stripe = new \Stripe\StripeClient( env('Stripe_Secret'));
						if($this->stripecustomerid == NULL || $this->stripecustomerid == ''){
							//Generate Stripe id	
							\Log::info("User customer id created in user's own function");

            				try{
                                $customer = $stripe->customers->create([
                                    'description' => 'Braver Customer',
                                    'email' => $this->email,
                                    'name' => $this->name,
                                
                                 ]);
                                 \Log::info($customer);
                                 
                                $stripeid= $customer['id'];
                                $this->stripecustomerid = $stripeid;
                                $this->save();
                            }
                            catch(\Exception $e){
                                \Log::info("User stripe not created");
                                \Log::info($e);
                            }
						}
    }
}
