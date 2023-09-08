<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::attempt(['email' => request()->email, 'password' => request()->password])) 
        {
            return response()->json([
              'success' => true,
              'token' => request()->user()->createToken(config('settings.token_name'))->plainTextToken,
            ]);

        } 
        else 
        {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 422);
        }
    }


    public function register()
    {
        request()->validate([
            'email' => ['required','string','email','max:255','unique:App\Models\User',],
            'password' => ['bail', 'required', 'string', 'confirmed'],
            'password_confirmation' => ['bail', 'required', 'string'],
            ]);

        $user = new User;
        
		$user->email = request()->email;

		$user->password = request()->password;

        $user->created_at = now();

		$user->save();

        return response([
            'token' => $user->createToken(config('settings.token_name'))->plainTextToken, 
            'success' => true
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();
		
		request()->session()->invalidate();

        request()->session()->regenerateToken();

        return response([
            'success' => true
        ]);
    }

}

