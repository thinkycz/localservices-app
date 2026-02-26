<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $allowedProviders = ['google', 'facebook'];

        if (! in_array($provider, $allowedProviders)) {
            return redirect()->route('login')
                ->with('error', 'Invalid login provider.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        $allowedProviders = ['google', 'facebook'];

        if (! in_array($provider, $allowedProviders)) {
            return redirect()->route('login')
                ->with('error', 'Invalid login provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Unable to login with ' . ucfirst($provider) . '. Please try again.');
        }

        // Check if user exists with this email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link social account if not already linked
            if (! $user->social_provider) {
                $user->update([
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Create new user
        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(24)),
            'social_provider' => $provider,
            'social_id' => $socialUser->getId(),
            'email_verified_at' => now(), // Social logins are pre-verified
        ]);

        Auth::login($user, true);

        return redirect()->route('home')
            ->with('success', 'Welcome to Local Services! Your account has been created.');
    }

    /**
     * Link social account to existing user.
     */
    public function link(string $provider): RedirectResponse
    {
        $allowedProviders = ['google', 'facebook'];

        if (! in_array($provider, $allowedProviders)) {
            return back()->with('error', 'Invalid provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to link account. Please try again.');
        }

        $user = Auth::user();

        // Check if another user has this social account
        $existing = User::where('social_provider', $provider)
            ->where('social_id', $socialUser->getId())
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'This ' . ucfirst($provider) . ' account is already linked to another user.');
        }

        $user->update([
            'social_provider' => $provider,
            'social_id' => $socialUser->getId(),
        ]);

        return back()->with('success', ucfirst($provider) . ' account linked successfully.');
    }

    /**
     * Unlink social account.
     */
    public function unlink(string $provider): RedirectResponse
    {
        $user = Auth::user();

        if ($user->social_provider !== $provider) {
            return back()->with('error', 'This account is not linked to ' . ucfirst($provider));
        }

        // Don't allow unlinking if user has no password
        if (empty($user->password) || $user->password === Hash::make('')) {
            return back()->with('error', 'Please set a password before unlinking your social account.');
        }

        $user->update([
            'social_provider' => null,
            'social_id' => null,
        ]);

        return back()->with('success', ucfirst($provider) . ' account unlinked successfully.');
    }
}
