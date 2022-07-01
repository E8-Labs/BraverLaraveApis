<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
            return $domain.$fileName;

        
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


    // public function uploads($file, $path)
    // {
    //     if($file) {

    //         $fileName   = time() . $file->getClientOriginalName();
    //         Storage::disk('public')->put($path . $fileName, File::get($file));
    //         $file_name  = $file->getClientOriginalName();
    //         $file_type  = $file->getClientOriginalExtension();
    //         $filePath   = 'storage/'.$path . $fileName;

    //         return $file = [
    //             'fileName' => $file_name,
    //             'fileType' => $file_type,
    //             'filePath' => $filePath,
    //             'fileSize' => $this->fileSize($file)
    //         ];
    //     }
    // }

    // public function fileSize($file, $precision = 2)
    // {   
    //     $size = $file->getSize();

    //     if ( $size > 0 ) {
    //         $size = (int) $size;
    //         $base = log($size) / log(1024);
    //         $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
    //         return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    //     }

    //     return $size;
    // }
}
