<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validate the fields
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'nullable|string',
            'password' => 'required|string|confirmed|min:8',
        ] , [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        // check if the user role is provided
        if (isset($fields['role'])) {
            // check if the role is valid
            if (!in_array($fields['role'], ['admin', 'user'])) {
                return response()->json([
                    'message' => 'Invalid role'
                ], 400);
            }
        } else {
            // set the default role to user
            $fields['role'] = 'user';
        }

        // create the user
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'role' => $fields['role'],
            'password' => Hash::make($fields['password']),
        ]);
        // create a token for the user
        $token  = $user->createToken($user->email)->plainTextToken;

        // return the user and token in form of json
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // validate the fields
        $fields = $request->validate([
            'email'=>'required|email|exists:users,email',
            'password'=>'required|string',
        ],[
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.exists' => 'Email does not exist',
            'password.required' => 'Password is required',
        ]);
        // check if the user exists
        $user = User::where('email', $fields['email'])->first();
        if(!$user || !Hash::check($fields['password'] , $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // create a token for the user
        $token = $user->createToken($user->email)->plainTextToken;
        // return the user and token in form of json
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Handle user logout
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function user(Request $request)
    {
        // Return authenticated user
        return response()->json($request->user());
    }
}
