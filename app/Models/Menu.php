<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menu';



    public static function getIdForMenu($title){
    	$id = "Proper";
            if($title === "PLAN A TRIP"){
                $id = "Trip";
            }
            else if($title === "EXPERIENCES"){
                $id = "Experience";
            }
            else if($title === "VILLAS"){
                $id = "Villa";
            }
            else if($title === "YACHTS"){
                $id = "Yacht";
            }
            else if($title === "RESTAURANTS"){
                $id = "Restaurant";
            }
            else if($title === "CLUBS"){
                $id = "Club";
            }
            else if($title === "CUSTOM"){
                $id = "Custom";
            }
            else if($title === "PROPER STORE"){
                $id = "Proper";
            }

            return $id;
    }
}
