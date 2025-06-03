<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(){
        $locations = Location::all();
        return view('index', compact('locations'));
    }
    public function dashboard()
    {
        $locations = Location::all();
        return view('dashboard', compact('locations'));
    }
}
