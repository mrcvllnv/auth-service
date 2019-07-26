<?php

namespace AuthServer\Http\Controllers\Api;

use AuthServer\User;
use AuthServer\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register()
    {
        User::create(request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]));

        return response()->json(['created' => true], 201);
    }

    public function login()
    {
        request()->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        
        $credentials = request(['email', 'password']);

        if (auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = User::whereEmail(request()->email)->first()->createToken(request()->email)->accessToken;
        
        return response()->json(['token' => $token]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(['user' => auth()->user()]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->user()->token()->revoke();

        return response()->json(['logged_out' => true]);
    }
}
