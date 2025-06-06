<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function locations(){
        return $this->hasMany(Location::class);
    }
}
