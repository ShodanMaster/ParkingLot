<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Allocate;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AllocateController extends Controller
{
    public function index(){
        $vehicles = Vehicle::orderBy('name')->get();
        $allocates = Allocate::limit(5)->latest()->get();
        return view('transaction.allocate', compact( 'vehicles', 'allocates'));
    }

    
}
