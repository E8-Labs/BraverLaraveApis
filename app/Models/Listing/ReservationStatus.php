<?php

namespace App\Models\Listing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    use HasFactory;
    const StatusReserved = "Reserved";
    const StatusPendingPayment = "PendingPayment";
    const StatusCancedlled = "Cancelled";
    const StatusRefunded = "Refunded";
    const StatusPendingRefund = "PendingRefund";
}
