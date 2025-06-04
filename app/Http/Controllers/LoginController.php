<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index(){
        return view('auth.login');
    }

    public function logingIn(Request $request){
        try {
            $user = $this->authService->attemptLogin($request->only('username', 'password'), $request->has('remember'));

            return response()->json([
                'status' => 200,
                'message' => 'Login Successful',
                'route' => route('dashboard')
            ]);

        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage()
            ], 401);
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
