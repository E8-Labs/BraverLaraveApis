<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception;


class ReportController extends Controller
{
  	var $URL_CANDIDATES = 'https://api.checkr.com/v1/candidates';
    var $URL_REPORTS = 'https://api.checkr.com/v1/reports';
    var $CONNECTTIMEOUT = 10;

  const CURRENCY = 'usd';

  public function createreport(Request $request) {
      $rules = ['userid' => 'required', ];
      $customMessages = [ 'required' => 'Please fill attribute :attribute' ];
      $this->validate($request, $rules, $customMessages);
      $error; $isPiad = false;    
      try {
             $userid = $request->input('userid');
             $source = $request->input('source'); // payment method id 
             $hash = Operators::where('userid', $userid)->first();
             $isAlreadyPiad = $hash['is_paid'];
            if($hash){
                if($hash->count() > 0){
                            $candidate_id = $hash['chekrcandidateid'];
                            if($candidate_id == null || $candidate_id == '' ){
                              /////////////////////////////////////////////////////////////  GET CANDIDATE ID ///////////////////  
                                   $data = $this->getCandidatId($hash);
                                   if ($data->status == false ){
                                       return $this->generate_response(false,$data->message,$data->data,200); 
                                   }
                                    $candidate_id = $data->data;    
                                    Operators::where('userid',$hash['userid'])->update(['chekrcandidateid'=> $candidate_id]);
                             }
                                    $hashe = Operators::where('userid', $userid)->first();  
                                    $candidatid = $hashe['chekrcandidateid'];
                                    $reportid = $hashe['chekrreportid'];
                            if($candidatid != null){
                                     if($reportid == null || $reportid == '' ){
                                                   //////////////////////////////////////////////////////////////////////  PAYMENT /////////////////// 
                                                    $status;
                                                     if( $isAlreadyPiad == false ){    
                                                            $customerid=$hashe['stripecustomerid'];
                                                            $total=3000;
                                                            $paymentData = $this->makeStripePayment($total,$source,$customerid);  
    
                                                            if ($paymentData->status == false ){
                                                              return $this->generate_response(false,$paymentData->message,$paymentData->data,200); 
                                                            }
                                                            $status= $paymentData->data["status"];// $charge['status'];
                                                      }else{
                                                            $status = 'succeeded';  // already paid  
                                                      }
                                            
                                                      if($status == 'succeeded'){
                                                
                                                     //////////////////////////////////////////////////////////////////////////  REPORT /////////////////// 
                                                                      $response = $this->getCheckrReport($candidatid);
                                                                      if(array_key_exists("error",$response)){
                                                                           return $this->generate_response(true,$response,null,200); 
                                                                      }
                                                                      Operators::where('userid', $userid)->update([
                                                                                                                    'chekrstatus'=> $response->status,
                                                                                                                    'chekrreportid'=> $response->id,
                                                                                                                    
                                                                                                                    'ssn_trace'=> $response->ssn_trace_id,
                                                                                                                    'sex_offender_status'=> $response->sex_offender_search_id,
                                                                                                                    'national_status'=> $response->national_criminal_search_id,
                                                                                                                    'federal_status'=> $response->federal_criminal_search_id,
                                                                                                                    'is_paid' => true
                                                                                                                    ]);
                                                                      $hashes = Operators::where('userid', $userid)->first();
                                                                      return $this->generate_response(true,"Successfully created!",$hashes,200); 
                                                      }else{ 
                                                                  return $this->generate_response(false,'Payment failed',null,500);
                                                      }
                                  }else{ $error = 'Report already created'; }
                          }else{ $error = 'No candidate_id found';}
                 }else{  $error = 'No user found';}
          }else{ $error = 'No user found';}
        } catch (\Illuminate\Database\QueryException $ex) {
            $error =$ex->getMessage();
        }
        return $this->generate_response(false,$error,null,200);
    }


  private function makeStripePayment($total,$source,$customerid){
    $error;
    try{ 
        $stripe = new \Stripe\StripeClient( env('Stripe_Secret') );
        $array = array();
        if($source != null || $source != ''){
            $array = ['amount' => $total,
                      'currency' => self::CURRENCY ,
                      'customer' => $customerid,
                      'source' => $source,
                      'description' => 'Watering Can Charge'];
        }
        else{
          $array = ['amount' => $total,
                    'currency' => self::CURRENCY ,
                    'customer' => $customerid,
                    'description' => 'Watering Can Charge']; 
        }
          $charge = $stripe->charges->create($array);
          return new Data(true,"",$charge);

        } catch(\Stripe\Exception\CardException $e) {
               $error= $e->getError()->message . " Card";
        } catch (\Stripe\Exception\RateLimitException $e) {
              $error= $e->getError()->message;
        } catch (\Stripe\Exception\InvalidRequestException $e) {
               $error= $e->getError()->message . " invalid";
        } catch (\Stripe\Exception\AuthenticationException $e) {
               $error= $e->getError()->message;
        } catch (\Stripe\Exception\ApiConnectionException $e) {
               $error= $e->getError()->message;
        } catch (\Stripe\Exception\ApiErrorException $e) {
               $error= $e->getError()->message;
        } 
          return new Data(false,$error,null);
  }

  public function getCheckrReport($candidatid){
        $api_key=env('chekrapikey');
        $work_locations = ["city" => "San Francisco", 'state' => 'CA', 'country' => 'US'];
        $report_params = [ "candidate_id" => $candidatid,  "package" => "tasker_standard", 'work_locations' => [$work_locations]];
        // echo json_encode($report_params);
        // die();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->URL_REPORTS);
        curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($report_params));
        $json = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return json_decode($json);
  }

  private function getCandidatId($hash){
    $api_key=env('chekrapikey');
    $dob = $hash['birthdate'];
    $time = strtotime($dob);
    $newformat = date('Y-m-d',$time);
    $candidate_params = [
              "first_name" => $hash['firstname'],
              "last_name" => $hash['lastname'],
              "phone" => $hash['phone'],
              "email" => $hash['email'],
              "zipcode"=>$hash['zip'],
              "dob" => $newformat,//$hash['birthdate'],
              "ssn" => $hash['ssn'],
              "no_middle_name" => true];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->URL_CANDIDATES);
    curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($candidate_params));
    // api call
    $json = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $hashd = json_decode($json, true);
    if(isset($hashd['id'])){
        $candidate_id = $hashd['id']; 
        return new Data(true,"",$candidate_id);
    }
    else{
        return  new Data(false,"m",$hashd);
    }
  }
}

