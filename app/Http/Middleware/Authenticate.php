<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * On top of the default authentication check, this also kills the
     * session for accounts that have been suspended after they logged in.
     * Without this, an admin suspending a user has no effect until that
     * user's existing session naturally expires or they log out.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        parent::authenticate($request, $guards);

        $user = $request->user();

        if ($user && ($user->suspended ?? false)) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            throw new AuthenticationException(
                'Ce compte a été suspendu.', $guards, $this->redirectTo($request)
            );
        }
    }
}
