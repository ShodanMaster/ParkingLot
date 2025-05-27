<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = [];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class);
    }
}
