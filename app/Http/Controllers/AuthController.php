<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\AccessTokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|min:2|string',
            'lastName' => 'string',
            'email' => 'required|string|email',
            'password' => 'required|min:6|string',
        ]);
        $user = UserResource::make(User::create($validated));

        return response()->success($user, 'User created', 200);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->error('Invalid credentials or not registered', 404);
        }
        // if (AccessTokens::where('tokenable_id', $user->id)->first()) { // delete existing token 
        //     $user->tokens()->delete();
        // } 

        $accessToken = $user->createToken('accessToken')->plainTextToken;
        $data = [
            'type' => 'Bearer',
            'token' => $accessToken,
        ];

        return response()->success($data, 'Logged in.', 200);
    }

    public function googleOAuth($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function googleOAuthCallback($provider)
    {
        try{
            $returnedUser = Socialite::driver($provider)->stateless()->user();
            
            if ($user = User::where('email', $returnedUser->getEmail())->first()) {
                $user->tokens()->delete();
                $accessToken = $user->createToken('accessToken')->plainTextToken;
            } else {
                $name = explode(' ', $returnedUser->getName());
                $firstName = $name[0] ?? explode('@', $returnedUser->getEmail()[0]);
                $lastName = $name[1] ?? '';
                $user = User::create([
                    'OAuthProvider' => 'google',
                    'OAuthProviderId' => $returnedUser->getId(),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $returnedUser->getEmail(),
                    'email_verified_at' => now(),
                    'avatarUrl' => $returnedUser->getAvatar(),
                ]);
                $accessToken = $user->createToken('accessToken')->plainTextToken;
            }
        $token = $user->createToken('unstickynotes-token')->plainTextToken;
        $deepLinkUrl = "unstickynotes://oauth/callback?token={$token}";

    // Return HTML page with JS auto-redirect and a fallback button
        return response()->make("
            <!DOCTYPE html>
            <html>
            <head>
                <title>Authentication Successful</title>
                <style>
                    body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #18181b; color: #fff; margin: 0; }
                    .card { text-align: center; padding: 2rem; background: #27272a; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
                    a { color: #38bdf8; text-decoration: none; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class=\"card\">
                    <h2>Login Successful!</h2>
                    <p>Redirecting you back to <strong>UnstickyNotes</strong>...</p>
                    <p>If the app does not open automatically, <a href=\"{$deepLinkUrl}\">click here</a>.</p>
                </div>
                <script>
                    // Trigger deep link redirect immediately
                    window.location.href = \"{$deepLinkUrl}\";
                    
                    // Close browser tab after handoff if allowed
                    setTimeout(() => {
                        window.close();
                    }, 2000);
                </script>
            </body>
            </html>
        ", 200, ['Content-Type' => 'text/html']);
            // return redirect('unstickynotes://oauth/callback?token=' . $accessToken);
        }catch(Throwable){
            return redirect('unstickynotes://oauth/?error=Google+sign-in+failed');
        }
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            Auth()->user()->tokens()->delete();

            return response()->success([], $message = 'Logged out', 200);
        }

        return response()->error('you wish to see the darkness before the light, fool.(login first nigga', 403);
    }
}
