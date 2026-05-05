<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed: ' . $e->getMessage());
        }

        // Find or create the user
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Check if user with same email exists
            $userByEmail = User::where('email', $googleUser->getEmail())->first();

            if ($userByEmail) {
                // Link Google account to existing user
                $userByEmail->update([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'display_name' => $googleUser->getName(),
                    'email_verified_at' => now(), // Assume Google emails are verified
                ]);
                $user = $userByEmail;
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'display_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(16)), // Required field, but users will use Google
                    'role' => 'user', // Default role
                    'email_verified_at' => now(), // Assume Google emails are verified
                ]);
            }
        } else {
            // Update user info from Google
            $user->update([
                'avatar_url' => $googleUser->getAvatar(),
                'display_name' => $googleUser->getName(),
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
