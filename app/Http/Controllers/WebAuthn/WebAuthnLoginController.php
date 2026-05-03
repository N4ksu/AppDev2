<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

use Illuminate\Validation\ValidationException;
use App\Models\User;

use function response;

class WebAuthnLoginController
{
    /**
     * Returns the challenge to assertion.
     */
    public function options(AssertionRequest $request): Responsable
    {
        $validated = $request->validate(['email' => 'sometimes|email|string']);
        
        if (isset($validated['email'])) {
            $user = User::where('email', $validated['email'])->first();
            if ($user && $user->is_locked && ($user->locked_until === null || now()->lessThan($user->locked_until))) {
                throw ValidationException::withMessages([
                    'email' => 'This account has been locked due to security monitoring.',
                ]);
            }
        }

        return $request->toVerify($validated);
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request): Response
    {
        return response()->noContent($request->login() ? 204 : 422);
    }
}
