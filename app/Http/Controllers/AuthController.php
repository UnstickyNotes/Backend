<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AccessTokens;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'firstName' => 'required|min:2|string',
            'lastName' => 'string',
            'email' => 'required|string|email',
            'password' => 'required|min:6|string',
        ]);
        $user = User::create($validated);
        return response()->success($user, 'User created', 200);
    }

    public function login(Request $request){
        $validated = $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string'
        ]);
        $user = User::where('email', $validated['email'])->first();

        if(!$user || !Hash::check($validated['password'], $user->password)){
            return response()->error('Invalid credentials or not registered', 404);
        }
        if(AccessTokens::where('tokenable_id', $user->id)->first()){
            $user->tokens()->delete();
        }

        $accessToken = $user->createToken('accessToken')->plainTextToken;
        $data = [
            'type' => 'Bearer',
            'token' => $accessToken
        ];
        return response()->success($data,'Loged in.', 200);
    }

    public function googleOAuth($provider){
        return Socialite::driver($provider)->stateless()->redirect();
    }
        
        public function googleOAuthCallback($provider){
            $returnedUser = Socialite::driver($provider)->stateless()->user();
            
            if($user = User::where('email', $returnedUser->getEmail())->first()){
                $user->tokens()->delete();
                $accessToken = $user->createToken('accessToken')->plainTextToken;
            } else {
                $name = explode(' ', $returnedUser->getName());
                $firstName = $name[0] ?? explode('@', $returnedUser->getEmail()[0]);
                $lastName = $name[1] ?? '';
                $user = User::create([
                    'OAuthProvider'=>'google',
                    'OAuthProviderId'=>$returnedUser->getId(),
                    'firstName'=> $firstName,
                    'lastName'=> $lastName,
                    'email' => $returnedUser->getEmail(),
                    'email_verified_at'=>now(),
                    'avatarUrl'=>$returnedUser->getAvatar()
                ]);
                $accessToken = $user->createToken('accessToken')->plainTextToken;
            }
            $data = [
                'type' => 'Bearer',
                'token' => $accessToken
            ];
        return response()->success($data, 'Logged in via google', 200);
    }

    public function logout(Request $request){
        if($request->user()){
            Auth()->user()->tokens()->delete();
            return response()->success([],$message = 'Logged out', 200);
        }
        return response()->error('you wish to see the darkness before the light, fool.(login first nigga', 403);
    }
}
