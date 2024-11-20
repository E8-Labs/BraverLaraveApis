<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use app\Models\Auth\User;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $APIKEY = "kinsal0349";
    private $API_KEY_FCM = "AAAAH8FOIuk:APA91bH4ZOKCMWE3iQa3qDvvrx4FC5nt3YJhCzKePhThvK1mFt1nTM7_V-F232Um3WQSUOXQ_itNRkLWgV2Kz537arfGWttjWszmXMvO-400MPhs2oZGcWhTrEokm6u__a99VoNwW80s";


//"/braver/storage/app/Images/"
    public function saveBase64Iamge($image,$domain){
        
            $ima = $image;
            $fileName =  rand(). date("h:i:s").'image.png';

            $ima = trim($ima);
            $ima = str_replace('data:image/png;base64,', '', $ima);
            $ima = str_replace('data:image/jpg;base64,', '', $ima);
            $ima = str_replace('data:image/jpeg;base64,', '', $ima);
            $ima = str_replace('data:image/gif;base64,', '', $ima);
            $ima = str_replace(' ', '+', $ima);
        
            $imageData = base64_decode($ima);
            //Set image whole path here 
            $filePath = $_SERVER['DOCUMENT_ROOT'].$domain. $fileName;

            // return $filePath;
            if(!Storage::exists($_SERVER['DOCUMENT_ROOT'].$domain)){
                Storage::makeDirectory($_SERVER['DOCUMENT_ROOT'].$domain);
            }
            file_put_contents($filePath, $imageData);
            return "Images/".$fileName;

        
    }

    public function Push_Notification($token,$data) {
        try{


            $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
            
            $extraNotificationData = ["message" => $data];
    
            $fcmNotification = [
                //'registration_ids' => $tokenList, //multple token array
                'to'        => $token, //single token
                'notification' => $data,
                'data' => $extraNotificationData
            ];
            //var_dump($fcmNotification); exit;
    
            $headers = [
                'Authorization: key='. $this->API_KEY_FCM,
                'Content-Type: application/json'
            ];
    
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            $result = curl_exec($ch);
            curl_close($ch);
            return true;

        }catch(\Illuminate\Database\QueryException $ex){
            return false;
        }
    }

    public static function sendFirebasePushNotification($toToken, $title, $body)
{
    try {
        // Initialize Firebase Messaging
        $firebaseCredentials = [
          'type' => 'service_account',
          'project_id' => env('FIREBASE_PROJECT_ID'),
          'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
          'private_key' => env('FIREBASE_PRIVATE_KEY'),
          'client_email' => env('FIREBASE_CLIENT_EMAIL'),
          'client_id' => env('FIREBASE_CLIENT_ID'),
          "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
          "token_uri"=> "https://oauth2.googleapis.com/token",
          "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
          "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-ccq3e%40proper-9d82d.iam.gserviceaccount.com",
          "universe_domain"=> "googleapis.com"
      ];
        $factory = (new Factory)->withServiceAccount($firebaseCredentials);
      $messaging = $factory->createMessaging();

        // Create the notification
        $notification = Notification::create($title, $body);

        // Create the Cloud Message
        $message = CloudMessage::withTarget('token', $toToken)
            ->withNotification($notification)
            ->withData([
                'customKey' => 'customValue' // Add any custom data here
            ]);

        // Send the message
        $result = $messaging->send($message);

        \Log::info('Push notification sent successfully: ' . json_encode($result));

        return $result;
    } catch (\Exception $e) {
        \Log::error('Error sending push notification: ' . $e->getMessage());
        return null;
    }
}

    public function createCheckrCandidate($data){
        $data["copy_requested"] = true;
        $data['no_middle_name'] = true;
        $api_key = env('chekrapikey');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/candidates');
        curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        $json = json_decode($response, true);
        return $json;
        if(array_key_exists('id', $json)){
            return $json['id'];
        }
        return NULL;
    }


    public function getchekrreportFromServer($user)
    {
       // echo "<pre>".print_r($_REQUEST,true)."</pre"; die();
       

        try {
            
            $userid = $user->userid;
           $getuser = $user;
        if ($getuser) {
            $chekr=$getuser['chekrreportid'];
            // if($chekr == null){
            //     $rep = new ReportController();
                
            //     $report = $rep->getCheckrReport($user->chekrcandidateid);
            //     // return $report;
            //     $chekr = $report['id'];
            // }
            // return "Checkr";
         if ($chekr != null) {
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/reports/'.$chekr);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArray = json_decode(json_encode($response), true);
  $ssnid=$myArray['ssn_trace_id'];
  $sexoid=$myArray['sex_offender_search_id'];
  $nationalid=$myArray['national_criminal_search_id'];
  $federalid=$myArray['federal_criminal_search_id'];
  $stateid=$myArray['state_criminal_search_ids'];
  //$docid=$myArray['document_ids'];
 
if ($ssnid != null) {
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/ssn_traces/'.$ssnid);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArrays = json_decode(json_encode($response), true);
  $ssnstatus=$myArrays['status'];
 
}else{
 $ssnstatus=null;   
}
if ($sexoid != null) {
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/sex_offender_searches/'.$sexoid);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArrayssex = json_decode(json_encode($response), true);
  $sexstatus=$myArrayssex['status'];
 
  
}else{
    $sexstatus=null;
}
if ($nationalid != null) {
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/national_criminal_searches/'.$nationalid);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArraysnat = json_decode(json_encode($response), true);
  $natstatus=$myArraysnat['status'];
 
  
}else{
    $natstatus=null;
}
if ($federalid != null) {
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/federal_criminal_searches/'.$federalid);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArraysfed = json_decode(json_encode($response), true);
  $fedstatus=$myArraysfed['status'];
 
  
}else{
    $fedstatus=null;
}if ($stateid) {
if ($stateid->count() > 0) {
            $sid=$stateid[0];
               $api_key=env('chekrapikey');  
          $curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/state_criminal_searches/'.$sid);
curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
 
$json = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
curl_close($curl);
   
  //$ssnid=$json['data']['ssn_trace_id'];
 //$ssnid=$json['ssn_trace_id'];
//var_dump($ssnid); exit;
$response = json_decode($json);

  $myArrayssta = json_decode(json_encode($response), true);
  $statestatus=$myArrayssta['status'];
 
  
}else{
    $statestatus=null;
}}else{
  $statestatus=null;  
}

$sarray=array();
$sarray['ssn_trace']=$ssnstatus;
$sarray['sex_offender_status']=$sexstatus;
$sarray['national_status']=$natstatus;
$sarray['federal_status']=$fedstatus;
$sarray['state_status']=$statestatus;

 User::where('userid',$getuser['userid'])->update(['ssn_trace'=> $ssnstatus,'sex_offender_status'=> $sexstatus,'national_status'=> $natstatus,'federal_status'=> $fedstatus,'state_status'=> $statestatus]);
  


//var_dump($stateid); exit;
 
 
//echo $ssnid
       

return $sarray;
// return $this->generate_response(true,"Success!",$sarray,200);

         }else{
            return "Candidate didn't create report until now.";
             // return $this->generate_response(true,"Candidate does not create report until now!",null);
         }
           
            
        }else{
            return "No user";
            // return $this->generate_response(true,"No user found!",null);  
        }
           
            
             
           
           
        } catch (\Illuminate\Database\QueryException $ex) {
            $ex_msg =$ex->getMessage();
            return $ex_msg;
            // return $this->generate_response(false,$ex_msg,null,500);
        
        }
    }
}
