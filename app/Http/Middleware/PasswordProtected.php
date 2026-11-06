<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

class PasswordProtected
{
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        $password = config('rave.password_protection');

        if (empty($password) ||
            $this->isNotInProtectedOnlyPath($request)) {
            return $next($request);
        }

        $passwords = explode(',', $password);

        if (in_array($request->get('password'), $passwords)) {
            setcookie('password', encrypt($request->get('password')), 0, '/');

            return redirect($request->url());
        }

        try {
            $usersPassword = decrypt(\Arr::get($_COOKIE, 'password'));
            if (in_array($usersPassword, $passwords)) {
                return $next($request);
            }
        } catch (DecryptException $e) {
            // empty value in cookie
        }

        return response(view('password-protection.password'), 403);
    }

    private function isNotInProtectedOnlyPath($request)
    {
        return false;
    }
}
