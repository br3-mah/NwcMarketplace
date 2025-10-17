<?php

namespace App\Services\Auth;

use App\Helpers\AuthHelper;
use App\Models\User;
use App\Models\UserAuthCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SignInService
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    public function startPhone(array $input): array
    {
        $phone = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_PHONE, $input['phone']);
        $gender = AuthHelper::normalizeGender($input['gender'] ?? null);
        $firstName = trim((string) $input['fname']);
        $lastName = trim((string) $input['lname']);

        return DB::transaction(function () use ($phone, $gender, $firstName, $lastName) {
            $user = User::firstOrNew(['phone' => $phone]);

            $user->fname = $firstName;
            $user->lname = $lastName;
            $user->gender = $gender;
            $user->name = AuthHelper::fullName($firstName, $lastName);
            $user->phone = $phone;

            if (!$user->exists) {
                $user->email = AuthHelper::placeholderEmailForPhone($phone);
                $user->phone_verified = 'No';
                $user->phone_verified_at = null;
                $user->status = 0;
                $user->password = bcrypt('Newme25');
            } else {
                $user->status = $user->status ?? 0;
                $user->phone_verified = $user->phone_verified ?? 'No';
                if (!$user->password) {
                    $user->password = bcrypt('Newme25');
                }
            }

            $user->save();

            $otp = $this->otpService->issue(
                OtpService::CHANNEL_PHONE,
                $phone,
                [
                    'context' => 'phone_signin',
                    'requested_at' => now()->toIso8601String(),
                ],
                $user
            );

            return [$user, $otp];
        });
    }

    public function verifyPhone(string $phone, string $otpCode): array
    {
        $otp = $this->otpService->verify(OtpService::CHANNEL_PHONE, $phone, $otpCode);

        $user = $otp->user ?? User::where('phone', $otp->identifier)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => 'Account not found for the provided phone number.',
            ]);
        }

        $user->phone_verified_at = Carbon::now();
        $user->phone_verified = 'Yes';
        $user->status = 1;
        $user->save();

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }

    public function startEmail(array $input): array
    {
        $email = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_EMAIL, $input['email']);
        $gender = AuthHelper::normalizeGender($input['gender'] ?? null);
        $firstName = trim((string) $input['fname']);
        $lastName = trim((string) $input['lname']);

        return DB::transaction(function () use ($email, $gender, $firstName, $lastName) {
            $user = User::firstOrNew(['email' => $email]);

            $user->fname = $firstName;
            $user->lname = $lastName;
            $user->gender = $gender;
            $user->name = AuthHelper::fullName($firstName, $lastName);
            $user->email = $email;
            $user->email_verified = 'No';
            $user->email_verified_at = null;
            $user->status = $user->status ?? 0;
            if (!$user->password) {
                $user->password = bcrypt('Newme25');
            }

            $user->save();

            $otp = $this->otpService->issue(
                OtpService::CHANNEL_EMAIL,
                $email,
                [
                    'context' => 'email_signin',
                    'requested_at' => now()->toIso8601String(),
                ],
                $user
            );

            return [$user, $otp];
        });
    }

    public function resendEmail(string $email): UserAuthCode
    {
        $normalizedEmail = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_EMAIL, $email);

        $user = User::where('email', $normalizedEmail)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Account not found for the provided email address.',
            ]);
        }

        if ($user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => 'Email address is already verified.',
            ]);
        }

        return $this->otpService->issue(
            OtpService::CHANNEL_EMAIL,
            $normalizedEmail,
            [
                'context' => 'email_resend',
                'requested_at' => now()->toIso8601String(),
            ],
            $user
        );
    }

    public function verifyEmail(string $email, string $otpCode): array
    {
        $otp = $this->otpService->verify(OtpService::CHANNEL_EMAIL, $email, $otpCode);

        $user = $otp->user ?? User::where('email', $otp->identifier)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Account not found for the provided email address.',
            ]);
        }

        $user->email_verified = 'Yes';
        $user->email_verified_at = Carbon::now();
        $user->status = 1;
        $user->save();

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }
}
