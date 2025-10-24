<?php

namespace App\Mail;

use App\Models\UserAuthCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly UserAuthCode $otp)
    {
    }

    public function build(): self
    {
        $appName = config('app.name');

        return $this->subject(sprintf('%s verification code', $appName))
            ->view('emails.otp_code')
            ->with([
                'code' => $this->otp->code,
                'channel' => $this->otp->channel,
                'identifier' => $this->otp->identifier,
                'expiresAt' => $this->otp->expires_at,
                'context' => $this->otp->payload['context'] ?? null,
                'appName' => $appName,
            ]);
    }
}
