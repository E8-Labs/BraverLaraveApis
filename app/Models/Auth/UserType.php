<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;
    const TypeUser = "USER";
    const TypeTeam = "TEAM";
    const TypeAdmin = "ADMIN";
    const TypeFreeUser = "FREEUSER";
}
