<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\NotificationTypes;
use App\Models\Listing\Reservation;
use App\Models\Listing\ReservationStatus;

use App\Models\Listing;
use App\Http\Controllers\Controller;





class Notification extends Model
{
    use HasFactory;
    protected $table = "notifications";
    protected $fillable = ['notification_type', 'from_user', 'to_user', 'notifiable_id', 'notifiable_type', 'message'];


public static function add(int $notification_type, string $from_user, string $to_user = NULL, $notification_for = null, string $message = null)
    {
        \Log::info("Adding Notification ");
        \Log::info($notification_type);
        $notifiable_type = null;
        $notifiable_id   = null;

        if ($notification_for) {
            $primary_key = $notification_for->getKeyName();
            $notifiable_type  = get_class($notification_for);
            $notifiable_id    = $notification_for->$primary_key;
        }

        try{
            $notification = self::create([
                'notification_type' => $notification_type,
                'from_user'         => $from_user,
                'to_user'           => $to_user,
                'notifiable_id'     => $notifiable_id,
                'notifiable_type'   => $notifiable_type,
                'message'           => $message,
            ]);
            // self::sendFirebasePushNotification($notification);
            // self::Push_Notification($notification);
            $result = $this->sendPushNotification($toToken, $title, $body);

            if ($result) {
                \Log::info('Push notification sent successfully.');
                
            } else {
                \Log::error('Failed to send push notification.');
            }
            \Log::info($result);
            return $notification;
        }
        catch(\Exception $e){
            \Log::info("Exception ");
            \Log::info($e);
            return null;
        }
    }

    //Firebase New HTTP v1 api
    public static function Push_Notification($notification) {
        $sendToUser = User::where('userid', $notification->to_user)->first();

    if (isset($sendToUser->fcmtoken) && $sendToUser->fcmtoken) {
        try {
            // Define Firebase credentials
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

            // Get the OAuth2 token
            $accessToken = self::getFirebaseAccessTokenFromCredentials($firebaseCredentials);

            // Prepare the payload
            $data = [
                "message" => [
                    "token" => $sendToUser->fcmtoken,
                    "notification" => [
                        "title" => $notification->getTitleAttribute(),
                        "body" => $notification->getSubtitleAttribute(),
                        'data' => $notification,
                    ],
                    'data' => $notification,
                ]
            ];

            // Prepare the headers
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            // Send the notification
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/your-project-id/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                \Log::error('Curl error: ' . curl_error($ch));
            }

            curl_close($ch);

            \Log::info("Push notification sent successfully. Result: " . $result);

            return $result;
        } catch (\Exception $e) {
            \Log::error("Push notification failed: " . $e->getMessage());
        }
    }
    }

    /**
 * Get OAuth2 Token for Firebase HTTP v1 API from credentials array.
 */
private static function getFirebaseAccessTokenFromCredentials($credentials)
{
    $now = time();
    $token = [
        "iss" => $credentials['client_email'],
        "sub" => $credentials['client_email'],
        "aud" => "https://oauth2.googleapis.com/token",
        "iat" => $now,
        "exp" => $now + 3600,
        "scope" => "https://www.googleapis.com/auth/firebase.messaging"
    ];

    $jwt = self::generateJWT($token, $credentials['private_key']);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]));

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    \Log::info("Access Token Response: " . json_encode($response));

    return $response['access_token'] ?? null;
}


/**
 * Generate JWT for Firebase.
 */
private static function generateJWT($payload, $privateKey)
{
    $header = [
        "alg" => "RS256",
        "typ" => "JWT"
    ];

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $privateKey, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}


    public static function sendFirebasePushNotification(Notification $notification)
    {
        \Log::info($notification);
        $API_KEY_FCM = "AAAAH8FOIuk:APA91bH4ZOKCMWE3iQa3qDvvrx4FC5nt3YJhCzKePhThvK1mFt1nTM7_V-F232Um3WQSUOXQ_itNRkLWgV2Kz537arfGWttjWszmXMvO-400MPhs2oZGcWhTrEokm6u__a99VoNwW80s";
        $sendToUser = User::where('userid', $notification->to_user)->first();
        if (isset($sendToUser->fcmtoken) && $sendToUser->fcmtoken)
        {
            \Log::info("Sending push to ". $sendToUser->fcmtoken);
            $SERVER_API_KEY = env('FCM_SERVER_API_KEY');
            // $message = $notification->getMessageAttribute();
            $data = [
                "registration_ids" => [$sendToUser->fcmtoken],
                "notification" => [
                    "title" => $notification->getTitleAttribute(),
                    "body" => $notification->getSubtitleAttribute(),
                    'data' => $notification,
                ]
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization: key=' . $API_KEY_FCM,
                'Content-Type: application/json',
            ];
            

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $result = curl_exec($ch);
            \Log::info("Sending push result");
            \Log::info($result);
            return $result;
        }
        return null;
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function getTitleAttribute()
    {
        $title = "";

        switch ($this->notification_type) {
            case NotificationTypes::NewUser:
                $title = "New User!";
                break;
            case NotificationTypes::TypeMessage:
                $title = "Message";
                break;
            case NotificationTypes::TypeReservation:
                $title = "New Reservation";
                break;
            case NotificationTypes::InvoicePaid:
                $title = "Invoice paid";
                break;
            case NotificationTypes::AdminBroadcast:
                $title = "New Broadcast";
                break;
            case NotificationTypes::TypeReservationCancelled:
                $title = "Reservation cancelled";
                break;
            case NotificationTypes::TeamMemberReservationInvite:
                $title = "Reservation invitation";
                break;
            case NotificationTypes::AccountApproved:
                $title = "Congrats";
                break;
            
        }

        return $title;
    }

    public function getSubtitleAttribute()
    {
        $message = "";
        $from = User::where('userid', $this->from_user)->first();
        $to = User::where('userid', $this->to_user)->first();
        switch ($this->notification_type) {
            case NotificationTypes::NewUser:
                $message = $from->name . " just registered";
                break;
            case NotificationTypes::TypeMessage:
                $message = $from->name . " sent you a message";
                break;
            case NotificationTypes::TypeReservation:
                $res = Reservation::where('reservationid', $this->notifiable_id)->first();
                $message = $from->name . " sent a reservation request";
                if($res){
                    $yid = $res->yachtid;
                    $yacht = Listing::where('yachtid', $yid)->first();
                    if($yacht){
                        $message = " request to reserve ". $yacht->type . " (". $yacht->yachtname . ")";
                    }
                    else{
                        $message = " request to reserve ". $yid;
                    }
                    
                }
                break;
            case NotificationTypes::InvoicePaid:
                $message = $from->name . " paid the invoice";
                break;
            case NotificationTypes::AdminBroadcast:
                $message = $this->message;
                break;
            case NotificationTypes::TeamMemberReservationInvite:
                $message = "Admin invited you to manage a reservation";
                break;
            case NotificationTypes::AccountApproved:
                $message = "Your Braver account has been approved.";
                break;
        }
        return $message;
    }
}
