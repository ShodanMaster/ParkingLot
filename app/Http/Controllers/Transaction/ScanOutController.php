<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScanOutController extends Controller
{
    public function index(){
        return view('transaction.scanout');
    }

    public function scanOut(Request $request){
        dd($request->all());
    }
}
