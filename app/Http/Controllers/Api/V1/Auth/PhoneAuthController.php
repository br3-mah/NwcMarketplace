<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\PhoneSignInRequest;
use App\Http\Requests\Api\V1\Auth\PhoneVerifyRequest;
use App\Services\Auth\OtpService;
use App\Services\Auth\SignInService;
use Illuminate\Http\JsonResponse;

class PhoneAuthController extends Controller
{
    public function __construct(private readonly SignInService $signInService)
    {
    }

    public function signIn(PhoneSignInRequest $request, string $role): JsonResponse
    {
        $role = strtolower($role);

        [$user, $otp] = $this->signInService->startPhone($request->validated());

        $response = [
            'message' => sprintf(
                'OTP sent to %s',
                AuthHelper::maskedIdentifier(OtpService::CHANNEL_PHONE, $otp->identifier)
            ),
            'data' => [
                'user_id' => $user->id,
                'channel' => OtpService::CHANNEL_PHONE,
                'role' => $role,
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

    public function verify(PhoneVerifyRequest $request): JsonResponse
    {
        [$user, $token] = $this->signInService->verifyPhone($request->input('phone'), $request->input('otp'));

        return response()->json([
            'message' => 'Phone number verified successfully.',
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
        ];
    }
}
