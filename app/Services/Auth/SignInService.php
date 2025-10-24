<?php

namespace App\Services\Auth;

use App\Helpers\AuthHelper;
use App\Mail\OtpCodeMail;
use App\Models\User;
use App\Models\UserAuthCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SignInService
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    public function startPhone(array $input, string $role): array
    {
        $phone = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_PHONE, $input['phone']);
        $gender = AuthHelper::normalizeGender($input['gender'] ?? null);
        $firstName = trim((string) $input['fname']);
        $lastName = trim((string) $input['lname']);
        $normalizedRole = $this->normalizeRole($role);

        return DB::transaction(function () use ($phone, $gender, $firstName, $lastName, $normalizedRole) {
            $user = User::firstOrNew(['phone' => $phone]);
            $existed = $user->exists;
            $hadRole = false;

            if ($existed) {
                $hadRole = $user->roles()
                    ->where('name', ucfirst($normalizedRole))
                    ->exists();
            }

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
                    'role' => $normalizedRole,
                ],
                $user
            );

            $user->load('roles');

            return [$user, $otp, [
                'is_existing_user' => $existed,
                'has_role' => $hadRole,
                'role' => $normalizedRole,
            ]];
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

        $this->assignUserRole($user, $otp->payload['role'] ?? null);

        $user->save();

        $user->loadMissing('roles');

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }

    public function startEmail(array $input, string $role): array
    {
        $email = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_EMAIL, $input['email']);
        $gender = AuthHelper::normalizeGender($input['gender'] ?? null);
        $firstName = trim((string) $input['fname']);
        $lastName = trim((string) $input['lname']);
        $normalizedRole = $this->normalizeRole($role);

        [$user, $otp, $meta] = DB::transaction(function () use ($email, $gender, $firstName, $lastName, $normalizedRole) {
            $user = User::firstOrNew(['email' => $email]);
            $existed = $user->exists;
            $hadRole = false;

            if ($existed) {
                $hadRole = $user->roles()
                    ->where('name', ucfirst($normalizedRole))
                    ->exists();
            }

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
                    'role' => $normalizedRole,
                ],
                $user
            );

            $user->load('roles');

            return [$user, $otp, [
                'is_existing_user' => $existed,
                'has_role' => $hadRole,
                'role' => $normalizedRole,
            ]];
        });

        $this->sendEmailOtp($user, $otp);

        return [$user, $otp, $meta];
    }

    public function resendEmail(string $email): UserAuthCode
    {
        $normalizedEmail = $this->otpService->normalizeIdentifier(OtpService::CHANNEL_EMAIL, $email);

        $user = User::where('email', $normalizedEmail)->first();
        $latestOtp = UserAuthCode::where('channel', OtpService::CHANNEL_EMAIL)
            ->where('identifier', $normalizedEmail)
            ->orderByDesc('id')
            ->first();
        $roleFromUser = $user?->roles()->pluck('name')->first();
        $resolvedRole = $this->normalizeRole($latestOtp?->payload['role'] ?? $roleFromUser);

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

        $otp = $this->otpService->issue(
            OtpService::CHANNEL_EMAIL,
            $normalizedEmail,
            [
                'context' => 'email_resend',
                'requested_at' => now()->toIso8601String(),
                'role' => $resolvedRole,
            ],
            $user
        );

        $this->sendEmailOtp($user, $otp);

        return $otp;
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

        $this->assignUserRole($user, $otp->payload['role'] ?? null);

        $user->save();

        $user->loadMissing('roles');

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }

    private function sendEmailOtp(User $user, UserAuthCode $otp): void
    {
        if (!$user->email) {
            return;
        }

        try {
            Mail::to($user->email)->send(new OtpCodeMail($otp));
        } catch (\Throwable $exception) {
            Log::error('Failed to send OTP email.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_id' => $otp->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeRole(?string $role): string
    {
        $allowed = ['admin', 'user', 'vendor'];

        $normalized = strtolower(trim((string) $role));

        return in_array($normalized, $allowed, true) ? $normalized : 'user';
    }

    private function assignUserRole(User $user, ?string $role): void
    {
        $normalized = $this->normalizeRole($role);

        switch ($normalized) {
            case 'vendor':
                $user->is_vendor = 2;
                break;
            default:
                if ($user->is_vendor === 2) {
                    $user->is_vendor = 0;
                }
        }

        $user->syncRoles([$normalized]);
    }
}
