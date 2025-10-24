<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\EmailResendRequest;
use App\Http\Requests\Api\V1\Auth\EmailSignInRequest;
use App\Http\Requests\Api\V1\Auth\EmailVerifyRequest;
use App\Services\Auth\OtpService;
use App\Services\Auth\SignInService;
use Illuminate\Http\JsonResponse;

class EmailAuthController extends Controller
{
    public function __construct(private readonly SignInService $signInService)
    {
    }

    public function signIn(EmailSignInRequest $request, string $role): JsonResponse
    {
        $role = strtolower($role);

        [$user, $otp, $meta] = $this->signInService->startEmail($request->validated(), $role);

        $message = $meta['has_role']
            ? sprintf(
                'Welcome back! OTP sent to %s',
                AuthHelper::maskedIdentifier(OtpService::CHANNEL_EMAIL, $otp->identifier)
            )
            : sprintf(
                'OTP sent to %s',
                AuthHelper::maskedIdentifier(OtpService::CHANNEL_EMAIL, $otp->identifier)
            );

        $response = [
            'message' => $message,
            'data' => [
                'user_id' => $user->id,
                'channel' => OtpService::CHANNEL_EMAIL,
                'role' => $role,
                'identifier' => $otp->identifier,
                'expires_at' => $otp->expires_at?->toIso8601String(),
                'is_existing_user' => $meta['is_existing_user'],
                'has_role' => $meta['has_role'],
            ],
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'otp' => $otp->code,
            ];
        }

        return response()->json($response);
    }

    public function resend(EmailResendRequest $request): JsonResponse
    {
        $otp = $this->signInService->resendEmail($request->input('email'));

        $response = [
            'message' => sprintf(
                'OTP resent to %s',
                AuthHelper::maskedIdentifier(OtpService::CHANNEL_EMAIL, $otp->identifier)
            ),
            'data' => [
                'channel' => OtpService::CHANNEL_EMAIL,
                'identifier' => $otp->identifier,
                'expires_at' => $otp->expires_at?->toIso8601String(),
            ],
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'otp' => $otp->code,
            ];
        }

        return response()->json($response);
    }

    public function verify(EmailVerifyRequest $request): JsonResponse
    {
        [$user, $token] = $this->signInService->verifyEmail($request->input('email'), $request->input('otp'));

        return response()->json([
            'message' => 'Email verified successfully.',
            'data' => $this->transformUser($user),
            'token' => [
                'token_type' => 'Bearer',
                'access_token' => $token,
            ],
        ]);
    }

    private function transformUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'fname' => $user->fname,
            'lname' => $user->lname,
            'gender' => $user->gender,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_verified_at' => $user->phone_verified_at?->toIso8601String(),
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'roles' => $user->roles->pluck('name')->map(fn ($name) => strtolower($name))->values()->all(),
        ];
    }
}
