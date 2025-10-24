<p>Hello,</p>

<p>
    Use the verification code below to complete your
    {{ $context ? str_replace('_', ' ', $context) : 'request' }} on {{ $appName ?? config('app.name') }}.
</p>

<p style="font-size: 24px; font-weight: bold; letter-spacing: 4px;">
    {{ $code }}
</p>

@isset($expiresAt)
    <p>This code expires at {{ $expiresAt->toDayDateTimeString() }}.</p>
@endisset

<p>If you did not request this code, you can safely ignore this email.</p>

<p>Thanks,<br>{{ $appName ?? config('app.name') }}</p>
