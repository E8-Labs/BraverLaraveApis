<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingTypes extends Model
{
    use HasFactory;
    const TypeYacht = 'yacht';
    const TypeTrip = "Trip";
    const TypeRestaurant = "Restaurant";
    const TypeClub = "Club";
    const TypeExperience = "Experience";
    const TypeVilla = "Villa";
    const TypeCustom = "Custom";
 }
