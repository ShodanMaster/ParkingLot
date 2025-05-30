<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allocate extends Model
{
    protected $guarded = [];

    public static function codeGenerator($locationId){

        $location = Location::findOrFail($locationId);
        $location_name = strtolower(str_replace(' ', '_', $location->name));

        $lastAllocate = Allocate::where('location_id', $locationId)
                                ->orderBy('created_at', 'desc')
                                ->first();

        $lastNumber = $lastAllocate ? (int) substr($lastAllocate->barcode, -6) : 0;
        $nextNumber = $lastNumber + 1;

        $nextBarcode = 'pl_' . $location_name .'_'. str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return $nextBarcode;
    }
}
