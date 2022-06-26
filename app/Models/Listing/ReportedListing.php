<?php

namespace App\Models\Listing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportedListing extends Model
{
    use HasFactory;
    protected $table = "report";
    public $timestamps = false;
}
