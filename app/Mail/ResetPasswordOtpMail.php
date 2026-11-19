<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
        $this->subject = 'Kode verifikasi Smart RW';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Kode verifikasi Smart RW')
            ->view('emails.otp_html')
            ->text('emails.otp_text')
            ->with([
                'otp' => $this->otp,
            ]);
    }
}
