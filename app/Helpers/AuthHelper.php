<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AuthHelper
{
    public static function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        return strtolower(trim($email));
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        $phone = str_replace(['(', ')', '-', ' '], '', $phone);

        if (Str::startsWith($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        if (!Str::startsWith($phone, '+')) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        } else {
            $phone = '+' . preg_replace('/[^0-9]/', '', substr($phone, 1));
        }

        return $phone;
    }

    public static function normalizeGender(?string $gender): ?string
    {
        if ($gender === null) {
            return null;
        }

        $gender = strtoupper(trim($gender));

        return match ($gender) {
            'M', 'MALE' => 'M',
            'F', 'FEMALE' => 'F',
            'O', 'OTHER', 'X', 'NON-BINARY', 'NONBINARY' => 'O',
            default => null,
        };
    }

    public static function fullName(?string $firstName, ?string $lastName): string
    {
        $firstName = $firstName ? trim($firstName) : '';
        $lastName = $lastName ? trim($lastName) : '';

        return trim($firstName . ' ' . $lastName) ?: trim($firstName) ?: trim($lastName) ?: '';
    }

    public static function maskedIdentifier(string $channel, string $identifier): string
    {
        if ($channel === 'email') {
            [$user, $domain] = explode('@', $identifier);
            $user = Str::substr($user, 0, 2) . '***';
            return $user . '@' . $domain;
        }

        if ($channel === 'phone') {
            $identifier = preg_replace('/\D/', '', $identifier);
            $lastFour = Str::substr($identifier, -4);
            return '****' . $lastFour;
        }

        return $identifier;
    }

    public static function placeholderEmailForPhone(string $phone): string
    {
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        return sprintf('phone%s@no-email.local', $normalized);
    }
}

