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
            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
