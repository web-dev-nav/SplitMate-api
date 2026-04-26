<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const RESET_CODE_EXPIRY_MINUTES = 15;

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ApiPayload::user($user),
        ], 201);
    }

    /**
     * Login user and return token.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ApiPayload::user($user),
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(ApiPayload::user($user));
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Send a one-time email verification code.
     */
    public function sendVerificationCode(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::raw(
                "Your SplitMate verification code is: {$code}. It expires in 15 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('SplitMate Email Verification');
                }
            );
        } catch (\Throwable $e) {
            // Allow local/dev flows even when mail is not configured.
        }

        $response = [
            'message' => 'Verification code sent.',
        ];

        if (app()->environment('local')) {
            $response['debug_code'] = $code;
        }

        return response()->json($response);
    }

    /**
     * Verify current user's email using a one-time code.
     */
    public function verifyEmailCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified.',
                'user' => ApiPayload::user($user),
            ]);
        }

        $record = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $record->update([
            'used_at' => now(),
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => ApiPayload::user($user->fresh()),
        ]);
    }

    /**
     * Send a one-time password reset code to the user's email.
     */
    public function sendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user) {
            return response()->json([
                'message' => 'If the account exists, a password reset code has been sent.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        PasswordResetCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::RESET_CODE_EXPIRY_MINUTES),
        ]);

        try {
            Mail::raw(
                "Your SplitMate password reset code is: {$code}. It expires in ".self::RESET_CODE_EXPIRY_MINUTES." minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('SplitMate Password Reset');
                }
            );
        } catch (\Throwable $e) {
            // Allow local/dev flows even when mail is not configured.
        }

        $response = [
            'message' => 'If the account exists, a password reset code has been sent.',
        ];

        if (app()->environment('local')) {
            $response['debug_code'] = $code;
        }

        return response()->json($response);
    }

    /**
     * Reset password using one-time code.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or reset code.'],
            ]);
        }

        $record = PasswordResetCode::where('user_id', $user->id)
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired reset code.'],
            ]);
        }

        $record->update([
            'used_at' => now(),
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please sign in again.',
        ]);
    }
}
