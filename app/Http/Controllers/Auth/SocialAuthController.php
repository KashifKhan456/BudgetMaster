<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->intended('/dashboard');
            } else {
                // Check if user with this email already exists
                $existingUser = User::where('email', $googleUser->email)->first();

                if ($existingUser) {
                    // Link Google account to existing user
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                    Auth::login($existingUser);
                } else {
                    // Create new user
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'password' => bcrypt(Str::random(16)), // Random password
                    ]);
                    Auth::login($newUser);
                }

                return redirect()->intended('/dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Something went wrong with Google Login.');
        }
    }
}
