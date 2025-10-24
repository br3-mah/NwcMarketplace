<?php

namespace App\Services\Auth;

use App\Helpers\AuthHelper;
use App\Models\User;
use App\Models\UserAuthCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const CHANNEL_PHONE = 'phone';
    public const CHANNEL_EMAIL = 'email';

    private const DEFAULT_CODE_LENGTH = 6;
    private const DEFAULT_EXPIRY_MINUTES = 10;
    private const DEFAULT_COOLDOWN_SECONDS = 5;
    private const DEFAULT_MAX_ATTEMPTS = 5;

    public function issue(
        string $channel,
        string $identifier,
        array $payload = [],
        ?User $user = null,
        ?int $codeLength = null,
        ?int $ttlMinutes = null,
        ?int $cooldownSeconds = null
    ): UserAuthCode {
        $codeLength ??= self::DEFAULT_CODE_LENGTH;
        $ttlMinutes ??= self::DEFAULT_EXPIRY_MINUTES;
        $cooldownSeconds ??= self::DEFAULT_COOLDOWN_SECONDS;

        $identifier = $this->normalizeIdentifier($channel, $identifier);

        $latestCode = UserAuthCode::where('channel', $channel)
            ->where('identifier', $identifier)
            ->latest()
            ->first();

        if ($latestCode && !$latestCode->is_verified && $latestCode->created_at) {
            $secondsSinceLast = $latestCode->created_at->diffInSeconds(now());
            if ($secondsSinceLast < $cooldownSeconds) {
                throw ValidationException::withMessages([
                    'identifier' => sprintf(
                        'OTP already sent. Please wait %d seconds before requesting a new code.',
                        $cooldownSeconds - $secondsSinceLast
                    ),
                ]);
            }
        }

        $code = $this->generateCode($codeLength);

        $expiresAt = Carbon::now()->addMinutes($ttlMinutes);

        $record = DB::transaction(function () use ($channel, $identifier, $code, $expiresAt, $user, $payload) {
            return tap(UserAuthCode::create([
                'channel' => $channel,
                'identifier' => $identifier,
                'code' => $code,
                'expires_at' => $expiresAt,
                'user_id' => $user?->id,
                'payload' => $payload,
                'attempts' => 0,
                'max_attempts' => $payload['max_attempts'] ?? self::DEFAULT_MAX_ATTEMPTS,
            ]), function (UserAuthCode $authCode) use ($channel) {
                $this->logIssuedCode($authCode, $channel);
            });
        });

        return $record;
    }

    public function verify(string $channel, string $identifier, string $code): UserAuthCode
    {
        $identifier = $this->normalizeIdentifier($channel, $identifier);
        $providedCode = trim((string) $code);

        /** @var UserAuthCode|null $latestCode */
        $latestCode = UserAuthCode::where('channel', $channel)
            ->where('identifier', $identifier)
            ->orderByDesc('id')
            ->first();

        if (!$latestCode) {
            throw ValidationException::withMessages([
                'Otp' => 'Invalid or expired verification code.',
            ]);
        }

        if ($providedCode === '') {
            throw ValidationException::withMessages([
                'Otp' => 'Verification code is required.',
            ]);
        }

        if ($latestCode->is_verified) {
            if (hash_equals($latestCode->code, $providedCode)) {
                return $latestCode;
            }

            throw ValidationException::withMessages([
                'Otp' => 'Invalid or expired verification code.',
            ]);
        }

        if ($latestCode->expires_at && $latestCode->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'Otp' => 'Verification code has expired. Please request a new one.',
            ]);
        }

        $maxAttempts = $latestCode->max_attempts ?? self::DEFAULT_MAX_ATTEMPTS;

        if ($latestCode->attempts >= $maxAttempts) {
            throw ValidationException::withMessages([
                'Otp' => 'Too many incorrect attempts. Please request a new verification code.',
            ]);
        }

        if (!hash_equals($latestCode->code, $providedCode)) {
            $latestCode->increment('attempts');

            throw ValidationException::withMessages([
                'Otp' => 'Incorrect verification code.',
            ]);
        }

        $latestCode->forceFill([
            'is_verified' => true,
        ])->save();

        return $latestCode;
    }

    public function normalizeIdentifier(string $channel, string $identifier): string
    {
        return match ($channel) {
            self::CHANNEL_EMAIL => (string) AuthHelper::normalizeEmail($identifier),
            self::CHANNEL_PHONE => (string) AuthHelper::normalizePhone($identifier),
            default => $identifier,
        };
    }

    private function generateCode(int $length): string
    {
        $length = max(4, $length);

        $rangeStart = 10 ** ($length - 1);
        $rangeEnd = (10 ** $length) - 1;

        return (string) random_int($rangeStart, $rangeEnd);
    }

    private function logIssuedCode(UserAuthCode $authCode, string $channel): void
    {
        $mask = AuthHelper::maskedIdentifier($channel, $authCode->identifier);

        $context = [
            'channel' => $channel,
            'identifier' => $mask,
            'expires_at' => $authCode->expires_at?->toIso8601String(),
            'code_id' => $authCode->id,
        ];

        if (config('app.debug')) {
            $context['debug_code'] = $authCode->code;
        }

        Log::info('OTP issued', $context);
    }
}
