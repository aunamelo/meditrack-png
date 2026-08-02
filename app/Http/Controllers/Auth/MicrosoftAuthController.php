<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PortalLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class MicrosoftAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! PortalLoginService::microsoftConfigured()) {
            return back()->withErrors([
                'email' => 'Microsoft 365 sign-in is not configured yet. Use email and password, or ask an administrator to set Azure app credentials.',
            ]);
        }

        $role = $request->query('role');
        if (is_string($role) && $role !== '') {
            $request->session()->put('microsoft_oauth_role', $role);
        } else {
            $request->session()->forget('microsoft_oauth_role');
        }

        return Socialite::driver('microsoft')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! PortalLoginService::microsoftConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Microsoft 365 sign-in is not configured.']);
        }

        $portalSlug = $request->session()->pull('microsoft_oauth_role');

        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
        } catch (Throwable $e) {
            Log::warning('Microsoft 365 OAuth failed', ['message' => $e->getMessage()]);

            return redirect()
                ->route('login', array_filter(['role' => $portalSlug]))
                ->withErrors(['email' => 'Microsoft 365 sign-in was cancelled or failed. Please try again.']);
        }

        $email = strtolower(trim((string) ($microsoftUser->getEmail() ?: $microsoftUser->user['mail'] ?? $microsoftUser->user['userPrincipalName'] ?? '')));

        if ($email === '') {
            return redirect()
                ->route('login', array_filter(['role' => $portalSlug]))
                ->withErrors(['email' => 'Microsoft 365 did not return an email address for this account.']);
        }

        $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return redirect()
                ->route('login', array_filter(['role' => $portalSlug]))
                ->withErrors([
                    'email' => 'No MediTrack account exists for '.$email.'. Ask your administrator to provision access first.',
                ]);
        }

        if ($user->trashed()) {
            return redirect()
                ->route('login', array_filter(['role' => $portalSlug]))
                ->withErrors([
                    'email' => 'This MediTrack account has been deactivated. Contact an administrator if you need access restored.',
                ]);
        }

        try {
            PortalLoginService::assertPortalRole($user, is_string($portalSlug) ? $portalSlug : null);
        } catch (ValidationException $e) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = collect($e->errors()['email'] ?? [])->first() ?: 'You do not have access to this portal.';

            return redirect()
                ->route('login', array_filter(['role' => $portalSlug]))
                ->withErrors(['email' => $message]);
        }

        if ($user->name === null || $user->name === '' || $user->name === $user->email) {
            $displayName = $microsoftUser->getName();
            if (filled($displayName)) {
                $user->forceFill(['name' => $displayName])->save();
            }
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return PortalLoginService::completeLogin($request, $user);
    }
}
