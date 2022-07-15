<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;
    protected $primaryKey = 'yachtid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'yacht';
    public $timestamps = false;
}
