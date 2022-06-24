<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model
{
    use HasFactory;
    const Pending = "Pending";
    const Approved = "Approved";
    const Deleted = "Deleted";
}
