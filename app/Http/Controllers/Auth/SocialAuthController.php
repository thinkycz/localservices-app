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

        if (Auth::check() && session('social_linking') === $provider) {
            session()->forget('social_linking');

            $user = Auth::user();

            $existing = User::where('social_provider', $provider)
                ->where('social_id', $socialUser->getId())
                ->where('id', '!=', $user->id)
                ->first();

            if ($existing) {
                return redirect()->route('profile.edit')
                    ->with('error', 'This ' . ucfirst($provider) . ' account is already linked to another user.');
            }

            $user->update([
                'social_provider' => $provider,
                'social_id' => $socialUser->getId(),
            ]);

            return redirect()->route('profile.edit')
                ->with('success', ucfirst($provider) . ' account linked successfully.');
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

            $user->forceFill(['last_login_at' => now()])->save();
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'))
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
            'has_local_password' => false,
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return redirect()->route('dashboard')
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

        session(['social_linking' => $provider]);

        return Socialite::driver($provider)->redirect();
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

        if (! $user->has_local_password) {
            return back()->with('error', 'Please set a password before unlinking your social account.');
        }

        $user->update([
            'social_provider' => null,
            'social_id' => null,
        ]);

        return back()->with('success', ucfirst($provider) . ' account unlinked successfully.');
    }
}
