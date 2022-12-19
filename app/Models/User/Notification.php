<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\NotificationTypes;
use App\Models\Listing\Reservation;
use App\Models\Listing\ReservationStatus;

use App\Models\Listing;
class Notification extends Model
{
    use HasFactory;
    protected $table = "notifications";
    protected $fillable = ['notification_type', 'from_user', 'to_user', 'notifiable_id', 'notifiable_type', 'message'];


public static function add(int $notification_type, string $from_user, string $to_user = NULL, $notification_for = null, string $message = null)
    {
        $notifiable_type = null;
        $notifiable_id   = null;

        if ($notification_for) {
            $primary_key = $notification_for->getKeyName();
            $notifiable_type  = get_class($notification_for);
            $notifiable_id    = $notification_for->$primary_key;
        }

        $notification = self::create([
            'notification_type' => $notification_type,
            'from_user'         => $from_user,
            'to_user'           => $to_user,
            'notifiable_id'     => $notifiable_id,
            'notifiable_type'   => $notifiable_type,
            'message'           => $message,
        ]);
        self::sendFirebasePushNotification($notification);
        return $notification;
    }

    public function Push_Notification($token,$data) {
        try{

            $API_KEY_FCM = "AAAAH8FOIuk:APA91bH4ZOKCMWE3iQa3qDvvrx4FC5nt3YJhCzKePhThvK1mFt1nTM7_V-F232Um3WQSUOXQ_itNRkLWgV2Kz537arfGWttjWszmXMvO-400MPhs2oZGcWhTrEokm6u__a99VoNwW80s";

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
                'Authorization: key='. $API_KEY_FCM,
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


    public static function sendFirebasePushNotification(Notification $notification)
    {
        \Log::info($notification);
        $API_KEY_FCM = "AAAAH8FOIuk:APA91bH4ZOKCMWE3iQa3qDvvrx4FC5nt3YJhCzKePhThvK1mFt1nTM7_V-F232Um3WQSUOXQ_itNRkLWgV2Kz537arfGWttjWszmXMvO-400MPhs2oZGcWhTrEokm6u__a99VoNwW80s";
        $sendToUser = User::where('userid', $notification->to_user)->first();
        if (isset($sendToUser->fcmtoken) && $sendToUser->fcmtoken)
        {
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

            return curl_exec($ch);
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
        }
        return $message;
    }
}
