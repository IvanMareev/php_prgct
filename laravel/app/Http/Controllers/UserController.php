<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => ['message' => 'The provided credentials are incorrect.']
            ]);

        }

        $token = $user->createToken('login')->plainTextToken;

        return response()->json([
            'access_token' => $token,
        ]);

    }
}
