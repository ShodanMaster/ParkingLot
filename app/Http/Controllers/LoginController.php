<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        return view('auth.login');
    }

    public function logingIn(Request $request){

        $credentials = $request->only('username', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            return response()->json([
                'status' => 200,
                'message' => 'Login Successful',
                'route' => route('dashboard')
            ]);
        }

        return response()->json([
            'status' => 401,
            'message' => 'Invalid credentials. Please try again.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
