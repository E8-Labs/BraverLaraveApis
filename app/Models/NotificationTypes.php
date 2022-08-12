<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTypes extends Model
{
    use HasFactory;
    const TypeMessage = 1;
    const TypeReservation = 2;
    const InvoicePaid = 3;
    const NewUser = 4;
    const AdminBroadcast = 5;
    const TypeReservationCancelled = 2;

}
