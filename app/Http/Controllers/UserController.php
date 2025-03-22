<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all()->load(['sector']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user->load(['sector']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'sector_id' => 'required|exists:sectors,id',
        ] ,[
            'sector_id.exists' => 'The selected sector is invalid.'
        ]);

        $user->update($validated);

        return response()->json($user->load(['sector']));
    }

    /**
     * Update the user password
     *
     */
    public function updatePassword(Request $request, User $user)
    {
        // Authorize the user for password updates
        Gate::authorize('update', $user);

        // Validate the password input
        $validated = $request->validate([
            'current_password' => 'required', // Require the user's current password for verification
            'new_password' => 'required|string|min:8', // Require a new password with confirmation
        ]);

        // Verify the current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The provided current password is incorrect.',
            ], 400);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json( $user->load(['sector']) , 200);
    }
    public function destroy(User $user)
    {
        Gate::authorize('update', $user);
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
