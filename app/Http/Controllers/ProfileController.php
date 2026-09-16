<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $data = UserResource::make($request->user());

        return response()->success($data, 'profile details', 200);
    }

    public function setPfp(Request $request)
    {
        // to be implemented in the next version, i hope
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'min:2|string',
            'lastName' => 'string',
            'password' => 'min:6|string',
        ]);
        $request->user()->update($validated);
        $data = UserResource::make($request->user());

        return response()->success($data, 'Profile updated.', 200);
    }

    public function deleteAccount(Request $request, string $password)
    {
        $user = $request->user();
        if (!Hash::check($password, $user->password)){
            return response()->error('Incorrect password', 403);
        }
        $user->tokens()->delete();
        $user->delete();
        $data = UserResource::make($user);

        return response()->success($data, 'Account deleted.', 200);
    }
}
