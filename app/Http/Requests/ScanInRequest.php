<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Location;
use App\Models\Allocate;

class ScanInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicleNumber' => 'required|string',
            'vehicleId' => 'required|integer|exists:vehicles,id',
            'locationId' => 'required|integer|exists:locations,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('locationId')) {
                $location = Location::find($this->locationId);

                if (!$location) {
                    $validator->errors()->add('locationId', 'Location not found.');
                    return;
                }

                $totalSlots = $location->slot;

                $occupiedSlots = Allocate::where('location_id', $this->locationId)
                    ->whereNull('out_time')
                    ->count();

                $availableSlots = $totalSlots - $occupiedSlots;

                if ($availableSlots <= 0) {
                    $validator->errors()->add('locationId', 'No available slots at this location.');
                }
            }
        });
    }
}
