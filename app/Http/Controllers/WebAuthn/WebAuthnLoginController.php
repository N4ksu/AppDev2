<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
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
            if ($user && $user->is_locked) {
                throw ValidationException::withMessages([
                    'email' => 'This account has been restricted for security reasons. Please contact an administrator.',
                ]);
            }
        }

        return $request->toVerify($validated);
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request): BaseResponse
    {
        // Resolve the user from the assertion BEFORE logging in
        $user = User::where('email', $request->validated()['email'] ?? null)
            ->orWhereHas('webAuthnCredentials', function ($q) use ($request) {
                // Fallback: look up by credential ID in the request
                $q->where('id', $request->input('id'));
            })
            ->first();

        // Enforce account lock — block even if options() pre-check was bypassed
        if ($user && $user->is_locked) {
            \App\Models\LoginLog::create([
                'user_id'      => $user->id,
                'email'        => $user->email,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'status'       => 'failed',
                'action'       => 'login',
                'login_method' => 'passkey',
                'risk_score'   => 80,
                'risk_level'   => 'high_risk',
                'action_taken' => 'denied',
            ]);

            return response()->json([
                'message' => 'This account is locked. Contact an administrator to restore access.',
            ], 422);
        }

        $success = $request->login();

        return response()->noContent($success ? 204 : 422);
    }
}
